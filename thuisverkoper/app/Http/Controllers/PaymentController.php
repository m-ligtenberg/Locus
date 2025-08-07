<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Stripe\Webhook;
use Stripe\Exception\SignatureVerificationException;

class PaymentController extends Controller
{
    protected PaymentService $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
        
        // Disable CSRF protection for webhooks
        $this->middleware('throttle:60,1')->only(['stripeWebhook', 'mollieWebhook']);
    }

    /**
     * Handle Stripe webhook events.
     */
    public function stripeWebhook(Request $request): JsonResponse
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $endpointSecret = config('services.stripe.webhook_secret');

        try {
            // Verify webhook signature
            $event = Webhook::constructEvent($payload, $sigHeader, $endpointSecret);
        } catch (\UnexpectedValueException $e) {
            Log::warning('Stripe webhook: Invalid payload', ['error' => $e->getMessage()]);
            return $this->webhookResponse('Invalid payload', 400);
        } catch (SignatureVerificationException $e) {
            Log::warning('Stripe webhook: Invalid signature', ['error' => $e->getMessage()]);
            return $this->webhookResponse('Invalid signature', 400);
        }

        Log::info('Stripe webhook received', [
            'type' => $event->type,
            'id' => $event->id,
        ]);

        try {
            switch ($event->type) {
                case 'checkout.session.completed':
                    $this->handleStripeCheckoutCompleted($event->data->object);
                    break;

                case 'payment_intent.succeeded':
                    $this->handleStripePaymentSucceeded($event->data->object);
                    break;

                case 'payment_intent.payment_failed':
                    $this->handleStripePaymentFailed($event->data->object);
                    break;

                case 'charge.dispute.created':
                    $this->handleStripeDisputeCreated($event->data->object);
                    break;

                case 'invoice.payment_succeeded':
                    $this->handleStripeInvoicePaymentSucceeded($event->data->object);
                    break;

                case 'invoice.payment_failed':
                    $this->handleStripeInvoicePaymentFailed($event->data->object);
                    break;

                default:
                    Log::info('Stripe webhook: Unhandled event type', ['type' => $event->type]);
                    break;
            }

            return $this->webhookResponse('Webhook processed successfully');

        } catch (\Exception $e) {
            Log::error('Stripe webhook processing failed', [
                'event_type' => $event->type,
                'event_id' => $event->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return $this->webhookResponse('Webhook processing failed', 500);
        }
    }

    /**
     * Handle Mollie webhook events.
     */
    public function mollieWebhook(Request $request): JsonResponse
    {
        $paymentId = $request->input('id');
        
        if (!$paymentId) {
            Log::warning('Mollie webhook: Missing payment ID');
            return $this->webhookResponse('Missing payment ID', 400);
        }

        Log::info('Mollie webhook received', ['payment_id' => $paymentId]);

        try {
            $mollie = app('mollie');
            $payment = $mollie->payments->get($paymentId);

            $order = Order::where('payment_id', $paymentId)->first();

            if (!$order) {
                Log::warning('Mollie webhook: Order not found', ['payment_id' => $paymentId]);
                return $this->webhookResponse('Order not found', 404);
            }

            switch ($payment->status) {
                case 'paid':
                    $this->handleMolliePaymentPaid($order, $payment);
                    break;

                case 'failed':
                case 'canceled':
                case 'expired':
                    $this->handleMolliePaymentFailed($order, $payment);
                    break;

                case 'refunded':
                case 'partially_refunded':
                    $this->handleMolliePaymentRefunded($order, $payment);
                    break;

                case 'charged_back':
                    $this->handleMollieChargeBack($order, $payment);
                    break;

                default:
                    Log::info('Mollie webhook: Unhandled payment status', [
                        'payment_id' => $paymentId,
                        'status' => $payment->status
                    ]);
                    break;
            }

            return $this->webhookResponse('Webhook processed successfully');

        } catch (\Exception $e) {
            Log::error('Mollie webhook processing failed', [
                'payment_id' => $paymentId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return $this->webhookResponse('Webhook processing failed', 500);
        }
    }

    /**
     * Handle Stripe checkout session completed.
     */
    protected function handleStripeCheckoutCompleted($session): void
    {
        $order = Order::where('payment_id', $session->id)->first();

        if (!$order) {
            Log::warning('Stripe checkout completed: Order not found', [
                'session_id' => $session->id
            ]);
            return;
        }

        if ($order->status === 'paid') {
            Log::info('Stripe checkout completed: Order already marked as paid', [
                'order_id' => $order->id
            ]);
            return;
        }

        $this->updateOrderAsPaid($order, [
            'payment_status' => 'completed',
            'payment_details' => [
                'stripe_session_id' => $session->id,
                'stripe_payment_intent_id' => $session->payment_intent,
                'amount_total' => $session->amount_total,
                'currency' => $session->currency,
            ]
        ]);

        Log::info('Stripe checkout completed successfully', [
            'order_id' => $order->id,
            'session_id' => $session->id
        ]);
    }

    /**
     * Handle Stripe payment succeeded.
     */
    protected function handleStripePaymentSucceeded($paymentIntent): void
    {
        $order = Order::where('payment_id', $paymentIntent->id)->first();

        if (!$order) {
            // Try to find by session ID if available
            if (isset($paymentIntent->metadata->order_id)) {
                $order = Order::find($paymentIntent->metadata->order_id);
            }
        }

        if (!$order) {
            Log::warning('Stripe payment succeeded: Order not found', [
                'payment_intent_id' => $paymentIntent->id
            ]);
            return;
        }

        if ($order->status === 'paid') {
            return;
        }

        $this->updateOrderAsPaid($order, [
            'payment_status' => 'succeeded',
            'payment_details' => [
                'stripe_payment_intent_id' => $paymentIntent->id,
                'amount_received' => $paymentIntent->amount_received,
                'currency' => $paymentIntent->currency,
            ]
        ]);

        Log::info('Stripe payment succeeded', [
            'order_id' => $order->id,
            'payment_intent_id' => $paymentIntent->id
        ]);
    }

    /**
     * Handle Stripe payment failed.
     */
    protected function handleStripePaymentFailed($paymentIntent): void
    {
        $order = Order::where('payment_id', $paymentIntent->id)->first();

        if (!$order) {
            Log::warning('Stripe payment failed: Order not found', [
                'payment_intent_id' => $paymentIntent->id
            ]);
            return;
        }

        $order->update([
            'status' => 'cancelled',
            'notes' => 'Payment failed: ' . ($paymentIntent->last_payment_error->message ?? 'Unknown error')
        ]);

        // Send failure notification email
        $this->sendPaymentFailureNotification($order, $paymentIntent->last_payment_error->message ?? 'Unknown error');

        Log::info('Stripe payment failed', [
            'order_id' => $order->id,
            'payment_intent_id' => $paymentIntent->id,
            'error' => $paymentIntent->last_payment_error->message ?? 'Unknown error'
        ]);
    }

    /**
     * Handle Stripe dispute created.
     */
    protected function handleStripeDisputeCreated($dispute): void
    {
        $chargeId = $dispute->charge;
        
        // Find order by charge ID or payment intent
        // This might require additional metadata tracking
        Log::warning('Stripe dispute created', [
            'dispute_id' => $dispute->id,
            'charge_id' => $chargeId,
            'amount' => $dispute->amount,
            'reason' => $dispute->reason
        ]);

        // TODO: Implement dispute handling logic
        // - Find related order
        // - Update order status
        // - Send notification to admin/user
    }

    /**
     * Handle Mollie payment paid.
     */
    protected function handleMolliePaymentPaid(Order $order, $payment): void
    {
        if ($order->status === 'paid') {
            return;
        }

        $this->updateOrderAsPaid($order, [
            'payment_status' => 'paid',
            'payment_details' => [
                'mollie_payment_id' => $payment->id,
                'amount_value' => $payment->amount->value,
                'amount_currency' => $payment->amount->currency,
                'method' => $payment->method,
            ]
        ]);

        Log::info('Mollie payment paid', [
            'order_id' => $order->id,
            'payment_id' => $payment->id
        ]);
    }

    /**
     * Handle Mollie payment failed.
     */
    protected function handleMolliePaymentFailed(Order $order, $payment): void
    {
        $order->update([
            'status' => 'cancelled',
            'notes' => 'Payment ' . $payment->status . ': ' . ($payment->description ?? '')
        ]);

        $this->sendPaymentFailureNotification($order, 'Payment ' . $payment->status);

        Log::info('Mollie payment failed', [
            'order_id' => $order->id,
            'payment_id' => $payment->id,
            'status' => $payment->status
        ]);
    }

    /**
     * Handle Mollie payment refunded.
     */
    protected function handleMolliePaymentRefunded(Order $order, $payment): void
    {
        $order->update(['status' => 'refunded']);

        // Send refund confirmation email
        $this->sendRefundNotification($order, $payment->amount->value);

        Log::info('Mollie payment refunded', [
            'order_id' => $order->id,
            'payment_id' => $payment->id,
            'status' => $payment->status
        ]);
    }

    /**
     * Handle Mollie chargeback.
     */
    protected function handleMollieChargeBack(Order $order, $payment): void
    {
        $order->update([
            'status' => 'cancelled',
            'notes' => 'Chargeback received for payment'
        ]);

        Log::warning('Mollie chargeback received', [
            'order_id' => $order->id,
            'payment_id' => $payment->id
        ]);

        // TODO: Send chargeback notification to admin
    }

    /**
     * Handle Stripe invoice payment succeeded.
     */
    protected function handleStripeInvoicePaymentSucceeded($invoice): void
    {
        // This would be used for subscription-based services
        Log::info('Stripe invoice payment succeeded', [
            'invoice_id' => $invoice->id,
            'subscription_id' => $invoice->subscription ?? null
        ]);
    }

    /**
     * Handle Stripe invoice payment failed.
     */
    protected function handleStripeInvoicePaymentFailed($invoice): void
    {
        // This would be used for subscription-based services
        Log::warning('Stripe invoice payment failed', [
            'invoice_id' => $invoice->id,
            'subscription_id' => $invoice->subscription ?? null
        ]);
    }

    /**
     * Update order as paid and send confirmation.
     */
    protected function updateOrderAsPaid(Order $order, array $paymentDetails): void
    {
        $order->update([
            'status' => 'paid',
            'notes' => 'Payment completed successfully'
        ]);

        // Send order confirmation email
        $this->sendOrderConfirmation($order);

        // Log payment success
        Log::info('Order marked as paid', array_merge([
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'amount' => $order->total_amount
        ], $paymentDetails));
    }

    /**
     * Send order confirmation email.
     */
    protected function sendOrderConfirmation(Order $order): void
    {
        try {
            // TODO: Implement email notification
            // Mail::to($order->user->email)->send(new OrderConfirmationMail($order));
            
            Log::info('Order confirmation email queued', [
                'order_id' => $order->id,
                'user_email' => $order->user->email
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send order confirmation email', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Send payment failure notification.
     */
    protected function sendPaymentFailureNotification(Order $order, string $reason): void
    {
        try {
            // TODO: Implement email notification
            // Mail::to($order->user->email)->send(new PaymentFailureMail($order, $reason));
            
            Log::info('Payment failure notification queued', [
                'order_id' => $order->id,
                'user_email' => $order->user->email,
                'reason' => $reason
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send payment failure notification', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Send refund notification.
     */
    protected function sendRefundNotification(Order $order, string $amount): void
    {
        try {
            // TODO: Implement email notification
            // Mail::to($order->user->email)->send(new RefundNotificationMail($order, $amount));
            
            Log::info('Refund notification queued', [
                'order_id' => $order->id,
                'user_email' => $order->user->email,
                'amount' => $amount
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send refund notification', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Standard webhook response.
     */
    protected function webhookResponse(string $message, int $status = 200): JsonResponse
    {
        return response()->json([
            'message' => $message,
            'timestamp' => now()->toISOString()
        ], $status);
    }
}
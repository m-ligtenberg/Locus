<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;
use Stripe\StripeClient;
use Mollie\Api\MollieApiClient;

class PaymentService
{
    protected StripeClient $stripe;
    protected MollieApiClient $mollie;

    public function __construct()
    {
        // Initialize Stripe
        $this->stripe = new StripeClient(config('services.stripe.secret'));

        // Initialize Mollie
        $this->mollie = new MollieApiClient();
        $this->mollie->setApiKey(config('services.mollie.key'));
    }

    /**
     * Create payment session for the given provider.
     */
    public function createPaymentSession(string $provider, array $data): array
    {
        try {
            switch ($provider) {
                case 'stripe':
                    return $this->createStripeSession($data);
                case 'mollie':
                    return $this->createMollieSession($data);
                default:
                    return $this->errorResponse('Unsupported payment provider');
            }
        } catch (\Exception $e) {
            Log::error('Payment session creation failed', [
                'provider' => $provider,
                'error' => $e->getMessage(),
                'data' => $data
            ]);

            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Create Stripe checkout session.
     */
    protected function createStripeSession(array $data): array
    {
        try {
            $sessionData = [
                'payment_method_types' => ['card', 'ideal', 'bancontact'],
                'line_items' => [
                    [
                        'price_data' => [
                            'currency' => $data['currency'],
                            'product_data' => [
                                'name' => $data['description'],
                            ],
                            'unit_amount' => intval($data['amount'] * 100), // Convert to cents
                        ],
                        'quantity' => 1,
                    ],
                ],
                'mode' => 'payment',
                'success_url' => $data['success_url'],
                'cancel_url' => $data['cancel_url'],
                'metadata' => [
                    'order_id' => $data['order_id'],
                ],
                'customer_email' => $data['billing_details']['email'] ?? null,
                'billing_address_collection' => 'required',
                'shipping_address_collection' => [
                    'allowed_countries' => ['NL', 'BE', 'DE'],
                ],
                'payment_intent_data' => [
                    'metadata' => [
                        'order_id' => $data['order_id'],
                    ],
                ],
            ];

            // Add billing details if provided
            if (!empty($data['billing_details'])) {
                $sessionData['customer_email'] = $data['billing_details']['email'];
                
                if (!empty($data['billing_details']['name'])) {
                    $sessionData['customer_creation'] = 'always';
                    $sessionData['customer_email'] = $data['billing_details']['email'];
                }
            }

            $session = $this->stripe->checkout->sessions->create($sessionData);

            return [
                'success' => true,
                'payment_id' => $session->id,
                'checkout_url' => $session->url,
                'session_id' => $session->id,
            ];

        } catch (\Stripe\Exception\ApiErrorException $e) {
            Log::error('Stripe session creation failed', [
                'error' => $e->getMessage(),
                'type' => get_class($e),
                'data' => $data
            ]);

            return $this->errorResponse('Failed to create Stripe checkout session: ' . $e->getMessage());
        }
    }

    /**
     * Create Mollie payment session.
     */
    protected function createMollieSession(array $data): array
    {
        try {
            $paymentData = [
                'amount' => [
                    'currency' => $data['currency'],
                    'value' => number_format($data['amount'], 2, '.', ''), // Mollie expects string format
                ],
                'description' => $data['description'],
                'redirectUrl' => $data['success_url'],
                'webhookUrl' => route('webhooks.mollie'),
                'metadata' => [
                    'order_id' => $data['order_id'],
                ],
                'method' => [
                    'ideal', 
                    'creditcard', 
                    'bancontact', 
                    'sofort', 
                    'eps', 
                    'giropay', 
                    'kbc', 
                    'belfius'
                ],
                'locale' => 'nl_NL',
            ];

            // Add billing details if provided
            if (!empty($data['billing_details'])) {
                $billingAddress = $data['billing_details']['address'] ?? [];
                
                $paymentData['billingAddress'] = [
                    'streetAndNumber' => ($billingAddress['line1'] ?? '') . ' ' . ($billingAddress['line2'] ?? ''),
                    'city' => $billingAddress['city'] ?? '',
                    'postalCode' => $billingAddress['postal_code'] ?? '',
                    'country' => $billingAddress['country'] ?? 'NL',
                ];

                if (!empty($data['billing_details']['email'])) {
                    $paymentData['customerEmail'] = $data['billing_details']['email'];
                }
            }

            $payment = $this->mollie->payments->create($paymentData);

            return [
                'success' => true,
                'payment_id' => $payment->id,
                'checkout_url' => $payment->getCheckoutUrl(),
            ];

        } catch (\Mollie\Api\Exceptions\ApiException $e) {
            Log::error('Mollie payment creation failed', [
                'error' => $e->getMessage(),
                'type' => get_class($e),
                'data' => $data
            ]);

            return $this->errorResponse('Failed to create Mollie payment: ' . $e->getMessage());
        }
    }

    /**
     * Get payment status for the given provider.
     */
    public function getPaymentStatus(string $provider, string $paymentId): array
    {
        try {
            switch ($provider) {
                case 'stripe':
                    return $this->getStripePaymentStatus($paymentId);
                case 'mollie':
                    return $this->getMolliePaymentStatus($paymentId);
                default:
                    return $this->errorResponse('Unsupported payment provider');
            }
        } catch (\Exception $e) {
            Log::error('Payment status check failed', [
                'provider' => $provider,
                'payment_id' => $paymentId,
                'error' => $e->getMessage()
            ]);

            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Get Stripe payment status.
     */
    protected function getStripePaymentStatus(string $sessionId): array
    {
        try {
            $session = $this->stripe->checkout->sessions->retrieve($sessionId);
            
            $isPaid = $session->payment_status === 'paid';
            $paymentIntent = null;
            
            if ($session->payment_intent) {
                $paymentIntent = $this->stripe->paymentIntents->retrieve($session->payment_intent);
                $isPaid = $paymentIntent->status === 'succeeded';
            }

            return [
                'success' => true,
                'is_paid' => $isPaid,
                'status' => $session->payment_status,
                'amount_total' => $session->amount_total,
                'payment_intent_status' => $paymentIntent ? $paymentIntent->status : null,
            ];

        } catch (\Stripe\Exception\ApiErrorException $e) {
            return $this->errorResponse('Failed to retrieve Stripe payment status: ' . $e->getMessage());
        }
    }

    /**
     * Get Mollie payment status.
     */
    protected function getMolliePaymentStatus(string $paymentId): array
    {
        try {
            $payment = $this->mollie->payments->get($paymentId);

            return [
                'success' => true,
                'is_paid' => $payment->isPaid(),
                'status' => $payment->status,
                'amount' => $payment->amount->value,
                'method' => $payment->method,
            ];

        } catch (\Mollie\Api\Exceptions\ApiException $e) {
            return $this->errorResponse('Failed to retrieve Mollie payment status: ' . $e->getMessage());
        }
    }

    /**
     * Cancel payment for the given provider.
     */
    public function cancelPayment(string $provider, string $paymentId): array
    {
        try {
            switch ($provider) {
                case 'stripe':
                    return $this->cancelStripePayment($paymentId);
                case 'mollie':
                    return $this->cancelMolliePayment($paymentId);
                default:
                    return $this->errorResponse('Unsupported payment provider');
            }
        } catch (\Exception $e) {
            Log::error('Payment cancellation failed', [
                'provider' => $provider,
                'payment_id' => $paymentId,
                'error' => $e->getMessage()
            ]);

            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Cancel Stripe payment.
     */
    protected function cancelStripePayment(string $sessionId): array
    {
        try {
            // For Stripe, we can't cancel a checkout session directly
            // We can only cancel the payment intent if it exists
            $session = $this->stripe->checkout->sessions->retrieve($sessionId);
            
            if ($session->payment_intent) {
                $paymentIntent = $this->stripe->paymentIntents->retrieve($session->payment_intent);
                
                if (in_array($paymentIntent->status, ['requires_payment_method', 'requires_confirmation', 'requires_action'])) {
                    $this->stripe->paymentIntents->cancel($session->payment_intent);
                    
                    return [
                        'success' => true,
                        'message' => 'Payment cancelled successfully'
                    ];
                }
            }

            return [
                'success' => false,
                'message' => 'Payment cannot be cancelled in its current state'
            ];

        } catch (\Stripe\Exception\ApiErrorException $e) {
            return $this->errorResponse('Failed to cancel Stripe payment: ' . $e->getMessage());
        }
    }

    /**
     * Cancel Mollie payment.
     */
    protected function cancelMolliePayment(string $paymentId): array
    {
        try {
            $payment = $this->mollie->payments->get($paymentId);
            
            if ($payment->isCancelable()) {
                $this->mollie->payments->delete($payment);
                
                return [
                    'success' => true,
                    'message' => 'Payment cancelled successfully'
                ];
            }

            return [
                'success' => false,
                'message' => 'Payment cannot be cancelled in its current state'
            ];

        } catch (\Mollie\Api\Exceptions\ApiException $e) {
            return $this->errorResponse('Failed to cancel Mollie payment: ' . $e->getMessage());
        }
    }

    /**
     * Process refund for the given provider.
     */
    public function processRefund(string $provider, string $paymentId, float $amount, string $reason): array
    {
        try {
            switch ($provider) {
                case 'stripe':
                    return $this->processStripeRefund($paymentId, $amount, $reason);
                case 'mollie':
                    return $this->processMollieRefund($paymentId, $amount, $reason);
                default:
                    return $this->errorResponse('Unsupported payment provider');
            }
        } catch (\Exception $e) {
            Log::error('Refund processing failed', [
                'provider' => $provider,
                'payment_id' => $paymentId,
                'amount' => $amount,
                'error' => $e->getMessage()
            ]);

            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Process Stripe refund.
     */
    protected function processStripeRefund(string $sessionId, float $amount, string $reason): array
    {
        try {
            $session = $this->stripe->checkout->sessions->retrieve($sessionId);
            
            if (!$session->payment_intent) {
                return $this->errorResponse('No payment intent found for refund');
            }

            $refund = $this->stripe->refunds->create([
                'payment_intent' => $session->payment_intent,
                'amount' => intval($amount * 100), // Convert to cents
                'reason' => 'requested_by_customer',
                'metadata' => [
                    'reason' => $reason,
                    'refunded_at' => now()->toISOString(),
                ],
            ]);

            return [
                'success' => true,
                'refund_id' => $refund->id,
                'status' => $refund->status,
                'amount' => $refund->amount / 100, // Convert back to euros
            ];

        } catch (\Stripe\Exception\ApiErrorException $e) {
            return $this->errorResponse('Failed to process Stripe refund: ' . $e->getMessage());
        }
    }

    /**
     * Process Mollie refund.
     */
    protected function processMollieRefund(string $paymentId, float $amount, string $reason): array
    {
        try {
            $payment = $this->mollie->payments->get($paymentId);
            
            $refundData = [
                'amount' => [
                    'currency' => 'EUR',
                    'value' => number_format($amount, 2, '.', ''),
                ],
                'description' => $reason,
            ];

            $refund = $payment->refund($refundData);

            return [
                'success' => true,
                'refund_id' => $refund->id,
                'status' => $refund->status,
                'amount' => floatval($refund->amount->value),
            ];

        } catch (\Mollie\Api\Exceptions\ApiException $e) {
            return $this->errorResponse('Failed to process Mollie refund: ' . $e->getMessage());
        }
    }

    /**
     * Get available payment methods for the given provider.
     */
    public function getAvailablePaymentMethods(string $provider, string $locale = 'nl_NL'): array
    {
        try {
            switch ($provider) {
                case 'stripe':
                    return $this->getStripePaymentMethods();
                case 'mollie':
                    return $this->getMolliePaymentMethods($locale);
                default:
                    return $this->errorResponse('Unsupported payment provider');
            }
        } catch (\Exception $e) {
            Log::error('Failed to get payment methods', [
                'provider' => $provider,
                'error' => $e->getMessage()
            ]);

            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Get Stripe payment methods.
     */
    protected function getStripePaymentMethods(): array
    {
        return [
            'success' => true,
            'methods' => [
                'card' => ['name' => 'Credit/Debit Card', 'type' => 'card'],
                'ideal' => ['name' => 'iDEAL', 'type' => 'redirect'],
                'bancontact' => ['name' => 'Bancontact', 'type' => 'redirect'],
                'sepa_debit' => ['name' => 'SEPA Direct Debit', 'type' => 'sepa_debit'],
            ]
        ];
    }

    /**
     * Get Mollie payment methods.
     */
    protected function getMolliePaymentMethods(string $locale): array
    {
        try {
            $methods = $this->mollie->methods->allActive(['locale' => $locale]);
            
            $formattedMethods = [];
            foreach ($methods as $method) {
                $formattedMethods[$method->id] = [
                    'name' => $method->description,
                    'type' => 'redirect',
                    'image' => $method->image->svg ?? null,
                ];
            }

            return [
                'success' => true,
                'methods' => $formattedMethods
            ];

        } catch (\Mollie\Api\Exceptions\ApiException $e) {
            return $this->errorResponse('Failed to get Mollie payment methods: ' . $e->getMessage());
        }
    }

    /**
     * Validate webhook signature for security.
     */
    public function validateWebhookSignature(string $provider, string $payload, string $signature): bool
    {
        try {
            switch ($provider) {
                case 'stripe':
                    return $this->validateStripeSignature($payload, $signature);
                case 'mollie':
                    // Mollie doesn't use signature validation, webhook URL should be secret
                    return true;
                default:
                    return false;
            }
        } catch (\Exception $e) {
            Log::error('Webhook signature validation failed', [
                'provider' => $provider,
                'error' => $e->getMessage()
            ]);
            
            return false;
        }
    }

    /**
     * Validate Stripe webhook signature.
     */
    protected function validateStripeSignature(string $payload, string $signature): bool
    {
        try {
            \Stripe\Webhook::constructEvent(
                $payload,
                $signature,
                config('services.stripe.webhook_secret')
            );
            
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Format error response.
     */
    protected function errorResponse(string $message): array
    {
        return [
            'success' => false,
            'message' => $message
        ];
    }
}
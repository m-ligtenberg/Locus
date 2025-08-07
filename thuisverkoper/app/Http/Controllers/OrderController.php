<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Service;
use App\Http\Requests\OrderRequest;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

class OrderController extends Controller
{
    protected PaymentService $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->middleware('auth');
        $this->paymentService = $paymentService;
    }

    /**
     * Display a listing of the user's orders.
     */
    public function index(Request $request): View
    {
        $query = auth()->user()->orders()->with(['items.service', 'property']);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        // Search by order number
        if ($request->filled('search')) {
            $query->where('order_number', 'like', '%' . $request->input('search') . '%');
        }

        // Date filtering
        if ($request->filled('date_from')) {
            $query->where('created_at', '>=', $request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->where('created_at', '<=', $request->input('date_to') . ' 23:59:59');
        }

        $orders = $query->orderBy('created_at', 'desc')
                       ->paginate(10)
                       ->appends($request->query());

        $statusCounts = auth()->user()->orders()
            ->selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        return view('orders.index', compact('orders', 'statusCounts'));
    }

    /**
     * Display the specified order.
     */
    public function show(Order $order): View
    {
        $this->authorize('view', $order);

        $order->load(['items.service', 'property', 'user']);

        return view('orders.show', compact('order'));
    }

    /**
     * Display the checkout page from cart.
     */
    public function checkout(Request $request): View|RedirectResponse
    {
        $cart = Session::get('cart', []);
        
        if (empty($cart)) {
            return redirect()->route('services.cart')
                ->with('error', 'Your cart is empty. Please add services before checkout.');
        }

        // Load services from cart
        $serviceIds = array_keys($cart);
        $services = Service::whereIn('id', $serviceIds)->where('is_active', true)->get();
        
        // Verify all cart services are still available
        $unavailableServices = [];
        foreach ($cart as $serviceId => $quantity) {
            if (!$services->where('id', $serviceId)->first()) {
                $unavailableServices[] = $serviceId;
            }
        }

        if (!empty($unavailableServices)) {
            // Remove unavailable services from cart
            foreach ($unavailableServices as $serviceId) {
                unset($cart[$serviceId]);
            }
            Session::put('cart', $cart);
            
            if (empty($cart)) {
                return redirect()->route('services.cart')
                    ->with('error', 'Some services in your cart are no longer available.');
            }
        }

        // Calculate totals
        $subtotal = 0;
        $orderItems = [];
        
        foreach ($services as $service) {
            $quantity = $cart[$service->id] ?? 1;
            $itemTotal = $service->price * $quantity;
            $subtotal += $itemTotal;
            
            $orderItems[] = [
                'service' => $service,
                'quantity' => $quantity,
                'price' => $service->price,
                'total' => $itemTotal
            ];
        }

        // Calculate tax (21% VAT for Netherlands)
        $taxRate = 0.21;
        $taxAmount = $subtotal * $taxRate;
        $totalAmount = $subtotal + $taxAmount;

        $user = auth()->user();
        $properties = $user->properties()->active()->get();

        return view('orders.checkout', compact(
            'orderItems', 
            'subtotal', 
            'taxAmount', 
            'totalAmount',
            'properties'
        ));
    }

    /**
     * Create order and initiate payment.
     */
    public function store(OrderRequest $request): RedirectResponse|JsonResponse
    {
        $cart = Session::get('cart', []);
        
        if (empty($cart)) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Cart is empty'
            ], 400);
        }

        try {
            DB::beginTransaction();

            // Load services and calculate totals
            $serviceIds = array_keys($cart);
            $services = Service::whereIn('id', $serviceIds)->where('is_active', true)->get();
            
            $subtotal = 0;
            $orderItemsData = [];
            
            foreach ($services as $service) {
                $quantity = $cart[$service->id] ?? 1;
                $itemTotal = $service->price * $quantity;
                $subtotal += $itemTotal;
                
                $orderItemsData[] = [
                    'service_id' => $service->id,
                    'quantity' => $quantity,
                    'price' => $service->price,
                ];
            }

            // Calculate tax
            $taxRate = 0.21;
            $taxAmount = $subtotal * $taxRate;
            $totalAmount = $subtotal + $taxAmount;

            // Create order
            $order = Order::create([
                'user_id' => auth()->id(),
                'property_id' => $request->input('property_id'),
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'total_amount' => $totalAmount,
                'status' => 'pending',
                'payment_method' => $request->input('payment_method'),
            ]);

            // Create order items
            foreach ($orderItemsData as $itemData) {
                $order->items()->create($itemData);
            }

            // Create payment session
            $paymentData = [
                'order_id' => $order->id,
                'amount' => $totalAmount,
                'currency' => 'EUR',
                'description' => 'Order #' . $order->order_number,
                'billing_details' => $request->getBillingDetails(),
                'success_url' => $request->input('success_url', route('orders.success', $order)),
                'cancel_url' => $request->input('cancel_url', route('orders.cancelled', $order)),
            ];

            $paymentResult = $this->paymentService->createPaymentSession(
                $request->input('payment_method'),
                $paymentData
            );

            if (!$paymentResult['success']) {
                throw new \Exception($paymentResult['message'] ?? 'Payment session creation failed');
            }

            // Update order with payment ID
            $order->update([
                'payment_id' => $paymentResult['payment_id']
            ]);

            DB::commit();

            // Clear cart on successful order creation
            Session::forget('cart');

            if ($request->expectsJson()) {
                return $this->jsonResponse([
                    'success' => true,
                    'message' => 'Order created successfully',
                    'order_id' => $order->id,
                    'checkout_url' => $paymentResult['checkout_url']
                ]);
            }

            return redirect($paymentResult['checkout_url']);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Order creation failed', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            if ($request->expectsJson()) {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'Failed to create order: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()
                ->with('error', 'Failed to create order. Please try again.')
                ->withInput();
        }
    }

    /**
     * Handle successful payment.
     */
    public function success(Request $request, Order $order): View|RedirectResponse
    {
        $this->authorize('view', $order);

        // Verify payment status
        try {
            $paymentStatus = $this->paymentService->getPaymentStatus(
                $order->payment_method,
                $order->payment_id
            );

            if ($paymentStatus['is_paid']) {
                $order->update(['status' => 'paid']);
                
                // Send confirmation email
                // TODO: Implement email notification
                
                return view('orders.success', compact('order'));
            }
        } catch (\Exception $e) {
            Log::error('Payment verification failed', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);
        }

        return redirect()->route('orders.show', $order)
            ->with('warning', 'Payment verification pending. We will update you shortly.');
    }

    /**
     * Handle cancelled payment.
     */
    public function cancelled(Order $order): RedirectResponse
    {
        $this->authorize('view', $order);

        return redirect()->route('orders.show', $order)
            ->with('error', 'Payment was cancelled. You can retry payment from your order details.');
    }

    /**
     * Update order status.
     */
    public function updateStatus(OrderRequest $request, Order $order): RedirectResponse
    {
        $this->authorize('update', $order);

        $oldStatus = $order->status;
        $newStatus = $request->input('status');

        $order->update([
            'status' => $newStatus,
            'notes' => $request->input('notes'),
        ]);

        // Log status change
        Log::info('Order status updated', [
            'order_id' => $order->id,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'updated_by' => auth()->id(),
        ]);

        // Send notification email if status changed to completed
        if ($newStatus === 'completed' && $oldStatus !== 'completed') {
            // TODO: Send completion email
        }

        return redirect()->route('orders.show', $order)
            ->with('success', 'Order status updated successfully.');
    }

    /**
     * Cancel an order.
     */
    public function cancel(Order $order): RedirectResponse
    {
        $this->authorize('update', $order);

        if (!in_array($order->status, ['pending', 'processing'])) {
            return redirect()->back()
                ->with('error', 'This order cannot be cancelled.');
        }

        try {
            // Cancel payment if exists
            if ($order->payment_id) {
                $this->paymentService->cancelPayment(
                    $order->payment_method,
                    $order->payment_id
                );
            }

            $order->update(['status' => 'cancelled']);

            return redirect()->route('orders.index')
                ->with('success', 'Order cancelled successfully.');

        } catch (\Exception $e) {
            Log::error('Order cancellation failed', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);

            return redirect()->back()
                ->with('error', 'Failed to cancel order. Please contact support.');
        }
    }

    /**
     * Process refund for an order.
     */
    public function refund(Order $order, Request $request): RedirectResponse
    {
        $this->authorize('refund', $order);

        $request->validate([
            'amount' => ['nullable', 'numeric', 'min:0.01', 'max:' . $order->total_amount],
            'reason' => ['required', 'string', 'max:255'],
        ]);

        if ($order->status !== 'paid') {
            return redirect()->back()
                ->with('error', 'Only paid orders can be refunded.');
        }

        try {
            $refundAmount = $request->input('amount', $order->total_amount);
            
            $refundResult = $this->paymentService->processRefund(
                $order->payment_method,
                $order->payment_id,
                $refundAmount,
                $request->input('reason')
            );

            if ($refundResult['success']) {
                $order->update(['status' => 'refunded']);
                
                Log::info('Order refunded', [
                    'order_id' => $order->id,
                    'refund_amount' => $refundAmount,
                    'reason' => $request->input('reason'),
                    'processed_by' => auth()->id(),
                ]);

                return redirect()->route('orders.show', $order)
                    ->with('success', 'Refund processed successfully.');
            } else {
                throw new \Exception($refundResult['message']);
            }

        } catch (\Exception $e) {
            Log::error('Refund processing failed', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);

            return redirect()->back()
                ->with('error', 'Failed to process refund: ' . $e->getMessage());
        }
    }

    /**
     * Generate and download invoice PDF.
     */
    public function downloadInvoice(Order $order): Response
    {
        $this->authorize('view', $order);

        if (!in_array($order->status, ['paid', 'completed', 'refunded'])) {
            abort(403, 'Invoice not available for this order status.');
        }

        $order->load(['items.service', 'property', 'user']);

        $pdf = Pdf::loadView('orders.invoice', compact('order'));
        
        $filename = 'invoice-' . $order->order_number . '.pdf';
        
        return $pdf->download($filename);
    }

    /**
     * Retry payment for failed/cancelled order.
     */
    public function retryPayment(Order $order): RedirectResponse
    {
        $this->authorize('update', $order);

        if (!in_array($order->status, ['pending', 'cancelled'])) {
            return redirect()->back()
                ->with('error', 'Payment retry not available for this order.');
        }

        try {
            $paymentData = [
                'order_id' => $order->id,
                'amount' => $order->total_amount,
                'currency' => 'EUR',
                'description' => 'Order #' . $order->order_number,
                'success_url' => route('orders.success', $order),
                'cancel_url' => route('orders.cancelled', $order),
            ];

            $paymentResult = $this->paymentService->createPaymentSession(
                $order->payment_method,
                $paymentData
            );

            if (!$paymentResult['success']) {
                throw new \Exception($paymentResult['message']);
            }

            $order->update([
                'payment_id' => $paymentResult['payment_id'],
                'status' => 'pending'
            ]);

            return redirect($paymentResult['checkout_url']);

        } catch (\Exception $e) {
            Log::error('Payment retry failed', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);

            return redirect()->back()
                ->with('error', 'Failed to retry payment: ' . $e->getMessage());
        }
    }

    /**
     * Get order statistics for dashboard.
     */
    public function getStats(): JsonResponse
    {
        $stats = [
            'total_orders' => auth()->user()->orders()->count(),
            'pending_orders' => auth()->user()->orders()->where('status', 'pending')->count(),
            'completed_orders' => auth()->user()->orders()->where('status', 'completed')->count(),
            'total_spent' => auth()->user()->orders()->where('status', 'paid')->sum('total_amount'),
            'recent_orders' => auth()->user()->orders()
                ->with(['items.service'])
                ->latest()
                ->take(5)
                ->get()
                ->map(function ($order) {
                    return [
                        'id' => $order->id,
                        'order_number' => $order->order_number,
                        'total_amount' => $order->formatted_total,
                        'status' => $order->status,
                        'created_at' => $order->created_at->format('M j, Y'),
                        'items_count' => $order->items->count(),
                    ];
                }),
        ];

        return $this->jsonResponse([
            'success' => true,
            'data' => $stats
        ]);
    }

    /**
     * Search orders via AJAX.
     */
    public function search(Request $request): JsonResponse
    {
        $request->validate([
            'query' => ['required', 'string', 'min:2'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);

        $query = auth()->user()->orders()
            ->with(['items.service'])
            ->where('order_number', 'like', '%' . $request->input('query') . '%')
            ->latest()
            ->take($request->input('limit', 10));

        $orders = $query->get()->map(function ($order) {
            return [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'total_amount' => $order->formatted_total,
                'status' => $order->status,
                'created_at' => $order->created_at->format('M j, Y H:i'),
                'items_count' => $order->items->count(),
                'url' => route('orders.show', $order),
            ];
        });

        return $this->jsonResponse([
            'success' => true,
            'data' => $orders
        ]);
    }

    /**
     * Helper method for consistent JSON responses.
     */
    private function jsonResponse(array $data, int $status = 200): JsonResponse
    {
        return response()->json($data, $status);
    }
}
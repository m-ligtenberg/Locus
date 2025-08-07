<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Http\Requests\ServiceRequest;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class ServiceController extends Controller
{
    protected PaymentService $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
        $this->middleware('auth')->except(['index', 'show', 'getByCategory', 'search']);
    }

    /**
     * Display a listing of services with filtering and search.
     */
    public function index(Request $request): View
    {
        $query = Service::active();

        // Search functionality
        if ($request->filled('search')) {
            $searchTerm = $request->input('search');
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', '%' . $searchTerm . '%')
                  ->orWhere('description', 'like', '%' . $searchTerm . '%');
            });
        }

        // Price filtering
        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->input('min_price'));
        }

        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->input('max_price'));
        }

        // Category filtering
        if ($request->filled('category')) {
            $query->byCategory($request->input('category'));
        }

        // Sorting
        $sortBy = $request->input('sort', 'name');
        $sortOrder = $request->input('order', 'asc');
        
        // Validate sort column
        $allowedSorts = ['name', 'price', 'category', 'created_at'];
        if (!in_array($sortBy, $allowedSorts)) {
            $sortBy = 'name';
        }
        
        $query->orderBy($sortBy, $sortOrder);

        $services = $query->paginate(12)->appends($request->query());

        // Get Dutch-specific service categories
        $categories = $this->getDutchServiceCategories();

        // Get cart information
        $cartCount = $this->getCartCount();
        $cartTotal = $this->getCartTotal();

        return view('services.index', compact('services', 'categories', 'cartCount', 'cartTotal'));
    }

    /**
     * Show the form for creating a new service (Admin only).
     */
    public function create(): View
    {
        $this->authorize('create', Service::class);
        
        $categories = $this->getDutchServiceCategories();

        return view('services.create', compact('categories'));
    }

    /**
     * Store a newly created service in storage (Admin only).
     */
    public function store(ServiceRequest $request): RedirectResponse
    {
        $this->authorize('create', Service::class);

        try {
            $validated = $request->validated();
            $validated['is_active'] = $validated['is_active'] ?? true;

            $service = Service::create($validated);

            // Clear category cache
            Cache::forget('services_by_category_' . $service->category);

            Log::info('Nieuwe dienst aangemaakt', [
                'service_id' => $service->id,
                'name' => $service->name,
                'category' => $service->category,
                'admin_user' => auth()->id()
            ]);

            return redirect()->route('services.show', $service)
                ->with('success', 'Dienst succesvol aangemaakt!');
        } catch (\Exception $e) {
            Log::error('Fout bij aanmaken dienst', [
                'error' => $e->getMessage(),
                'input' => $request->all()
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'Er is een fout opgetreden bij het aanmaken van de dienst.');
        }
    }

    /**
     * Display the specified service with related services.
     */
    public function show(Service $service): View
    {
        $this->authorize('view', $service);

        // Get related services from the same category (cached for 1 hour)
        $relatedServices = Cache::remember(
            'related_services_' . $service->id,
            3600,
            function () use ($service) {
                return Service::active()
                    ->where('id', '!=', $service->id)
                    ->byCategory($service->category)
                    ->limit(4)
                    ->get();
            }
        );

        $cartCount = $this->getCartCount();
        $inCart = $this->isInCart($service->id);
        $categories = $this->getDutchServiceCategories();
        
        // Get category info for breadcrumbs
        $categoryInfo = $categories[$service->category] ?? ['name' => $service->category];

        return view('services.show', compact(
            'service', 
            'relatedServices', 
            'cartCount', 
            'inCart', 
            'categoryInfo'
        ));
    }

    /**
     * Show the form for editing the specified service (Admin only).
     */
    public function edit(Service $service): View
    {
        $this->authorize('update', $service);
        
        $categories = $this->getDutchServiceCategories();

        return view('services.edit', compact('service', 'categories'));
    }

    /**
     * Update the specified service in storage (Admin only).
     */
    public function update(ServiceRequest $request, Service $service): RedirectResponse
    {
        $this->authorize('update', $service);

        try {
            $validated = $request->validated();
            $oldCategory = $service->category;
            
            $service->update($validated);

            // Clear relevant caches
            Cache::forget('services_by_category_' . $oldCategory);
            Cache::forget('services_by_category_' . $service->category);
            Cache::forget('related_services_' . $service->id);

            Log::info('Dienst bijgewerkt', [
                'service_id' => $service->id,
                'name' => $service->name,
                'changes' => $service->getChanges(),
                'admin_user' => auth()->id()
            ]);

            return redirect()->route('services.show', $service)
                ->with('success', 'Dienst succesvol bijgewerkt!');
        } catch (\Exception $e) {
            Log::error('Fout bij bijwerken dienst', [
                'service_id' => $service->id,
                'error' => $e->getMessage(),
                'input' => $request->all()
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'Er is een fout opgetreden bij het bijwerken van de dienst.');
        }
    }

    /**
     * Remove the specified service from storage (Admin only).
     */
    public function destroy(Service $service): RedirectResponse
    {
        $this->authorize('delete', $service);

        try {
            // Check if service has any orders
            if ($service->orderItems()->exists()) {
                return redirect()->back()
                    ->with('error', 'Kan dienst niet verwijderen omdat deze al besteld is.');
            }

            $serviceData = [
                'id' => $service->id,
                'name' => $service->name,
                'category' => $service->category
            ];

            $service->delete();

            // Clear relevant caches
            Cache::forget('services_by_category_' . $serviceData['category']);

            Log::info('Dienst verwijderd', [
                'service_data' => $serviceData,
                'admin_user' => auth()->id()
            ]);

            return redirect()->route('services.index')
                ->with('success', 'Dienst succesvol verwijderd.');
        } catch (\Exception $e) {
            Log::error('Fout bij verwijderen dienst', [
                'service_id' => $service->id,
                'error' => $e->getMessage()
            ]);

            return redirect()->back()
                ->with('error', 'Er is een fout opgetreden bij het verwijderen van de dienst.');
        }
    }

    /**
     * Toggle service status (active/inactive) - Admin only.
     */
    public function toggleStatus(Service $service): RedirectResponse
    {
        $this->authorize('update', $service);

        try {
            $newStatus = !$service->is_active;
            $service->update(['is_active' => $newStatus]);

            // Clear relevant caches
            Cache::forget('services_by_category_' . $service->category);
            Cache::forget('related_services_' . $service->id);

            $message = $newStatus 
                ? 'Dienst is nu actief en beschikbaar voor aankoop!' 
                : 'Dienst is nu inactief en verborgen in lijstingen.';

            Log::info('Dienst status gewijzigd', [
                'service_id' => $service->id,
                'new_status' => $newStatus,
                'admin_user' => auth()->id()
            ]);

            return redirect()->back()->with('success', $message);
        } catch (\Exception $e) {
            Log::error('Fout bij wijzigen dienst status', [
                'service_id' => $service->id,
                'error' => $e->getMessage()
            ]);

            return redirect()->back()
                ->with('error', 'Er is een fout opgetreden bij het wijzigen van de dienst status.');
        }
    }

    /**
     * Add service to cart.
     */
    public function addToCart(Request $request, Service $service): JsonResponse|RedirectResponse
    {
        if (!$service->is_active) {
            $message = 'Deze dienst is momenteel niet beschikbaar.';
            
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $message], 400);
            }
            
            return redirect()->back()->with('error', $message);
        }

        $cart = $this->validateAndCleanCart();
        
        if (isset($cart[$service->id])) {
            $message = 'Dienst staat al in je winkelwagentje.';
            
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $message], 400);
            }
            
            return redirect()->back()->with('info', $message);
        }

        $cart[$service->id] = [
            'id' => $service->id,
            'name' => $service->name,
            'price' => $service->price,
            'category' => $service->category,
            'added_at' => now()->toISOString(),
        ];

        Session::put('cart', $cart);

        $cartCount = count($cart);
        $cartTotal = $this->getCartTotal();
        $message = 'Dienst toegevoegd aan winkelwagentje!';

        Log::info('Dienst toegevoegd aan winkelwagentje', [
            'service_id' => $service->id,
            'user_id' => auth()->id(),
            'cart_count' => $cartCount
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'cartCount' => $cartCount,
                'cartTotal' => $cartTotal,
                'formattedTotal' => '€' . number_format($cartTotal, 2, ',', '.')
            ]);
        }

        return redirect()->back()->with('success', $message);
    }

    /**
     * Remove service from cart.
     */
    public function removeFromCart(Request $request, Service $service): JsonResponse|RedirectResponse
    {
        $cart = Session::get('cart', []);
        
        if (!isset($cart[$service->id])) {
            $message = 'Dienst staat niet in je winkelwagentje.';
            
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $message], 400);
            }
            
            return redirect()->back()->with('info', $message);
        }

        unset($cart[$service->id]);
        Session::put('cart', $cart);

        $cartCount = count($cart);
        $cartTotal = $this->getCartTotal();
        $message = 'Dienst verwijderd uit winkelwagentje!';

        Log::info('Dienst verwijderd uit winkelwagentje', [
            'service_id' => $service->id,
            'user_id' => auth()->id(),
            'cart_count' => $cartCount
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'cartCount' => $cartCount,
                'cartTotal' => $cartTotal,
                'formattedTotal' => '€' . number_format($cartTotal, 2, ',', '.')
            ]);
        }

        return redirect()->back()->with('success', $message);
    }

    /**
     * View cart contents with checkout preparation.
     */
    public function viewCart(): View
    {
        $cart = $this->validateAndCleanCart();
        $services = [];
        $total = 0;
        $hasInactiveServices = false;

        if (!empty($cart)) {
            $serviceIds = array_keys($cart);
            $dbServices = Service::whereIn('id', $serviceIds)->get()->keyBy('id');
            
            foreach ($cart as $id => $cartItem) {
                if (isset($dbServices[$id])) {
                    $service = $dbServices[$id];
                    $services[] = [
                        'id' => $service->id,
                        'name' => $service->name,
                        'description' => $service->description,
                        'price' => $service->price,
                        'category' => $service->category,
                        'formatted_price' => $service->formatted_price,
                        'is_active' => $service->is_active,
                        'requirements' => $service->requirements ?? [],
                    ];
                    
                    if ($service->is_active) {
                        $total += $service->price;
                    } else {
                        $hasInactiveServices = true;
                    }
                }
            }
        }

        $formattedTotal = '€' . number_format($total, 2, ',', '.');
        $categories = $this->getDutchServiceCategories();
        
        // Get available payment methods for checkout
        $paymentMethods = [];
        if ($total > 0) {
            $stripeMethodsResult = $this->paymentService->getAvailablePaymentMethods('stripe');
            $mollieMethodsResult = $this->paymentService->getAvailablePaymentMethods('mollie');
            
            if ($stripeMethodsResult['success']) {
                $paymentMethods['stripe'] = $stripeMethodsResult['methods'];
            }
            
            if ($mollieMethodsResult['success']) {
                $paymentMethods['mollie'] = $mollieMethodsResult['methods'];
            }
        }

        return view('services.cart', compact(
            'services', 
            'total', 
            'formattedTotal', 
            'categories', 
            'hasInactiveServices',
            'paymentMethods'
        ));
    }

    /**
     * Clear all items from cart.
     */
    public function clearCart(Request $request): JsonResponse|RedirectResponse
    {
        $cartCount = $this->getCartCount();
        Session::forget('cart');
        
        $message = 'Winkelwagentje geleegd!';

        Log::info('Winkelwagentje geleegd', [
            'previous_count' => $cartCount,
            'user_id' => auth()->id()
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'cartCount' => 0,
                'cartTotal' => 0,
                'formattedTotal' => '€0,00'
            ]);
        }

        return redirect()->route('services.cart')->with('success', $message);
    }

    /**
     * Get services by category for AJAX requests.
     */
    public function getByCategory(Request $request, string $category): JsonResponse
    {
        if (!in_array($category, array_keys($this->getDutchServiceCategories()))) {
            return response()->json(['error' => 'Ongeldige categorie'], 400);
        }

        $services = Cache::remember(
            'services_by_category_' . $category,
            3600, // Cache for 1 hour
            function () use ($category) {
                return Service::active()
                    ->byCategory($category)
                    ->orderBy('name')
                    ->get()
                    ->map(function ($service) {
                        return [
                            'id' => $service->id,
                            'name' => $service->name,
                            'description' => substr($service->description, 0, 150) . '...',
                            'price' => $service->price,
                            'formatted_price' => $service->formatted_price,
                            'requirements_count' => count($service->requirements ?? []),
                            'url' => route('services.show', $service),
                        ];
                    });
            }
        );

        return response()->json([
            'success' => true,
            'services' => $services,
            'category' => $this->getDutchServiceCategories()[$category] ?? null
        ]);
    }

    /**
     * Search services for AJAX requests.
     */
    public function search(Request $request): JsonResponse
    {
        $query = trim($request->input('q', ''));
        
        if (strlen($query) < 2) {
            return response()->json([
                'success' => true,
                'services' => [],
                'message' => 'Voer minimaal 2 tekens in om te zoeken'
            ]);
        }

        $services = Service::active()
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', '%' . $query . '%')
                  ->orWhere('description', 'like', '%' . $query . '%');
            })
            ->orderBy('name')
            ->limit(10)
            ->get()
            ->map(function ($service) {
                $categories = $this->getDutchServiceCategories();
                return [
                    'id' => $service->id,
                    'name' => $service->name,
                    'category' => $service->category,
                    'category_name' => $categories[$service->category]['name'] ?? $service->category,
                    'price' => $service->price,
                    'formatted_price' => $service->formatted_price,
                    'description' => substr($service->description, 0, 100) . '...',
                    'url' => route('services.show', $service),
                ];
            });

        return response()->json([
            'success' => true,
            'services' => $services,
            'query' => $query,
            'results_count' => $services->count()
        ]);
    }

    /**
     * Get Dutch-specific service categories with descriptions.
     */
    private function getDutchServiceCategories(): array
    {
        return [
            'notary' => [
                'name' => 'Notaris Diensten',
                'description' => 'Officiele eigendomsoverdracht en juridische documentatie'
            ],
            'epc-certificate' => [
                'name' => 'Energielabel (EPC)',
                'description' => 'Verplicht energieprestatiecertificaat voor woningverkoop'
            ],
            'property-valuation' => [
                'name' => 'Woningtaxatie',
                'description' => 'Professionele waardering door gecertificeerde taxateur'
            ],
            'mortgage-advice' => [
                'name' => 'Hypotheekadvies',
                'description' => 'Financieringsoplossingen en hypotheekbemiddeling'
            ],
            'home-inspection' => [
                'name' => 'Bouwkundige Keuring',
                'description' => 'Technische inspectie van de woning door expert'
            ],
            'legal-advice' => [
                'name' => 'Juridisch Advies',
                'description' => 'Rechtsbijstand bij verkoop en contractuele zaken'
            ],
        ];
    }

    /**
     * Get current cart count.
     */
    private function getCartCount(): int
    {
        $cart = Session::get('cart', []);
        return count($cart);
    }

    /**
     * Check if service is in cart.
     */
    private function isInCart(int $serviceId): bool
    {
        $cart = Session::get('cart', []);
        return isset($cart[$serviceId]);
    }

    /**
     * Get cart total amount.
     */
    private function getCartTotal(): float
    {
        $cart = Session::get('cart', []);
        if (empty($cart)) {
            return 0;
        }

        $serviceIds = array_keys($cart);
        $services = Service::whereIn('id', $serviceIds)->get();
        
        return $services->where('is_active', true)->sum('price');
    }

    /**
     * Validate cart contents and remove invalid items.
     */
    private function validateAndCleanCart(): array
    {
        $cart = Session::get('cart', []);
        if (empty($cart)) {
            return [];
        }

        $serviceIds = array_keys($cart);
        $validServices = Service::active()->whereIn('id', $serviceIds)->pluck('id')->toArray();
        
        $cleanedCart = [];
        $removedCount = 0;
        
        foreach ($cart as $serviceId => $cartItem) {
            if (in_array($serviceId, $validServices)) {
                $cleanedCart[$serviceId] = $cartItem;
            } else {
                $removedCount++;
            }
        }

        if ($removedCount > 0) {
            Session::put('cart', $cleanedCart);
            Session::flash('info', "Er zijn {$removedCount} niet-beschikbare diensten uit je winkelwagentje verwijderd.");
        }

        return $cleanedCart;
    }
}
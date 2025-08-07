@extends('layouts.app')

@section('title', '- Diensten Marktplaats')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Diensten Marktplaats</h1>
        <p class="mt-2 text-lg text-gray-600">
            Ontdek professionele diensten voor uw woningverkoop. Van notaris tot energielabel.
        </p>
    </div>

    <!-- Cart Status Bar -->
    @if($cartCount > 0)
    <div class="mb-6 bg-primary-50 border border-primary-200 rounded-lg p-4">
        <div class="flex justify-between items-center">
            <div class="flex items-center">
                <svg class="h-5 w-5 text-primary-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M3 1a1 1 0 000 2h1.22l.305 1.222a.997.997 0 00.01.042l1.358 5.43-.893.892C3.74 11.846 4.632 14 6.414 14H15a1 1 0 000-2H6.414l1-1H14a1 1 0 00.894-.553l3-6A1 1 0 0017 3H6.28l-.31-1.243A1 1 0 005 1H3zM16 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM6.5 18a1.5 1.5 0 100-3 1.5 1.5 0 000 3z"/>
                </svg>
                <span class="text-primary-700 font-medium">{{ $cartCount }} dienst{{ $cartCount !== 1 ? 'en' : '' }} in winkelwagentje</span>
            </div>
            <div class="flex items-center space-x-3">
                <span class="text-primary-800 font-bold">€{{ number_format($cartTotal, 2, ',', '.') }}</span>
                <a href="{{ route('services.cart') }}" class="btn-primary text-sm">
                    Bekijk Winkelwagentje
                </a>
            </div>
        </div>
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
        <!-- Sidebar Filters -->
        <div class="lg:col-span-1">
            <div class="card p-6 sticky top-4" x-data="{ 
                showFilters: true, 
                priceRange: { min: {{ request('min_price', '') }}, max: {{ request('max_price', '') }} },
                selectedCategory: '{{ request('category', '') }}',
                searchQuery: '{{ request('search', '') }}'
            }">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Filters</h3>
                    <button @click="showFilters = !showFilters" class="lg:hidden">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                </div>

                <div x-show="showFilters" class="space-y-6">
                    <!-- Search -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Zoeken</label>
                        <form method="GET" action="{{ route('services.index') }}">
                            <div class="relative">
                                <input type="text" 
                                       name="search" 
                                       x-model="searchQuery"
                                       placeholder="Zoek in diensten..."
                                       class="form-input pr-10"
                                       value="{{ request('search') }}">
                                <button type="submit" class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                    <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                    </svg>
                                </button>
                            </div>
                            @foreach(request()->except(['search', 'page']) as $key => $value)
                                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                            @endforeach
                        </form>
                    </div>

                    <!-- Categories -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-3">Categorie</label>
                        <div class="space-y-2">
                            <a href="{{ route('services.index', array_merge(request()->except(['category', 'page']), [])) }}" 
                               class="block px-3 py-2 rounded-md text-sm {{ !request('category') ? 'bg-primary-100 text-primary-800 font-medium' : 'text-gray-600 hover:bg-gray-100' }}">
                                Alle Categorieën
                            </a>
                            @foreach($categories as $key => $category)
                            <a href="{{ route('services.index', array_merge(request()->except(['category', 'page']), ['category' => $key])) }}" 
                               class="block px-3 py-2 rounded-md text-sm {{ request('category') === $key ? 'bg-primary-100 text-primary-800 font-medium' : 'text-gray-600 hover:bg-gray-100' }}">
                                {{ $category['name'] }}
                            </a>
                            @endforeach
                        </div>
                    </div>

                    <!-- Price Range -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-3">Prijsbereik</label>
                        <form method="GET" action="{{ route('services.index') }}">
                            <div class="grid grid-cols-2 gap-3 mb-3">
                                <div>
                                    <label class="text-xs text-gray-500">Min (€)</label>
                                    <input type="number" 
                                           name="min_price" 
                                           x-model="priceRange.min"
                                           placeholder="0"
                                           class="form-input text-sm"
                                           value="{{ request('min_price') }}">
                                </div>
                                <div>
                                    <label class="text-xs text-gray-500">Max (€)</label>
                                    <input type="number" 
                                           name="max_price" 
                                           x-model="priceRange.max"
                                           placeholder="5000"
                                           class="form-input text-sm"
                                           value="{{ request('max_price') }}">
                                </div>
                            </div>
                            <button type="submit" class="w-full btn-secondary text-sm">Toepassen</button>
                            @foreach(request()->except(['min_price', 'max_price', 'page']) as $key => $value)
                                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                            @endforeach
                        </form>
                    </div>

                    <!-- Quick Price Ranges -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Veelgebruikt</label>
                        <div class="space-y-1">
                            @php
                            $quickRanges = [
                                ['label' => 'Tot €250', 'max' => 250],
                                ['label' => '€250 - €500', 'min' => 250, 'max' => 500],
                                ['label' => '€500 - €1000', 'min' => 500, 'max' => 1000],
                                ['label' => 'Vanaf €1000', 'min' => 1000],
                            ];
                            @endphp
                            @foreach($quickRanges as $range)
                            <a href="{{ route('services.index', array_merge(request()->except(['min_price', 'max_price', 'page']), array_filter(['min_price' => $range['min'] ?? null, 'max_price' => $range['max'] ?? null]))) }}" 
                               class="block px-2 py-1 text-xs text-gray-600 hover:bg-gray-100 rounded">
                                {{ $range['label'] }}
                            </a>
                            @endforeach
                        </div>
                    </div>

                    <!-- Clear Filters -->
                    @if(request()->hasAny(['search', 'category', 'min_price', 'max_price']))
                    <div class="pt-4 border-t border-gray-200">
                        <a href="{{ route('services.index') }}" 
                           class="block text-center py-2 text-sm text-gray-600 hover:text-gray-800">
                            🗑️ Wis alle filters
                        </a>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Services Grid -->
        <div class="lg:col-span-3">
            <!-- Results Header -->
            <div class="flex justify-between items-center mb-6">
                <div class="text-sm text-gray-600">
                    {{ $services->total() }} diensten gevonden
                    @if(request()->hasAny(['search', 'category', 'min_price', 'max_price']))
                        voor uw zoekcriteria
                    @endif
                </div>
                
                <!-- Sort Options -->
                <div class="flex items-center space-x-2">
                    <span class="text-sm text-gray-500">Sorteren op:</span>
                    <form method="GET" action="{{ route('services.index') }}" class="inline-block">
                        <select name="sort" onchange="this.form.submit()" class="form-select text-sm">
                            <option value="name" {{ request('sort') === 'name' ? 'selected' : '' }}>Naam</option>
                            <option value="price" {{ request('sort') === 'price' ? 'selected' : '' }}>Prijs</option>
                            <option value="category" {{ request('sort') === 'category' ? 'selected' : '' }}>Categorie</option>
                            <option value="created_at" {{ request('sort') === 'created_at' ? 'selected' : '' }}>Nieuwste</option>
                        </select>
                        <select name="order" onchange="this.form.submit()" class="form-select text-sm ml-2">
                            <option value="asc" {{ request('order') === 'asc' ? 'selected' : '' }}>Oplopend</option>
                            <option value="desc" {{ request('order') === 'desc' ? 'selected' : '' }}>Aflopend</option>
                        </select>
                        @foreach(request()->except(['sort', 'order', 'page']) as $key => $value)
                            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                        @endforeach
                    </form>
                </div>
            </div>

            @if($services->count() > 0)
                <!-- Services Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6" 
                     x-data="serviceCart()" 
                     @cart-updated.window="updateCartDisplay($event.detail)">
                    
                    @foreach($services as $service)
                    <div class="card hover:shadow-lg transition-shadow duration-200">
                        <!-- Service Image Placeholder -->
                        <div class="h-48 bg-gradient-to-br from-primary-50 to-primary-100 p-8">
                            <div class="h-full flex items-center justify-center">
                                @php
                                $categoryIcons = [
                                    'notary' => '⚖️',
                                    'epc-certificate' => '🏷️',
                                    'property-valuation' => '📊',
                                    'mortgage-advice' => '🏦',
                                    'home-inspection' => '🔍',
                                    'legal-advice' => '👨‍💼',
                                ];
                                @endphp
                                <div class="text-center">
                                    <div class="text-4xl mb-2">{{ $categoryIcons[$service->category] ?? '🏠' }}</div>
                                    <div class="text-xs text-primary-600 font-medium">{{ $categories[$service->category]['name'] ?? $service->category }}</div>
                                </div>
                            </div>
                        </div>

                        <div class="p-6">
                            <div class="flex justify-between items-start mb-2">
                                <h3 class="text-lg font-semibold text-gray-900 flex-1">
                                    <a href="{{ route('services.show', $service) }}" class="hover:text-primary-600">
                                        {{ $service->name }}
                                    </a>
                                </h3>
                                <div class="text-right ml-3">
                                    <div class="text-lg font-bold text-primary-600">{{ $service->formatted_price }}</div>
                                </div>
                            </div>

                            <p class="text-gray-600 text-sm mb-4 line-clamp-3">
                                {{ Str::limit($service->description, 120) }}
                            </p>

                            @if($service->requirements && count($service->requirements) > 0)
                            <div class="mb-4">
                                <div class="text-xs text-gray-500 mb-1">Benodigdheden:</div>
                                <div class="flex flex-wrap gap-1">
                                    @foreach(array_slice($service->requirements, 0, 3) as $requirement)
                                    <span class="inline-block bg-gray-100 text-gray-600 text-xs px-2 py-1 rounded">
                                        {{ $requirement }}
                                    </span>
                                    @endforeach
                                    @if(count($service->requirements) > 3)
                                    <span class="inline-block text-gray-500 text-xs px-1">
                                        +{{ count($service->requirements) - 3 }} meer
                                    </span>
                                    @endif
                                </div>
                            </div>
                            @endif

                            <div class="flex space-x-3">
                                <a href="{{ route('services.show', $service) }}" 
                                   class="btn-secondary flex-1 text-center text-sm">
                                    Details
                                </a>
                                
                                @php
                                    $inCart = session('cart.' . $service->id) !== null;
                                @endphp
                                
                                @if($inCart)
                                <button onclick="removeFromCart({{ $service->id }})"
                                        class="btn-danger flex-1 text-sm"
                                        data-service-id="{{ $service->id }}">
                                    <svg class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                    Verwijderen
                                </button>
                                @else
                                <button onclick="addToCart({{ $service->id }})"
                                        class="btn-primary flex-1 text-sm"
                                        data-service-id="{{ $service->id }}">
                                    <svg class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-2 8m2-8h10m-10 0V9a5 5 0 1110 0v4m-5 0a2 2 0 11-4 0 2 2 0 014 0z"/>
                                    </svg>
                                    Toevoegen
                                </button>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>

                <!-- Pagination -->
                <div class="mt-8">
                    {{ $services->links() }}
                </div>

            @else
                <!-- Empty State -->
                <div class="text-center py-12">
                    <div class="text-6xl mb-4">🔍</div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Geen diensten gevonden</h3>
                    <p class="text-gray-600 mb-6">
                        @if(request()->hasAny(['search', 'category', 'min_price', 'max_price']))
                            Probeer andere zoekfilters of 
                            <a href="{{ route('services.index') }}" class="text-primary-600 hover:text-primary-800">wis alle filters</a>.
                        @else
                            Er zijn momenteel geen diensten beschikbaar.
                        @endif
                    </p>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Cart Management Scripts -->
<script>
function serviceCart() {
    return {
        updateCartDisplay(data) {
            // Update cart count in header if exists
            const cartBadge = document.querySelector('[data-cart-count]');
            if (cartBadge) {
                cartBadge.textContent = data.cartCount;
            }
            
            // Update cart status bar
            setTimeout(() => {
                if (data.cartCount > 0) {
                    location.reload(); // Simple reload to show updated cart bar
                }
            }, 1000);
        }
    }
}

function addToCart(serviceId) {
    fetch(`/services/${serviceId}/add-to-cart`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update button state
            const button = document.querySelector(`button[data-service-id="${serviceId}"]`);
            if (button) {
                button.className = 'btn-danger flex-1 text-sm';
                button.innerHTML = `
                    <svg class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                    Verwijderen
                `;
                button.setAttribute('onclick', `removeFromCart(${serviceId})`);
            }
            
            // Dispatch cart update event
            window.dispatchEvent(new CustomEvent('cart-updated', { detail: data }));
            
            // Show success message
            showNotification(data.message, 'success');
        } else {
            showNotification(data.message, 'error');
        }
    })
    .catch(error => {
        showNotification('Er is een fout opgetreden', 'error');
    });
}

function removeFromCart(serviceId) {
    fetch(`/services/${serviceId}/remove-from-cart`, {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update button state
            const button = document.querySelector(`button[data-service-id="${serviceId}"]`);
            if (button) {
                button.className = 'btn-primary flex-1 text-sm';
                button.innerHTML = `
                    <svg class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-2 8m2-8h10m-10 0V9a5 5 0 1110 0v4m-5 0a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    Toevoegen
                `;
                button.setAttribute('onclick', `addToCart(${serviceId})`);
            }
            
            // Dispatch cart update event
            window.dispatchEvent(new CustomEvent('cart-updated', { detail: data }));
            
            // Show success message
            showNotification(data.message, 'success');
        } else {
            showNotification(data.message, 'error');
        }
    })
    .catch(error => {
        showNotification('Er is een fout opgetreden', 'error');
    });
}

function showNotification(message, type) {
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 px-6 py-3 rounded-lg shadow-lg z-50 ${
        type === 'success' ? 'bg-green-500 text-white' : 'bg-red-500 text-white'
    }`;
    notification.textContent = message;
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.remove();
    }, 3000);
}
</script>

<style>
.line-clamp-3 {
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
@endsection
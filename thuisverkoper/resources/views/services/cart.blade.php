@extends('layouts.app')

@section('title', '- Winkelwagentje')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Winkelwagentje</h1>
        <p class="mt-2 text-gray-600">
            Controleer uw geselecteerde diensten voordat u doorgaat naar de checkout.
        </p>
    </div>

    @if(count($services) > 0)
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Cart Items -->
            <div class="lg:col-span-2">
                <div class="card">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-900">
                            Geselecteerde Diensten ({{ count($services) }})
                        </h2>
                    </div>

                    <div class="divide-y divide-gray-200" x-data="cartManager()">
                        @foreach($services as $service)
                        <div class="p-6 {{ !$service['is_active'] ? 'bg-gray-50' : '' }}" 
                             x-data="{ removing: false }">
                            
                            @if(!$service['is_active'])
                            <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-3 py-2 rounded text-sm mb-4">
                                ⚠️ Deze dienst is momenteel niet beschikbaar en wordt niet meegenomen in uw bestelling.
                            </div>
                            @endif

                            <div class="flex items-start space-x-4">
                                <!-- Service Icon -->
                                <div class="flex-shrink-0">
                                    <div class="w-16 h-16 bg-gradient-to-br from-primary-50 to-primary-100 rounded-lg flex items-center justify-center">
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
                                        <span class="text-xl">{{ $categoryIcons[$service['category']] ?? '🏠' }}</span>
                                    </div>
                                </div>

                                <!-- Service Details -->
                                <div class="flex-1 min-w-0">
                                    <div class="flex justify-between items-start">
                                        <div class="flex-1">
                                            <h3 class="text-lg font-medium text-gray-900">
                                                <a href="{{ route('services.show', $service['id']) }}" 
                                                   class="hover:text-primary-600">
                                                    {{ $service['name'] }}
                                                </a>
                                            </h3>
                                            <p class="mt-1 text-sm text-gray-600">
                                                {{ $categories[$service['category']]['name'] ?? $service['category'] }}
                                            </p>
                                            <p class="mt-2 text-sm text-gray-500">
                                                {{ Str::limit($service['description'], 150) }}
                                            </p>

                                            @if(!empty($service['requirements']))
                                            <div class="mt-3">
                                                <p class="text-xs text-gray-500 mb-1">Benodigdheden:</p>
                                                <div class="flex flex-wrap gap-1">
                                                    @foreach(array_slice($service['requirements'], 0, 3) as $requirement)
                                                    <span class="inline-block bg-gray-100 text-gray-600 text-xs px-2 py-1 rounded">
                                                        {{ $requirement }}
                                                    </span>
                                                    @endforeach
                                                    @if(count($service['requirements']) > 3)
                                                    <span class="inline-block text-gray-500 text-xs px-1">
                                                        +{{ count($service['requirements']) - 3 }} meer
                                                    </span>
                                                    @endif
                                                </div>
                                            </div>
                                            @endif
                                        </div>

                                        <!-- Price and Actions -->
                                        <div class="ml-6 text-right">
                                            <div class="text-lg font-semibold {{ $service['is_active'] ? 'text-primary-600' : 'text-gray-400' }}">
                                                {{ $service['formatted_price'] }}
                                            </div>
                                            
                                            <div class="mt-3 space-y-2">
                                                <button onclick="removeFromCart({{ $service['id'] }})"
                                                        class="text-sm text-red-600 hover:text-red-800 flex items-center"
                                                        x-bind:disabled="removing"
                                                        x-bind:class="removing ? 'opacity-50' : ''">
                                                    <svg class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                    </svg>
                                                    <span x-text="removing ? 'Verwijderen...' : 'Verwijderen'">Verwijderen</span>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                <!-- Continue Shopping -->
                <div class="mt-6 text-center">
                    <a href="{{ route('services.index') }}" 
                       class="inline-flex items-center text-primary-600 hover:text-primary-800">
                        <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                        Meer diensten bekijken
                    </a>
                </div>
            </div>

            <!-- Order Summary -->
            <div class="lg:col-span-1">
                <div class="card p-6 sticky top-4">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Bestelling Overzicht</h3>

                    <!-- Summary Items -->
                    <div class="space-y-3 mb-6">
                        @php $activeServicesCount = collect($services)->where('is_active', true)->count(); @endphp
                        
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Diensten ({{ $activeServicesCount }})</span>
                            <span class="font-medium">{{ $formattedTotal }}</span>
                        </div>

                        @if($hasInactiveServices)
                        <div class="text-xs text-yellow-700 bg-yellow-50 p-2 rounded">
                            Inactieve diensten worden niet meegenomen in de bestelling.
                        </div>
                        @endif

                        <div class="border-t border-gray-200 pt-3">
                            <div class="flex justify-between items-center">
                                <span class="text-base font-semibold text-gray-900">Totaal</span>
                                <span class="text-xl font-bold text-primary-600">{{ $formattedTotal }}</span>
                            </div>
                            <p class="text-xs text-gray-500 mt-1">Inclusief BTW waar van toepassing</p>
                        </div>
                    </div>

                    @if($total > 0)
                        <!-- Checkout Button -->
                        @auth
                        <form action="{{ route('orders.store') }}" method="POST" class="mb-4">
                            @csrf
                            <button type="submit" class="w-full btn-primary">
                                Doorgaan naar Checkout
                            </button>
                        </form>
                        @else
                        <div class="space-y-3 mb-4">
                            <p class="text-sm text-gray-600 text-center">
                                Log in om uw bestelling af te ronden
                            </p>
                            <a href="{{ route('login', ['redirect' => route('services.cart')]) }}" 
                               class="w-full btn-primary text-center">
                                Inloggen
                            </a>
                            <a href="{{ route('register', ['redirect' => route('services.cart')]) }}" 
                               class="w-full btn-secondary text-center">
                                Account Aanmaken
                            </a>
                        </div>
                        @endauth

                        <!-- Payment Methods Preview -->
                        @if(!empty($paymentMethods))
                        <div class="border-t border-gray-200 pt-4">
                            <h4 class="text-sm font-medium text-gray-900 mb-3">Betaalmethoden</h4>
                            <div class="grid grid-cols-3 gap-2">
                                @if(!empty($paymentMethods['stripe']))
                                    @foreach(array_slice($paymentMethods['stripe'], 0, 3) as $method)
                                    <div class="flex items-center justify-center h-8 bg-gray-50 rounded border text-xs text-gray-600">
                                        {{ ucfirst($method) }}
                                    </div>
                                    @endforeach
                                @endif
                                @if(!empty($paymentMethods['mollie']))
                                    @foreach(array_slice($paymentMethods['mollie'], 0, 3) as $method)
                                    <div class="flex items-center justify-center h-8 bg-gray-50 rounded border text-xs text-gray-600">
                                        {{ ucfirst($method) }}
                                    </div>
                                    @endforeach
                                @endif
                            </div>
                        </div>
                        @endif

                    @else
                        <div class="text-center py-6">
                            <div class="text-gray-400 mb-2">💳</div>
                            <p class="text-sm text-gray-500">Geen beschikbare diensten voor bestelling</p>
                        </div>
                    @endif

                    <!-- Clear Cart -->
                    <div class="border-t border-gray-200 pt-4 mt-6">
                        <button onclick="clearCart()" 
                                class="w-full text-sm text-gray-600 hover:text-gray-800 py-2">
                            🗑️ Leeg winkelwagentje
                        </button>
                    </div>
                </div>

                <!-- Security Badge -->
                <div class="card p-4 mt-6 text-center">
                    <div class="flex items-center justify-center mb-2">
                        <svg class="h-5 w-5 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                        </svg>
                        <span class="text-sm font-medium text-gray-900">Veilig Betalen</span>
                    </div>
                    <p class="text-xs text-gray-600">
                        Uw gegevens zijn beveiligd met SSL-encryptie
                    </p>
                </div>
            </div>
        </div>

    @else
        <!-- Empty Cart -->
        <div class="text-center py-16">
            <div class="text-8xl mb-6">🛒</div>
            <h2 class="text-2xl font-bold text-gray-900 mb-4">Uw winkelwagentje is leeg</h2>
            <p class="text-gray-600 mb-8 max-w-md mx-auto">
                Ontdek onze professionele diensten om uw woningverkoop succesvol af te ronden.
            </p>
            <div class="space-y-4">
                <a href="{{ route('services.index') }}" 
                   class="btn-primary inline-flex items-center">
                    <svg class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 7a2 2 0 012-2h5.586a1 1 0 01.707.293L16 8h3a2 2 0 012 2v1M5 7v10a2 2 0 002 2h8a2 2 0 002-2V9a2 2 0 00-2-2H9.414a1 1 0 01-.707-.293L7 5H5a2 2 0 00-2 2z"/>
                    </svg>
                    Bekijk Diensten
                </a>
            </div>

            <!-- Popular Services -->
            <div class="mt-12">
                <h3 class="text-lg font-semibold text-gray-900 mb-6">Populaire Diensten</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 max-w-2xl mx-auto">
                    @php
                    $popularServices = [
                        ['name' => 'Energielabel (EPC)', 'price' => '€89', 'category' => 'epc-certificate', 'icon' => '🏷️'],
                        ['name' => 'Woningtaxatie', 'price' => '€395', 'category' => 'property-valuation', 'icon' => '📊'],
                        ['name' => 'Notaris Diensten', 'price' => '€895', 'category' => 'notary', 'icon' => '⚖️'],
                    ];
                    @endphp

                    @foreach($popularServices as $popular)
                    <a href="{{ route('services.index', ['category' => $popular['category']]) }}" 
                       class="card p-4 hover:shadow-md transition-shadow duration-200 text-center">
                        <div class="text-2xl mb-2">{{ $popular['icon'] }}</div>
                        <h4 class="font-medium text-gray-900 mb-1">{{ $popular['name'] }}</h4>
                        <p class="text-sm text-primary-600 font-semibold">Vanaf {{ $popular['price'] }}</p>
                    </a>
                    @endforeach
                </div>
            </div>
        </div>
    @endif
</div>

<!-- Scripts -->
<script>
function cartManager() {
    return {
        // Additional cart management functionality can be added here
    }
}

function removeFromCart(serviceId) {
    if (!confirm('Weet u zeker dat u deze dienst uit het winkelwagentje wilt verwijderen?')) {
        return;
    }

    // Show loading state
    const button = event.target;
    const originalText = button.innerHTML;
    button.innerHTML = 'Verwijderen...';
    button.disabled = true;

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
            showNotification(data.message, 'success');
            // Reload page to update cart display
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            showNotification(data.message, 'error');
            // Restore button
            button.innerHTML = originalText;
            button.disabled = false;
        }
    })
    .catch(error => {
        showNotification('Er is een fout opgetreden', 'error');
        // Restore button
        button.innerHTML = originalText;
        button.disabled = false;
    });
}

function clearCart() {
    if (!confirm('Weet u zeker dat u alle diensten uit het winkelwagentje wilt verwijderen?')) {
        return;
    }

    fetch('/services/cart/clear', {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification(data.message, 'success');
            // Redirect to empty cart view
            setTimeout(() => {
                window.location.reload();
            }, 1000);
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
@endsection
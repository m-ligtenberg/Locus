@extends('layouts.app')

@section('title', '- ' . $service->name)

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Breadcrumb -->
    <nav class="flex mb-8" aria-label="Breadcrumb">
        <ol class="flex items-center space-x-4">
            <li>
                <a href="{{ route('services.index') }}" class="text-gray-500 hover:text-gray-700">Diensten</a>
            </li>
            <li class="flex items-center">
                <svg class="flex-shrink-0 h-4 w-4 text-gray-400 mx-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 111.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                </svg>
                <a href="{{ route('services.index', ['category' => $service->category]) }}" class="text-gray-500 hover:text-gray-700">
                    {{ $categoryInfo['name'] ?? $service->category }}
                </a>
            </li>
            <li class="flex items-center">
                <svg class="flex-shrink-0 h-4 w-4 text-gray-400 mx-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 111.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                </svg>
                <span class="text-gray-800 font-medium">{{ $service->name }}</span>
            </li>
        </ol>
    </nav>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Content -->
        <div class="lg:col-span-2">
            <!-- Service Header -->
            <div class="card p-8 mb-8">
                <div class="flex items-start justify-between mb-6">
                    <div class="flex-1">
                        <div class="flex items-center mb-3">
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
                            <span class="text-3xl mr-3">{{ $categoryIcons[$service->category] ?? '🏠' }}</span>
                            <div>
                                <h1 class="text-3xl font-bold text-gray-900">{{ $service->name }}</h1>
                                <p class="text-lg text-gray-600 mt-1">{{ $categoryInfo['name'] ?? $service->category }}</p>
                            </div>
                        </div>
                        
                        @if(!$service->is_active)
                        <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded mb-4">
                            ⚠️ Deze dienst is momenteel niet beschikbaar voor bestelling.
                        </div>
                        @endif
                    </div>
                    
                    <div class="text-right ml-6">
                        <div class="text-3xl font-bold text-primary-600 mb-2">{{ $service->formatted_price }}</div>
                        <div class="text-sm text-gray-500">Eenmalig tarief</div>
                    </div>
                </div>

                <!-- Service Description -->
                <div class="prose max-w-none">
                    <h3 class="text-lg font-semibold text-gray-900 mb-3">Beschrijving</h3>
                    <p class="text-gray-700 leading-relaxed">{{ $service->description }}</p>
                </div>

                <!-- Requirements -->
                @if($service->requirements && count($service->requirements) > 0)
                <div class="mt-8">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Benodigdheden</h3>
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <div class="flex items-start">
                            <svg class="h-5 w-5 text-blue-400 mt-0.5 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                            </svg>
                            <div>
                                <p class="text-blue-800 font-medium mb-2">Voor deze dienst heeft u het volgende nodig:</p>
                                <ul class="text-blue-700 space-y-1">
                                    @foreach($service->requirements as $requirement)
                                    <li class="flex items-start">
                                        <span class="text-blue-500 mr-2">•</span>
                                        {{ $requirement }}
                                    </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Service Features Based on Category -->
                @php
                $categoryFeatures = [
                    'notary' => [
                        'Juridisch bindende akte',
                        'Kadaster registratie',
                        'Eigendomsoverdracht',
                        'Hypotheekafhandeling'
                    ],
                    'epc-certificate' => [
                        'Officieel energielabel',
                        'Geldig 10 jaar',
                        'Wettelijk verplicht',
                        'Online beschikbaar'
                    ],
                    'property-valuation' => [
                        'NRVT gecertificeerd',
                        'Marktconform rapport',
                        'Hypotheekdoeleinden',
                        '5 werkdagen levertijd'
                    ],
                    'mortgage-advice' => [
                        'Onafhankelijk advies',
                        'Meerdere aanbieders',
                        'Beste voorwaarden',
                        'Complete begeleiding'
                    ],
                    'home-inspection' => [
                        'NEN 2767 conform',
                        'Uitgebreid rapport',
                        'Foto documentatie',
                        'Hersteladvies'
                    ],
                    'legal-advice' => [
                        'Ervaren juristen',
                        'Vastgoedspecialisten',
                        'Contractbeoordeling',
                        'Persoonlijk advies'
                    ]
                ];
                
                $features = $categoryFeatures[$service->category] ?? [];
                @endphp

                @if(!empty($features))
                <div class="mt-8">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Inbegrepen</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        @foreach($features as $feature)
                        <div class="flex items-center">
                            <svg class="h-5 w-5 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <span class="text-gray-700">{{ $feature }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>

            <!-- Testimonials Section -->
            <div class="card p-8 mb-8">
                <h3 class="text-lg font-semibold text-gray-900 mb-6">Klantbeoordelingen</h3>
                
                @php
                $testimonials = [
                    [
                        'name' => 'Marie van der Berg',
                        'rating' => 5,
                        'comment' => 'Uitstekende service! Alles werd duidelijk uitgelegd en snel afgehandeld.',
                        'service_type' => 'Notaris Diensten'
                    ],
                    [
                        'name' => 'Peter Jansen',
                        'rating' => 5,
                        'comment' => 'Professioneel en betrouwbaar. Precies wat we nodig hadden voor onze verkoop.',
                        'service_type' => 'Woningtaxatie'
                    ]
                ];
                @endphp

                <div class="space-y-6">
                    @foreach($testimonials as $testimonial)
                    <div class="border-l-4 border-primary-200 pl-4">
                        <div class="flex items-center mb-2">
                            <div class="flex text-yellow-400 mr-2">
                                @for($i = 1; $i <= 5; $i++)
                                <svg class="h-4 w-4 {{ $i <= $testimonial['rating'] ? 'text-yellow-400' : 'text-gray-300' }}" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                </svg>
                                @endfor
                            </div>
                            <span class="font-medium text-gray-900">{{ $testimonial['name'] }}</span>
                            <span class="text-gray-500 text-sm ml-2">• {{ $testimonial['service_type'] }}</span>
                        </div>
                        <p class="text-gray-700">{{ $testimonial['comment'] }}</p>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="lg:col-span-1">
            <!-- Order Card -->
            <div class="card p-6 sticky top-4" x-data="{ 
                quantity: 1,
                inCart: {{ $inCart ? 'true' : 'false' }},
                isActive: {{ $service->is_active ? 'true' : 'false' }}
            }">
                <div class="text-center mb-6">
                    <div class="text-2xl font-bold text-primary-600 mb-1">{{ $service->formatted_price }}</div>
                    <div class="text-sm text-gray-500">Eenmalige service</div>
                </div>

                @if($service->is_active)
                    <div class="space-y-4" 
                         @cart-updated.window="inCart = $event.detail.cartCount > 0">
                        
                        <div x-show="!inCart">
                            <button onclick="addToCart({{ $service->id }})"
                                    class="w-full btn-primary">
                                <svg class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-2 8m2-8h10m-10 0V9a5 5 0 1110 0v4m-5 0a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                                Toevoegen aan Winkelwagentje
                            </button>
                        </div>

                        <div x-show="inCart">
                            <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-4">
                                <div class="flex items-center">
                                    <svg class="h-5 w-5 text-green-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                    <span class="text-green-800 font-medium">In winkelwagentje</span>
                                </div>
                            </div>
                            
                            <div class="space-y-3">
                                <a href="{{ route('services.cart') }}" class="w-full btn-primary text-center">
                                    Naar Winkelwagentje
                                </a>
                                <button onclick="removeFromCart({{ $service->id }})"
                                        class="w-full btn-secondary">
                                    Verwijderen
                                </button>
                            </div>
                        </div>

                        <div class="text-center">
                            <a href="{{ route('services.index') }}" class="text-sm text-gray-600 hover:text-gray-800">
                                ← Terug naar diensten
                            </a>
                        </div>
                    </div>
                @else
                    <div class="text-center">
                        <div class="bg-gray-100 text-gray-500 py-3 px-4 rounded-lg mb-4">
                            Momenteel niet beschikbaar
                        </div>
                        <a href="{{ route('services.index') }}" class="text-sm text-primary-600 hover:text-primary-800">
                            Bekijk andere diensten →
                        </a>
                    </div>
                @endif
            </div>

            <!-- Category Information -->
            <div class="card p-6 mt-6">
                <h4 class="font-semibold text-gray-900 mb-3">Over {{ $categoryInfo['name'] ?? $service->category }}</h4>
                <p class="text-gray-600 text-sm">
                    {{ $categoryInfo['description'] ?? 'Professionele dienstverlening voor uw woningverkoop.' }}
                </p>
            </div>

            <!-- Contact Card -->
            <div class="card p-6 mt-6">
                <h4 class="font-semibold text-gray-900 mb-3">Vragen over deze dienst?</h4>
                <p class="text-gray-600 text-sm mb-4">
                    Onze experts helpen u graag verder met vragen over deze dienst.
                </p>
                <div class="space-y-3">
                    <a href="tel:0800-123456" class="flex items-center text-sm text-gray-700 hover:text-primary-600">
                        <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                        </svg>
                        0800-123456
                    </a>
                    <a href="mailto:info@thuisverkoper.nl" class="flex items-center text-sm text-gray-700 hover:text-primary-600">
                        <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                        info@thuisverkoper.nl
                    </a>
                </div>
            </div>

            <!-- Cart Summary -->
            @if($cartCount > 0)
            <div class="card p-6 mt-6 bg-primary-50 border-primary-200">
                <div class="flex items-center justify-between mb-2">
                    <span class="font-medium text-primary-800">Winkelwagentje</span>
                    <span class="text-primary-600">{{ $cartCount }} item{{ $cartCount !== 1 ? 's' : '' }}</span>
                </div>
                <div class="flex items-center justify-between mb-4">
                    <span class="text-primary-700">Totaal:</span>
                    <span class="font-bold text-primary-800">€{{ number_format(session('cart', collect())->sum('price'), 2, ',', '.') }}</span>
                </div>
                <a href="{{ route('services.cart') }}" class="w-full btn-primary text-center text-sm">
                    Naar Winkelwagentje
                </a>
            </div>
            @endif
        </div>
    </div>

    <!-- Related Services -->
    @if($relatedServices->count() > 0)
    <div class="mt-12">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-2xl font-bold text-gray-900">Gerelateerde Diensten</h2>
            <a href="{{ route('services.index', ['category' => $service->category]) }}" 
               class="text-primary-600 hover:text-primary-800 text-sm font-medium">
                Bekijk alle {{ $categoryInfo['name'] ?? $service->category }} →
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach($relatedServices as $relatedService)
            <div class="card hover:shadow-lg transition-shadow duration-200">
                <div class="h-32 bg-gradient-to-br from-primary-50 to-primary-100 p-6">
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
                            <div class="text-2xl mb-1">{{ $categoryIcons[$relatedService->category] ?? '🏠' }}</div>
                        </div>
                    </div>
                </div>

                <div class="p-4">
                    <h4 class="font-medium text-gray-900 mb-2">
                        <a href="{{ route('services.show', $relatedService) }}" class="hover:text-primary-600">
                            {{ $relatedService->name }}
                        </a>
                    </h4>
                    <p class="text-gray-600 text-sm mb-3">
                        {{ Str::limit($relatedService->description, 80) }}
                    </p>
                    <div class="flex justify-between items-center">
                        <span class="font-bold text-primary-600">{{ $relatedService->formatted_price }}</span>
                        <a href="{{ route('services.show', $relatedService) }}" 
                           class="btn-secondary text-xs px-3 py-1">
                            Bekijken
                        </a>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif
</div>

<!-- Cart Management Scripts (same as in index.blade.php) -->
<script>
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
            window.dispatchEvent(new CustomEvent('cart-updated', { detail: data }));
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
            window.dispatchEvent(new CustomEvent('cart-updated', { detail: data }));
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
@endsection
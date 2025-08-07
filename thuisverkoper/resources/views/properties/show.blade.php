@extends('layouts.app')

@section('title', "- {{ $property->title }}")

@section('content')
<div class="bg-white" x-data="propertyViewer({{ json_encode($property) }})">
    <!-- Image Gallery Section -->
    <div class="relative">
        <!-- Main Image Display -->
        <div class="aspect-w-16 aspect-h-9 lg:aspect-h-6">
            <div class="w-full h-96 lg:h-[500px] bg-gray-200 relative overflow-hidden">
                @if($property->images && count($property->images) > 0)
                    <!-- Main Image -->
                    <img x-show="!lightboxOpen" 
                         :src="`/storage/${property.images[currentImageIndex]}`" 
                         :alt="`${property.title} - Afbeelding ${currentImageIndex + 1}`"
                         class="w-full h-full object-cover cursor-pointer"
                         @click="openLightbox(currentImageIndex)">
                    
                    <!-- Navigation Arrows -->
                    <div x-show="property.images.length > 1" class="absolute inset-y-0 left-0 flex items-center">
                        <button @click="previousImage()" 
                                class="ml-4 bg-black bg-opacity-50 text-white rounded-full p-2 hover:bg-opacity-70 transition-all">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                            </svg>
                        </button>
                    </div>
                    
                    <div x-show="property.images.length > 1" class="absolute inset-y-0 right-0 flex items-center">
                        <button @click="nextImage()" 
                                class="mr-4 bg-black bg-opacity-50 text-white rounded-full p-2 hover:bg-opacity-70 transition-all">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </button>
                    </div>

                    <!-- Image Counter -->
                    <div class="absolute bottom-4 right-4 bg-black bg-opacity-50 text-white px-3 py-1 rounded-full text-sm">
                        <span x-text="currentImageIndex + 1"></span> / <span x-text="property.images.length"></span>
                    </div>

                    <!-- View All Photos Button -->
                    <div class="absolute bottom-4 left-4">
                        <button @click="openLightbox(0)" 
                                class="bg-white text-gray-800 px-4 py-2 rounded-lg font-medium hover:bg-gray-100 transition-colors flex items-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            Alle foto's bekijken
                        </button>
                    </div>
                @else
                    <!-- No Images Placeholder -->
                    <div class="w-full h-full flex items-center justify-center">
                        <div class="text-center">
                            <svg class="mx-auto h-16 w-16 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                            <p class="mt-2 text-lg text-gray-500">Geen foto's beschikbaar</p>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Thumbnail Strip -->
        @if($property->images && count($property->images) > 1)
            <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black to-transparent p-4">
                <div class="flex space-x-2 overflow-x-auto pb-2 scrollbar-hide">
                    @foreach($property->images as $index => $image)
                        <button @click="currentImageIndex = {{ $index }}"
                                class="flex-shrink-0 w-16 h-16 rounded-lg overflow-hidden transition-all"
                                :class="currentImageIndex === {{ $index }} ? 'ring-2 ring-white' : 'opacity-70 hover:opacity-100'">
                            <img src="{{ asset('storage/' . $image) }}" 
                                 alt="Thumbnail {{ $index + 1 }}" 
                                 class="w-full h-full object-cover">
                        </button>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    <!-- Property Details -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="lg:grid lg:grid-cols-3 lg:gap-8">
            <!-- Main Content -->
            <div class="lg:col-span-2">
                <!-- Property Header -->
                <div class="mb-8">
                    <div class="flex items-start justify-between">
                        <div>
                            <h1 class="text-3xl font-bold text-gray-900 mb-2">{{ $property->title }}</h1>
                            <div class="flex items-center text-gray-600 mb-4">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                <span>{{ $property->address }}, {{ $property->city }}</span>
                                <span class="ml-4 px-2 py-1 bg-gray-100 text-gray-800 text-sm rounded-full">
                                    {{ $property->postal_code }}
                                </span>
                            </div>
                        </div>
                        
                        <div class="text-right">
                            <div class="text-3xl font-bold text-primary-600">{{ $property->formatted_price }}</div>
                            <div class="text-sm text-gray-500 capitalize">
                                @switch($property->property_type)
                                    @case('house') Eengezinswoning @break
                                    @case('apartment') Appartement @break
                                    @case('condo') Condominium @break
                                    @default {{ ucfirst($property->property_type) }}
                                @endswitch
                            </div>
                        </div>
                    </div>

                    <!-- Property Stats -->
                    <div class="flex flex-wrap gap-6 text-sm text-gray-600 border-b border-gray-200 pb-6">
                        @if($property->bedrooms)
                            <div class="flex items-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2 2v0" />
                                </svg>
                                <span>{{ $property->bedrooms }} slaapkamer{{ $property->bedrooms != 1 ? 's' : '' }}</span>
                            </div>
                        @endif

                        @if($property->bathrooms)
                            <div class="flex items-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z" />
                                </svg>
                                <span>{{ $property->bathrooms }} badkamer{{ $property->bathrooms != 1 ? 's' : '' }}</span>
                            </div>
                        @endif

                        @if($property->square_meters)
                            <div class="flex items-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4" />
                                </svg>
                                <span>{{ $property->square_meters }} m²</span>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Description -->
                <div class="mb-8">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">Beschrijving</h2>
                    <div class="prose text-gray-700 whitespace-pre-line">{{ $property->description }}</div>
                </div>

                <!-- Features -->
                @if($property->features && count($property->features) > 0)
                    <div class="mb-8">
                        <h2 class="text-xl font-semibold text-gray-900 mb-4">Voorzieningen</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @foreach($property->features as $feature)
                                <div class="flex items-center p-3 bg-gray-50 rounded-lg">
                                    <svg class="w-5 h-5 text-green-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    <span class="text-gray-800">
                                        @switch($feature)
                                            @case('garden') Tuin @break
                                            @case('parking') Parkeerplaats @break
                                            @case('garage') Garage @break
                                            @case('balcony') Balkon @break
                                            @case('elevator') Lift @break
                                            @case('fireplace') Open haard @break
                                            @case('air_conditioning') Airconditioning @break
                                            @case('storage') Berging @break
                                            @case('solar_panels') Zonnepanelen @break
                                            @case('energy_efficient') Energiezuinig @break
                                            @case('furnished') Gemeubileerd @break
                                            @case('pets_allowed') Huisdieren toegestaan @break
                                            @default {{ ucfirst(str_replace('_', ' ', $feature)) }}
                                        @endswitch
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Virtual Tour -->
                @if($property->virtual_tour_url)
                    <div class="mb-8">
                        <h2 class="text-xl font-semibold text-gray-900 mb-4">Virtuele Tour</h2>
                        <div class="bg-gray-100 rounded-lg p-6 text-center">
                            <svg class="mx-auto h-12 w-12 text-primary-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                            </svg>
                            <p class="text-gray-600 mb-4">Bekijk deze woning in 360° vanuit je luie stoel</p>
                            <a href="{{ $property->virtual_tour_url }}" 
                               target="_blank" 
                               rel="noopener" 
                               class="btn-primary">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                </svg>
                                Start Virtuele Tour
                            </a>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Sidebar -->
            <div class="mt-8 lg:mt-0">
                <!-- Contact Card -->
                <div class="bg-white border border-gray-200 rounded-lg shadow-lg sticky top-8">
                    <div class="p-6">
                        <div class="flex items-center mb-4">
                            <div class="h-12 w-12 bg-primary-600 rounded-full flex items-center justify-center">
                                <span class="text-lg font-semibold text-white">
                                    {{ substr($property->user->name, 0, 1) }}
                                </span>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-lg font-medium text-gray-900">{{ $property->user->name }}</h3>
                                <p class="text-sm text-gray-600">Eigenaar</p>
                            </div>
                        </div>

                        <!-- Contact Actions -->
                        <div class="space-y-3">
                            <button @click="showContactForm = !showContactForm" 
                                    class="w-full btn-primary justify-center">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                                Contact Opnemen
                            </button>

                            @auth
                                <a href="{{ route('bookings.create', ['property' => $property->id]) }}" 
                                   class="w-full btn-secondary justify-center">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                    </svg>
                                    Bezichtiging Plannen
                                </a>
                            @else
                                <a href="{{ route('login') }}" 
                                   class="w-full btn-secondary justify-center">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                    </svg>
                                    Inloggen voor Bezichtiging
                                </a>
                            @endauth
                        </div>

                        <!-- Contact Form -->
                        <div x-show="showContactForm" x-transition class="mt-4 pt-4 border-t border-gray-200">
                            <form @submit.prevent="submitContactForm()" class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Naam</label>
                                    <input type="text" 
                                           x-model="contactForm.name"
                                           class="form-input"
                                           required>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">E-mail</label>
                                    <input type="email" 
                                           x-model="contactForm.email"
                                           class="form-input"
                                           required>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Telefoon</label>
                                    <input type="tel" 
                                           x-model="contactForm.phone"
                                           class="form-input">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Bericht</label>
                                    <textarea x-model="contactForm.message" 
                                              rows="3" 
                                              class="form-textarea"
                                              placeholder="Ik ben geïnteresseerd in deze woning..."
                                              required></textarea>
                                </div>
                                <div class="flex space-x-3">
                                    <button type="button" 
                                            @click="showContactForm = false"
                                            class="flex-1 btn-secondary justify-center">
                                        Annuleren
                                    </button>
                                    <button type="submit" 
                                            class="flex-1 btn-primary justify-center"
                                            x-bind:disabled="submittingContact"
                                            x-bind:class="submittingContact ? 'opacity-50 cursor-not-allowed' : ''">
                                        <span x-show="!submittingContact">Versturen</span>
                                        <span x-show="submittingContact">Versturen...</span>
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- Property Actions -->
                        @can('update', $property)
                            <div class="mt-6 pt-6 border-t border-gray-200">
                                <div class="flex space-x-3">
                                    <a href="{{ route('properties.edit', $property) }}" 
                                       class="flex-1 btn-secondary justify-center text-sm">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                        Bewerken
                                    </a>
                                </div>
                            </div>
                        @endcan
                    </div>
                </div>

                <!-- Property Info Card -->
                <div class="mt-6 bg-gray-50 rounded-lg p-6">
                    <h3 class="font-medium text-gray-900 mb-4">Woning Details</h3>
                    <dl class="space-y-3 text-sm">
                        <div class="flex justify-between">
                            <dt class="text-gray-600">Postcode</dt>
                            <dd class="font-medium text-gray-900">{{ $property->postal_code }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-gray-600">Type</dt>
                            <dd class="font-medium text-gray-900 capitalize">
                                @switch($property->property_type)
                                    @case('house') Eengezinswoning @break
                                    @case('apartment') Appartement @break
                                    @case('condo') Condominium @break
                                    @default {{ ucfirst($property->property_type) }}
                                @endswitch
                            </dd>
                        </div>
                        @if($property->square_meters)
                            <div class="flex justify-between">
                                <dt class="text-gray-600">Oppervlakte</dt>
                                <dd class="font-medium text-gray-900">{{ $property->square_meters }} m²</dd>
                            </div>
                        @endif
                        <div class="flex justify-between">
                            <dt class="text-gray-600">Geplaatst</dt>
                            <dd class="font-medium text-gray-900">{{ $property->created_at->format('d M Y') }}</dd>
                        </div>
                    </dl>
                </div>
            </div>
        </div>

        <!-- Related Properties -->
        @if($relatedProperties->count() > 0)
            <div class="mt-16 border-t border-gray-200 pt-16">
                <h2 class="text-2xl font-bold text-gray-900 mb-8">Vergelijkbare Woningen in {{ $property->city }}</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    @foreach($relatedProperties as $relatedProperty)
                        <a href="{{ route('properties.show', $relatedProperty) }}" class="group">
                            <div class="bg-white rounded-lg overflow-hidden shadow-sm border border-gray-200 group-hover:shadow-md transition-shadow">
                                <div class="aspect-w-16 aspect-h-9">
                                    @if($relatedProperty->main_image)
                                        <img src="{{ asset('storage/' . $relatedProperty->main_image) }}" 
                                             alt="{{ $relatedProperty->title }}" 
                                             class="w-full h-48 object-cover group-hover:scale-105 transition-transform">
                                    @else
                                        <div class="w-full h-48 bg-gray-200 flex items-center justify-center">
                                            <svg class="h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                                <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                            </svg>
                                        </div>
                                    @endif
                                </div>
                                <div class="p-4">
                                    <h3 class="font-medium text-gray-900 mb-1 line-clamp-1">{{ $relatedProperty->title }}</h3>
                                    <p class="text-sm text-gray-600 mb-2">{{ $relatedProperty->address }}, {{ $relatedProperty->city }}</p>
                                    <div class="flex items-center justify-between">
                                        <span class="text-lg font-bold text-primary-600">{{ $relatedProperty->formatted_price }}</span>
                                        <div class="flex space-x-2 text-xs text-gray-500">
                                            @if($relatedProperty->bedrooms)
                                                <span>{{ $relatedProperty->bedrooms }}k</span>
                                            @endif
                                            @if($relatedProperty->square_meters)
                                                <span>{{ $relatedProperty->square_meters }}m²</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    <!-- Lightbox Modal -->
    <div x-show="lightboxOpen" 
         x-transition:enter="transition ease-out duration-300" 
         x-transition:enter-start="opacity-0" 
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200" 
         x-transition:leave-start="opacity-100" 
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-black bg-opacity-90 z-50 flex items-center justify-center p-4"
         @keydown.escape.window="closeLightbox()"
         @click.self="closeLightbox()">
        
        <div class="relative max-w-5xl max-h-full">
            <!-- Close Button -->
            <button @click="closeLightbox()" 
                    class="absolute top-4 right-4 z-10 bg-black bg-opacity-50 text-white rounded-full p-2 hover:bg-opacity-70">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>

            <!-- Main Image -->
            <div class="relative">
                <img :src="`/storage/${property.images[lightboxIndex]}`" 
                     :alt="`${property.title} - Afbeelding ${lightboxIndex + 1}`"
                     class="max-w-full max-h-[80vh] object-contain mx-auto">
                
                <!-- Navigation Arrows -->
                <div x-show="property.images.length > 1">
                    <button @click="previousLightboxImage()" 
                            class="absolute left-4 top-1/2 transform -translate-y-1/2 bg-black bg-opacity-50 text-white rounded-full p-3 hover:bg-opacity-70">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </button>
                    
                    <button @click="nextLightboxImage()" 
                            class="absolute right-4 top-1/2 transform -translate-y-1/2 bg-black bg-opacity-50 text-white rounded-full p-3 hover:bg-opacity-70">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Image Counter -->
            <div class="absolute bottom-4 left-1/2 transform -translate-x-1/2 bg-black bg-opacity-50 text-white px-4 py-2 rounded-full">
                <span x-text="lightboxIndex + 1"></span> / <span x-text="property.images.length"></span>
            </div>

            <!-- Thumbnail Strip -->
            <div x-show="property.images.length > 1" class="absolute bottom-16 left-1/2 transform -translate-x-1/2 max-w-full overflow-x-auto">
                <div class="flex space-x-2 px-4">
                    <template x-for="(image, index) in property.images" :key="index">
                        <button @click="lightboxIndex = index"
                                class="flex-shrink-0 w-16 h-16 rounded-lg overflow-hidden transition-all"
                                :class="lightboxIndex === index ? 'ring-2 ring-white' : 'opacity-60 hover:opacity-100'">
                            <img :src="`/storage/${image}`" 
                                 :alt="`Thumbnail ${index + 1}`" 
                                 class="w-full h-full object-cover">
                        </button>
                    </template>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function propertyViewer(property) {
    return {
        property: property,
        currentImageIndex: 0,
        lightboxOpen: false,
        lightboxIndex: 0,
        showContactForm: false,
        submittingContact: false,
        contactForm: {
            name: '',
            email: '',
            phone: '',
            message: ''
        },

        nextImage() {
            if (this.property.images && this.property.images.length > 1) {
                this.currentImageIndex = (this.currentImageIndex + 1) % this.property.images.length;
            }
        },

        previousImage() {
            if (this.property.images && this.property.images.length > 1) {
                this.currentImageIndex = this.currentImageIndex === 0 
                    ? this.property.images.length - 1 
                    : this.currentImageIndex - 1;
            }
        },

        openLightbox(index) {
            if (this.property.images && this.property.images.length > 0) {
                this.lightboxIndex = index;
                this.lightboxOpen = true;
                document.body.style.overflow = 'hidden';
            }
        },

        closeLightbox() {
            this.lightboxOpen = false;
            document.body.style.overflow = 'auto';
        },

        nextLightboxImage() {
            if (this.property.images && this.property.images.length > 1) {
                this.lightboxIndex = (this.lightboxIndex + 1) % this.property.images.length;
            }
        },

        previousLightboxImage() {
            if (this.property.images && this.property.images.length > 1) {
                this.lightboxIndex = this.lightboxIndex === 0 
                    ? this.property.images.length - 1 
                    : this.lightboxIndex - 1;
            }
        },

        async submitContactForm() {
            if (this.submittingContact) return;
            
            this.submittingContact = true;
            
            try {
                // Here you would typically send the contact form to your backend
                console.log('Contact form submitted:', this.contactForm);
                
                // Simulate API call
                await new Promise(resolve => setTimeout(resolve, 1000));
                
                alert('Bedankt voor je bericht! De eigenaar neemt zo snel mogelijk contact met je op.');
                
                // Reset form
                this.contactForm = {
                    name: '',
                    email: '',
                    phone: '',
                    message: ''
                };
                this.showContactForm = false;
                
            } catch (error) {
                alert('Er is een fout opgetreden bij het versturen van je bericht. Probeer het opnieuw.');
            } finally {
                this.submittingContact = false;
            }
        }
    }
}
</script>

<style>
.scrollbar-hide {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
.scrollbar-hide::-webkit-scrollbar {
    display: none;
}

.line-clamp-1 {
    display: -webkit-box;
    -webkit-line-clamp: 1;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
@endsection
@extends('layouts.app')

@section('title', '- Woning Bewerken')

@section('content')
<div class="min-h-screen bg-gray-50 py-8" x-data="propertyEditForm({{ json_encode($property) }})">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Woning Bewerken</h1>
                    <p class="mt-2 text-gray-600">Pas de gegevens van je woning aan</p>
                </div>
                <div class="flex items-center space-x-4">
                    <!-- Status Toggle -->
                    <form action="{{ route('properties.toggle-status', $property) }}" method="POST" class="inline">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="flex items-center px-3 py-2 rounded-md text-sm font-medium transition-colors
                            {{ $property->status === 'active' ? 'bg-green-100 text-green-800 hover:bg-green-200' : 'bg-yellow-100 text-yellow-800 hover:bg-yellow-200' }}">
                            <div class="w-2 h-2 rounded-full mr-2 {{ $property->status === 'active' ? 'bg-green-600' : 'bg-yellow-600' }}"></div>
                            {{ $property->status === 'active' ? 'Actief' : 'Concept' }}
                        </button>
                    </form>
                    
                    <a href="{{ route('properties.show', $property) }}" class="btn-secondary">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                        Bekijk Advertentie
                    </a>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Main Form -->
            <div class="lg:col-span-2 space-y-8">
                <!-- Property Details Card -->
                <div class="bg-white shadow-lg rounded-lg overflow-hidden">
                    <form action="{{ route('properties.update', $property) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        
                        <div class="px-6 py-8">
                            <h2 class="text-xl font-semibold text-gray-900 mb-6">Eigenschap Details</h2>
                            
                            <div class="space-y-6">
                                <!-- Title -->
                                <div>
                                    <label for="title" class="block text-sm font-medium text-gray-700 mb-2">Titel *</label>
                                    <input type="text" 
                                           id="title" 
                                           name="title" 
                                           value="{{ old('title', $property->title) }}" 
                                           required 
                                           x-model="formData.title"
                                           class="form-input">
                                    @error('title')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Description -->
                                <div>
                                    <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Beschrijving *</label>
                                    <textarea id="description" 
                                              name="description" 
                                              rows="6" 
                                              required 
                                              x-model="formData.description"
                                              class="form-textarea">{{ old('description', $property->description) }}</textarea>
                                    @error('description')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Property Type & Price -->
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label for="property_type" class="block text-sm font-medium text-gray-700 mb-2">Woningtype *</label>
                                        <select id="property_type" 
                                                name="property_type" 
                                                required 
                                                x-model="formData.property_type"
                                                class="form-select">
                                            <option value="">Selecteer type</option>
                                            <option value="house" {{ old('property_type', $property->property_type) == 'house' ? 'selected' : '' }}>Eengezinswoning</option>
                                            <option value="apartment" {{ old('property_type', $property->property_type) == 'apartment' ? 'selected' : '' }}>Appartement</option>
                                            <option value="condo" {{ old('property_type', $property->property_type) == 'condo' ? 'selected' : '' }}>Condominium</option>
                                            <option value="other" {{ old('property_type', $property->property_type) == 'other' ? 'selected' : '' }}>Anders</option>
                                        </select>
                                        @error('property_type')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div>
                                        <label for="price" class="block text-sm font-medium text-gray-700 mb-2">Vraagprijs (€) *</label>
                                        <input type="number" 
                                               id="price" 
                                               name="price" 
                                               value="{{ old('price', $property->price) }}" 
                                               required 
                                               min="0" 
                                               step="1000"
                                               x-model="formData.price"
                                               class="form-input">
                                        @error('price')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>

                                <!-- Address Fields -->
                                <div>
                                    <label for="address" class="block text-sm font-medium text-gray-700 mb-2">Adres *</label>
                                    <input type="text" 
                                           id="address" 
                                           name="address" 
                                           value="{{ old('address', $property->address) }}" 
                                           required 
                                           x-model="formData.address"
                                           class="form-input">
                                    @error('address')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label for="city" class="block text-sm font-medium text-gray-700 mb-2">Plaats *</label>
                                        <input type="text" 
                                               id="city" 
                                               name="city" 
                                               value="{{ old('city', $property->city) }}" 
                                               required 
                                               x-model="formData.city"
                                               class="form-input">
                                        @error('city')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div>
                                        <label for="postal_code" class="block text-sm font-medium text-gray-700 mb-2">Postcode *</label>
                                        <input type="text" 
                                               id="postal_code" 
                                               name="postal_code" 
                                               value="{{ old('postal_code', $property->postal_code) }}" 
                                               required 
                                               pattern="[0-9]{4}\s?[A-Za-z]{2}"
                                               x-model="formData.postal_code"
                                               class="form-input">
                                        @error('postal_code')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>

                                <!-- Property Specs -->
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                    <div>
                                        <label for="bedrooms" class="block text-sm font-medium text-gray-700 mb-2">Slaapkamers</label>
                                        <input type="number" 
                                               id="bedrooms" 
                                               name="bedrooms" 
                                               value="{{ old('bedrooms', $property->bedrooms) }}" 
                                               min="0" 
                                               x-model="formData.bedrooms"
                                               class="form-input">
                                        @error('bedrooms')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div>
                                        <label for="bathrooms" class="block text-sm font-medium text-gray-700 mb-2">Badkamers</label>
                                        <input type="number" 
                                               id="bathrooms" 
                                               name="bathrooms" 
                                               value="{{ old('bathrooms', $property->bathrooms) }}" 
                                               min="0" 
                                               step="0.5"
                                               x-model="formData.bathrooms"
                                               class="form-input">
                                        @error('bathrooms')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div>
                                        <label for="square_meters" class="block text-sm font-medium text-gray-700 mb-2">Oppervlakte (m²)</label>
                                        <input type="number" 
                                               id="square_meters" 
                                               name="square_meters" 
                                               value="{{ old('square_meters', $property->square_meters) }}" 
                                               min="0"
                                               x-model="formData.square_meters"
                                               class="form-input">
                                        @error('square_meters')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>

                                <!-- Virtual Tour -->
                                <div>
                                    <label for="virtual_tour_url" class="block text-sm font-medium text-gray-700 mb-2">Virtuele Tour URL (optioneel)</label>
                                    <input type="url" 
                                           id="virtual_tour_url" 
                                           name="virtual_tour_url" 
                                           value="{{ old('virtual_tour_url', $property->virtual_tour_url) }}" 
                                           x-model="formData.virtual_tour_url"
                                           class="form-input">
                                    <p class="mt-1 text-xs text-gray-500">Link naar een 360° tour of virtuele bezichtiging</p>
                                    @error('virtual_tour_url')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Features Section -->
                        <div class="border-t border-gray-200 px-6 py-8">
                            <h3 class="text-lg font-medium text-gray-900 mb-6">Voorzieningen & Kenmerken</h3>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                <template x-for="feature in availableFeatures" :key="feature.key">
                                    <label class="relative flex items-start p-4 border rounded-lg cursor-pointer hover:bg-gray-50"
                                           :class="selectedFeatures.includes(feature.key) ? 'border-primary-500 bg-primary-50' : 'border-gray-300'">
                                        <div class="flex items-center h-5">
                                            <input type="checkbox" 
                                                   name="features[]" 
                                                   :value="feature.key"
                                                   x-model="selectedFeatures"
                                                   class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded">
                                        </div>
                                        <div class="ml-3 text-sm">
                                            <div class="font-medium text-gray-700" x-text="feature.label"></div>
                                            <div class="text-gray-500" x-text="feature.description"></div>
                                        </div>
                                    </label>
                                </template>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="px-6 py-4 bg-gray-50 flex justify-between items-center">
                            <a href="{{ route('properties.show', $property) }}" class="text-gray-600 hover:text-gray-800">Annuleren</a>
                            <button type="submit" class="btn-primary">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                Wijzigingen Opslaan
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="lg:col-span-1 space-y-6">
                <!-- Current Images -->
                <div class="bg-white shadow-lg rounded-lg overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Huidige Foto's</h3>
                    </div>
                    
                    <div class="p-6">
                        @if($property->images && count($property->images) > 0)
                            <div class="space-y-4">
                                @foreach($property->images as $index => $image)
                                    <div class="relative group">
                                        <img src="{{ asset('storage/' . $image) }}" 
                                             alt="Property image {{ $index + 1 }}" 
                                             class="w-full h-24 object-cover rounded-lg">
                                        
                                        <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-40 transition-all duration-200 rounded-lg flex items-center justify-center">
                                            <form action="{{ route('properties.remove-image', $property) }}" 
                                                  method="POST" 
                                                  class="opacity-0 group-hover:opacity-100 transition-opacity"
                                                  onsubmit="return confirm('Weet je zeker dat je deze foto wilt verwijderen?')">
                                                @csrf
                                                @method('DELETE')
                                                <input type="hidden" name="image_index" value="{{ $index }}">
                                                <button type="submit" class="bg-red-600 hover:bg-red-700 text-white rounded-full p-2">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                    </svg>
                                                </button>
                                            </form>
                                        </div>
                                        
                                        @if($index === 0)
                                            <div class="absolute bottom-2 left-2 bg-primary-600 text-white text-xs px-2 py-1 rounded">
                                                Hoofdfoto
                                            </div>
                                        @endif
                                        
                                        <div class="absolute bottom-2 right-2 bg-black bg-opacity-50 text-white text-xs px-1 rounded">
                                            {{ $index + 1 }}
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-8">
                                <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                    <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                <p class="mt-2 text-sm text-gray-500">Geen foto's geüpload</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Add New Images -->
                <div class="bg-white shadow-lg rounded-lg overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Nieuwe Foto's Toevoegen</h3>
                    </div>
                    
                    <form action="{{ route('properties.update', $property) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        
                        <div class="p-6">
                            <div class="border-2 border-dashed border-gray-300 rounded-lg p-4 text-center hover:border-primary-400 transition-colors"
                                 x-on:dragover.prevent="$event.dataTransfer.dropEffect = 'copy'"
                                 x-on:drop.prevent="handleFileDrop($event)"
                                 x-on:dragenter.prevent="$el.classList.add('border-primary-400', 'bg-primary-50')"
                                 x-on:dragleave.prevent="$el.classList.remove('border-primary-400', 'bg-primary-50')">
                                
                                <svg class="mx-auto h-8 w-8 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                    <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                
                                <div class="mt-2">
                                    <label for="new_images" class="cursor-pointer">
                                        <span class="text-sm font-medium text-gray-900">
                                            Sleep foto's hier of 
                                            <span class="text-primary-600 hover:text-primary-500">browse</span>
                                        </span>
                                        <input id="new_images" name="images[]" type="file" class="sr-only" multiple accept="image/*" x-on:change="handleFileSelect($event)">
                                    </label>
                                    <p class="mt-1 text-xs text-gray-500">Maximaal 10 foto's, 5MB per foto</p>
                                </div>
                            </div>

                            <!-- New Images Preview -->
                            <div x-show="newImagePreviews.length > 0" class="mt-4 space-y-2">
                                <template x-for="(image, index) in newImagePreviews" :key="index">
                                    <div class="flex items-center space-x-3 p-2 bg-gray-50 rounded">
                                        <img :src="image.url" :alt="`New image ${index + 1}`" class="h-12 w-12 object-cover rounded">
                                        <div class="flex-1 text-sm text-gray-700" x-text="image.name"></div>
                                        <button type="button" 
                                                x-on:click="removeNewImage(index)"
                                                class="text-red-600 hover:text-red-800">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    </div>
                                </template>
                            </div>

                            <div x-show="newImagePreviews.length > 0" class="mt-4">
                                <button type="submit" class="w-full btn-primary">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                    </svg>
                                    Foto's Uploaden
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Quick Actions -->
                <div class="bg-white shadow-lg rounded-lg overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Acties</h3>
                    </div>
                    
                    <div class="p-6 space-y-3">
                        <a href="{{ route('properties.show', $property) }}" class="w-full btn-secondary justify-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            Bekijk Advertentie
                        </a>

                        <form action="{{ route('properties.toggle-status', $property) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="w-full btn-secondary justify-center">
                                @if($property->status === 'active')
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L8.464 8.464M9.878 9.878l4.242 4.242M8.464 8.464l1.414 1.414M8.464 8.464L5.636 5.636M15.121 15.121l1.414 1.414M15.121 15.121L18.364 18.364" />
                                    </svg>
                                    Verbergen
                                @else
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                    Publiceren
                                @endif
                            </button>
                        </form>

                        <form action="{{ route('properties.destroy', $property) }}" 
                              method="POST" 
                              onsubmit="return confirm('Weet je zeker dat je deze woning wilt verwijderen? Deze actie kan niet ongedaan gemaakt worden.')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="w-full btn-danger justify-center">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                                Woning Verwijderen
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function propertyEditForm(property) {
    return {
        formData: {
            title: property.title || '',
            description: property.description || '',
            property_type: property.property_type || '',
            price: property.price || '',
            address: property.address || '',
            city: property.city || '',
            postal_code: property.postal_code || '',
            bedrooms: property.bedrooms || 0,
            bathrooms: property.bathrooms || 0,
            square_meters: property.square_meters || '',
            virtual_tour_url: property.virtual_tour_url || ''
        },
        selectedFeatures: property.features || [],
        newImagePreviews: [],
        availableFeatures: [
            { key: 'garden', label: 'Tuin', description: 'Eigen tuin' },
            { key: 'parking', label: 'Parkeerplaats', description: 'Eigen parkeerplaats' },
            { key: 'garage', label: 'Garage', description: 'Eigen garage' },
            { key: 'balcony', label: 'Balkon', description: 'Balkon of terras' },
            { key: 'elevator', label: 'Lift', description: 'Lift in het gebouw' },
            { key: 'fireplace', label: 'Open haard', description: 'Open haard aanwezig' },
            { key: 'air_conditioning', label: 'Airconditioning', description: 'Airco systeem' },
            { key: 'storage', label: 'Berging', description: 'Aparte berging' },
            { key: 'solar_panels', label: 'Zonnepanelen', description: 'Zonnepanelen geïnstalleerd' },
            { key: 'energy_efficient', label: 'Energiezuinig', description: 'Energielabel A of B' },
            { key: 'furnished', label: 'Gemeubileerd', description: 'Volledig gemeubileerd' },
            { key: 'pets_allowed', label: 'Huisdieren', description: 'Huisdieren toegestaan' }
        ],

        handleFileSelect(event) {
            this.processFiles(event.target.files);
        },

        handleFileDrop(event) {
            event.currentTarget.classList.remove('border-primary-400', 'bg-primary-50');
            this.processFiles(event.dataTransfer.files);
        },

        processFiles(files) {
            const maxFiles = 10;
            const maxSize = 5 * 1024 * 1024; // 5MB

            Array.from(files).forEach((file, index) => {
                if (this.newImagePreviews.length >= maxFiles) {
                    alert(`Maximaal ${maxFiles} foto's toegestaan.`);
                    return;
                }

                if (file.size > maxSize) {
                    alert(`${file.name} is te groot. Maximale bestandsgrootte is 5MB.`);
                    return;
                }

                if (!file.type.startsWith('image/')) {
                    alert(`${file.name} is geen geldige afbeelding.`);
                    return;
                }

                const reader = new FileReader();
                reader.onload = (e) => {
                    this.newImagePreviews.push({
                        file: file,
                        url: e.target.result,
                        name: file.name
                    });
                };
                reader.readAsDataURL(file);
            });
        },

        removeNewImage(index) {
            this.newImagePreviews.splice(index, 1);
        }
    }
}
</script>
@endsection
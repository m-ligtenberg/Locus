@extends('layouts.app')

@section('title', '- Nieuwe Woning Toevoegen')

@section('content')
<div class="min-h-screen bg-gray-50 py-8" x-data="propertyCreateWizard()">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Woning Toevoegen</h1>
            <p class="mt-2 text-gray-600">Plaats je woning op de markt met een paar eenvoudige stappen</p>
        </div>

        <!-- Progress Bar -->
        <div class="mb-8">
            <div class="flex items-center">
                <template x-for="(step, index) in steps" :key="index">
                    <div class="flex items-center" :class="index > 0 ? 'ml-8' : ''">
                        <div class="flex items-center relative">
                            <div class="rounded-full transition duration-500 ease-in-out h-8 w-8 flex items-center justify-center text-sm font-medium"
                                 :class="currentStep >= index + 1 ? 'bg-primary-600 text-white' : 'bg-gray-300 text-gray-600'">
                                <span x-text="index + 1"></span>
                            </div>
                            <div class="absolute top-0 -ml-10 text-center mt-10 w-32 text-xs font-medium text-gray-600">
                                <span x-text="step.title"></span>
                            </div>
                        </div>
                        <div x-show="index < steps.length - 1" class="flex-auto border-t-2 transition duration-500 ease-in-out"
                             :class="currentStep > index + 1 ? 'border-primary-600' : 'border-gray-300'"></div>
                    </div>
                </template>
            </div>
        </div>

        <!-- Form Card -->
        <div class="bg-white shadow-lg rounded-lg overflow-hidden">
            <form action="{{ route('properties.store') }}" method="POST" enctype="multipart/form-data" x-ref="propertyForm">
                @csrf

                <!-- Step 1: Property Details -->
                <div x-show="currentStep === 1" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100">
                    <div class="px-6 py-8">
                        <h2 class="text-xl font-semibold text-gray-900 mb-6">Eigenschap Details</h2>
                        
                        <div class="grid grid-cols-1 gap-6">
                            <!-- Title -->
                            <div>
                                <label for="title" class="block text-sm font-medium text-gray-700 mb-2">Titel *</label>
                                <input type="text" 
                                       id="title" 
                                       name="title" 
                                       value="{{ old('title') }}" 
                                       required 
                                       x-model="formData.title"
                                       class="form-input"
                                       placeholder="Bijv. Schitterende eengezinswoning in Amsterdam">
                                @error('title')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Description -->
                            <div>
                                <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Beschrijving *</label>
                                <textarea id="description" 
                                          name="description" 
                                          rows="4" 
                                          required 
                                          x-model="formData.description"
                                          class="form-textarea"
                                          placeholder="Beschrijf je woning uitgebreid. Vertel over de locatie, bijzonderheden, en wat de woning uniek maakt...">{{ old('description') }}</textarea>
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
                                        <option value="house" {{ old('property_type') == 'house' ? 'selected' : '' }}>Eengezinswoning</option>
                                        <option value="apartment" {{ old('property_type') == 'apartment' ? 'selected' : '' }}>Appartement</option>
                                        <option value="condo" {{ old('property_type') == 'condo' ? 'selected' : '' }}>Condominium</option>
                                        <option value="other" {{ old('property_type') == 'other' ? 'selected' : '' }}>Anders</option>
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
                                           value="{{ old('price') }}" 
                                           required 
                                           min="0" 
                                           step="1000"
                                           x-model="formData.price"
                                           class="form-input"
                                           placeholder="299000">
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
                                       value="{{ old('address') }}" 
                                       required 
                                       x-model="formData.address"
                                       class="form-input"
                                       placeholder="Voorbeeldstraat 123">
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
                                           value="{{ old('city') }}" 
                                           required 
                                           x-model="formData.city"
                                           class="form-input"
                                           placeholder="Amsterdam">
                                    @error('city')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="postal_code" class="block text-sm font-medium text-gray-700 mb-2">Postcode *</label>
                                    <input type="text" 
                                           id="postal_code" 
                                           name="postal_code" 
                                           value="{{ old('postal_code') }}" 
                                           required 
                                           x-model="formData.postal_code"
                                           pattern="[0-9]{4}\s?[A-Za-z]{2}"
                                           class="form-input"
                                           placeholder="1234 AB">
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
                                           value="{{ old('bedrooms', 0) }}" 
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
                                           value="{{ old('bathrooms', 0) }}" 
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
                                           value="{{ old('square_meters') }}" 
                                           min="0"
                                           x-model="formData.square_meters"
                                           class="form-input">
                                    @error('square_meters')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 2: Features -->
                <div x-show="currentStep === 2" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100">
                    <div class="px-6 py-8">
                        <h2 class="text-xl font-semibold text-gray-900 mb-6">Voorzieningen & Kenmerken</h2>
                        
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

                        <!-- Virtual Tour URL -->
                        <div class="mt-8">
                            <label for="virtual_tour_url" class="block text-sm font-medium text-gray-700 mb-2">Virtuele Tour URL (optioneel)</label>
                            <input type="url" 
                                   id="virtual_tour_url" 
                                   name="virtual_tour_url" 
                                   value="{{ old('virtual_tour_url') }}" 
                                   x-model="formData.virtual_tour_url"
                                   class="form-input"
                                   placeholder="https://matterport.com/discover/space/...">
                            <p class="mt-1 text-xs text-gray-500">Link naar een 360° tour of virtuele bezichtiging</p>
                            @error('virtual_tour_url')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Step 3: Images -->
                <div x-show="currentStep === 3" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100">
                    <div class="px-6 py-8">
                        <h2 class="text-xl font-semibold text-gray-900 mb-6">Foto's Uploaden</h2>
                        
                        <div class="space-y-6">
                            <!-- Upload Area -->
                            <div class="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center hover:border-primary-400 transition-colors"
                                 x-on:dragover.prevent="$event.dataTransfer.dropEffect = 'copy'"
                                 x-on:drop.prevent="handleFileDrop($event)"
                                 x-on:dragenter.prevent="$el.classList.add('border-primary-400', 'bg-primary-50')"
                                 x-on:dragleave.prevent="$el.classList.remove('border-primary-400', 'bg-primary-50')">
                                <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                    <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                <div class="mt-4">
                                    <label for="images" class="cursor-pointer">
                                        <span class="mt-2 block text-sm font-medium text-gray-900">
                                            Sleep foto's hierheen of 
                                            <span class="text-primary-600 hover:text-primary-500">browse bestanden</span>
                                        </span>
                                        <input id="images" name="images[]" type="file" class="sr-only" multiple accept="image/*" x-on:change="handleFileSelect($event)">
                                    </label>
                                    <p class="mt-1 text-xs text-gray-500">PNG, JPG, GIF tot 5MB per foto. Maximaal 10 foto's.</p>
                                </div>
                            </div>

                            <!-- Image Preview Grid -->
                            <div x-show="previewImages.length > 0" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                                <template x-for="(image, index) in previewImages" :key="index">
                                    <div class="relative group">
                                        <img :src="image.url" :alt="`Preview ${index + 1}`" class="h-24 w-full object-cover rounded-lg">
                                        <button type="button" 
                                                x-on:click="removePreviewImage(index)"
                                                class="absolute top-1 right-1 bg-red-600 text-white rounded-full h-6 w-6 flex items-center justify-center text-xs opacity-0 group-hover:opacity-100 transition-opacity hover:bg-red-700">
                                            ×
                                        </button>
                                        <div class="absolute bottom-1 left-1 bg-black bg-opacity-50 text-white text-xs px-1 rounded" x-text="`${index + 1}`"></div>
                                    </div>
                                </template>
                            </div>

                            <p class="text-sm text-gray-600">
                                <strong>Tip:</strong> De eerste foto wordt gebruikt als hoofdafbeelding. Sleep foto's om de volgorde te wijzigen.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Step 4: Preview -->
                <div x-show="currentStep === 4" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100">
                    <div class="px-6 py-8">
                        <h2 class="text-xl font-semibold text-gray-900 mb-6">Voorbeeld van je Advertentie</h2>
                        
                        <div class="border rounded-lg overflow-hidden">
                            <!-- Preview Header -->
                            <div x-show="previewImages.length > 0" class="relative h-64 bg-gray-200">
                                <img x-show="previewImages.length > 0" :src="previewImages[0]?.url" alt="Main property image" class="w-full h-full object-cover">
                                <div class="absolute bottom-4 right-4 bg-black bg-opacity-50 text-white px-2 py-1 rounded text-sm" x-text="`${previewImages.length} foto's`"></div>
                            </div>

                            <!-- Preview Content -->
                            <div class="p-6">
                                <div class="flex justify-between items-start mb-4">
                                    <div>
                                        <h3 class="text-2xl font-bold text-gray-900" x-text="formData.title || 'Titel van de woning'"></h3>
                                        <p class="text-gray-600" x-text="`${formData.address || 'Adres'}, ${formData.city || 'Plaats'}`"></p>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-2xl font-bold text-primary-600" x-text="formData.price ? '€' + parseInt(formData.price).toLocaleString('nl-NL') : '€ ---'"></div>
                                        <div class="text-sm text-gray-500" x-text="formData.property_type ? propertyTypeLabels[formData.property_type] : 'Type woning'"></div>
                                    </div>
                                </div>

                                <!-- Property specs -->
                                <div class="flex space-x-6 text-sm text-gray-600 mb-4">
                                    <span x-show="formData.bedrooms" x-text="`${formData.bedrooms} slaapkamers`"></span>
                                    <span x-show="formData.bathrooms" x-text="`${formData.bathrooms} badkamers`"></span>
                                    <span x-show="formData.square_meters" x-text="`${formData.square_meters} m²`"></span>
                                </div>

                                <!-- Description preview -->
                                <div class="prose text-gray-700 mb-4">
                                    <p x-text="formData.description || 'Beschrijving van de woning...'"></p>
                                </div>

                                <!-- Features preview -->
                                <div x-show="selectedFeatures.length > 0">
                                    <h4 class="font-medium text-gray-900 mb-2">Voorzieningen</h4>
                                    <div class="flex flex-wrap gap-2">
                                        <template x-for="featureKey in selectedFeatures" :key="featureKey">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-primary-100 text-primary-800" 
                                                  x-text="availableFeatures.find(f => f.key === featureKey)?.label"></span>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Publishing options -->
                        <div class="mt-8 p-4 bg-gray-50 rounded-lg">
                            <h4 class="font-medium text-gray-900 mb-3">Publicatie Opties</h4>
                            <div class="space-y-2">
                                <label class="flex items-center">
                                    <input type="radio" name="publish_option" value="draft" checked class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300">
                                    <span class="ml-2 text-sm text-gray-700">Opslaan als concept (niet zichtbaar voor bezoekers)</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="radio" name="publish_option" value="publish" class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300">
                                    <span class="ml-2 text-sm text-gray-700">Direct publiceren (zichtbaar voor alle bezoekers)</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Navigation Buttons -->
                <div class="px-6 py-4 bg-gray-50 flex justify-between">
                    <button type="button" 
                            x-show="currentStep > 1"
                            x-on:click="previousStep()"
                            class="btn-secondary">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                        Vorige
                    </button>

                    <div x-show="currentStep < 4">
                        <button type="button" 
                                x-on:click="nextStep()"
                                x-bind:disabled="!canProceed()"
                                class="btn-primary"
                                :class="!canProceed() ? 'opacity-50 cursor-not-allowed' : ''">
                            Volgende
                            <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </button>
                    </div>

                    <div x-show="currentStep === 4" class="space-x-3">
                        <button type="submit" 
                                name="status" 
                                value="draft"
                                class="btn-secondary">
                            Opslaan als Concept
                        </button>
                        <button type="submit" 
                                name="status" 
                                value="active"
                                class="btn-primary">
                            Publiceren
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function propertyCreateWizard() {
    return {
        currentStep: 1,
        steps: [
            { title: 'Details' },
            { title: 'Kenmerken' },
            { title: 'Foto\'s' },
            { title: 'Voorbeeld' }
        ],
        formData: {
            title: '',
            description: '',
            property_type: '',
            price: '',
            address: '',
            city: '',
            postal_code: '',
            bedrooms: 0,
            bathrooms: 0,
            square_meters: '',
            virtual_tour_url: ''
        },
        selectedFeatures: [],
        previewImages: [],
        propertyTypeLabels: {
            'house': 'Eengezinswoning',
            'apartment': 'Appartement',
            'condo': 'Condominium',
            'other': 'Anders'
        },
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

        nextStep() {
            if (this.canProceed()) {
                this.currentStep++;
            }
        },

        previousStep() {
            if (this.currentStep > 1) {
                this.currentStep--;
            }
        },

        canProceed() {
            switch (this.currentStep) {
                case 1:
                    return this.formData.title.trim() && 
                           this.formData.description.trim() && 
                           this.formData.property_type && 
                           this.formData.price && 
                           this.formData.address.trim() && 
                           this.formData.city.trim() && 
                           this.formData.postal_code.trim();
                case 2:
                    return true; // Features are optional
                case 3:
                    return true; // Images are optional but recommended
                default:
                    return true;
            }
        },

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
                if (this.previewImages.length >= maxFiles) {
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
                    this.previewImages.push({
                        file: file,
                        url: e.target.result,
                        name: file.name
                    });
                };
                reader.readAsDataURL(file);
            });
        },

        removePreviewImage(index) {
            this.previewImages.splice(index, 1);
        }
    }
}
</script>
@endsection
@extends('layouts.app')

@section('title', '- Nieuwe Dienst Aanmaken')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header -->
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Nieuwe Dienst Aanmaken</h1>
                <p class="mt-2 text-gray-600">Voeg een nieuwe dienst toe aan de marktplaats.</p>
            </div>
            <a href="{{ route('services.index') }}" 
               class="btn-secondary">
                ← Terug naar Diensten
            </a>
        </div>
    </div>

    <!-- Form Card -->
    <div class="card">
        <form action="{{ route('services.store') }}" 
              method="POST" 
              enctype="multipart/form-data"
              x-data="serviceForm()" 
              @submit="submitForm">
            @csrf

            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">Dienst Informatie</h2>
            </div>

            <div class="p-6 space-y-6">
                <!-- Basic Information -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Service Name -->
                    <div class="md:col-span-2">
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                            Dienst Naam <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               id="name" 
                               name="name" 
                               required
                               class="form-input @error('name') border-red-300 @enderror"
                               placeholder="Bijv. Energielabel (EPC) Certificaat"
                               value="{{ old('name') }}"
                               x-model="formData.name">
                        @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Category -->
                    <div>
                        <label for="category" class="block text-sm font-medium text-gray-700 mb-2">
                            Categorie <span class="text-red-500">*</span>
                        </label>
                        <select id="category" 
                                name="category" 
                                required
                                class="form-select @error('category') border-red-300 @enderror"
                                x-model="formData.category">
                            <option value="">Selecteer een categorie</option>
                            @foreach($categories as $key => $category)
                            <option value="{{ $key }}" {{ old('category') === $key ? 'selected' : '' }}>
                                {{ $category['name'] }}
                            </option>
                            @endforeach
                        </select>
                        @error('category')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Price -->
                    <div>
                        <label for="price" class="block text-sm font-medium text-gray-700 mb-2">
                            Prijs (€) <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="text-gray-500 text-sm">€</span>
                            </div>
                            <input type="number" 
                                   id="price" 
                                   name="price" 
                                   required
                                   min="0" 
                                   step="0.01"
                                   class="form-input pl-7 @error('price') border-red-300 @enderror"
                                   placeholder="0.00"
                                   value="{{ old('price') }}"
                                   x-model="formData.price">
                        </div>
                        @error('price')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Description -->
                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                        Beschrijving <span class="text-red-500">*</span>
                    </label>
                    <textarea id="description" 
                              name="description" 
                              rows="5" 
                              required
                              class="form-textarea @error('description') border-red-300 @enderror"
                              placeholder="Geef een uitgebreide beschrijving van de dienst, wat er inbegrepen is, en hoe het proces verloopt..."
                              x-model="formData.description">{{ old('description') }}</textarea>
                    <div class="mt-1 text-sm text-gray-500" x-show="formData.description.length > 0">
                        <span x-text="formData.description.length"></span> / 1000 karakters
                    </div>
                    @error('description')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Requirements -->
                <div x-data="requirementsManager()">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Benodigdheden
                    </label>
                    <p class="text-sm text-gray-500 mb-3">
                        Welke documenten of informatie moet de klant aanleveren voor deze dienst?
                    </p>
                    
                    <div class="space-y-3" x-show="requirements.length > 0">
                        <template x-for="(requirement, index) in requirements" :key="index">
                            <div class="flex items-center space-x-3 p-3 bg-gray-50 rounded-lg">
                                <input type="text" 
                                       x-model="requirement.text"
                                       :name="'requirements[' + index + ']'"
                                       class="flex-1 form-input text-sm"
                                       placeholder="Bijv. Kopie identiteitsbewijs">
                                <button type="button" 
                                        @click="removeRequirement(index)"
                                        class="text-red-600 hover:text-red-800">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </div>
                        </template>
                    </div>

                    <button type="button" 
                            @click="addRequirement()"
                            class="btn-secondary text-sm">
                        <svg class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                        </svg>
                        Voeg Benodigdheid Toe
                    </button>
                </div>

                <!-- Status -->
                <div>
                    <div class="flex items-center">
                        <input type="checkbox" 
                               id="is_active" 
                               name="is_active" 
                               value="1"
                               class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded"
                               {{ old('is_active', true) ? 'checked' : '' }}>
                        <label for="is_active" class="ml-2 block text-sm text-gray-700">
                            Dienst direct activeren en beschikbaar maken voor klanten
                        </label>
                    </div>
                    @error('is_active')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Preview Section -->
                <div class="border-t border-gray-200 pt-6" x-show="formData.name || formData.description || formData.price">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Preview</h3>
                    <div class="card max-w-sm mx-auto">
                        <!-- Service Preview -->
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
                                    <div class="text-2xl mb-1" x-text="getCategoryIcon(formData.category)">🏠</div>
                                    <div class="text-xs text-primary-600 font-medium" 
                                         x-text="getCategoryName(formData.category)">
                                        Selecteer categorie
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="p-4">
                            <div class="flex justify-between items-start mb-2">
                                <h4 class="font-medium text-gray-900 flex-1" 
                                    x-text="formData.name || 'Dienst Naam'"></h4>
                                <div class="text-sm font-bold text-primary-600 ml-2" 
                                     x-text="formatPrice(formData.price)">€0,00</div>
                            </div>
                            <p class="text-gray-600 text-xs line-clamp-2" 
                               x-text="formData.description || 'Dienst beschrijving...'"></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-between">
                <a href="{{ route('services.index') }}" class="btn-secondary">
                    Annuleren
                </a>
                
                <div class="space-x-3">
                    <button type="button" 
                            @click="resetForm()" 
                            class="btn-secondary">
                        Reset
                    </button>
                    <button type="submit" 
                            class="btn-primary"
                            x-bind:disabled="!isFormValid()"
                            x-bind:class="!isFormValid() ? 'opacity-50 cursor-not-allowed' : ''">
                        <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Dienst Aanmaken
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Scripts -->
<script>
function serviceForm() {
    return {
        formData: {
            name: '{{ old('name', '') }}',
            category: '{{ old('category', '') }}',
            price: '{{ old('price', '') }}',
            description: '{{ old('description', '') }}'
        },
        
        categories: @json($categories),
        
        categoryIcons: {
            'notary': '⚖️',
            'epc-certificate': '🏷️',
            'property-valuation': '📊',
            'mortgage-advice': '🏦',
            'home-inspection': '🔍',
            'legal-advice': '👨‍💼',
        },

        getCategoryIcon(categoryKey) {
            return this.categoryIcons[categoryKey] || '🏠';
        },

        getCategoryName(categoryKey) {
            return this.categories[categoryKey]?.name || 'Selecteer categorie';
        },

        formatPrice(price) {
            if (!price) return '€0,00';
            return '€' + parseFloat(price).toFixed(2).replace('.', ',');
        },

        isFormValid() {
            return this.formData.name && 
                   this.formData.category && 
                   this.formData.price && 
                   this.formData.description;
        },

        resetForm() {
            if (confirm('Weet u zeker dat u het formulier wilt resetten? Alle ingevulde gegevens gaan verloren.')) {
                this.formData = {
                    name: '',
                    category: '',
                    price: '',
                    description: ''
                };
                // Reset form fields
                document.querySelector('form').reset();
            }
        },

        submitForm(event) {
            if (!this.isFormValid()) {
                event.preventDefault();
                alert('Vul alle verplichte velden in voordat u het formulier verzendt.');
                return false;
            }
        }
    }
}

function requirementsManager() {
    return {
        requirements: @json(array_map(function($req) { return ['text' => $req]; }, old('requirements', []))),

        addRequirement() {
            this.requirements.push({ text: '' });
        },

        removeRequirement(index) {
            this.requirements.splice(index, 1);
        }
    }
}
</script>

<style>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
@endsection
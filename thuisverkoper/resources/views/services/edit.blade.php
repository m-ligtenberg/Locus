@extends('layouts.app')

@section('title', '- ' . $service->name . ' Bewerken')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header -->
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Dienst Bewerken</h1>
                <p class="mt-2 text-gray-600">{{ $service->name }}</p>
            </div>
            <div class="flex space-x-3">
                <a href="{{ route('services.show', $service) }}" 
                   class="btn-secondary">
                    Bekijken
                </a>
                <a href="{{ route('services.index') }}" 
                   class="btn-secondary">
                    ← Terug naar Diensten
                </a>
            </div>
        </div>
    </div>

    <!-- Form Card -->
    <div class="card">
        <form action="{{ route('services.update', $service) }}" 
              method="POST" 
              enctype="multipart/form-data"
              x-data="serviceEditForm()" 
              @submit="submitForm">
            @csrf
            @method('PUT')

            <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">Dienst Informatie</h2>
                    <p class="text-sm text-gray-500 mt-1">Laatst bijgewerkt: {{ $service->updated_at->format('d-m-Y H:i') }}</p>
                </div>
                
                <!-- Status Toggle -->
                <form action="{{ route('services.toggle-status', $service) }}" method="POST" class="inline">
                    @csrf
                    @method('PATCH')
                    <button type="submit" 
                            class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium {{ $service->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                        <span class="w-2 h-2 rounded-full {{ $service->is_active ? 'bg-green-400' : 'bg-gray-400' }} mr-1"></span>
                        {{ $service->is_active ? 'Actief' : 'Inactief' }}
                    </button>
                </form>
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
                               value="{{ old('name', $service->name) }}"
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
                            <option value="{{ $key }}" {{ old('category', $service->category) === $key ? 'selected' : '' }}>
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
                                   value="{{ old('price', $service->price) }}"
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
                              x-model="formData.description">{{ old('description', $service->description) }}</textarea>
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
                               {{ old('is_active', $service->is_active) ? 'checked' : '' }}>
                        <label for="is_active" class="ml-2 block text-sm text-gray-700">
                            Dienst actief en beschikbaar maken voor klanten
                        </label>
                    </div>
                    @error('is_active')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Current Orders Check -->
                @if($service->orderItems()->exists())
                <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded">
                    <div class="flex items-start">
                        <svg class="h-5 w-5 text-yellow-400 mt-0.5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                        <div>
                            <p class="font-medium">Let op: Deze dienst is al besteld</p>
                            <p class="text-sm mt-1">
                                Er zijn {{ $service->orderItems()->count() }} bestelling(en) voor deze dienst. 
                                Wijzigingen kunnen invloed hebben op lopende bestellingen.
                            </p>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Changes Preview -->
                <div class="border-t border-gray-200 pt-6" x-show="hasChanges()">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                        <svg class="h-5 w-5 text-blue-500 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        Preview Wijzigingen
                    </h3>
                    <div class="card max-w-sm mx-auto">
                        <!-- Service Preview -->
                        <div class="h-32 bg-gradient-to-br from-primary-50 to-primary-100 p-6">
                            <div class="h-full flex items-center justify-center">
                                <div class="text-center">
                                    <div class="text-2xl mb-1" x-text="getCategoryIcon(formData.category)">🏠</div>
                                    <div class="text-xs text-primary-600 font-medium" 
                                         x-text="getCategoryName(formData.category)">
                                        {{ $service->category }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="p-4">
                            <div class="flex justify-between items-start mb-2">
                                <h4 class="font-medium text-gray-900 flex-1" 
                                    x-text="formData.name || '{{ $service->name }}'"></h4>
                                <div class="text-sm font-bold text-primary-600 ml-2" 
                                     x-text="formatPrice(formData.price || '{{ $service->price }}')"></div>
                            </div>
                            <p class="text-gray-600 text-xs line-clamp-2" 
                               x-text="formData.description || '{{ Str::limit($service->description, 100) }}'"></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-between">
                <div class="flex space-x-3">
                    <a href="{{ route('services.show', $service) }}" class="btn-secondary">
                        Annuleren
                    </a>
                    
                    <!-- Delete Button -->
                    @if(!$service->orderItems()->exists())
                    <form action="{{ route('services.destroy', $service) }}" 
                          method="POST" 
                          class="inline"
                          onsubmit="return confirm('Weet u zeker dat u deze dienst permanent wilt verwijderen?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn-danger">
                            <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                            Verwijderen
                        </button>
                    </form>
                    @endif
                </div>
                
                <div class="space-x-3">
                    <button type="button" 
                            @click="resetForm()" 
                            class="btn-secondary">
                        Reset
                    </button>
                    <button type="submit" 
                            class="btn-primary"
                            x-bind:disabled="!isFormValid() || !hasChanges()"
                            x-bind:class="(!isFormValid() || !hasChanges()) ? 'opacity-50 cursor-not-allowed' : ''">
                        <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span x-text="hasChanges() ? 'Wijzigingen Opslaan' : 'Opslaan'">Wijzigingen Opslaan</span>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Scripts -->
<script>
function serviceEditForm() {
    return {
        formData: {
            name: '{{ old('name', $service->name) }}',
            category: '{{ old('category', $service->category) }}',
            price: '{{ old('price', $service->price) }}',
            description: '{{ old('description', $service->description) }}'
        },
        
        originalData: {
            name: '{{ $service->name }}',
            category: '{{ $service->category }}',
            price: '{{ $service->price }}',
            description: '{{ $service->description }}'
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
            return this.categories[categoryKey]?.name || categoryKey;
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

        hasChanges() {
            return this.formData.name !== this.originalData.name ||
                   this.formData.category !== this.originalData.category ||
                   parseFloat(this.formData.price) !== parseFloat(this.originalData.price) ||
                   this.formData.description !== this.originalData.description;
        },

        resetForm() {
            if (confirm('Weet u zeker dat u alle wijzigingen wilt ongedaan maken?')) {
                this.formData = { ...this.originalData };
                // Reset form fields to original values
                document.getElementById('name').value = this.originalData.name;
                document.getElementById('category').value = this.originalData.category;
                document.getElementById('price').value = this.originalData.price;
                document.getElementById('description').value = this.originalData.description;
            }
        },

        submitForm(event) {
            if (!this.isFormValid()) {
                event.preventDefault();
                alert('Vul alle verplichte velden in voordat u het formulier verzendt.');
                return false;
            }

            if (!this.hasChanges()) {
                event.preventDefault();
                alert('Er zijn geen wijzigingen om op te slaan.');
                return false;
            }
        }
    }
}

function requirementsManager() {
    const existingRequirements = @json(old('requirements', $service->requirements ?? []));
    
    return {
        requirements: existingRequirements.map(req => ({ text: req })),

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
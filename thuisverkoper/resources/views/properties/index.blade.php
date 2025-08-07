@extends('layouts.app')
@section('title', '- Properties')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-4">Find Your Dream Home</h1>
        <p class="text-gray-600">Browse through {{ number_format($properties->total()) }} available properties in the Netherlands</p>
    </div>

    <!-- Search and Filters -->
    <div class="bg-white rounded-lg shadow p-6 mb-8">
        <form method="GET" action="{{ route('properties.index') }}" x-data="{ showFilters: false }">
            <!-- Search Bar -->
            <div class="flex flex-col lg:flex-row gap-4 mb-4">
                <div class="flex-1">
                    <input type="text" 
                           name="search" 
                           value="{{ request('search') }}"
                           placeholder="Search properties, locations, or keywords..."
                           class="form-input">
                </div>
                <div class="flex gap-2">
                    <button type="button" 
                            @click="showFilters = !showFilters"
                            class="btn-secondary">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4"></path>
                        </svg>
                        Filters
                    </button>
                    <button type="submit" class="btn-primary">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                        Search
                    </button>
                </div>
            </div>

            <!-- Advanced Filters -->
            <div x-show="showFilters" x-transition class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 pt-4 border-t border-gray-200">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Property Type</label>
                    <select name="property_type" class="form-select">
                        <option value="">All Types</option>
                        @foreach($propertyTypes as $type)
                            <option value="{{ $type }}" {{ request('property_type') == $type ? 'selected' : '' }}>
                                {{ ucfirst($type) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">City</label>
                    <select name="city" class="form-select">
                        <option value="">All Cities</option>
                        @foreach($cities as $city)
                            <option value="{{ $city }}" {{ request('city') == $city ? 'selected' : '' }}>
                                {{ $city }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Price Range</label>
                    <div class="grid grid-cols-2 gap-2">
                        <input type="number" 
                               name="min_price" 
                               value="{{ request('min_price') }}"
                               placeholder="Min €"
                               class="form-input">
                        <input type="number" 
                               name="max_price" 
                               value="{{ request('max_price') }}"
                               placeholder="Max €"
                               class="form-input">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Bedrooms</label>
                    <select name="bedrooms" class="form-select">
                        <option value="">Any</option>
                        @for($i = 1; $i <= 5; $i++)
                            <option value="{{ $i }}" {{ request('bedrooms') == $i ? 'selected' : '' }}>
                                {{ $i }}{{ $i == 5 ? '+' : '' }} bedroom{{ $i != 1 ? 's' : '' }}
                            </option>
                        @endfor
                    </select>
                </div>
            </div>

            <!-- Sort Options -->
            <div class="flex justify-between items-center pt-4 border-t border-gray-200">
                <div class="flex items-center space-x-4">
                    <label class="text-sm font-medium text-gray-700">Sort by:</label>
                    <select name="sort" class="form-select w-auto">
                        <option value="created_at" {{ request('sort') == 'created_at' ? 'selected' : '' }}>Newest</option>
                        <option value="price" {{ request('sort') == 'price' ? 'selected' : '' }}>Price</option>
                        <option value="title" {{ request('sort') == 'title' ? 'selected' : '' }}>Name</option>
                        <option value="city" {{ request('sort') == 'city' ? 'selected' : '' }}>Location</option>
                    </select>
                    <select name="order" class="form-select w-auto">
                        <option value="asc" {{ request('order') == 'asc' ? 'selected' : '' }}>Ascending</option>
                        <option value="desc" {{ request('order') == 'desc' ? 'selected' : '' }}>Descending</option>
                    </select>
                </div>

                @if(request()->hasAny(['search', 'property_type', 'city', 'min_price', 'max_price', 'bedrooms']))
                    <a href="{{ route('properties.index') }}" class="text-sm text-gray-500 hover:text-gray-700">
                        Clear Filters
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Results Count -->
    <div class="flex justify-between items-center mb-6">
        <p class="text-gray-600">
            Showing {{ $properties->firstItem() ?? 0 }} to {{ $properties->lastItem() ?? 0 }} 
            of {{ number_format($properties->total()) }} properties
        </p>
        
        @auth
            <a href="{{ route('properties.create') }}" class="btn-primary">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                Add Property
            </a>
        @endauth
    </div>

    <!-- Property Grid -->
    @if($properties->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
            @foreach($properties as $property)
                <div class="card hover:shadow-lg transition duration-300">
                    <!-- Property Image -->
                    <div class="relative h-48 bg-gray-200">
                        @if($property->main_image)
                            <img src="{{ Storage::url($property->main_image) }}" 
                                 alt="{{ $property->title }}"
                                 class="w-full h-full object-cover">
                        @else
                            <div class="w-full h-full flex items-center justify-center">
                                <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path>
                                </svg>
                            </div>
                        @endif
                        
                        <!-- Property Type Badge -->
                        <div class="absolute top-4 right-4">
                            <span class="bg-white/90 text-gray-800 px-2 py-1 rounded text-sm font-medium">
                                {{ ucfirst($property->property_type) }}
                            </span>
                        </div>

                        <!-- Price -->
                        <div class="absolute bottom-4 left-4">
                            <span class="bg-black/70 text-white px-3 py-1 rounded text-lg font-bold">
                                {{ $property->formatted_price }}
                            </span>
                        </div>
                    </div>

                    <!-- Property Details -->
                    <div class="p-6">
                        <h3 class="text-xl font-semibold mb-2">
                            <a href="{{ route('properties.show', $property) }}" class="hover:text-primary-600 transition">
                                {{ $property->title }}
                            </a>
                        </h3>
                        
                        <p class="text-gray-600 mb-3">
                            <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                            {{ $property->address }}, {{ $property->city }}
                        </p>

                        <div class="flex justify-between text-sm text-gray-500 mb-4">
                            <span>{{ $property->bedrooms }} beds</span>
                            <span>{{ $property->bathrooms }} baths</span>
                            <span>{{ $property->square_meters }}m²</span>
                        </div>

                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-500">
                                Listed by {{ $property->user->name }}
                            </span>
                            <a href="{{ route('properties.show', $property) }}" class="btn-primary">
                                View Details
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="flex justify-center">
            {{ $properties->links() }}
        </div>
    @else
        <!-- No Results -->
        <div class="text-center py-12">
            <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"></path>
            </svg>
            <h3 class="text-lg font-medium text-gray-900 mb-2">No properties found</h3>
            <p class="text-gray-500 mb-4">
                @if(request()->hasAny(['search', 'property_type', 'city', 'min_price', 'max_price', 'bedrooms']))
                    Try adjusting your search criteria or <a href="{{ route('properties.index') }}" class="text-primary-600 hover:underline">view all properties</a>.
                @else
                    There are no properties available at the moment.
                @endif
            </p>
            
            @auth
                <a href="{{ route('properties.create') }}" class="btn-primary">
                    List Your Property
                </a>
            @else
                <a href="{{ route('register') }}" class="btn-primary">
                    Start Selling Your Home
                </a>
            @endauth
        </div>
    @endif
</div>
@endsection
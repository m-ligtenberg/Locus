@extends('layouts.app')

@section('title', '- Dashboard')

@section('content')
<div class="min-h-screen bg-gray-50" x-data="dashboardData()">
    <!-- Dashboard Header -->
    <div class="bg-white shadow">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="md:flex md:items-center md:justify-between">
                <div class="flex-1 min-w-0">
                    <h1 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl">
                        Welkom terug, {{ auth()->user()->name }}! 👋
                    </h1>
                    <p class="mt-1 text-sm text-gray-500">
                        Hier is een overzicht van uw verkoop activiteiten
                    </p>
                </div>
                <div class="mt-4 flex md:mt-0 md:ml-4 space-x-3">
                    <a href="{{ route('properties.create') }}" class="btn-primary">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                        </svg>
                        Woning Toevoegen
                    </a>
                    <a href="{{ route('services.index') }}" class="btn-secondary">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                        </svg>
                        Diensten Bekijken
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
            <!-- Total Properties Card -->
            <div class="card p-6 hover:shadow-lg transition-shadow duration-200">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4 flex-1">
                        <p class="text-sm font-medium text-gray-600">Totaal Woningen</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $stats['total_properties'] }}</p>
                        <p class="text-xs text-gray-500 mt-1">
                            @if($stats['active_properties'] > 0)
                                {{ $stats['active_properties'] }} actief
                            @else
                                Geen actieve woningen
                            @endif
                        </p>
                    </div>
                </div>
            </div>

            <!-- Active Properties Card -->
            <div class="card p-6 hover:shadow-lg transition-shadow duration-200">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4 flex-1">
                        <p class="text-sm font-medium text-gray-600">Actieve Woningen</p>
                        <p class="text-2xl font-bold text-green-600">{{ $stats['active_properties'] }}</p>
                        <p class="text-xs text-gray-500 mt-1">
                            @if($stats['draft_properties'] > 0)
                                {{ $stats['draft_properties'] }} concept
                            @else
                                Geen concepten
                            @endif
                        </p>
                    </div>
                </div>
            </div>

            <!-- Total Bookings Card -->
            <div class="card p-6 hover:shadow-lg transition-shadow duration-200">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4 flex-1">
                        <p class="text-sm font-medium text-gray-600">Totaal Bezichtigingen</p>
                        <p class="text-2xl font-bold text-purple-600">{{ $stats['total_bookings'] }}</p>
                        <p class="text-xs text-gray-500 mt-1">
                            @if($upcomingBookings->count() > 0)
                                {{ $upcomingBookings->count() }} aankomende
                            @else
                                Geen aankomende
                            @endif
                        </p>
                    </div>
                </div>
            </div>

            <!-- Total Earnings Card -->
            <div class="card p-6 hover:shadow-lg transition-shadow duration-200">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4 flex-1">
                        <p class="text-sm font-medium text-gray-600">Totale Inkomsten</p>
                        <p class="text-2xl font-bold text-yellow-600">€{{ number_format($stats['total_earnings'], 2) }}</p>
                        <p class="text-xs text-gray-500 mt-1">
                            {{ $stats['total_orders'] }} bestellingen
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Left Column: Properties Overview & Recent Activity -->
            <div class="lg:col-span-2 space-y-8">
                <!-- Properties Overview -->
                <div class="card">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <div class="flex items-center justify-between">
                            <h2 class="text-lg font-semibold text-gray-900">Mijn Woningen</h2>
                            <a href="{{ route('properties.create') }}" class="text-sm text-primary-600 hover:text-primary-800">
                                Nieuwe toevoegen →
                            </a>
                        </div>
                    </div>
                    <div class="p-6">
                        @if($properties->count() > 0)
                            <div class="space-y-4">
                                @foreach($properties->take(3) as $property)
                                <div class="border border-gray-200 rounded-lg p-4 hover:border-primary-300 transition-colors">
                                    <div class="flex items-start justify-between">
                                        <div class="flex-1">
                                            <h3 class="text-lg font-medium text-gray-900">{{ $property->title }}</h3>
                                            <p class="text-sm text-gray-500">{{ $property->address }}</p>
                                            <div class="mt-2 flex items-center space-x-4 text-sm text-gray-600">
                                                <span class="flex items-center">
                                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                    </svg>
                                                    {{ $property->bookings_count }} bezichtigingen
                                                </span>
                                                <span class="flex items-center">
                                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                                                    </svg>
                                                    {{ $property->orders_count }} bestellingen
                                                </span>
                                                <span class="flex items-center">
                                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                    </svg>
                                                    {{ $property->documents_count }} documenten
                                                </span>
                                            </div>
                                        </div>
                                        <div class="flex items-center space-x-2">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                @if($property->status === 'active') bg-green-100 text-green-800
                                                @elseif($property->status === 'sold') bg-blue-100 text-blue-800
                                                @else bg-gray-100 text-gray-800
                                                @endif">
                                                @if($property->status === 'active') 
                                                    Actief
                                                @elseif($property->status === 'sold') 
                                                    Verkocht
                                                @elseif($property->status === 'draft')
                                                    Concept
                                                @else
                                                    {{ ucfirst($property->status) }}
                                                @endif
                                            </span>
                                            <div x-data="{ open: false }" class="relative">
                                                <button @click="open = !open" class="text-gray-400 hover:text-gray-600">
                                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                                        <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"/>
                                                    </svg>
                                                </button>
                                                <div x-show="open" @click.away="open = false" class="origin-top-right absolute right-0 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 z-10">
                                                    <div class="py-1">
                                                        <a href="{{ route('properties.show', $property) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Bekijken</a>
                                                        <a href="{{ route('properties.edit', $property) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Bewerken</a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mt-3 text-right">
                                        <span class="text-lg font-bold text-gray-900">€{{ number_format($property->price) }}</span>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                            @if($properties->count() > 3)
                            <div class="mt-4 text-center">
                                <a href="{{ route('properties.index') }}" class="text-primary-600 hover:text-primary-800">
                                    Alle woningen bekijken ({{ $properties->count() }}) →
                                </a>
                            </div>
                            @endif
                        @else
                            <div class="text-center py-8">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                </svg>
                                <h3 class="mt-2 text-sm font-medium text-gray-900">Nog geen woningen</h3>
                                <p class="mt-1 text-sm text-gray-500">Begin met het toevoegen van uw eerste woning.</p>
                                <div class="mt-6">
                                    <a href="{{ route('properties.create') }}" class="btn-primary">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                        </svg>
                                        Eerste Woning Toevoegen
                                    </a>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Recent Activity -->
                <div class="card">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-900">Recente Activiteit</h2>
                    </div>
                    <div class="p-6">
                        @if($recentOrders->count() > 0)
                            <div class="space-y-4">
                                @foreach($recentOrders->take(5) as $order)
                                <div class="flex items-start space-x-3">
                                    <div class="flex-shrink-0">
                                        <div class="w-8 h-8 bg-primary-100 rounded-full flex items-center justify-center">
                                            <svg class="w-4 h-4 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm text-gray-900">
                                            <span class="font-medium">Bestelling #{{ $order->id }}</span>
                                            @if($order->property)
                                                voor {{ $order->property->title }}
                                            @endif
                                        </p>
                                        <p class="text-sm text-gray-500">€{{ number_format($order->total_amount, 2) }} • {{ $order->created_at->diffForHumans() }}</p>
                                    </div>
                                    <div>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                            @if($order->status === 'paid') bg-green-100 text-green-800
                                            @elseif($order->status === 'pending') bg-yellow-100 text-yellow-800
                                            @else bg-gray-100 text-gray-800
                                            @endif">
                                            @if($order->status === 'paid') 
                                                Betaald
                                            @elseif($order->status === 'pending') 
                                                In behandeling
                                            @else
                                                {{ ucfirst($order->status) }}
                                            @endif
                                        </span>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-4">
                                <p class="text-sm text-gray-500">Nog geen recente activiteit</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Right Column: Quick Actions & Notifications -->
            <div class="space-y-8">
                <!-- Quick Actions -->
                <div class="card">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-900">Snelle Acties</h2>
                    </div>
                    <div class="p-6 space-y-3">
                        <a href="{{ route('properties.create') }}" class="w-full btn-primary justify-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                            </svg>
                            Nieuwe Woning
                        </a>
                        <a href="{{ route('services.index') }}" class="w-full btn-secondary justify-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                            </svg>
                            Diensten Bekijken
                        </a>
                        @if(session('cart') && count(session('cart')) > 0)
                        <a href="{{ route('services.cart') }}" class="w-full btn-secondary justify-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-2.5 5M7 13l2.5 5m6 0v6a2 2 0 11-4 0v-6m4 0V9a2 2 0 10-4 0v4.01"/>
                            </svg>
                            Winkelwagen ({{ count(session('cart')) }})
                        </a>
                        @endif
                        @if($properties->where('status', 'active')->count() > 0)
                        <button @click="$dispatch('schedule-viewing')" class="w-full btn-secondary justify-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            Bezichtiging Plannen
                        </button>
                        @endif
                    </div>
                </div>

                <!-- Upcoming Bookings -->
                @if($upcomingBookings->count() > 0)
                <div class="card">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-900">Aankomende Bezichtigingen</h2>
                    </div>
                    <div class="p-6">
                        <div class="space-y-4">
                            @foreach($upcomingBookings as $booking)
                            <div class="border-l-4 border-primary-400 pl-4">
                                <div class="flex items-start justify-between">
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">
                                            {{ $booking->property->title ?? 'Onbekende woning' }}
                                        </p>
                                        <p class="text-sm text-gray-500">
                                            {{ $booking->visitor_name }} • {{ $booking->visitor_email }}
                                        </p>
                                        <p class="text-xs text-gray-500 mt-1">
                                            {{ $booking->scheduled_at->format('d/m/Y H:i') }}
                                            ({{ $booking->scheduled_at->diffForHumans() }})
                                        </p>
                                    </div>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        Bevestigd
                                    </span>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endif

                <!-- Notifications/Alerts -->
                <div class="card">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-900">Notificaties</h2>
                    </div>
                    <div class="p-6">
                        <div class="space-y-3">
                            @if($stats['draft_properties'] > 0)
                            <div class="flex items-start space-x-3">
                                <div class="flex-shrink-0">
                                    <svg class="w-5 h-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-900">{{ $stats['draft_properties'] }} concept woning(en)</p>
                                    <p class="text-xs text-gray-500">Maak deze af om ze te publiceren</p>
                                </div>
                            </div>
                            @endif

                            @if($upcomingBookings->where('scheduled_at', '<', now()->addDay())->count() > 0)
                            <div class="flex items-start space-x-3">
                                <div class="flex-shrink-0">
                                    <svg class="w-5 h-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-900">Bezichtiging binnen 24 uur</p>
                                    <p class="text-xs text-gray-500">Zorg dat u voorbereid bent</p>
                                </div>
                            </div>
                            @endif

                            @if($recentOrders->where('status', 'pending')->count() > 0)
                            <div class="flex items-start space-x-3">
                                <div class="flex-shrink-0">
                                    <svg class="w-5 h-5 text-orange-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-900">{{ $recentOrders->where('status', 'pending')->count() }} bestelling(en) in behandeling</p>
                                    <p class="text-xs text-gray-500">Wachtend op bevestiging</p>
                                </div>
                            </div>
                            @endif

                            @if($stats['active_properties'] === 0 && $stats['draft_properties'] === 0)
                            <div class="flex items-start space-x-3">
                                <div class="flex-shrink-0">
                                    <svg class="w-5 h-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-900">Klaar om te beginnen!</p>
                                    <p class="text-xs text-gray-500">Voeg uw eerste woning toe</p>
                                </div>
                            </div>
                            @endif

                            @if($stats['total_properties'] === 0 && $stats['total_orders'] === 0 && $stats['total_bookings'] === 0)
                            <div class="text-center py-4">
                                <p class="text-sm text-gray-500">Geen meldingen op dit moment</p>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function dashboardData() {
    return {
        init() {
            // Initialize any dashboard-specific functionality
            this.setupNotifications();
        },
        
        setupNotifications() {
            // Set up any real-time notifications if needed
            // This could connect to websockets or poll for updates
        }
    }
}
</script>
@endsection
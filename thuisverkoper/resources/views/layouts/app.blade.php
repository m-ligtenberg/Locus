<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'DIY Home Platform') }} @yield('title')</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-white shadow-sm border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <!-- Logo -->
                    <div class="flex-shrink-0">
                        <a href="{{ route('home') }}" class="text-xl font-bold text-gray-900">
                            🏠 {{ config('app.name') }}
                        </a>
                    </div>

                    <!-- Navigation Links -->
                    <div class="hidden sm:ml-6 sm:flex sm:space-x-8">
                        <a href="{{ route('properties.index') }}" 
                           class="inline-flex items-center px-1 pt-1 text-sm font-medium {{ request()->routeIs('properties.*') ? 'border-b-2 border-primary-500 text-gray-900' : 'text-gray-500 hover:text-gray-700' }}">
                            Woningen
                        </a>
                        <a href="{{ route('services.index') }}" 
                           class="inline-flex items-center px-1 pt-1 text-sm font-medium {{ request()->routeIs('services.*') ? 'border-b-2 border-primary-500 text-gray-900' : 'text-gray-500 hover:text-gray-700' }}">
                            Diensten
                            @php $cartCount = count(session('cart', [])); @endphp
                            @if($cartCount > 0)
                            <span class="ml-1 bg-primary-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center" data-cart-count>
                                {{ $cartCount }}
                            </span>
                            @endif
                        </a>
                        @auth
                            <a href="{{ route('dashboard') }}" 
                               class="inline-flex items-center px-1 pt-1 text-sm font-medium {{ request()->routeIs('dashboard') ? 'border-b-2 border-primary-500 text-gray-900' : 'text-gray-500 hover:text-gray-700' }}">
                                Dashboard
                            </a>
                        @endauth
                    </div>
                </div>

                <div class="flex items-center">
                    @guest
                        <div class="hidden sm:ml-6 sm:flex sm:items-center sm:space-x-4">
                            <a href="{{ route('login') }}" class="text-gray-500 hover:text-gray-700">
                                Login
                            </a>
                            <a href="{{ route('register') }}" class="btn-primary">
                                Get Started
                            </a>
                        </div>
                    @else
                        <div class="hidden sm:ml-6 sm:flex sm:items-center">
                            <!-- User Dropdown -->
                            <div x-data="{ open: false }" class="relative">
                                <button @click="open = !open" class="flex items-center text-sm rounded-full focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                                    <span class="sr-only">Open user menu</span>
                                    <div class="h-8 w-8 bg-primary-600 rounded-full flex items-center justify-center">
                                        <span class="text-sm font-medium text-white">
                                            {{ substr(auth()->user()->name, 0, 1) }}
                                        </span>
                                    </div>
                                    <span class="ml-2 text-gray-700">{{ auth()->user()->name }}</span>
                                </button>

                                <div x-show="open" @click.away="open = false" class="origin-top-right absolute right-0 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5">
                                    <div class="py-1">
                                        <a href="{{ route('dashboard') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Dashboard</a>
                                        <a href="{{ route('properties.create') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Add Property</a>
                                        <div class="border-t border-gray-100"></div>
                                        <form method="POST" action="{{ route('logout') }}">
                                            @csrf
                                            <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                                Logout
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endguest

                    <!-- Mobile menu button -->
                    <div class="sm:hidden">
                        <button type="button" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-primary-500">
                            <span class="sr-only">Open main menu</span>
                            <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Flash Messages -->
    @if (session('success'))
        <div class="alert-success mb-4 mx-4 mt-4" x-data="{ show: true }" x-show="show" x-transition>
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm">{{ session('success') }}</p>
                </div>
                <div class="ml-auto pl-3">
                    <button @click="show = false" class="inline-flex text-green-400 hover:text-green-600">
                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    @endif

    @if (session('error'))
        <div class="alert-error mb-4 mx-4 mt-4" x-data="{ show: true }" x-show="show" x-transition>
            {{ session('error') }}
        </div>
    @endif

    <!-- Page Content -->
    <main>
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white mt-12">
        <div class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div class="md:col-span-2">
                    <h3 class="text-lg font-semibold">{{ config('app.name') }}</h3>
                    <p class="mt-2 text-gray-300">
                        The easiest way to sell your home in the Netherlands. 
                        Take control of your property sale with our comprehensive platform.
                    </p>
                </div>
                <div>
                    <h4 class="font-semibold">Platform</h4>
                    <ul class="mt-2 space-y-1 text-gray-300">
                        <li><a href="{{ route('properties.index') }}" class="hover:text-white">Woningen Bekijken</a></li>
                        <li><a href="{{ route('services.index') }}" class="hover:text-white">Diensten Marktplaats</a></li>
                        <li><a href="{{ route('register') }}" class="hover:text-white">Verkoop Uw Woning</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-semibold">Support</h4>
                    <ul class="mt-2 space-y-1 text-gray-300">
                        <li><a href="#" class="hover:text-white">Help Center</a></li>
                        <li><a href="#" class="hover:text-white">Contact Us</a></li>
                    </ul>
                </div>
            </div>
            <div class="border-t border-gray-700 mt-8 pt-8 text-center text-gray-400">
                <p>&copy; {{ date('Y') }} {{ config('app.name') }}. Built for the Dutch housing market.</p>
            </div>
        </div>
    </footer>
</body>
</html>
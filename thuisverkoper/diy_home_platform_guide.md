# DIY Home-Selling Platform - Complete Implementation Guide

## Architecture Overview

```
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│   Frontend      │    │   Backend       │    │   External      │
│                 │    │                 │    │                 │
│ • Blade Views   │◄──►│ • Laravel API   │◄──►│ • Stripe/Mollie │
│ • Alpine.js     │    │ • MySQL         │    │ • SMTP Service  │
│ • Tailwind CSS  │    │ • File Storage  │    │ • PDF Generator │
│ • 360° Viewer   │    │ • Queue System  │    │                 │
└─────────────────┘    └─────────────────┘    └─────────────────┘
```

## 1. Project Setup & Environment

### Hostinger Configuration
```bash
# Create project directory
mkdir diy-home-platform
cd diy-home-platform

# Initialize Laravel project
composer create-project laravel/laravel . "^10.0"

# Install required packages
composer require laravel/breeze laravel/sanctum spatie/laravel-permission
composer require barryvdh/laravel-dompdf intervention/image
composer require stripe/stripe-php mollie/laravel-mollie

# Frontend dependencies
npm install alpinejs @tailwindcss/forms @tailwindcss/typography
npm install pannellum signature_pad fullcalendar
```

### Environment Configuration (.env)
```env
APP_NAME="DIY Home Platform"
APP_ENV=production
APP_KEY=base64:your-app-key
APP_DEBUG=false
APP_URL=https://yourdomain.com

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_password

FILESYSTEM_DISK=local
STORAGE_PATH=/home/yourusername/public_html/storage

MAIL_MAILER=smtp
MAIL_HOST=smtp.hostinger.com
MAIL_PORT=587
MAIL_USERNAME=noreply@yourdomain.com
MAIL_PASSWORD=your_email_password
MAIL_ENCRYPTION=tls

STRIPE_KEY=pk_live_your_stripe_key
STRIPE_SECRET=sk_live_your_stripe_secret

MOLLIE_KEY=live_your_mollie_key
```

## 2. Database Schema

### Migration Files

```php
// create_properties_table.php
Schema::create('properties', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->string('title');
    $table->text('description');
    $table->string('address');
    $table->string('city');
    $table->string('postal_code');
    $table->decimal('price', 12, 2);
    $table->integer('bedrooms');
    $table->integer('bathrooms');
    $table->integer('square_meters');
    $table->enum('property_type', ['house', 'apartment', 'condo', 'other']);
    $table->enum('status', ['draft', 'active', 'under_offer', 'sold']);
    $table->json('features')->nullable();
    $table->json('images')->nullable();
    $table->string('virtual_tour_url')->nullable();
    $table->timestamps();
    
    $table->fullText(['title', 'description', 'address']);
});

// create_services_table.php
Schema::create('services', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->text('description');
    $table->decimal('price', 8, 2);
    $table->string('category');
    $table->boolean('is_active')->default(true);
    $table->json('requirements')->nullable();
    $table->timestamps();
});

// create_orders_table.php
Schema::create('orders', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained();
    $table->foreignId('property_id')->constrained();
    $table->string('order_number')->unique();
    $table->decimal('subtotal', 10, 2);
    $table->decimal('tax_amount', 10, 2);
    $table->decimal('total_amount', 10, 2);
    $table->enum('status', ['pending', 'paid', 'processing', 'completed', 'cancelled']);
    $table->string('payment_id')->nullable();
    $table->string('payment_method')->nullable();
    $table->timestamps();
});

// create_bookings_table.php
Schema::create('bookings', function (Blueprint $table) {
    $table->id();
    $table->foreignId('property_id')->constrained();
    $table->foreignId('user_id')->constrained();
    $table->dateTime('scheduled_at');
    $table->enum('type', ['virtual', 'in_person']);
    $table->text('notes')->nullable();
    $table->enum('status', ['pending', 'confirmed', 'completed', 'cancelled']);
    $table->timestamps();
});

// create_documents_table.php
Schema::create('documents', function (Blueprint $table) {
    $table->id();
    $table->foreignId('property_id')->constrained();
    $table->foreignId('user_id')->constrained();
    $table->string('title');
    $table->string('type');
    $table->string('file_path');
    $table->boolean('is_signed')->default(false);
    $table->timestamp('signed_at')->nullable();
    $table->json('signature_data')->nullable();
    $table->timestamps();
});
```

## 3. Core Models

### Property Model
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Property extends Model
{
    protected $fillable = [
        'user_id', 'title', 'description', 'address', 'city', 'postal_code',
        'price', 'bedrooms', 'bathrooms', 'square_meters', 'property_type',
        'status', 'features', 'images', 'virtual_tour_url'
    ];

    protected $casts = [
        'features' => 'array',
        'images' => 'array',
        'price' => 'decimal:2'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeSearch($query, $term)
    {
        return $query->whereFullText(['title', 'description', 'address'], $term);
    }

    public function getFormattedPriceAttribute()
    {
        return '€' . number_format($this->price, 0, ',', '.');
    }
}
```

## 4. Controllers

### PropertyController
```php
<?php

namespace App\Http\Controllers;

use App\Models\Property;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

class PropertyController extends Controller
{
    public function index(Request $request)
    {
        $query = Property::active()->with('user');

        if ($request->has('search')) {
            $query->search($request->search);
        }

        if ($request->has('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }

        if ($request->has('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        if ($request->has('property_type')) {
            $query->where('property_type', $request->property_type);
        }

        if ($request->has('city')) {
            $query->where('city', 'like', '%' . $request->city . '%');
        }

        $properties = $query->paginate(12);

        return view('properties.index', compact('properties'));
    }

    public function create()
    {
        return view('properties.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'address' => 'required|string|max:255',
            'city' => 'required|string|max:100',
            'postal_code' => 'required|string|max:10',
            'price' => 'required|numeric|min:0',
            'bedrooms' => 'required|integer|min:0',
            'bathrooms' => 'required|integer|min:0',
            'square_meters' => 'required|integer|min:1',
            'property_type' => 'required|in:house,apartment,condo,other',
            'features' => 'array',
            'images.*' => 'image|mimes:jpeg,png,jpg|max:5120'
        ]);

        $property = new Property($validated);
        $property->user_id = auth()->id();

        // Handle image uploads
        if ($request->hasFile('images')) {
            $images = [];
            foreach ($request->file('images') as $index => $image) {
                $filename = time() . '_' . $index . '.' . $image->getClientOriginalExtension();
                
                // Resize and optimize image
                $img = Image::make($image)
                    ->resize(1200, 800, function ($constraint) {
                        $constraint->aspectRatio();
                        $constraint->upsize();
                    })
                    ->encode('jpg', 85);

                Storage::disk('public')->put('properties/' . $filename, $img);
                $images[] = 'properties/' . $filename;
            }
            $property->images = $images;
        }

        $property->save();

        return redirect()->route('properties.show', $property)
            ->with('success', 'Property listed successfully!');
    }

    public function show(Property $property)
    {
        $property->load(['user', 'bookings']);
        return view('properties.show', compact('property'));
    }

    // Additional CRUD methods...
}
```

### ServiceController
```php
<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\PaymentIntent;

class ServiceController extends Controller
{
    public function index()
    {
        $services = Service::where('is_active', true)
            ->orderBy('category')
            ->orderBy('name')
            ->get()
            ->groupBy('category');

        return view('services.index', compact('services'));
    }

    public function addToCart(Request $request, Service $service)
    {
        $cart = session()->get('cart', []);
        
        if (isset($cart[$service->id])) {
            $cart[$service->id]['quantity']++;
        } else {
            $cart[$service->id] = [
                'service' => $service,
                'quantity' => 1,
                'price' => $service->price
            ];
        }

        session()->put('cart', $cart);

        return response()->json([
            'success' => true,
            'message' => 'Service added to cart',
            'cart_count' => count($cart)
        ]);
    }

    public function checkout(Request $request)
    {
        $cart = session()->get('cart', []);
        
        if (empty($cart)) {
            return redirect()->route('services.index')
                ->with('error', 'Your cart is empty');
        }

        $subtotal = collect($cart)->sum(function ($item) {
            return $item['price'] * $item['quantity'];
        });

        $taxRate = 0.21; // Dutch VAT
        $taxAmount = $subtotal * $taxRate;
        $total = $subtotal + $taxAmount;

        // Create order
        $order = Order::create([
            'user_id' => auth()->id(),
            'property_id' => $request->property_id,
            'order_number' => 'ORD-' . time(),
            'subtotal' => $subtotal,
            'tax_amount' => $taxAmount,
            'total_amount' => $total,
            'status' => 'pending'
        ]);

        // Create order items
        foreach ($cart as $serviceId => $item) {
            OrderItem::create([
                'order_id' => $order->id,
                'service_id' => $serviceId,
                'quantity' => $item['quantity'],
                'price' => $item['price']
            ]);
        }

        // Create Stripe Payment Intent
        Stripe::setApiKey(config('services.stripe.secret'));

        $paymentIntent = PaymentIntent::create([
            'amount' => $total * 100, // Convert to cents
            'currency' => 'eur',
            'metadata' => [
                'order_id' => $order->id,
                'user_id' => auth()->id()
            ]
        ]);

        $order->update(['payment_id' => $paymentIntent->id]);

        return view('services.checkout', compact('order', 'paymentIntent'));
    }
}
```

## 5. Frontend Components

### Property Listing Blade Template
```blade
<!-- resources/views/properties/index.blade.php -->
@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Search Filters -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-8">
        <form method="GET" action="{{ route('properties.index') }}" 
              x-data="{ showFilters: false }">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <input type="text" name="search" placeholder="Search properties..."
                           value="{{ request('search') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
                
                <div>
                    <select name="property_type" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                        <option value="">All Types</option>
                        <option value="house" {{ request('property_type') == 'house' ? 'selected' : '' }}>House</option>
                        <option value="apartment" {{ request('property_type') == 'apartment' ? 'selected' : '' }}>Apartment</option>
                        <option value="condo" {{ request('property_type') == 'condo' ? 'selected' : '' }}>Condo</option>
                    </select>
                </div>

                <div class="flex space-x-2">
                    <input type="number" name="min_price" placeholder="Min Price"
                           value="{{ request('min_price') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                    <input type="number" name="max_price" placeholder="Max Price"
                           value="{{ request('max_price') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                </div>

                <button type="submit" 
                        class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition">
                    Search
                </button>
            </div>
        </form>
    </div>

    <!-- Property Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($properties as $property)
            <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition">
                <div class="relative">
                    @if($property->images && count($property->images) > 0)
                        <img src="{{ Storage::url($property->images[0]) }}" 
                             alt="{{ $property->title }}"
                             class="w-full h-48 object-cover">
                    @else
                        <div class="w-full h-48 bg-gray-200 flex items-center justify-center">
                            <span class="text-gray-400">No Image</span>
                        </div>
                    @endif
                    
                    <div class="absolute top-4 right-4">
                        <span class="bg-blue-600 text-white px-2 py-1 rounded text-sm">
                            {{ ucfirst($property->property_type) }}
                        </span>
                    </div>
                </div>

                <div class="p-6">
                    <h3 class="text-xl font-semibold mb-2">
                        <a href="{{ route('properties.show', $property) }}" 
                           class="hover:text-blue-600 transition">
                            {{ $property->title }}
                        </a>
                    </h3>
                    
                    <p class="text-gray-600 mb-2">{{ $property->address }}, {{ $property->city }}</p>
                    
                    <div class="flex justify-between items-center mb-4">
                        <span class="text-2xl font-bold text-green-600">
                            {{ $property->formatted_price }}
                        </span>
                    </div>

                    <div class="flex justify-between text-sm text-gray-500 mb-4">
                        <span>{{ $property->bedrooms }} beds</span>
                        <span>{{ $property->bathrooms }} baths</span>
                        <span>{{ $property->square_meters }}m²</span>
                    </div>

                    <a href="{{ route('properties.show', $property) }}"
                       class="w-full bg-blue-600 text-white text-center py-2 rounded-lg hover:bg-blue-700 transition block">
                        View Details
                    </a>
                </div>
            </div>
        @empty
            <div class="col-span-3 text-center py-12">
                <p class="text-gray-500 text-lg">No properties found</p>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    <div class="mt-8">
        {{ $properties->links() }}
    </div>
</div>
@endsection
```

### Virtual Tour Component
```blade
<!-- resources/views/components/virtual-tour.blade.php -->
<div x-data="virtualTour()" class="relative">
    <div id="panorama-{{ $property->id }}" 
         class="w-full h-96 rounded-lg overflow-hidden">
    </div>
    
    @if($property->virtual_tour_url)
        <div class="absolute top-4 left-4">
            <button @click="toggleFullscreen()"
                    class="bg-black bg-opacity-50 text-white px-3 py-2 rounded-lg hover:bg-opacity-70 transition">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M3 4a1 1 0 011-1h3a1 1 0 010 2H5.414l1.293 1.293a1 1 0 01-1.414 1.414L4 6.414V8a1 1 0 01-2 0V4zM16 4a1 1 0 00-1-1h-3a1 1 0 000 2h1.586l-1.293 1.293a1 1 0 001.414 1.414L15 6.414V8a1 1 0 002 0V4zM4 16a1 1 0 001 1h3a1 1 0 000-2H6.414l1.293-1.293a1 1 0 00-1.414-1.414L5 13.586V12a1 1 0 00-2 0v4zM16 16a1 1 0 00-1 1h-3a1 1 0 000-2h1.586l-1.293-1.293a1 1 0 001.414-1.414L15 13.586V12a1 1 0 002 0v4z"/>
                </svg>
            </button>
        </div>
    @endif
</div>

<script>
function virtualTour() {
    return {
        viewer: null,
        
        init() {
            if (window.pannellum && '{{ $property->virtual_tour_url }}') {
                this.viewer = pannellum.viewer('panorama-{{ $property->id }}', {
                    type: 'equirectangular',
                    panorama: '{{ $property->virtual_tour_url }}',
                    autoLoad: true,
                    showZoomCtrl: true,
                    showFullscreenCtrl: false,
                    showControls: true
                });
            }
        },
        
        toggleFullscreen() {
            if (this.viewer) {
                this.viewer.toggleFullscreen();
            }
        }
    }
}
</script>
```

## 6. Payment Integration

### Stripe Payment Component
```php
<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\Webhook;

class PaymentController extends Controller
{
    public function __construct()
    {
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    public function processPayment(Request $request)
    {
        try {
            $order = Order::findOrFail($request->order_id);
            
            if ($order->status === 'paid') {
                return response()->json(['error' => 'Order already paid'], 400);
            }

            $paymentIntent = \Stripe\PaymentIntent::retrieve($order->payment_id);
            
            if ($paymentIntent->status === 'succeeded') {
                $order->update([
                    'status' => 'paid',
                    'payment_method' => $paymentIntent->payment_method_types[0]
                ]);

                // Clear cart
                session()->forget('cart');

                return response()->json([
                    'success' => true,
                    'redirect' => route('orders.confirmation', $order)
                ]);
            }

            return response()->json(['error' => 'Payment failed'], 400);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function webhook(Request $request)
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('stripe-signature');
        $endpointSecret = config('services.stripe.webhook_secret');

        try {
            $event = Webhook::constructEvent($payload, $sigHeader, $endpointSecret);
        } catch (\Exception $e) {
            return response('Invalid signature', 400);
        }

        if ($event->type === 'payment_intent.succeeded') {
            $paymentIntent = $event->data->object;
            $orderId = $paymentIntent->metadata->order_id;
            
            $order = Order::find($orderId);
            if ($order && $order->status === 'pending') {
                $order->update(['status' => 'paid']);
            }
        }

        return response('Success', 200);
    }
}
```

## 7. Document Management & E-Signatures

### Document Controller
```php
<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\Property;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class DocumentController extends Controller
{
    public function generateContract(Property $property)
    {
        $data = [
            'property' => $property,
            'seller' => $property->user,
            'date' => now()->format('d/m/Y')
        ];

        $pdf = Pdf::loadView('documents.sales-contract', $data);
        
        $filename = 'contract_' . $property->id . '_' . time() . '.pdf';
        $path = 'documents/' . $filename;
        
        Storage::disk('public')->put($path, $pdf->output());

        $document = Document::create([
            'property_id' => $property->id,
            'user_id' => auth()->id(),
            'title' => 'Sales Contract',
            'type' => 'contract',
            'file_path' => $path,
        ]);

        return redirect()->route('documents.sign', $document);
    }

    public function showSigningPage(Document $document)
    {
        return view('documents.sign', compact('document'));
    }

    public function processSignature(Request $request, Document $document)
    {
        $request->validate([
            'signature' => 'required|string',
            'name' => 'required|string|max:255'
        ]);

        $signatureData = [
            'signature' => $request->signature,
            'name' => $request->name,
            'signed_at' => now(),
            'ip_address' => $request->ip()
        ];

        $document->update([
            'is_signed' => true,
            'signed_at' => now(),
            'signature_data' => $signatureData
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Document signed successfully'
        ]);
    }
}
```

### Digital Signature Blade Component
```blade
<!-- resources/views/documents/sign.blade.php -->
@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8" x-data="documentSigner()">
    <div class="max-w-4xl mx-auto">
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            <div class="p-6 border-b">
                <h1 class="text-2xl font-bold">Sign Document: {{ $document->title }}</h1>
                <p class="text-gray-600 mt-2">Please review the document and provide your digital signature</p>
            </div>

            <!-- PDF Viewer -->
            <div class="p-6">
                <iframe src="{{ Storage::url($document->file_path) }}" 
                        class="w-full h-96 border rounded-lg"></iframe>
            </div>

            <!-- Signature Section -->
            <div class="p-6 border-t bg-gray-50" x-show="!signed">
                <h3 class="text-lg font-semibold mb-4">Digital Signature</h3>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Full Name</label>
                    <input type="text" x-model="signerName" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Signature</label>
                    <div class="border-2 border-dashed border-gray-300 rounded-lg p-4">
                        <canvas id="signature-pad" 
                                class="w-full h-32 bg-white rounded cursor-crosshair"
                                width="800" height="200"></canvas>
                    </div>
                    <div class="flex justify-between mt-2">
                        <button @click="clearSignature()" 
                                class="text-sm text-gray-600 hover:text-gray-800">Clear</button>
                        <span class="text-sm text-gray-500">Sign above</span>
                    </div>
                </div>

                <div class="flex justify-end space-x-4">
                    <button @click="signDocument()" 
                            :disabled="!canSign"
                            :class="canSign ? 'bg-green-600 hover:bg-green-700' : 'bg-gray-400 cursor-not-allowed'"
                            class="px-6 py-2 text-white rounded-lg transition">
                        <span x-show="!signing">Sign Document</span>
                        <span x-show="signing">Signing...</span>
                    </button>
                </div>
            </div>

            <!-- Success Message -->
            <div x-show="signed" class="p-6 bg-green-50 border-t">
                <div class="flex items-center">
                    <svg class="w-6 h-6 text-green-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"/>
                    </svg>
                    <span class="text-green-800 font-medium">Document signed successfully!</span>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>

<script>
function documentSigner() {
    return {
        signaturePad: null,
        signerName: '',
        signed: {{ $document->is_signed ? 'true' : 'false' }},
        signing: false,

        init() {
            const canvas = document.getElementById('signature-pad');
            this.signaturePad = new SignaturePad(canvas, {
                backgroundColor: 'rgba(255, 255, 255, 0)',
                penColor: 'rgb(0, 0, 0)'
            });
        },

        get canSign() {
            return this.signerName.trim() !== '' && !this.signaturePad?.isEmpty() && !this.signing;
        },

        clearSignature() {
            this.signaturePad.clear();
        },

        async signDocument() {
            if (!this.canSign) return;

            this.signing = true;

            try {
                const signatureData = this.signaturePad.toDataURL();
                
                const response = await fetch('{{ route("documents.sign", $document) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        signature: signatureData,
                        name: this.signerName
                    })
                });

                const result = await response.json();

                if (result.success) {
                    this.signed = true;
                } else {
                    alert('Failed to sign document. Please try again.');
                }
            } catch (error) {
                console.error('Signing error:', error);
                alert('An error occurred while signing. Please try again.');
            } finally {
                this.signing = false;
            }
        }
    }
}
</script>
@endsection
```

## 8. Deployment on Hostinger

### File Upload via Git
```bash
# Initialize git repository
git init
git add .
git commit -m "Initial commit"

# Connect to Hostinger via Git (if supported) or use FTP
# For FTP deployment:
# Upload files to public_html directory
# Ensure storage/ and bootstrap/cache/ are writable

# Set up symbolic link for storage
php artisan storage:link

# Run migrations
php artisan migrate --force

# Clear and cache config
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### .htaccess Configuration
```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    
    # Handle Angular and Laravel routing
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
    
    # Force HTTPS
    RewriteCond %{HTTPS} off
    RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
    
    # Gzip compression
    <IfModule mod_deflate.c>
        AddOutputFilterByType DEFLATE text/plain
        AddOutputFilterByType DEFLATE text/html
        AddOutputFilterByType DEFLATE text/xml
        AddOutputFilterByType DEFLATE text/css
        AddOutputFilterByType DEFLATE application/xml
        AddOutputFilterByType DEFLATE application/xhtml+xml
        AddOutputFilterByType DEFLATE application/rss+xml
        AddOutputFilterByType DEFLATE application/javascript
        AddOutputFilterByType DEFLATE application/x-javascript
    </IfModule>
</IfModule>

# Cache headers
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType image/jpg "access plus 1 month"
    ExpiresByType image/jpeg "access plus 1 month"
    ExpiresByType image/gif "access plus 1 month"
    ExpiresByType image/png "access plus 1 month"
    ExpiresByType text/css "access plus 1 month"
    ExpiresByType application/pdf "access plus 1 month"
    ExpiresByType application/javascript "access plus 1 month"
    ExpiresByType application/x-javascript "access plus 1 month"
    ExpiresByType application/x-shockwave-flash "access plus 1 month"
    ExpiresByType image/x-icon "access plus 1 year"
    ExpiresDefault "access plus 2 days"
</IfModule>
```

### Cron Jobs Setup
```bash
# Add to Hostinger cron jobs (every minute)
* * * * * cd /home/yourusername/public_html && php artisan schedule:run >> /dev/null 2>&1

# For queue processing (every 5 minutes)
*/5 * * * * cd /home/yourusername/public_html && php artisan queue:work --stop-when-empty >> /dev/null 2>&1
```

## 9. Performance Optimization

### Laravel Optimization
```php
// config/cache.php - Use file cache for shared hosting
'default' => env('CACHE_DRIVER', 'file'),

// config/session.php - Use file sessions
'driver' => env('SESSION_DRIVER', 'file'),

// config/queue.php - Use database queue
'default' => env('QUEUE_CONNECTION', 'database'),
```

### Image Optimization Service
```php
<?php

namespace App\Services;

use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Storage;

class ImageOptimizationService
{
    public function optimizePropertyImages(array $images): array
    {
        $optimizedImages = [];

        foreach ($images as $image) {
            // Create multiple sizes
            $sizes = [
                'thumbnail' => [300, 200],
                'medium' => [600, 400],
                'large' => [1200, 800]
            ];

            $filename = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME);
            $extension = $image->getClientOriginalExtension();

            foreach ($sizes as $sizeName => $dimensions) {
                $img = Image::make($image)
                    ->resize($dimensions[0], $dimensions[1], function ($constraint) {
                        $constraint->aspectRatio();
                        $constraint->upsize();
                    })
                    ->encode('jpg', 85);

                $path = "properties/{$filename}_{$sizeName}.jpg";
                Storage::disk('public')->put($path, $img);
                
                $optimizedImages[$sizeName][] = $path;
            }
        }

        return $optimizedImages;
    }
}
```

## 10. Testing Implementation

### Feature Tests
```php
<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Property;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PropertyManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_property_listing()
    {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user)->post('/properties', [
            'title' => 'Beautiful Family Home',
            'description' => 'A lovely 3-bedroom house in Amsterdam',
            'address' => 'Dam Square 1',
            'city' => 'Amsterdam',
            'postal_code' => '1012 JS',
            'price' => 450000,
            'bedrooms' => 3,
            'bathrooms' => 2,
            'square_meters' => 120,
            'property_type' => 'house'
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('properties', [
            'title' => 'Beautiful Family Home',
            'user_id' => $user->id
        ]);
    }

    public function test_property_search_functionality()
    {
        $property = Property::factory()->create([
            'title' => 'Amsterdam Apartment',
            'city' => 'Amsterdam',
            'price' => 300000
        ]);

        $response = $this->get('/properties?search=Amsterdam');
        
        $response->assertStatus(200);
        $response->assertSee('Amsterdam Apartment');
    }
}
```

This comprehensive implementation guide provides a complete foundation for building your DIY home-selling platform on Hostinger. The architecture is designed to be cost-effective while delivering professional functionality including property listings, service marketplace, document management, e-signatures, virtual tours, and secure payments.

Key features implemented:
- **Complete CRUD operations** for properties and services
- **Advanced search and filtering** with MySQL full-text search
- **Secure payment processing** with Stripe integration
- **Document generation and e-signatures** using DomPDF and signature capture
- **Virtual property tours** with Pannellum integration
- **Responsive design** with Tailwind CSS and Alpine.js
- **Image optimization** and file management
- **Performance optimization** for shared hosting environments

The platform is ready for deployment on Hostinger and can handle the complete property selling workflow from listing creation to transaction completion.
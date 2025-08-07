<?php

namespace App\Http\Controllers;

use App\Models\Property;
use App\Http\Requests\PropertyRequest;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Str;

class PropertyController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth')->except(['index', 'show']);
    }

    /**
     * Display a listing of properties.
     */
    public function index(Request $request): View
    {
        $query = Property::active()->with('user');

        // Search functionality
        if ($request->filled('search')) {
            $searchTerm = $request->input('search');
            $query->where(function ($q) use ($searchTerm) {
                $q->where('title', 'like', '%' . $searchTerm . '%')
                  ->orWhere('description', 'like', '%' . $searchTerm . '%')
                  ->orWhere('address', 'like', '%' . $searchTerm . '%')
                  ->orWhere('city', 'like', '%' . $searchTerm . '%');
            });
        }

        // Price filtering
        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->input('min_price'));
        }

        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->input('max_price'));
        }

        // Property type filtering
        if ($request->filled('property_type')) {
            $query->where('property_type', $request->input('property_type'));
        }

        // City filtering
        if ($request->filled('city')) {
            $query->where('city', 'like', '%' . $request->input('city') . '%');
        }

        // Bedroom filtering
        if ($request->filled('bedrooms')) {
            $query->where('bedrooms', '>=', $request->input('bedrooms'));
        }

        // Sorting
        $sortBy = $request->input('sort', 'created_at');
        $sortOrder = $request->input('order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $properties = $query->paginate(12)->appends($request->query());

        // Get filter options for dropdowns
        $cities = Property::active()->distinct()->pluck('city')->sort()->values();
        $propertyTypes = ['house', 'apartment', 'condo', 'other'];

        return view('properties.index', compact('properties', 'cities', 'propertyTypes'));
    }

    /**
     * Show the form for creating a new property.
     */
    public function create(): View
    {
        return view('properties.create');
    }

    /**
     * Store a newly created property in storage.
     */
    public function store(PropertyRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        
        $property = new Property($validated);
        $property->user_id = auth()->id();
        $property->status = 'draft'; // New properties start as draft

        // Handle image uploads
        if ($request->hasFile('images')) {
            $images = $this->handleImageUploads($request->file('images'));
            $property->images = $images;
        }

        $property->save();

        return redirect()->route('properties.show', $property)
            ->with('success', 'Property created successfully! You can now manage your listing.');
    }

    /**
     * Display the specified property.
     */
    public function show(Property $property): View
    {
        $property->load(['user', 'bookings' => function ($query) {
            $query->confirmed()->upcoming();
        }]);

        $relatedProperties = Property::active()
            ->where('id', '!=', $property->id)
            ->where('city', $property->city)
            ->limit(4)
            ->get();

        return view('properties.show', compact('property', 'relatedProperties'));
    }

    /**
     * Show the form for editing the specified property.
     */
    public function edit(Property $property): View
    {
        $this->authorize('update', $property);
        
        return view('properties.edit', compact('property'));
    }

    /**
     * Update the specified property in storage.
     */
    public function update(PropertyRequest $request, Property $property): RedirectResponse
    {
        $this->authorize('update', $property);

        $validated = $request->validated();
        
        // Handle image uploads
        if ($request->hasFile('images')) {
            // Delete old images if replacing
            if ($property->images) {
                $this->deleteImages($property->images);
            }
            
            $images = $this->handleImageUploads($request->file('images'));
            $validated['images'] = $images;
        }

        $property->update($validated);

        return redirect()->route('properties.show', $property)
            ->with('success', 'Property updated successfully!');
    }

    /**
     * Remove the specified property from storage.
     */
    public function destroy(Property $property): RedirectResponse
    {
        $this->authorize('delete', $property);

        // Delete associated images
        if ($property->images) {
            $this->deleteImages($property->images);
        }

        $property->delete();

        return redirect()->route('dashboard')
            ->with('success', 'Property deleted successfully.');
    }

    /**
     * Toggle property status (draft/active)
     */
    public function toggleStatus(Property $property): RedirectResponse
    {
        $this->authorize('update', $property);

        $newStatus = $property->status === 'active' ? 'draft' : 'active';
        $property->update(['status' => $newStatus]);

        $message = $newStatus === 'active' 
            ? 'Property is now live and visible to buyers!' 
            : 'Property is now hidden from public listings.';

        return redirect()->back()->with('success', $message);
    }

    /**
     * Handle multiple image uploads with optimization
     */
    private function handleImageUploads(array $images): array
    {
        $uploadedImages = [];

        foreach ($images as $index => $image) {
            if ($image->isValid()) {
                $filename = time() . '_' . $index . '_' . Str::random(10) . '.' . $image->getClientOriginalExtension();
                
                try {
                    // Create optimized image
                    $img = Image::make($image)
                        ->resize(1200, 800, function ($constraint) {
                            $constraint->aspectRatio();
                            $constraint->upsize();
                        })
                        ->encode('jpg', 85);

                    // Store the image
                    Storage::disk('public')->put('properties/' . $filename, $img);
                    $uploadedImages[] = 'properties/' . $filename;

                    // Create thumbnail
                    $thumbFilename = 'thumb_' . $filename;
                    $thumbnail = Image::make($image)
                        ->resize(400, 300, function ($constraint) {
                            $constraint->aspectRatio();
                            $constraint->upsize();
                        })
                        ->encode('jpg', 80);
                    
                    Storage::disk('public')->put('properties/' . $thumbFilename, $thumbnail);

                } catch (\Exception $e) {
                    // Log error and continue with other images
                    logger()->error('Image upload failed: ' . $e->getMessage());
                }
            }
        }

        return $uploadedImages;
    }

    /**
     * Delete images from storage
     */
    private function deleteImages(array $images): void
    {
        foreach ($images as $imagePath) {
            Storage::disk('public')->delete($imagePath);
            
            // Also delete thumbnail if exists
            $thumbPath = str_replace('properties/', 'properties/thumb_', $imagePath);
            Storage::disk('public')->delete($thumbPath);
        }
    }

    /**
     * Remove single image from property
     */
    public function removeImage(Property $property, Request $request): RedirectResponse
    {
        $this->authorize('update', $property);

        $imageIndex = $request->input('image_index');
        $images = $property->images ?? [];

        if (isset($images[$imageIndex])) {
            $imagePath = $images[$imageIndex];
            
            // Delete from storage
            Storage::disk('public')->delete($imagePath);
            $thumbPath = str_replace('properties/', 'properties/thumb_', $imagePath);
            Storage::disk('public')->delete($thumbPath);

            // Remove from array
            unset($images[$imageIndex]);
            $images = array_values($images); // Reindex array

            $property->update(['images' => $images]);
        }

        return redirect()->back()->with('success', 'Image removed successfully.');
    }
}
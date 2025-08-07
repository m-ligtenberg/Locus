<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PropertyRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'min:50'],
            'address' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:100'],
            'postal_code' => ['required', 'string', 'max:10'],
            'price' => ['required', 'numeric', 'min:1000', 'max:50000000'],
            'bedrooms' => ['required', 'integer', 'min:0', 'max:20'],
            'bathrooms' => ['required', 'integer', 'min:0', 'max:10'],
            'square_meters' => ['required', 'integer', 'min:1', 'max:10000'],
            'property_type' => ['required', 'in:house,apartment,condo,other'],
            'features' => ['nullable', 'array'],
            'features.*' => ['string', 'max:100'],
            'images' => ['nullable', 'array', 'max:10'],
            'images.*' => [
                'image',
                'mimes:jpeg,png,jpg,webp',
                'max:5120', // 5MB max
                'dimensions:min_width=400,min_height=300'
            ],
            'virtual_tour_url' => ['nullable', 'url', 'max:500'],
        ];
    }

    /**
     * Get custom error messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'title.required' => 'Property title is required.',
            'description.required' => 'Property description is required.',
            'description.min' => 'Property description must be at least 50 characters.',
            'price.required' => 'Property price is required.',
            'price.min' => 'Property price must be at least €1,000.',
            'price.max' => 'Property price cannot exceed €50,000,000.',
            'bedrooms.required' => 'Number of bedrooms is required.',
            'bathrooms.required' => 'Number of bathrooms is required.',
            'square_meters.required' => 'Property size in square meters is required.',
            'property_type.required' => 'Property type selection is required.',
            'property_type.in' => 'Please select a valid property type.',
            'images.max' => 'You can upload a maximum of 10 images.',
            'images.*.image' => 'All uploaded files must be images.',
            'images.*.mimes' => 'Images must be in JPEG, PNG, JPG, or WebP format.',
            'images.*.max' => 'Each image must be smaller than 5MB.',
            'images.*.dimensions' => 'Images must be at least 400x300 pixels.',
            'virtual_tour_url.url' => 'Virtual tour must be a valid URL.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'square_meters' => 'size (m²)',
            'property_type' => 'property type',
            'virtual_tour_url' => 'virtual tour URL',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Clean and format data
        if ($this->has('price')) {
            $price = str_replace([',', '€', ' '], '', $this->input('price'));
            $this->merge(['price' => $price]);
        }

        if ($this->has('postal_code')) {
            $postalCode = strtoupper(str_replace(' ', '', $this->input('postal_code')));
            $this->merge(['postal_code' => $postalCode]);
        }

        // Clean features array
        if ($this->has('features')) {
            $features = array_filter($this->input('features', []), function ($feature) {
                return !empty(trim($feature));
            });
            $this->merge(['features' => array_values($features)]);
        }
    }
}
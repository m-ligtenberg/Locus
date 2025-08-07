<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ServiceRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'min:50'],
            'price' => ['required', 'numeric', 'min:0', 'max:999999.99'],
            'category' => ['required', 'in:notary,epc-certificate,property-valuation,mortgage-advice,home-inspection,legal-advice'],
            'is_active' => ['sometimes', 'boolean'],
            'requirements' => ['nullable', 'array'],
            'requirements.*' => ['string', 'max:255'],
        ];
    }

    /**
     * Get custom error messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Service name is required.',
            'name.max' => 'Service name cannot exceed 255 characters.',
            'description.required' => 'Service description is required.',
            'description.min' => 'Service description must be at least 50 characters.',
            'price.required' => 'Service price is required.',
            'price.numeric' => 'Service price must be a valid number.',
            'price.min' => 'Service price cannot be negative.',
            'price.max' => 'Service price cannot exceed €999,999.99.',
            'category.required' => 'Service category selection is required.',
            'category.in' => 'Please select a valid service category.',
            'requirements.*.string' => 'Each requirement must be text.',
            'requirements.*.max' => 'Each requirement cannot exceed 255 characters.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'is_active' => 'service status',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Clean and format price
        if ($this->has('price')) {
            $price = str_replace([',', '€', ' '], '', $this->input('price'));
            $this->merge(['price' => $price]);
        }

        // Clean requirements array
        if ($this->has('requirements')) {
            $requirements = array_filter($this->input('requirements', []), function ($requirement) {
                return !empty(trim($requirement));
            });
            $this->merge(['requirements' => array_values($requirements)]);
        }

        // Ensure is_active is boolean
        if ($this->has('is_active')) {
            $this->merge(['is_active' => (bool) $this->input('is_active')]);
        }
    }
}
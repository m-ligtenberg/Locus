<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $rules = [
            'property_id' => ['nullable', 'exists:properties,id'],
            'payment_method' => ['required', 'in:stripe,mollie'],
            'success_url' => ['nullable', 'url'],
            'cancel_url' => ['nullable', 'url'],
            'billing_details' => ['nullable', 'array'],
            'billing_details.name' => ['required_with:billing_details', 'string', 'max:255'],
            'billing_details.email' => ['required_with:billing_details', 'email', 'max:255'],
            'billing_details.phone' => ['nullable', 'string', 'max:20'],
            'billing_details.address' => ['nullable', 'array'],
            'billing_details.address.line1' => ['required_with:billing_details.address', 'string', 'max:255'],
            'billing_details.address.line2' => ['nullable', 'string', 'max:255'],
            'billing_details.address.city' => ['required_with:billing_details.address', 'string', 'max:100'],
            'billing_details.address.postal_code' => ['required_with:billing_details.address', 'string', 'max:20'],
            'billing_details.address.country' => ['required_with:billing_details.address', 'string', 'size:2'],
        ];

        // Validation for cart items when creating order from cart
        if ($this->isMethod('POST') && $this->route()->getName() === 'orders.store') {
            $rules['items'] = ['required', 'array', 'min:1'];
            $rules['items.*.service_id'] = ['required', 'exists:services,id'];
            $rules['items.*.quantity'] = ['required', 'integer', 'min:1', 'max:99'];
        }

        // Status update validation
        if ($this->isMethod('PATCH')) {
            $rules = [
                'status' => ['required', 'in:pending,processing,paid,completed,cancelled,refunded'],
                'notes' => ['nullable', 'string', 'max:1000'],
            ];
        }

        return $rules;
    }

    /**
     * Get custom error messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'property_id.exists' => 'The selected property does not exist.',
            'payment_method.required' => 'Please select a payment method.',
            'payment_method.in' => 'Please select a valid payment method (Stripe or Mollie).',
            'items.required' => 'At least one service must be selected.',
            'items.min' => 'At least one service must be selected.',
            'items.*.service_id.required' => 'Service selection is required.',
            'items.*.service_id.exists' => 'One or more selected services do not exist.',
            'items.*.quantity.required' => 'Quantity is required for each service.',
            'items.*.quantity.integer' => 'Quantity must be a valid number.',
            'items.*.quantity.min' => 'Quantity must be at least 1.',
            'items.*.quantity.max' => 'Maximum quantity per service is 99.',
            'billing_details.name.required_with' => 'Name is required for billing.',
            'billing_details.email.required_with' => 'Email is required for billing.',
            'billing_details.email.email' => 'Please provide a valid email address.',
            'billing_details.address.line1.required_with' => 'Street address is required.',
            'billing_details.address.city.required_with' => 'City is required.',
            'billing_details.address.postal_code.required_with' => 'Postal code is required.',
            'billing_details.address.country.required_with' => 'Country is required.',
            'billing_details.address.country.size' => 'Country must be a valid 2-letter country code.',
            'status.required' => 'Order status is required.',
            'status.in' => 'Please select a valid order status.',
            'success_url.url' => 'Success URL must be a valid URL.',
            'cancel_url.url' => 'Cancel URL must be a valid URL.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'billing_details.name' => 'billing name',
            'billing_details.email' => 'billing email',
            'billing_details.phone' => 'billing phone',
            'billing_details.address.line1' => 'billing address',
            'billing_details.address.line2' => 'billing address line 2',
            'billing_details.address.city' => 'billing city',
            'billing_details.address.postal_code' => 'billing postal code',
            'billing_details.address.country' => 'billing country',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Ensure payment method is lowercase
        if ($this->has('payment_method')) {
            $this->merge([
                'payment_method' => strtolower($this->input('payment_method'))
            ]);
        }

        // Clean billing details
        if ($this->has('billing_details')) {
            $billingDetails = $this->input('billing_details', []);
            
            // Clean phone number
            if (isset($billingDetails['phone'])) {
                $billingDetails['phone'] = preg_replace('/[^+\d\s()-]/', '', $billingDetails['phone']);
            }

            // Clean postal code for Dutch format
            if (isset($billingDetails['address']['postal_code'])) {
                $postalCode = $billingDetails['address']['postal_code'];
                $billingDetails['address']['postal_code'] = strtoupper(
                    str_replace(' ', '', $postalCode)
                );
            }

            // Ensure country is uppercase
            if (isset($billingDetails['address']['country'])) {
                $billingDetails['address']['country'] = strtoupper(
                    $billingDetails['address']['country']
                );
            }

            $this->merge(['billing_details' => $billingDetails]);
        }

        // Clean cart items data
        if ($this->has('items') && is_array($this->input('items'))) {
            $items = array_filter($this->input('items'), function ($item) {
                return isset($item['service_id']) && $item['service_id'] > 0;
            });
            
            $this->merge(['items' => array_values($items)]);
        }
    }

    /**
     * Get the validated data from the request for order creation.
     */
    public function getOrderData(): array
    {
        $validated = $this->validated();
        
        return [
            'property_id' => $validated['property_id'] ?? null,
            'payment_method' => $validated['payment_method'],
            'billing_details' => $validated['billing_details'] ?? null,
            'items' => $validated['items'] ?? [],
        ];
    }

    /**
     * Get the validated billing details formatted for payment processors.
     */
    public function getBillingDetails(): array
    {
        $billingDetails = $this->input('billing_details', []);
        
        return [
            'name' => $billingDetails['name'] ?? auth()->user()->name,
            'email' => $billingDetails['email'] ?? auth()->user()->email,
            'phone' => $billingDetails['phone'] ?? null,
            'address' => [
                'line1' => $billingDetails['address']['line1'] ?? null,
                'line2' => $billingDetails['address']['line2'] ?? null,
                'city' => $billingDetails['address']['city'] ?? null,
                'postal_code' => $billingDetails['address']['postal_code'] ?? null,
                'country' => $billingDetails['address']['country'] ?? 'NL',
            ],
        ];
    }
}
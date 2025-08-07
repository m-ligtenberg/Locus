<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class DocumentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::check();
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $rules = [
            'property_id' => 'required|exists:properties,id',
            'title' => 'required|string|max:255',
            'type' => 'required|in:koopovereenkomst,property_info,viewing_report,epc_certificate,service_contract,property_brochure,notary_deed,mortgage_info',
        ];

        // Add specific validation rules based on document type
        switch ($this->input('type')) {
            case 'koopovereenkomst':
                $rules = array_merge($rules, [
                    'buyer_name' => 'required|string|max:255',
                    'buyer_email' => 'required|email',
                    'buyer_phone' => 'nullable|string|max:20',
                    'buyer_address' => 'required|string|max:500',
                    'purchase_price' => 'required|numeric|min:0',
                    'deposit_amount' => 'nullable|numeric|min:0',
                    'transfer_date' => 'required|date|after:today',
                    'conditions' => 'nullable|string|max:2000',
                    'financing_condition' => 'boolean',
                    'inspection_condition' => 'boolean',
                    'notary_choice' => 'required|string|max:255',
                    'special_agreements' => 'nullable|string|max:2000',
                ]);
                break;

            case 'property_info':
                $rules = array_merge($rules, [
                    'energy_label' => 'nullable|string|max:10',
                    'construction_year' => 'nullable|integer|min:1800|max:' . date('Y'),
                    'plot_size' => 'nullable|numeric|min:0',
                    'living_area' => 'nullable|numeric|min:0',
                    'heating_type' => 'nullable|string|max:100',
                    'insulation_details' => 'nullable|string|max:1000',
                    'municipal_taxes' => 'nullable|numeric|min:0',
                    'water_board_taxes' => 'nullable|numeric|min:0',
                    'service_costs' => 'nullable|numeric|min:0',
                ]);
                break;

            case 'viewing_report':
                $rules = array_merge($rules, [
                    'viewer_name' => 'required|string|max:255',
                    'viewer_email' => 'required|email',
                    'viewing_date' => 'required|date',
                    'viewing_duration' => 'nullable|integer|min:1|max:300',
                    'viewer_feedback' => 'nullable|string|max:2000',
                    'interest_level' => 'nullable|in:low,medium,high,very_high',
                    'follow_up_required' => 'boolean',
                    'notes' => 'nullable|string|max:1000',
                ]);
                break;

            case 'service_contract':
                $rules = array_merge($rules, [
                    'service_provider' => 'required|string|max:255',
                    'service_description' => 'required|string|max:1000',
                    'service_price' => 'required|numeric|min:0',
                    'start_date' => 'required|date',
                    'end_date' => 'nullable|date|after_or_equal:start_date',
                    'payment_terms' => 'nullable|string|max:500',
                    'cancellation_terms' => 'nullable|string|max:500',
                ]);
                break;

            case 'epc_certificate':
                $rules = array_merge($rules, [
                    'energy_label' => 'required|string|max:10',
                    'energy_index' => 'nullable|numeric|min:0|max:500',
                    'certificate_number' => 'nullable|string|max:50',
                    'valid_until' => 'nullable|date|after:today',
                    'advisor_name' => 'nullable|string|max:255',
                    'advisor_qualification' => 'nullable|string|max:255',
                    'improvement_recommendations' => 'nullable|string|max:2000',
                ]);
                break;

            case 'property_brochure':
                $rules = array_merge($rules, [
                    'marketing_description' => 'nullable|string|max:2000',
                    'key_features' => 'nullable|array',
                    'key_features.*' => 'string|max:255',
                    'neighborhood_info' => 'nullable|string|max:1000',
                    'nearby_amenities' => 'nullable|array',
                    'nearby_amenities.*' => 'string|max:255',
                    'include_floor_plan' => 'boolean',
                    'include_energy_info' => 'boolean',
                ]);
                break;

            case 'notary_deed':
                $rules = array_merge($rules, [
                    'notary_name' => 'required|string|max:255',
                    'notary_address' => 'required|string|max:500',
                    'deed_type' => 'required|string|max:100',
                    'parties_involved' => 'required|array|min:1',
                    'parties_involved.*' => 'required|string|max:255',
                    'deed_conditions' => 'nullable|string|max:2000',
                    'registration_required' => 'boolean',
                ]);
                break;

            case 'mortgage_info':
                $rules = array_merge($rules, [
                    'lender_name' => 'nullable|string|max:255',
                    'mortgage_amount' => 'nullable|numeric|min:0',
                    'interest_rate' => 'nullable|numeric|min:0|max:20',
                    'mortgage_term' => 'nullable|integer|min:1|max:50',
                    'monthly_payment' => 'nullable|numeric|min:0',
                    'mortgage_type' => 'nullable|string|max:100',
                    'conditions' => 'nullable|string|max:1000',
                ]);
                break;
        }

        return $rules;
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'property_id' => 'eigendom',
            'title' => 'titel',
            'type' => 'documenttype',
            'buyer_name' => 'naam koper',
            'buyer_email' => 'email koper',
            'buyer_phone' => 'telefoon koper',
            'buyer_address' => 'adres koper',
            'purchase_price' => 'koopprijs',
            'deposit_amount' => 'aanbetaling',
            'transfer_date' => 'overdachtsdatum',
            'conditions' => 'voorwaarden',
            'financing_condition' => 'financieringsvoorwaarden',
            'inspection_condition' => 'inspectievoorwaarden',
            'notary_choice' => 'notariskeuze',
            'special_agreements' => 'bijzondere afspraken',
            'energy_label' => 'energielabel',
            'construction_year' => 'bouwjaar',
            'plot_size' => 'perceeloppervlakte',
            'living_area' => 'woonoppervlakte',
            'heating_type' => 'verwarmingstype',
            'insulation_details' => 'isolatiegegevens',
            'municipal_taxes' => 'gemeentelijke belastingen',
            'water_board_taxes' => 'waterschapsbelastingen',
            'service_costs' => 'servicekosten',
            'viewer_name' => 'naam bezichtiger',
            'viewer_email' => 'email bezichtiger',
            'viewing_date' => 'bezichtigingsdatum',
            'viewing_duration' => 'bezichtigingsduur',
            'viewer_feedback' => 'feedback bezichtiger',
            'interest_level' => 'interesse niveau',
            'follow_up_required' => 'follow-up vereist',
            'notes' => 'notities',
            'service_provider' => 'dienstverlener',
            'service_description' => 'servicebeschrijving',
            'service_price' => 'serviceprijs',
            'start_date' => 'startdatum',
            'end_date' => 'einddatum',
            'payment_terms' => 'betalingsvoorwaarden',
            'cancellation_terms' => 'annuleringsvoorwaarden',
            'energy_index' => 'energie-index',
            'certificate_number' => 'certificaatnummer',
            'valid_until' => 'geldig tot',
            'advisor_name' => 'adviseur naam',
            'advisor_qualification' => 'adviseur kwalificatie',
            'improvement_recommendations' => 'verbeteringsaanbevelingen',
            'marketing_description' => 'marketingbeschrijving',
            'key_features' => 'belangrijkste kenmerken',
            'neighborhood_info' => 'buurtinformatie',
            'nearby_amenities' => 'nabijgelegen voorzieningen',
            'include_floor_plan' => 'plattegrond opnemen',
            'include_energy_info' => 'energie-informatie opnemen',
            'notary_name' => 'notaris naam',
            'notary_address' => 'notaris adres',
            'deed_type' => 'aktesoort',
            'parties_involved' => 'betrokken partijen',
            'deed_conditions' => 'aktevoorwaarden',
            'registration_required' => 'registratie vereist',
            'lender_name' => 'geldverstrekker naam',
            'mortgage_amount' => 'hypotheekbedrag',
            'interest_rate' => 'rentepercentage',
            'mortgage_term' => 'hypotheeklooptijd',
            'monthly_payment' => 'maandelijkse betaling',
            'mortgage_type' => 'hypotheektype',
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'property_id.required' => 'Selecteer een eigendom voor dit document.',
            'property_id.exists' => 'Het geselecteerde eigendom bestaat niet.',
            'type.required' => 'Selecteer een documenttype.',
            'type.in' => 'Het geselecteerde documenttype is ongeldig.',
            'title.required' => 'Voer een titel in voor het document.',
            'title.max' => 'De titel mag maximaal 255 karakters bevatten.',
            'purchase_price.required' => 'Voer de koopprijs in.',
            'purchase_price.numeric' => 'De koopprijs moet een geldig getal zijn.',
            'purchase_price.min' => 'De koopprijs moet minimaal 0 zijn.',
            'transfer_date.required' => 'Voer de overdachtsdatum in.',
            'transfer_date.date' => 'De overdachtsdatum moet een geldige datum zijn.',
            'transfer_date.after' => 'De overdachtsdatum moet in de toekomst liggen.',
            'buyer_email.required' => 'Voer het email adres van de koper in.',
            'buyer_email.email' => 'Voer een geldig email adres in.',
            'construction_year.integer' => 'Het bouwjaar moet een geheel getal zijn.',
            'construction_year.min' => 'Het bouwjaar moet minimaal 1800 zijn.',
            'construction_year.max' => 'Het bouwjaar mag niet in de toekomst liggen.',
            'viewing_date.required' => 'Voer de bezichtigingsdatum in.',
            'viewing_date.date' => 'De bezichtigingsdatum moet een geldige datum zijn.',
            'viewer_email.required' => 'Voer het email adres van de bezichtiger in.',
            'viewer_email.email' => 'Voer een geldig email adres in.',
            'service_price.required' => 'Voer de serviceprijs in.',
            'service_price.numeric' => 'De serviceprijs moet een geldig getal zijn.',
            'service_price.min' => 'De serviceprijs moet minimaal 0 zijn.',
            'start_date.required' => 'Voer de startdatum in.',
            'start_date.date' => 'De startdatum moet een geldige datum zijn.',
            'end_date.date' => 'De einddatum moet een geldige datum zijn.',
            'end_date.after_or_equal' => 'De einddatum moet op of na de startdatum liggen.',
            'energy_label.required' => 'Voer het energielabel in.',
            'valid_until.date' => 'De geldigheidsatum moet een geldige datum zijn.',
            'valid_until.after' => 'De geldigheidsatum moet in de toekomst liggen.',
            'parties_involved.required' => 'Voer minimaal één betrokken partij in.',
            'parties_involved.array' => 'Betrokken partijen moet een lijst zijn.',
            'parties_involved.min' => 'Voer minimaal één betrokken partij in.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Ensure the selected property belongs to the authenticated user
        if ($this->has('property_id')) {
            $property = \App\Models\Property::where('id', $this->input('property_id'))
                ->where('user_id', Auth::id())
                ->first();
            
            if (!$property) {
                $this->merge(['property_id' => null]);
            }
        }

        // Convert string values to proper types
        if ($this->has('financing_condition')) {
            $this->merge(['financing_condition' => filter_var($this->input('financing_condition'), FILTER_VALIDATE_BOOLEAN)]);
        }

        if ($this->has('inspection_condition')) {
            $this->merge(['inspection_condition' => filter_var($this->input('inspection_condition'), FILTER_VALIDATE_BOOLEAN)]);
        }

        if ($this->has('follow_up_required')) {
            $this->merge(['follow_up_required' => filter_var($this->input('follow_up_required'), FILTER_VALIDATE_BOOLEAN)]);
        }

        if ($this->has('include_floor_plan')) {
            $this->merge(['include_floor_plan' => filter_var($this->input('include_floor_plan'), FILTER_VALIDATE_BOOLEAN)]);
        }

        if ($this->has('include_energy_info')) {
            $this->merge(['include_energy_info' => filter_var($this->input('include_energy_info'), FILTER_VALIDATE_BOOLEAN)]);
        }

        if ($this->has('registration_required')) {
            $this->merge(['registration_required' => filter_var($this->input('registration_required'), FILTER_VALIDATE_BOOLEAN)]);
        }

        // Clean up numeric fields
        $numericFields = ['purchase_price', 'deposit_amount', 'plot_size', 'living_area', 'municipal_taxes', 
                         'water_board_taxes', 'service_costs', 'service_price', 'energy_index', 
                         'mortgage_amount', 'interest_rate', 'monthly_payment'];

        foreach ($numericFields as $field) {
            if ($this->has($field) && $this->input($field) !== null) {
                $value = str_replace(['.', ','], ['', '.'], $this->input($field));
                $this->merge([$field => is_numeric($value) ? $value : null]);
            }
        }

        // Clean up array fields
        if ($this->has('key_features') && is_array($this->input('key_features'))) {
            $this->merge(['key_features' => array_filter($this->input('key_features'))]);
        }

        if ($this->has('nearby_amenities') && is_array($this->input('nearby_amenities'))) {
            $this->merge(['nearby_amenities' => array_filter($this->input('nearby_amenities'))]);
        }

        if ($this->has('parties_involved') && is_array($this->input('parties_involved'))) {
            $this->merge(['parties_involved' => array_filter($this->input('parties_involved'))]);
        }
    }

    /**
     * Get the validated data with proper type casting
     */
    public function validated($key = null, $default = null): array
    {
        $validated = parent::validated();

        // Ensure boolean fields are properly cast
        $booleanFields = ['financing_condition', 'inspection_condition', 'follow_up_required', 
                         'include_floor_plan', 'include_energy_info', 'registration_required'];

        foreach ($booleanFields as $field) {
            if (array_key_exists($field, $validated)) {
                $validated[$field] = (bool) $validated[$field];
            }
        }

        return $validated;
    }
}
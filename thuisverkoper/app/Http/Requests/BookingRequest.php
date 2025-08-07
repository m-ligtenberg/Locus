<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Booking;
use Carbon\Carbon;

class BookingRequest extends FormRequest
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
        $rules = [
            'property_id' => ['required', 'exists:properties,id'],
            'scheduled_at' => [
                'required',
                'date',
                'after:' . now()->addHours(2)->toDateTimeString(), // Minimum 2 hours notice
                'before:' . now()->addMonths(3)->toDateTimeString(), // Maximum 3 months ahead
                function ($attribute, $value, $fail) {
                    $this->validateBusinessHours($attribute, $value, $fail);
                    $this->validateNoConflicts($attribute, $value, $fail);
                }
            ],
            'type' => ['required', 'in:virtual,in_person'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];

        // Add status validation for updates
        if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
            $rules['status'] = ['sometimes', 'in:pending,confirmed,completed,cancelled'];
        }

        return $rules;
    }

    /**
     * Get custom error messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'property_id.required' => 'Property selection is required.',
            'property_id.exists' => 'Selected property does not exist.',
            'scheduled_at.required' => 'Booking date and time is required.',
            'scheduled_at.date' => 'Please provide a valid date and time.',
            'scheduled_at.after' => 'Booking must be scheduled at least 2 hours in advance.',
            'scheduled_at.before' => 'Booking cannot be scheduled more than 3 months ahead.',
            'type.required' => 'Booking type is required.',
            'type.in' => 'Please select either virtual or in-person viewing.',
            'notes.max' => 'Notes cannot exceed 1000 characters.',
            'status.in' => 'Invalid booking status.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'scheduled_at' => 'booking date and time',
            'property_id' => 'property',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Ensure the scheduled_at is properly formatted
        if ($this->has('scheduled_at')) {
            try {
                $scheduledAt = Carbon::parse($this->input('scheduled_at'));
                $this->merge(['scheduled_at' => $scheduledAt->toDateTimeString()]);
            } catch (\Exception $e) {
                // Let validation handle the invalid date
            }
        }

        // Clean notes
        if ($this->has('notes')) {
            $notes = trim($this->input('notes'));
            $this->merge(['notes' => empty($notes) ? null : $notes]);
        }
    }

    /**
     * Validate business hours (Monday-Sunday 9:00-20:00)
     */
    private function validateBusinessHours($attribute, $value, $fail): void
    {
        try {
            $dateTime = Carbon::parse($value);
            $hour = $dateTime->hour;
            
            // Business hours: 9:00 AM to 8:00 PM
            if ($hour < 9 || $hour >= 20) {
                $fail('Bookings are only available between 9:00 AM and 8:00 PM.');
                return;
            }

            // Round to 30-minute intervals
            $minutes = $dateTime->minute;
            if (!in_array($minutes, [0, 30])) {
                $fail('Bookings must be scheduled at 30-minute intervals (e.g., 10:00 or 10:30).');
            }

        } catch (\Exception $e) {
            $fail('Please provide a valid date and time.');
        }
    }

    /**
     * Validate no conflicting bookings exist
     */
    private function validateNoConflicts($attribute, $value, $fail): void
    {
        if (!$this->has('property_id')) {
            return; // Let property_id validation handle this
        }

        try {
            $propertyId = $this->input('property_id');
            $scheduledAt = Carbon::parse($value);
            
            // Check for conflicting bookings (1-hour window)
            $startTime = $scheduledAt->copy()->subMinutes(59);
            $endTime = $scheduledAt->copy()->addMinutes(59);

            $query = Booking::where('property_id', $propertyId)
                ->where('status', '!=', 'cancelled')
                ->whereBetween('scheduled_at', [$startTime, $endTime]);

            // Exclude current booking if updating
            if ($this->route('booking')) {
                $query->where('id', '!=', $this->route('booking')->id);
            }

            if ($query->exists()) {
                $fail('This time slot conflicts with an existing booking. Please choose a different time.');
            }

        } catch (\Exception $e) {
            // Let other validation handle date parsing errors
        }
    }

    /**
     * Get available time slots for a specific date and property
     */
    public static function getAvailableSlots($propertyId, $date): array
    {
        $slots = [];
        $targetDate = Carbon::parse($date)->startOfDay();
        
        // Generate 30-minute slots from 9:00 to 20:00
        $currentSlot = $targetDate->copy()->setHour(9)->setMinute(0)->setSecond(0);
        $endTime = $targetDate->copy()->setHour(20)->setMinute(0)->setSecond(0);

        while ($currentSlot < $endTime) {
            // Skip past time slots
            if ($currentSlot > now()->addHours(2)) {
                // Check if slot is available (no conflicting bookings)
                $conflictStart = $currentSlot->copy()->subMinutes(59);
                $conflictEnd = $currentSlot->copy()->addMinutes(59);
                
                $hasConflict = Booking::where('property_id', $propertyId)
                    ->where('status', '!=', 'cancelled')
                    ->whereBetween('scheduled_at', [$conflictStart, $conflictEnd])
                    ->exists();

                if (!$hasConflict) {
                    $slots[] = [
                        'time' => $currentSlot->format('H:i'),
                        'datetime' => $currentSlot->toDateTimeString(),
                        'formatted' => $currentSlot->format('H:i'),
                    ];
                }
            }

            $currentSlot->addMinutes(30);
        }

        return $slots;
    }
}
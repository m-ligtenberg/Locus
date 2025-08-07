<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Property;
use App\Http\Requests\BookingRequest;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Carbon\Carbon;

class BookingController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of bookings.
     */
    public function index(Request $request): View
    {
        $user = auth()->user();
        
        // Get bookings where user is either the booking creator or property owner
        $query = Booking::with(['property', 'user'])
            ->where(function ($q) use ($user) {
                $q->where('user_id', $user->id)
                  ->orWhereHas('property', function ($propQuery) use ($user) {
                      $propQuery->where('user_id', $user->id);
                  });
            });

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        // Filter by type
        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }

        // Filter by date range
        if ($request->filled('from_date')) {
            $query->where('scheduled_at', '>=', $request->input('from_date'));
        }

        if ($request->filled('to_date')) {
            $query->where('scheduled_at', '<=', $request->input('to_date') . ' 23:59:59');
        }

        // Filter by property (for property owners)
        if ($request->filled('property_id')) {
            $query->where('property_id', $request->input('property_id'));
        }

        // Sorting
        $sortBy = $request->input('sort', 'scheduled_at');
        $sortOrder = $request->input('order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $bookings = $query->paginate(15)->appends($request->query());

        // Get user's properties for filtering
        $userProperties = Property::where('user_id', $user->id)->get(['id', 'title']);

        // Get statistics
        $stats = $this->getBookingStats($user);

        return view('bookings.index', compact('bookings', 'userProperties', 'stats'));
    }

    /**
     * Show the form for creating a new booking.
     */
    public function create(Request $request): View
    {
        $property = null;
        
        if ($request->filled('property_id')) {
            $property = Property::findOrFail($request->input('property_id'));
            $this->authorize('create', [Booking::class, $property]);
        }

        return view('bookings.create', compact('property'));
    }

    /**
     * Store a newly created booking in storage.
     */
    public function store(BookingRequest $request): RedirectResponse
    {
        $property = Property::findOrFail($request->validated()['property_id']);
        $this->authorize('create', [Booking::class, $property]);

        $validated = $request->validated();
        
        $booking = new Booking($validated);
        $booking->user_id = auth()->id();
        $booking->status = 'pending';
        $booking->save();

        // Send notification to property owner
        $this->sendBookingNotification($booking, 'created');

        return redirect()->route('bookings.show', $booking)
            ->with('success', 'Booking request submitted successfully! The property owner will confirm your appointment.');
    }

    /**
     * Display the specified booking.
     */
    public function show(Booking $booking): View
    {
        $this->authorize('view', $booking);
        
        $booking->load(['property', 'user']);
        
        return view('bookings.show', compact('booking'));
    }

    /**
     * Show the form for editing the specified booking.
     */
    public function edit(Booking $booking): View
    {
        $this->authorize('update', $booking);
        
        $booking->load('property');
        
        return view('bookings.edit', compact('booking'));
    }

    /**
     * Update the specified booking in storage.
     */
    public function update(BookingRequest $request, Booking $booking): RedirectResponse
    {
        $this->authorize('update', $booking);

        $oldStatus = $booking->status;
        $oldScheduledAt = $booking->scheduled_at;
        
        $booking->update($request->validated());

        // Send notifications if status changed or booking was rescheduled
        if ($oldStatus !== $booking->status) {
            $this->sendBookingNotification($booking, 'status_changed');
        } elseif ($oldScheduledAt != $booking->scheduled_at) {
            $this->sendBookingNotification($booking, 'rescheduled');
        }

        return redirect()->route('bookings.show', $booking)
            ->with('success', 'Booking updated successfully!');
    }

    /**
     * Remove the specified booking from storage.
     */
    public function destroy(Booking $booking): RedirectResponse
    {
        $this->authorize('delete', $booking);

        // Send cancellation notification
        $this->sendBookingNotification($booking, 'cancelled');
        
        $booking->update(['status' => 'cancelled']);

        return redirect()->route('bookings.index')
            ->with('success', 'Booking cancelled successfully.');
    }

    /**
     * Confirm a pending booking (property owners only)
     */
    public function confirm(Booking $booking): RedirectResponse
    {
        $this->authorize('confirm', $booking);

        $booking->update(['status' => 'confirmed']);
        
        $this->sendBookingNotification($booking, 'confirmed');

        return redirect()->back()->with('success', 'Booking confirmed successfully!');
    }

    /**
     * Mark a booking as completed (property owners only)
     */
    public function complete(Booking $booking): RedirectResponse
    {
        $this->authorize('complete', $booking);

        $booking->update(['status' => 'completed']);
        
        $this->sendBookingNotification($booking, 'completed');

        return redirect()->back()->with('success', 'Booking marked as completed!');
    }

    /**
     * Reschedule a booking
     */
    public function reschedule(BookingRequest $request, Booking $booking): RedirectResponse
    {
        $this->authorize('reschedule', $booking);

        $oldDateTime = $booking->scheduled_at;
        $booking->update([
            'scheduled_at' => $request->validated()['scheduled_at'],
            'notes' => $request->validated()['notes'] ?? $booking->notes,
        ]);

        $this->sendBookingNotification($booking, 'rescheduled');

        return redirect()->back()->with('success', 'Booking rescheduled successfully!');
    }

    // CALENDAR API ENDPOINTS

    /**
     * Get calendar events for FullCalendar (JSON)
     */
    public function calendarEvents(Request $request): JsonResponse
    {
        $propertyId = $request->input('property_id');
        $start = $request->input('start');
        $end = $request->input('end');

        if (!$propertyId) {
            return response()->json([]);
        }

        $property = Property::findOrFail($propertyId);
        $this->authorize('viewCalendar', $property);

        $bookings = Booking::with(['user'])
            ->where('property_id', $propertyId)
            ->where('status', '!=', 'cancelled')
            ->whereBetween('scheduled_at', [$start, $end])
            ->get();

        $events = $bookings->map(function ($booking) {
            $color = match($booking->status) {
                'pending' => '#fbbf24',   // yellow
                'confirmed' => '#10b981', // green
                'completed' => '#6b7280', // gray
                default => '#ef4444'      // red
            };

            return [
                'id' => $booking->id,
                'title' => $booking->type === 'virtual' ? '🖥️ Virtual Tour' : '🏠 In-Person Tour',
                'start' => $booking->scheduled_at->toISOString(),
                'end' => $booking->scheduled_at->copy()->addHour()->toISOString(),
                'backgroundColor' => $color,
                'borderColor' => $color,
                'extendedProps' => [
                    'booking_id' => $booking->id,
                    'status' => $booking->status,
                    'type' => $booking->type,
                    'visitor_name' => $booking->user->name,
                    'visitor_email' => $booking->user->email,
                    'notes' => $booking->notes,
                ],
            ];
        });

        return response()->json($events);
    }

    /**
     * Get available time slots for a specific date and property (JSON)
     */
    public function availableSlots(Request $request): JsonResponse
    {
        $propertyId = $request->input('property_id');
        $date = $request->input('date');

        if (!$propertyId || !$date) {
            return response()->json(['error' => 'Property ID and date are required'], 400);
        }

        $property = Property::findOrFail($propertyId);
        $this->authorize('viewCalendar', $property);

        $slots = BookingRequest::getAvailableSlots($propertyId, $date);

        return response()->json(['slots' => $slots]);
    }

    /**
     * Get booking statistics for dashboard
     */
    public function stats(Request $request): JsonResponse
    {
        $user = auth()->user();
        $stats = $this->getBookingStats($user);

        return response()->json($stats);
    }

    /**
     * Search bookings (AJAX endpoint)
     */
    public function search(Request $request): JsonResponse
    {
        $user = auth()->user();
        $query = $request->input('query');

        $bookings = Booking::with(['property', 'user'])
            ->where(function ($q) use ($user) {
                $q->where('user_id', $user->id)
                  ->orWhereHas('property', function ($propQuery) use ($user) {
                      $propQuery->where('user_id', $user->id);
                  });
            })
            ->where(function ($q) use ($query) {
                $q->whereHas('property', function ($propQuery) use ($query) {
                    $propQuery->where('title', 'like', '%' . $query . '%')
                             ->orWhere('address', 'like', '%' . $query . '%');
                })
                ->orWhereHas('user', function ($userQuery) use ($query) {
                    $userQuery->where('name', 'like', '%' . $query . '%')
                             ->orWhere('email', 'like', '%' . $query . '%');
                })
                ->orWhere('notes', 'like', '%' . $query . '%');
            })
            ->orderBy('scheduled_at', 'desc')
            ->limit(20)
            ->get();

        return response()->json([
            'bookings' => $bookings->map(function ($booking) {
                return [
                    'id' => $booking->id,
                    'property_title' => $booking->property->title,
                    'visitor_name' => $booking->user->name,
                    'scheduled_at' => $booking->formatted_date,
                    'status' => $booking->status,
                    'type' => $booking->type,
                    'url' => route('bookings.show', $booking),
                ];
            })
        ]);
    }

    // HELPER METHODS

    /**
     * Get booking statistics for a user
     */
    private function getBookingStats($user): array
    {
        $baseQuery = Booking::where(function ($q) use ($user) {
            $q->where('user_id', $user->id)
              ->orWhereHas('property', function ($propQuery) use ($user) {
                  $propQuery->where('user_id', $user->id);
              });
        });

        return [
            'total' => $baseQuery->count(),
            'pending' => $baseQuery->where('status', 'pending')->count(),
            'confirmed' => $baseQuery->where('status', 'confirmed')->count(),
            'completed' => $baseQuery->where('status', 'completed')->count(),
            'cancelled' => $baseQuery->where('status', 'cancelled')->count(),
            'upcoming' => $baseQuery->upcoming()->count(),
            'this_week' => $baseQuery->whereBetween('scheduled_at', [
                now()->startOfWeek(),
                now()->endOfWeek()
            ])->count(),
        ];
    }

    /**
     * Send booking notifications via email
     */
    private function sendBookingNotification(Booking $booking, string $action): void
    {
        $booking->load(['property.user', 'user']);
        
        $propertyOwner = $booking->property->user;
        $visitor = $booking->user;

        try {
            switch ($action) {
                case 'created':
                    // Notify property owner of new booking request
                    Mail::send('emails.booking.new-request', compact('booking'), function ($message) use ($propertyOwner) {
                        $message->to($propertyOwner->email)
                               ->subject('New Viewing Request for Your Property');
                    });
                    break;

                case 'confirmed':
                    // Notify visitor that booking is confirmed
                    Mail::send('emails.booking.confirmed', compact('booking'), function ($message) use ($visitor) {
                        $message->to($visitor->email)
                               ->subject('Viewing Appointment Confirmed');
                    });
                    break;

                case 'cancelled':
                    // Notify both parties of cancellation
                    Mail::send('emails.booking.cancelled', compact('booking'), function ($message) use ($visitor, $propertyOwner) {
                        $message->to([$visitor->email, $propertyOwner->email])
                               ->subject('Viewing Appointment Cancelled');
                    });
                    break;

                case 'rescheduled':
                    // Notify both parties of reschedule
                    Mail::send('emails.booking.rescheduled', compact('booking'), function ($message) use ($visitor, $propertyOwner) {
                        $message->to([$visitor->email, $propertyOwner->email])
                               ->subject('Viewing Appointment Rescheduled');
                    });
                    break;

                case 'completed':
                    // Notify visitor and thank them
                    Mail::send('emails.booking.completed', compact('booking'), function ($message) use ($visitor) {
                        $message->to($visitor->email)
                               ->subject('Thank you for visiting our property');
                    });
                    break;
            }
        } catch (\Exception $e) {
            // Log email sending errors but don't fail the request
            logger()->error('Failed to send booking notification: ' . $e->getMessage(), [
                'booking_id' => $booking->id,
                'action' => $action,
            ]);
        }
    }
}
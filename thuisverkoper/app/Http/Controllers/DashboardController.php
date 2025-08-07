<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the user dashboard
     */
    public function index(): View
    {
        $user = auth()->user();
        
        // Get user's properties with counts
        $properties = $user->properties()->withCount(['bookings', 'orders', 'documents'])->latest()->get();
        
        // Get recent orders
        $recentOrders = $user->orders()->with('property')->latest()->limit(5)->get();
        
        // Get upcoming bookings
        $upcomingBookings = $user->bookings()
            ->with('property')
            ->where('scheduled_at', '>', now())
            ->where('status', 'confirmed')
            ->orderBy('scheduled_at')
            ->limit(5)
            ->get();
        
        // Statistics
        $stats = [
            'total_properties' => $properties->count(),
            'active_properties' => $properties->where('status', 'active')->count(),
            'draft_properties' => $properties->where('status', 'draft')->count(),
            'sold_properties' => $properties->where('status', 'sold')->count(),
            'total_bookings' => $properties->sum('bookings_count'),
            'total_orders' => $recentOrders->count(),
            'total_earnings' => $recentOrders->where('status', 'paid')->sum('total_amount'),
        ];

        return view('dashboard', compact('properties', 'recentOrders', 'upcomingBookings', 'stats'));
    }
}
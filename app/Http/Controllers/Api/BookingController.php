<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Car;
use App\Models\Extra;
use Carbon\Carbon;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        if ($user->role === 'admin') {
            $bookings = Booking::with([
                'user',
                'car',
                'pickupBranch',
                'dropoffBranch',
                'payments',
                'extras'
            ])->latest()->paginate(10);
        } else {
            $bookings = Booking::with([
                'car',
                'pickupBranch',
                'dropoffBranch',
                'payments',
                'extras'
            ])->where('user_id', $user->id)
              ->latest()
              ->paginate(10);
        }

        return response()->json($bookings);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'car_id' => 'required|exists:cars,id',
            'pickup_branch_id' => 'required|exists:branches,id',
            'dropoff_branch_id' => 'required|exists:branches,id',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after:start_date',
            'pickup_time' => 'required',
            'dropoff_time' => 'required',
            'driver_name' => 'required|string|max:255',
            'driver_phone' => 'required|string|max:50',
            'driver_license_number' => 'required|string|max:255',
            'notes' => 'nullable|string',
            'extras' => 'nullable|array',
            'extras.*.id' => 'required_with:extras|exists:extras,id',
            'extras.*.quantity' => 'nullable|integer|min:1',
        ]);

        $user = $request->user();
        $car = Car::findOrFail($validated['car_id']);

        if ($car->status !== 'active') {
            return response()->json([
                'message' => 'This car is not active for booking.'
            ], 422);
        }

        if (! $car->is_available) {
            return response()->json([
                'message' => 'This car is currently unavailable.'
            ], 422);
        }

        $hasConflict = Booking::where('car_id', $validated['car_id'])
            ->whereIn('status', ['pending', 'confirmed', 'ongoing'])
            ->where(function ($query) use ($validated) {
                $query->where('start_date', '<=', $validated['end_date'])
                      ->where('end_date', '>=', $validated['start_date']);
            })
            ->exists();

        if ($hasConflict) {
            return response()->json([
                'message' => 'This car is already booked for the selected dates.'
            ], 422);
        }

        $start = Carbon::parse($validated['start_date']);
        $end = Carbon::parse($validated['end_date']);
        $totalDays = max(1, $start->diffInDays($end));

        $subtotal = $car->daily_rate * $totalDays;
        $extrasTotal = 0;

        $booking = Booking::create([
            'user_id' => $user->id,
            'car_id' => $validated['car_id'],
            'pickup_branch_id' => $validated['pickup_branch_id'],
            'dropoff_branch_id' => $validated['dropoff_branch_id'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'pickup_time' => $validated['pickup_time'],
            'dropoff_time' => $validated['dropoff_time'],
            'total_days' => $totalDays,
            'daily_rate' => $car->daily_rate,
            'subtotal' => $subtotal,
            'extras_total' => 0,
            'tax_amount' => 0,
            'discount_amount' => 0,
            'total_amount' => $subtotal,
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'notes' => $validated['notes'] ?? null,
            'driver_name' => $validated['driver_name'],
            'driver_phone' => $validated['driver_phone'],
            'driver_license_number' => $validated['driver_license_number'],
        ]);

        if (! empty($validated['extras'])) {
            foreach ($validated['extras'] as $item) {
                $extra = Extra::findOrFail($item['id']);
                $quantity = $item['quantity'] ?? 1;
                $totalPrice = $extra->price_per_day * $totalDays * $quantity;
                $extrasTotal += $totalPrice;

                $booking->extras()->attach($extra->id, [
                    'quantity' => $quantity,
                    'price_per_day' => $extra->price_per_day,
                    'total_price' => $totalPrice,
                ]);
            }
        }

        $booking->update([
            'extras_total' => $extrasTotal,
            'total_amount' => $subtotal + $extrasTotal,
        ]);

        return response()->json([
            'message' => 'Booking created successfully',
            'data' => $booking->load(['car', 'extras', 'pickupBranch', 'dropoffBranch']),
        ], 201);
    }

    public function show(Request $request, string $id)
    {
        $booking = Booking::with([
            'user',
            'car',
            'pickupBranch',
            'dropoffBranch',
            'payments',
            'extras'
        ])->findOrFail($id);

        if ($request->user()->role !== 'admin' && $booking->user_id !== $request->user()->id) {
            return response()->json([
                'message' => 'Unauthorized.'
            ], 403);
        }

        return response()->json($booking);
    }

    public function update(Request $request, string $id)
    {
        $booking = Booking::findOrFail($id);

        if ($request->user()->role !== 'admin') {
            return response()->json([
                'message' => 'Only admins can update booking status.'
            ], 403);
        }

        $validated = $request->validate([
            'status' => 'sometimes|in:pending,confirmed,ongoing,completed,cancelled',
            'payment_status' => 'sometimes|in:unpaid,paid,refunded,partial',
            'notes' => 'nullable|string',
        ]);

        $booking->update($validated);

        return response()->json([
            'message' => 'Booking updated successfully',
            'data' => $booking,
        ]);
    }

    public function destroy(Request $request, string $id)
    {
        $booking = Booking::findOrFail($id);

        if ($request->user()->role !== 'admin') {
            return response()->json([
                'message' => 'Only admins can delete bookings.'
            ], 403);
        }

        $booking->delete();

        return response()->json([
            'message' => 'Booking deleted successfully',
        ]);
    }
}
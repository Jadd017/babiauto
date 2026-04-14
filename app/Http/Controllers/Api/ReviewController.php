<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function index()
    {
        return response()->json(
            Review::with(['user', 'car', 'booking'])->latest()->paginate(10)
        );
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'car_id' => 'required|exists:cars,id',
            'booking_id' => 'nullable|exists:bookings,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string',
        ]);

        $review = Review::create([
            'user_id' => $request->user()->id,
            'car_id' => $validated['car_id'],
            'booking_id' => $validated['booking_id'] ?? null,
            'rating' => $validated['rating'],
            'comment' => $validated['comment'] ?? null,
            'is_approved' => false,
        ]);

        return response()->json($review->load(['user', 'car', 'booking']), 201);
    }

    public function show(string $id)
    {
        return response()->json(
            Review::with(['user', 'car', 'booking'])->findOrFail($id)
        );
    }

    public function update(Request $request, string $id)
    {
        $review = Review::findOrFail($id);

        if ($request->user()->role !== 'admin' && $review->user_id !== $request->user()->id) {
            return response()->json([
                'message' => 'Unauthorized.'
            ], 403);
        }

        $validated = $request->validate([
            'rating' => 'sometimes|integer|min:1|max:5',
            'comment' => 'nullable|string',
            'is_approved' => 'sometimes|boolean',
        ]);

        if ($request->user()->role !== 'admin') {
            unset($validated['is_approved']);
        }

        $review->update($validated);

        return response()->json($review->load(['user', 'car', 'booking']));
    }

    public function destroy(Request $request, string $id)
    {
        $review = Review::findOrFail($id);

        if ($request->user()->role !== 'admin' && $review->user_id !== $request->user()->id) {
            return response()->json([
                'message' => 'Unauthorized.'
            ], 403);
        }

        $review->delete();

        return response()->json([
            'message' => 'Review deleted successfully',
        ]);
    }
}
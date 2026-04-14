<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function index()
    {
        return response()->json(
            Payment::with('booking')->latest()->paginate(10)
        );
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'booking_id' => 'required|exists:bookings,id',
            'transaction_id' => 'nullable|string|max:255',
            'method' => 'required|in:cash,card,online',
            'amount' => 'required|numeric|min:0',
            'currency' => 'nullable|string|max:10',
            'status' => 'required|in:pending,paid,failed,refunded',
            'paid_at' => 'nullable|date',
        ]);

        $booking = Booking::findOrFail($validated['booking_id']);

        $payment = Payment::create($validated);

        $this->updateBookingPaymentStatus($booking);

        return response()->json($payment->load('booking'), 201);
    }

    public function show(string $id)
    {
        return response()->json(
            Payment::with('booking')->findOrFail($id)
        );
    }

    public function update(Request $request, string $id)
    {
        $payment = Payment::findOrFail($id);

        $validated = $request->validate([
            'transaction_id' => 'nullable|string|max:255',
            'method' => 'sometimes|in:cash,card,online',
            'amount' => 'sometimes|numeric|min:0',
            'currency' => 'nullable|string|max:10',
            'status' => 'sometimes|in:pending,paid,failed,refunded',
            'paid_at' => 'nullable|date',
        ]);

        $payment->update($validated);

        $this->updateBookingPaymentStatus($payment->booking);

        return response()->json($payment->load('booking'));
    }

    public function destroy(string $id)
    {
        $payment = Payment::findOrFail($id);
        $booking = $payment->booking;

        $payment->delete();

        $this->updateBookingPaymentStatus($booking);

        return response()->json([
            'message' => 'Payment deleted successfully',
        ]);
    }

    private function updateBookingPaymentStatus(Booking $booking): void
    {
        $booking->load('payments');

        $totalPaid = $booking->payments
            ->where('status', 'paid')
            ->sum('amount');

        $hasRefund = $booking->payments
            ->where('status', 'refunded')
            ->count() > 0;

        if ($hasRefund) {
            $booking->update(['payment_status' => 'refunded']);
            return;
        }

        if ($totalPaid <= 0) {
            $booking->update(['payment_status' => 'unpaid']);
            return;
        }

        if ($totalPaid < $booking->total_amount) {
            $booking->update(['payment_status' => 'partial']);
            return;
        }

        $booking->update(['payment_status' => 'paid']);
    }
}
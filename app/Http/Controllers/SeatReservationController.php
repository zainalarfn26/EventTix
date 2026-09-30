<?php

namespace App\Http\Controllers;

use App\Exceptions\SeatAlreadyBookedException;
use App\Exceptions\SeatAlreadyLockedException;
use App\Services\MidtransPaymentService;
use App\Services\SeatLockService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SeatReservationController extends Controller
{
    protected SeatLockService $lockService;
    protected MidtransPaymentService $paymentService;

    public function __construct(SeatLockService $lockService, MidtransPaymentService $paymentService)
    {
        $this->lockService = $lockService;
        $this->paymentService = $paymentService;
    }

    public function lockSeat(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'event_id' => 'required|exists:events,id',
            'seat_id' => 'required|exists:seats,id',
        ]);

        try {
            $user = $request->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Silakan login terlebih dahulu untuk memesan kursi.',
                ], 401);
            }

            $reservation = $this->lockService->lockSeat(
                $user,
                (int) $validated['event_id'],
                (int) $validated['seat_id']
            );

            $transaction = $this->paymentService->createSnapToken($reservation);

            return response()->json([
                'success' => true,
                'message' => 'Kursi berhasil dikunci selama 10 menit.',
                'data' => [
                    'reservation_id' => $reservation->id,
                    'seat_id' => $reservation->seat_id,
                    'seat_number' => $reservation->seat->seat_number ?? '',
                    'expires_at' => $reservation->expires_at->toIso8601String(),
                    'snap_token' => $transaction->snap_token,
                    'order_id' => $transaction->order_id,
                    'gross_amount' => $transaction->gross_amount,
                ],
            ]);

        } catch (SeatAlreadyLockedException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 409);
        } catch (SeatAlreadyBookedException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses kunci kursi: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function releaseSeat(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'reservation_id' => 'required|exists:reservations,id',
        ]);

        $released = $this->lockService->releaseSeat(
            $request->user(),
            (int) $validated['reservation_id']
        );

        return response()->json([
            'success' => $released,
            'message' => $released ? 'Kunci kursi dibatalkan.' : 'Gagal membatalkan kunci kursi.',
        ]);
    }
}

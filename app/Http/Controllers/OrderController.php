<?php

namespace App\Http\Controllers;

use App\Exceptions\TicketSoldOutException;
use App\Services\MidtransPaymentService;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    protected OrderService $orderService;
    protected MidtransPaymentService $paymentService;

    public function __construct(OrderService $orderService, MidtransPaymentService $paymentService)
    {
        $this->orderService = $orderService;
        $this->paymentService = $paymentService;
    }

    /**
     * Create a new order (checkout) and get Midtrans payment token.
     */
    public function checkout(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'event_id' => 'required|exists:events,id',
            'items' => 'required|array|min:1',
            'items.*.ticket_tier_id' => 'required|exists:ticket_tiers,id',
            'items.*.quantity' => 'required|integer|min:1|max:5',
        ]);

        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Silakan login terlebih dahulu untuk membeli tiket.',
            ], 401);
        }

        try {
            $order = $this->orderService->createOrder(
                $user,
                (int) $validated['event_id'],
                $validated['items']
            );

            $transaction = $this->paymentService->createSnapToken($order);

            return response()->json([
                'success' => true,
                'message' => 'Order berhasil dibuat. Selesaikan pembayaran dalam 10 menit.',
                'data' => [
                    'order_id' => $order->id,
                    'order_code' => $order->order_code,
                    'total_amount' => $order->total_amount,
                    'expires_at' => $order->expires_at->toIso8601String(),
                    'snap_token' => $transaction->snap_token,
                    'items' => $order->items->map(fn($item) => [
                        'tier' => $item->ticketTier->name,
                        'quantity' => $item->quantity,
                        'unit_price' => $item->unit_price,
                        'subtotal' => $item->subtotal,
                    ]),
                ],
            ]);

        } catch (TicketSoldOutException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 409);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses order: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Cancel a pending order.
     */
    public function cancel(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'order_id' => 'required|exists:orders,id',
        ]);

        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
            ], 401);
        }

        $cancelled = $this->orderService->cancelOrder($user, (int) $validated['order_id']);

        return response()->json([
            'success' => $cancelled,
            'message' => $cancelled ? 'Order berhasil dibatalkan.' : 'Gagal membatalkan order.',
        ]);
    }

    /**
     * Verify payment status manually (useful for localhost testing without webhooks).
     */
    public function verify(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'order_code' => 'required|string',
        ]);

        try {
            $transaction = $this->paymentService->verifyOrder($validated['order_code']);

            return response()->json([
                'success' => true,
                'status' => $transaction->transaction_status,
                'message' => 'Status order berhasil diperbarui.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}

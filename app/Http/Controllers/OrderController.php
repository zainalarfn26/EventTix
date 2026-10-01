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
            'promo_code' => 'nullable|string',
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
                $validated['items'],
                10,
                $validated['promo_code'] ?? null
            );

            return response()->json([
                'success' => true,
                'message' => 'Order berhasil dibuat. Pilih metode pembayaran.',
                'data' => [
                    'order_code' => $order->order_code,
                    'redirect_url' => route('user.orders.payment', $order->order_code),
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

    /**
     * Verify and apply a promo code.
     */
    public function applyPromo(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'promo_code' => 'required|string',
            'subtotal' => 'required|numeric|min:0',
        ]);

        $promo = \App\Models\Promo::where('code', $validated['promo_code'])->first();

        if (!$promo) {
            return response()->json([
                'success' => false,
                'message' => 'Kode promo tidak ditemukan.',
            ], 404);
        }

        if (!$promo->isValid()) {
            return response()->json([
                'success' => false,
                'message' => 'Kode promo sudah tidak berlaku atau kuota habis.',
            ], 400);
        }

        $discount = $promo->calculateDiscount($validated['subtotal']);

        return response()->json([
            'success' => true,
            'data' => [
                'discount_amount' => $discount,
                'promo_code' => $promo->code,
                'final_total' => max(0, $validated['subtotal'] - $discount),
            ],
            'message' => 'Kode promo berhasil digunakan!',
        ]);
    }

    public function chargePayment(Request $request, string $orderCode): JsonResponse
    {
        $validated = $request->validate([
            'payment_type' => 'required|string',
            'bank' => 'nullable|string',
        ]);

        $order = \App\Models\Order::where('order_code', $orderCode)
            ->where('user_id', $request->user()->id)
            ->where('status', 'pending')
            ->firstOrFail();

        // If there is already a transaction, just return it
        $transaction = \App\Models\Transaction::where('order_id', $order->id)->where('transaction_status', 'pending')->first();

        if (!$transaction) {
            try {
                $transaction = $this->paymentService->createCoreApiTransaction($order, $validated['payment_type'], $validated['bank']);
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal memproses pembayaran: ' . $e->getMessage(),
                ], 500);
            }
        }

        return response()->json([
            'success' => true,
            'data' => [
                'redirect_url' => route('user.orders.instruction', $orderCode),
            ],
        ]);
    }
    public function checkStatus(Request $request, string $orderCode): JsonResponse
    {
        $order = \App\Models\Order::where('order_code', $orderCode)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        // Proactively check Midtrans Server for status if it's still pending in DB
        // This acts as a reliable fallback if webhooks are delayed or cannot reach localhost.
        if ($order->status === 'pending') {
            try {
                $this->paymentService->verifyOrder($orderCode);
                $order->refresh();
            } catch (\Exception $e) {
                // Ignore network errors, fallback to existing DB status
            }
        }

        return response()->json([
            'status' => $order->status,
        ]);
    }
}

<?php

namespace App\Http\Controllers;

use App\Exceptions\InvalidMidtransSignatureException;
use App\Services\MidtransPaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WebhookController extends Controller
{
    protected MidtransPaymentService $paymentService;

    public function __construct(MidtransPaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    public function handleMidtransWebhook(Request $request): JsonResponse
    {
        try {
            $payload = $request->all();
            $transaction = $this->paymentService->handleWebhook($payload);

            return response()->json([
                'success' => true,
                'message' => 'Webhook notification processed successfully.',
                'order_code' => $transaction->order_code,
                'status' => $transaction->transaction_status,
            ]);

        } catch (InvalidMidtransSignatureException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 403);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to process webhook: ' . $e->getMessage(),
            ], 500);
        }
    }
}

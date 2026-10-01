<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class TicketController extends Controller
{
    public function show(Ticket $ticket)
    {
        $ticket->load(['event.venue', 'ticketTier', 'user', 'order']);

        // Generate SVG string for QR code
        $qrCodeSvg = QrCode::size(200)->generate($ticket->qr_code_hash);

        return view('tickets.show', compact('ticket', 'qrCodeSvg'));
    }

    public function scanner()
    {
        return view('tickets.scanner');
    }

    /**
     * Scan QR code and check-in the ticket.
     * On success, instruct organizer to give the correct wristband color.
     */
    public function scan(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'qr_code_hash' => 'required|string',
        ]);

        $ticket = Ticket::with(['event', 'ticketTier', 'user'])
            ->where('qr_code_hash', $validated['qr_code_hash'])
            ->first();

        if (!$ticket) {
            return response()->json([
                'success' => false,
                'message' => 'Tiket tidak ditemukan / tidak valid.',
            ], 404);
        }

        if ($ticket->status === 'cancelled') {
            return response()->json([
                'success' => false,
                'message' => 'Tiket ini sudah dibatalkan.',
            ], 422);
        }

        if ($ticket->status === 'checked_in') {
            return response()->json([
                'success' => false,
                'message' => 'PERINGATAN: Tiket sudah pernah dipakai check-in pada ' . $ticket->checked_in_at->format('d M Y H:i'),
                'ticket' => $ticket,
            ], 409);
        }

        $ticket->update([
            'status' => 'checked_in',
            'checked_in_at' => now(),
            'checked_in_by' => $request->user()?->id,
        ]);

        return response()->json([
            'success' => true,
            'message' => '✅ Check-in Berhasil! Berikan gelang warna: ' . strtoupper($ticket->ticketTier->wristband_color ?? $ticket->ticketTier->name),
            'ticket' => [
                'code' => $ticket->ticket_code,
                'tier' => $ticket->ticketTier->name,
                'tier_color' => $ticket->ticketTier->color,
                'wristband_color' => $ticket->ticketTier->wristband_color ?? $ticket->ticketTier->name,
                'event' => $ticket->event->title,
                'customer' => $ticket->user->name ?? '',
            ],
        ]);
    }

    /**
     * Get recent scan history for the current organizer/admin.
     */
    public function history(Request $request): JsonResponse
    {
        $history = Ticket::with(['event', 'ticketTier', 'user'])
            ->whereNotNull('checked_in_at')
            ->where('checked_in_by', $request->user()->id)
            ->orderBy('checked_in_at', 'desc')
            ->limit(20)
            ->get()
            ->map(function ($ticket) {
                return [
                    'id' => $ticket->id,
                    'code' => $ticket->ticket_code,
                    'tier' => $ticket->ticketTier->name,
                    'tier_color' => $ticket->ticketTier->color,
                    'wristband_color' => $ticket->ticketTier->wristband_color ?? $ticket->ticketTier->name,
                    'event' => $ticket->event->title,
                    'customer' => $ticket->user->name ?? '',
                    'scanned_at' => $ticket->checked_in_at->diffForHumans(),
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $history,
        ]);
    }
}

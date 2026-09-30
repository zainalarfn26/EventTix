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
        $ticket->load(['reservation.event.venue', 'reservation.seat', 'reservation.user']);

        // Generate SVG string for QR code
        $qrCodeSvg = QrCode::size(200)->generate($ticket->qr_code_hash);

        return view('tickets.show', compact('ticket', 'qrCodeSvg'));
    }

    public function scanner()
    {
        return view('tickets.scanner');
    }

    public function scan(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'qr_code_hash' => 'required|string',
        ]);

        $ticket = Ticket::where('qr_code_hash', $validated['qr_code_hash'])->first();

        if (!$ticket) {
            return response()->json([
                'success' => false,
                'message' => 'Tiket tidak ditemukan / tidak valid.',
            ], 404);
        }

        if ($ticket->is_checked_in) {
            return response()->json([
                'success' => false,
                'message' => 'PERINGATAN: Tiket sudah pernah dipakai check-in pada ' . $ticket->checked_in_at->format('d M Y H:i'),
                'ticket' => $ticket,
            ], 409);
        }

        $ticket->update([
            'is_checked_in' => true,
            'checked_in_at' => now(),
            'checked_in_by' => $request->user()?->id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Check-in Berhasil! Silakan masuk ke venue.',
            'ticket' => [
                'code' => $ticket->ticket_code,
                'seat' => $ticket->reservation->seat->seat_number ?? '',
                'event' => $ticket->reservation->event->title ?? '',
                'customer' => $ticket->reservation->user->name ?? '',
            ],
        ]);
    }
}

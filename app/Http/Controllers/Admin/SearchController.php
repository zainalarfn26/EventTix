<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Order;
use App\Models\Promo;
use App\Models\Ticket;
use App\Models\User;
use App\Models\Venue;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        $query = trim((string) $request->get('q', ''));

        if ($query === '') {
            return view('admin.search.index', [
                'query' => '',
                'events' => collect(),
                'users' => collect(),
                'orders' => collect(),
                'tickets' => collect(),
                'venues' => collect(),
                'promos' => collect(),
                'totalResults' => 0,
            ]);
        }

        $term = '%' . $query . '%';

        $events = Event::with('venue')
            ->where(function ($q) use ($term) {
                $q->where('title', 'like', $term)
                  ->orWhere('description', 'like', $term)
                  ->orWhereHas('venue', fn ($v) => $v->where('name', 'like', $term)->orWhere('city', 'like', $term));
            })
            ->limit(8)
            ->get();

        $users = User::with('roles')
            ->where(function ($q) use ($term) {
                $q->where('name', 'like', $term)
                  ->orWhere('email', 'like', $term);
            })
            ->limit(8)
            ->get();

        $orders = Order::with(['user', 'event'])
            ->where(function ($q) use ($term) {
                $q->where('order_code', 'like', $term)
                  ->orWhere('promo_code', 'like', $term)
                  ->orWhereHas('user', fn ($u) => $u->where('name', 'like', $term)->orWhere('email', 'like', $term))
                  ->orWhereHas('event', fn ($e) => $e->where('title', 'like', $term));
            })
            ->limit(8)
            ->get();

        $tickets = Ticket::with(['event', 'ticketTier', 'user', 'order'])
            ->where(function ($q) use ($term) {
                $q->where('ticket_code', 'like', $term)
                  ->orWhereHas('user', fn ($u) => $u->where('name', 'like', $term)->orWhere('email', 'like', $term))
                  ->orWhereHas('event', fn ($e) => $e->where('title', 'like', $term));
            })
            ->limit(8)
            ->get();

        $venues = Venue::where(function ($q) use ($term) {
                $q->where('name', 'like', $term)
                  ->orWhere('city', 'like', $term)
                  ->orWhere('address', 'like', $term);
            })
            ->limit(8)
            ->get();

        $promos = Promo::where('code', 'like', $term)
            ->limit(8)
            ->get();

        $totalResults = $events->count() + $users->count() + $orders->count() + $tickets->count() + $venues->count() + $promos->count();

        return view('admin.search.index', compact(
            'query',
            'events',
            'users',
            'orders',
            'tickets',
            'venues',
            'promos',
            'totalResults'
        ));
    }
}

<?php

namespace App\Exceptions;

use Exception;

class SeatAlreadyLockedException extends Exception
{
    protected $message = 'Kursi ini sedang dalam proses pemesanan oleh pengguna lain. Silakan coba beberapa saat lagi.';
}

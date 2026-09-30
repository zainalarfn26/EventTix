<?php

namespace App\Exceptions;

use Exception;

class SeatAlreadyBookedException extends Exception
{
    protected $message = 'Kursi ini sudah terpesan atau tidak tersedia.';
}

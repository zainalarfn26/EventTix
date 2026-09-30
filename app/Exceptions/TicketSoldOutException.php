<?php

namespace App\Exceptions;

use Exception;

class TicketSoldOutException extends Exception
{
    public function __construct(string $message = 'Tiket sudah habis terjual.')
    {
        parent::__construct($message);
    }
}

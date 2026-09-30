<?php

namespace App\Exceptions;

use Exception;

class InvalidMidtransSignatureException extends Exception
{
    protected $message = 'Invalid Midtrans Webhook Signature key.';
}

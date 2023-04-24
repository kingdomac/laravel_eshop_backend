<?php

namespace App\Exceptions\Order;

use App\Exceptions\CustomException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Http;

class OrderInvalidException extends CustomException
{
    protected $message = 'The given data was invalid.';
    protected $statusCode = Response::HTTP_UNPROCESSABLE_ENTITY;
    protected $errors;

    public function __construct($errors)
    {
        $this->errors = $errors;
    }

    public function render($request)
    {
        return response()->json([
            'message' => $this->message,
            'errors' => $this->errors,
        ], $this->statusCode);
    }
}

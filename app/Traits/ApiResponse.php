<?php

namespace App\Traits;

trait ApiResponse
{
    protected function successResponse($data = null, string $message = 'Success', int $status = 200)
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data'    => $data,
            'meta'    => null,
            'status'  => $status,
        ], $status);
    }

    protected function errorResponse(string $message, int $status = 400, $meta = null)
    {
        return response()->json([
            'success' => false,
            'message' => $message,
        'meta'    => $meta,
            'status'  => $status,
        ], $status);
    }
}
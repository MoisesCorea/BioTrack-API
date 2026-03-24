<?php

namespace App\Http\Controllers;

abstract class Controller
{
    protected function successResponse($data = null, $message = null, $statusCode = 200)
    {
        return response()->json([
            'message' => $message,
            'statusCode' => $statusCode,
            'data' => $data
        ], $statusCode);
    }

    protected function errorResponse($message, $statusCode = 400, $errors = null)
    {
        $response = [
            'message' => $message,
            'statusCode' => $statusCode,
        ];

        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $statusCode);
    }
}

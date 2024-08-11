<?php

namespace App\Http\Controllers;

abstract class Controller
{
    /**
     * Create a standardized API response.
     *
     * @param  int  $status
     * @param  string  $message
     * @param  array  $data
     * @param  array  $errors
     * @return \Illuminate\Http\JsonResponse
     */
    public function jsonResponse($status = 200, $message = '', $data = [])
    {
        return response()->json([
            'message' => $message ? $message : __('success.data_fetched'),
            'data' => $data,
        ], $status);
    }
}

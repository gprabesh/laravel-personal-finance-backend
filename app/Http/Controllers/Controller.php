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
        $data['message'] = $message ? $message : __('success.data_fetched');
        return response()->json($data, $status);
    }
}

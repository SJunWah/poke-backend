<?php

namespace App\Traits;

trait HttpResponse
{
    protected function success($data, $message = "", $status = 200)
    {
        return response()->json([
            "error" => false,
            'status' => $status,
            'message' => $message,
            'data' => $data,
        ]);
    }

    protected function error($data, $message = "", $status = 400)
    {
        return response()->json([
            'error' => true,
            'status' => $status,
            'message' => $message,
            'data' => $data,
        ]);
    }
}

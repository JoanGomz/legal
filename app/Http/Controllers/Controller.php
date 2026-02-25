<?php

namespace App\Http\Controllers;

abstract class Controller
{
    public function responseLivewire($status = 'success', $message, $data = [])
    {
        return [
            'status' => $status,
            'message' => $message ?? 'success',
            'data' => $data
        ];
    }
}

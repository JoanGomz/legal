<?php

namespace App\Http\Controllers;

abstract class Controller
{
    public function responseLivewire(string $status, string $message, $data = [])
    {
        return [
            'status' => $status,
            'message' => $message ?? 'success',
            'data' => $data
        ];
    }
}

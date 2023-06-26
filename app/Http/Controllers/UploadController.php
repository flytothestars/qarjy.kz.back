<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class UploadController extends Controller
{
    public function upload(Request $request): JsonResponse
    {
        $path = $request->file('file')->store('files');
        return response()->json([
            'status' => 'success',
            'location' => $path,
            'url' => Storage::url($path),
        ]);
    }
}

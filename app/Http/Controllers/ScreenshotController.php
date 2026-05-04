<?php

// app/Http/Controllers/ScreenshotController.php
namespace App\Http\Controllers;

use App\Models\UserScreenshot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ScreenshotController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'image'    => 'required|string',         // data URL or base64
            'width'    => 'nullable|integer',
            'height'   => 'nullable|integer',
            'page_url' => 'nullable|string',
        ]);

        // Accept both "data:image/jpeg;base64,..." and raw base64
        if (str_starts_with($data['image'], 'data:image')) {
            [$meta, $b64] = explode(',', $data['image'], 2);
            $ext = str_contains($meta, 'png') ? 'png' : 'jpg';
        } else {
            $b64 = $data['image'];
            $ext = 'jpg';
        }

        $binary = base64_decode($b64);
        if ($binary === false) {
            return response()->json(['error' => 'Invalid image data'], 422);
        }

        $dir = 'user_screenshots/'.now()->format('Y/m/d');
        $name = uniqid('ss_').".$ext";
        $path = "$dir/$name";

        Storage::disk('public')->put($path, $binary);
        $url = Storage::disk('public')->url($path);

        $row = UserScreenshot::create([
            'user_id'    => $request->user()->id,
            'path'       => $path,
            'url'        => $url,
            'width'      => $data['width'] ?? null,
            'height'     => $data['height'] ?? null,
            'page_url'   => $data['page_url'] ?? $request->headers->get('referer'),
            'user_agent' => $request->userAgent(),
        ]);

        return response()->json([
            'id'  => $row->id,
            'url' => $row->url,
        ]);
    }
}


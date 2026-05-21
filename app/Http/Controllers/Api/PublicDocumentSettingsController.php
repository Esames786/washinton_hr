<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DocumentSetting;

class PublicDocumentSettingsController extends Controller
{
    public function index()
    {
        $documents = DocumentSetting::where('status', 1)
            ->orderBy('id')
            ->get(['id', 'title', 'is_required', 'description', 'input_type']);

        return response()->json([
            'success'   => true,
            'documents' => $documents,
        ]);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentSetting extends Model
{
    use HasFactory;

    protected $table = 'hr_document_settings';

    protected $fillable = [
        'title',
        'is_required',
        'description',
        'input_type',
        'max_files',   // P3: files allowed per document (Selfie/Workplace = 4)
        'file_kind',   // P3: any | image | video
        'condition',   // P3: null | own | rent (conditional documents)
        'status',
        'created_by',
        'updated_by'
    ];
}

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
        'status',
        'created_by',
        'updated_by'
    ];
}

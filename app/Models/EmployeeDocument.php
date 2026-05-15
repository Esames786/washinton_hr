<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeDocument extends Model
{
    use HasFactory;

    protected $table = 'hr_employee_documents';

    protected $fillable = [
        'employee_id',
        'document_setting_id',
        'file_path',
        'mime_type',
        'file_name',
        'status'
    ];
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'id');
    }

    public function documentSetting()
    {
        return $this->belongsTo(DocumentSetting::class, 'document_setting_id', 'id');
    }
}

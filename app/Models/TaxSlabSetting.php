<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaxSlabSetting extends Model
{
    use HasFactory;

    protected $table = 'hr_tax_slab_settings';

    protected $fillable = [
        'title',
        'min_income',
        'max_income',
        'rate',
        'type',
        'global_cap',
        'description',
        'status',
        'created_by',
        'updated_by',
    ];
}

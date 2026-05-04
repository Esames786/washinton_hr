<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CurrencyRate extends Model
{
    use HasFactory;

    protected $table = 'hr_currency_rates';

    protected $fillable = [
        'from_currency',
        'to_currency',
        'rate',
        'status'
    ];

}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PettyCashHead extends Model
{
    use HasFactory;

    protected $table = 'hr_petty_cash_heads';

    protected $fillable = [
        'name',
        'type', // expense / income
        'status'
    ];

    public function transactions()
    {
        return $this->hasMany(PettyCashTransaction::class, 'head_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PettyCashMaster extends Model
{
    use HasFactory;

    protected $table = 'hr_petty_cash_masters';

    protected $fillable = [
        'title',
        'opening_balance',
        'current_balance',
    ];

    public function histories()
    {
        return $this->hasMany(PettyCashMasterHistory::class, 'master_id');
    }

    public function transactions()
    {
        return $this->hasMany(PettyCashTransaction::class, 'master_id');
    }
}

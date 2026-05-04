<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PettyCashMasterHistory extends Model
{
    use HasFactory;
    protected $table = 'hr_petty_cash_master_histories';
    protected $fillable = [
        'master_id',
        'amount',
        'action',
        'description',
    ];

    public function master()
    {
        return $this->belongsTo(PettyCashMaster::class, 'master_id');
    }
}

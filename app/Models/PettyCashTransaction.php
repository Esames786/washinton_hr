<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PettyCashTransaction extends Model
{
    use HasFactory;

    protected $table = 'hr_petty_cash_transactions';

    protected $fillable = [
        'master_id',
        'head_id',
        'date',
        'entry_type',
        'amount',
        'description',
        'balance',
        'created_by',
        'status',
        'image'
    ];

    public function master()
    {
        return $this->belongsTo(PettyCashMaster::class, 'master_id');
    }

    public function head()
    {
        return $this->belongsTo(PettyCashHead::class, 'head_id');
    }
}

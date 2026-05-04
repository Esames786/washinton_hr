<?php

namespace App\Traits;

use App\Models\PettyCashMaster;
use Illuminate\Support\Facades\DB;

class PettyCashLedgerTrait
{
    public static function getLedger($head_id = null, $from = null, $to = null)
    {

        $master = PettyCashMaster::latest()->first();
        $master_id = $master ? $master->id : null;

        // 1) Fetch master's opening balance
        $master = DB::table('hr_petty_cash_masters')->where('id', $master_id )->first();
        if (!$master) {
            return [];  // Or throw error
        }
        $openingFromMaster = $master->opening_balance ?? 0;

        // 2) Net impact from pre-from transactions (for this master)
        $preNetQuery = DB::table('petty_cash_transactions as t')
            ->join('petty_cash_heads as h', 'h.id', '=', 't.head_id')
            ->where('t.master_id', 5)
            ->where('t.status', 'approved')
            ->when($head_id, fn($q) => $q->where('t.head_id', $head_id))
            ->when($from, fn($q) => $q->where('t.date', '<', $from))
            ->selectRaw("
                SUM(
                    CASE
                        WHEN t.entry_type = 'credit' THEN t.amount
                        ELSE -t.amount
                    END
                ) as net_impact
            ")
            ->first();

        $preNetImpact = $preNetQuery->net_impact ?? 0;
        $openingBalance = $openingFromMaster + $preNetImpact;


        // 2) Transactions (between from_date and to_date)
        $transactions = DB::table('petty_cash_transactions as t')
            ->join('petty_cash_heads as h', 'h.id', '=', 't.head_id')
            ->where('t.status', 'approved')
            ->when($head_id, fn($q) => $q->where('t.head_id', $head_id))
            ->when($from && $to, fn($q) => $q->whereBetween('t.date', [$from, $to]))
            ->select(
                't.id',
                't.date',
                't.description as particulars',
                'h.name as head',
                'h.type as head_type',
                't.entry_type',
                't.amount'
            )
            ->orderBy('t.date', 'asc')
            ->get();


        // 3) Build final ledger with running balance
        $ledger = [];
        $balance = $openingBalance;

        // Helper function for number formatting (Indian style: commas every 2 digits after thousands, 2 decimals)
        $formatNumber = fn($num) => number_format($num, 2, '.', ',');

        // Opening Balance row
        $ledger[] = [
            'id' => null,
            'date' => $from,
            'particulars' => 'Opening Balance',
            'head' => 'Cash at Hand',
            'debit' => $openingBalance > 0 ? $formatNumber($openingBalance) : null,
            'credit' => $openingBalance < 0 ? $formatNumber(abs($openingBalance)) : null,
            'balance' => $formatNumber($balance),
        ];

        // Transactions
        foreach ($transactions as $txn) {
            $debit = $credit = 0;

//            if ($txn->head_type === 'expense' && $txn->entry_type === 'debit') {
//                $debit = $txn->amount;
//                $balance -= $txn->amount;
//            } elseif ($txn->head_type === 'income' && $txn->entry_type === 'credit') {
//                $credit = $txn->amount;
//                $balance += $txn->amount;
//            }
            if ($txn->head_type === 'expense') {
                if ($txn->entry_type === 'debit') {
                    $debit = -$txn->amount; // cash out -> minus
                    $balance += $debit;      // balance -= amount
                } elseif ($txn->entry_type === 'credit') {
                    $credit = $txn->amount;  // refund / reversal -> plus
                    $balance += $credit;
                }
            } elseif ($txn->head_type === 'income') {
                if ($txn->entry_type === 'credit') {
                    $credit = $txn->amount; // cash in -> plus
                    $balance += $credit;
                } elseif ($txn->entry_type === 'debit') {
                    $debit = -$txn->amount; // reversal -> minus
                    $balance += $debit;
                }
            }

            $ledger[] = [
                'id' => $txn->id,
                'date' => $txn->date,
                'particulars' => $txn->particulars,
                'head' => $txn->head ." ($txn->head_type)",
                'debit' => $debit != 0 ? ($debit > 0 ? '+' : '-') . $formatNumber(abs($debit)) : null,
                'credit' => $credit != 0 ? ($credit > 0 ? '+' : '-') . $formatNumber(abs($credit)) : null,
                'balance' => $formatNumber($balance),
            ];
        }

        // Closing Balance row
        if ($to) {
            $closingDebit = $balance > 0 ? $balance : 0;
            $closingCredit = $balance < 0 ? abs($balance) : 0;

            $ledger[] = [
                'id' => null,
                'date' => $to,
                'particulars' => 'Closing Balance',
                'head' => $master->title,
                'debit' => $closingDebit > 0 ? $formatNumber($closingDebit) : null,
                'credit' => $closingCredit > 0 ? $formatNumber($closingCredit) : null,
                'balance' => $formatNumber($balance),
            ];
        }

        return $ledger;
    }
}

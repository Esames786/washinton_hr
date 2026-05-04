<?php

namespace App\Jobs;

use App\Models\CommissionSetting;
use App\Models\CurrencyRate;
use App\Models\Employee;
use App\Models\EmployeeAttendance;
use App\Models\GratuityBalance;
use App\Models\GratuitySetting;
use App\Models\Payroll;
use App\Models\PayrollDetail;
use App\Models\PayslipItem;
use App\Models\TaxSlabSetting;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class GeneratePayrollJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $payrollId;

    // Item type mapping
    const ITEM_EARNING  = 1;
    const ITEM_DEDUCTION = 2;
    const ITEM_INFO     = 3;

    public function __construct(int $payrollId)
    {
        $this->payrollId = $payrollId;
        $this->queue = 'payroll_generate_job';
    }

    public function handle()
    {
        $payroll = Payroll::find($this->payrollId);
        if (!$payroll) return;

        $from = Carbon::parse($payroll->from_date)->startOfDay();
        $to   = Carbon::parse($payroll->to_date)->endOfDay();
        $date = Carbon::now()->toDateString();

        $employees = Employee::where('employee_status_id', 1)->get();

        DB::beginTransaction();

        try {
            $total_basic_salary = 0;
            $total_payroll_deduction = 0;
            $total_payroll_tax=0;
            $total_net_salary=0;

            foreach ($employees as $emp) {

                $basicSalary = $emp->basic_salary ?? 0;

                $total_basic_salary+=$basicSalary;

                // -------------------------
                // Gratuity Calculation
                // -------------------------
                $employeeGratuity = 0;
                $companyGratuity = 0;

                if ($emp->gratuity_id){
                    if($date >= $emp->valid_gratuity_date){
                        $gratuity = GratuitySetting::where('status', 1)
                            ->where('id',$emp->gratuity_id)
                            ->first();

                        if ($gratuity && $gratuity->is_pf) {
                            $employee_percentage = $gratuity->employee_contribution_percentage ?? 0;
                            $company_percentage  = $gratuity->company_contribution_percentage ?? 0;

                            $employeeGratuity = round($basicSalary * ($employee_percentage / 100), 2);
                            $companyGratuity  = round($basicSalary * ($company_percentage / 100), 2);
                        }

                    }
                }


                // -------------------------
                // Attendance Calculation
                // -------------------------
//                    $earned = EmployeeAttendance::where('employee_id', $emp->id)
//                        ->whereBetween('attendance_date', [$from_date, $to_date])
//                        ->whereIn('attendance_status_id', [2])
//                        ->sum('calculated_salary');

                $from_date = $from->toDateString();
                $to_date = $to->toDateString();

                $absent_info = EmployeeAttendance::where('employee_id', $emp->id)
                    ->whereBetween('attendance_date', [$from_date, $to_date])
                    ->where('attendance_status_id', 5);

                $absent_deduction = $absent_info->sum('deducted_salary');
                $absent_count     = $absent_info->count();

                $late = EmployeeAttendance::where('employee_id', $emp->id)
                    ->whereBetween('attendance_date', [$from_date, $to_date])
                    ->where('attendance_status_id', 1);
                $late_count = $late->count();
                $late_deduction = $late->sum('deducted_salary');

                $early_exit = EmployeeAttendance::where('employee_id', $emp->id)
                    ->whereBetween('attendance_date', [$from_date, $to_date])
                    ->where('attendance_status_id', 4);
                $early_exit_count = $early_exit->count();
                $early_exit_deduction = $early_exit->sum('deducted_salary');

                $half_day = EmployeeAttendance::where('employee_id', $emp->id)
                    ->whereBetween('attendance_date', [$from_date, $to_date])
                    ->where('attendance_status_id', 3);
                $half_day_count = $half_day->count();
                $half_day_deduction = $half_day->sum('deducted_salary');

                $quarter = EmployeeAttendance::where('employee_id', $emp->id)
                    ->whereBetween('attendance_date', [$from_date, $to_date])
                    ->where('attendance_status_id', 9);
                $quarter_count = $quarter->count();
                $quarter_deduction = $quarter->sum('deducted_salary');


                // -------------------------
                // Commission Calculation
                // -------------------------
                $commission = 0;
                if(in_array($emp->account_type_id,[2,3])){
                    $commission = $this->calculateCommission($emp->commission_id, $from_date,$to_date,$emp->agent_id);
                }

                // -------------------------
                // PayrollDetail
                // -------------------------
                $totalDeductions = $absent_deduction
                    + $late_deduction
                    + $early_exit_deduction
                    + $half_day_deduction
                    + $quarter_deduction
                    + $employeeGratuity;


                // -------------------------
                // Tax Calculation
                // -------------------------
                $taxAmount = 0;
                $appliedSlabId =null;
                if ($emp->is_taxable && $emp->tax_slab_setting_id) {
                    $slab = TaxSlabSetting::where('status', 1)->find($emp->tax_slab_setting_id);
                    if ($slab) {
                        $taxableIncome = $basicSalary; // taxable base
//                        $taxableIncome = $basicSalary + $commission; // taxable base
                        $taxAmount = $slab->type === 'percentage'
                            ? round($taxableIncome * ($slab->rate / 100), 2)
                            : round($slab->rate, 2);

                        if ($slab->global_cap && $taxAmount > $slab->global_cap) {
                            $taxAmount = $slab->global_cap;
                        }
                        $appliedSlabId=$slab->id;
                        $totalDeductions += $taxAmount;

                        $total_payroll_tax += $taxAmount;
                    }
                }

                $total_payroll_deduction+=$totalDeductions;


                // -------------------------
                // Overtime Calculation
                // -------------------------
                $overtime_info = EmployeeAttendance::where('employee_id', $emp->id)
                    ->whereBetween('attendance_date', [$from_date, $to_date]);

                $totalOvertimeSeconds = $overtime_info->sum('overtime_seconds');
                $totalOvertimeAmount  = $overtime_info->sum('overtime_amount');

                $netSalary = round($basicSalary - $totalDeductions + $commission, 2);
                $final_salary = round($netSalary + $totalOvertimeAmount, 2);

                $total_net_salary += $final_salary;
//                $netSalary       = round($earned - $employeeGratuity + $commission, 2);

                $detail = new PayrollDetail();
                $detail->payroll_id        = $this->payrollId;
                $detail->employee_id       = $emp->id;
                $detail->basic_salary      = $basicSalary;
                $detail->total_commission  = $commission;
                $detail->employee_gratuity = $employeeGratuity;
                $detail->company_gratuity  = $companyGratuity;
                $detail->total_deductions  = round($totalDeductions, 2);
                $detail->net_salary        = $final_salary;
                $detail->tax_amount          = $taxAmount;
                $detail->tax_slab_setting_id = $appliedSlabId;
                $detail->status_id         = 1;
                $detail->created_at        = now();
                $detail->updated_at        = now();
                $detail->save();

                // -------------------------
                // Payslip Items
                // -------------------------
//                $this->insertItem($detail->id, self::ITEM_EARNING, 'Attendance-based Basic (earned)', $earned);

                // -------------------------
                    // Earnings
                // -------------------------

                if ($commission > 0) {
                    $this->insertItem($detail->id, self::ITEM_EARNING, 'Commission from Orders', $commission);
                }
                if ($totalOvertimeAmount > 0) {
//                    'Overtime (' . gmdate('H:i', $totalOvertimeSeconds) . ' hrs)',
                    $this->insertItem($detail->id,self::ITEM_EARNING,'Overtime',$totalOvertimeAmount);
                }

                // -------------------------
                    // Deductions
                // -------------------------

                if ($employeeGratuity > 0) {
                    $this->insertItem($detail->id, self::ITEM_DEDUCTION, 'Employee Gratuity Contribution', -$employeeGratuity);
                }

                if ($absent_deduction > 0) {
                    $this->insertItem($detail->id, self::ITEM_DEDUCTION, "Absent Days: $absent_count", -$absent_deduction);
                }

                if ($late_deduction > 0) {
                    $this->insertItem($detail->id, self::ITEM_DEDUCTION, "Late Days: $late_count", -$late_deduction);
                }

                if ($early_exit_deduction > 0) {
                    $this->insertItem($detail->id, self::ITEM_DEDUCTION, "Early Exit: $early_exit_count", -$early_exit_deduction);
                }

                if ($half_day_deduction > 0) {
                    $this->insertItem($detail->id, self::ITEM_DEDUCTION, "Half Days: $half_day_count", -$half_day_deduction);
                }

                if ($quarter_deduction > 0) {
                    $this->insertItem($detail->id, self::ITEM_DEDUCTION, "Quarter Days: $quarter_count", -$quarter_deduction);
                }

                if ($taxAmount > 0) {
                    $this->insertItem($detail->id, self::ITEM_DEDUCTION, "Income Tax", -$taxAmount);
                }

                // -------------------------
                // Info (non-deduction / non-earning)
                // -------------------------
                if ($companyGratuity > 0) {
                    $this->insertItem($detail->id, self::ITEM_INFO, 'Company Gratuity Contribution', $companyGratuity);
                }

                // -------------------------
                // Update Gratuity Balance
                // -------------------------
                if ($employeeGratuity > 0 && $companyGratuity > 0) {
                    $this->updateGratuityBalance($emp->id, $payroll->payroll_month, $employeeGratuity, $companyGratuity);
                }

            }

            $payroll->notes     = 'Payroll Draft Successfully ready for Approval';
            $payroll->status_id = 2;
            $payroll->total_basic_salary = round($total_basic_salary,2);
            $payroll->total_tax = round($total_payroll_tax,2);
            $payroll->total_deduction = round($total_payroll_deduction,2);
            $payroll->total_net_salary = round($total_net_salary,2);
            $payroll->updated_at = now();
            $payroll->save();

            DB::commit();

        } catch (\Throwable $e) {
            DB::rollBack();

            $payroll->notes = 'Generation failed: '.$e->getMessage();
            $payroll->status_id = 5;
            $payroll->updated_at = now();
            $payroll->save();

            throw $e;
        }
    }

    // -------------------------
    // Insert payslip item
    // -------------------------
    private function insertItem(int $detailId, int $type, string $desc, float $amount): void
    {
        $item = new PayslipItem();
        $item->payroll_detail_id = $detailId;
        $item->item_type_id      = $type;
        $item->description       = $desc;
        $item->amount            = $amount;
        $item->created_at        = now();
        $item->updated_at        = now();
        $item->save();
    }

    // -------------------------
    // Commission calculation
    // -------------------------
    private function calculateCommission($commission_id, $from_date, $to_date,$agent_id = null): float
    {
        if (!$agent_id) {
            return 0.0;
        }

        $commission = CommissionSetting::where('status', 1)->find($commission_id);
        if (!$commission) {
            return 0.0;
        }

        return DB::transaction(function () use ($commission, $from_date, $to_date, $agent_id) {
            $order_ids = DB::table('order_payments')
                ->where('user_id', $agent_id)
                ->whereBetween('confirmation_date', [$from_date, $to_date])
                ->where('payment_status', 'Payment Confirmed')
                ->where('is_paid', 0)
                ->pluck('id');

            if ($order_ids->isEmpty()) {
                return 0.0;
            }

            $profit_usd = DB::table('order_payments')
                ->whereIn('id', $order_ids)
                ->sum('profit');

            $currency_rate = CurrencyRate::where('from_currency', 'USD')
                ->where('to_currency', 'PKR')
                ->where('status', 1)
                ->latest('id')
                ->value('rate') ?? 281.10;

            $profit_pkr = $profit_usd * $currency_rate;

            $commission_amt = $commission->commission_type_id == 1
                ? ($profit_pkr * $commission->value) / 100
                : $commission->value;

            DB::table('order_payments')
                ->whereIn('id', $order_ids)
                ->update(['is_paid' => 1]);

            return round($commission_amt, 2);
        });
    }


    // -------------------------
    // Update gratuity balance
    // -------------------------
    private function updateGratuityBalance($employeeId, $month, float $empContr, float $compContr): void
    {
        $existing = GratuityBalance::where('employee_id', $employeeId)
            ->where('month', $month)
            ->first();

        if ($existing) {
            $existing->employee_contribution += $empContr;
            $existing->company_contribution  += $compContr;
            $existing->closing_balance       = $existing->opening_balance + $existing->employee_contribution + $existing->company_contribution;
            $existing->updated_at = now();
            $existing->save();
        } else {
            $prevMonth = Carbon::createFromFormat('Y-m', $month)->subMonth()->format('Y-m');
            $opening   = GratuityBalance::where('employee_id', $employeeId)
                ->where('month', $prevMonth)
                ->value('closing_balance') ?? 0.0;

            $balance = new GratuityBalance();
            $balance->employee_id           = $employeeId;
            $balance->month                 = $month;
            $balance->opening_balance       = $opening;
            $balance->employee_contribution = $empContr;
            $balance->company_contribution  = $compContr;
            $balance->closing_balance       = $opening + $empContr + $compContr;
            $balance->created_at = now();
            $balance->updated_at = now();
            $balance->save();
        }
    }
}

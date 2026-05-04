<?php

namespace App\Jobs;

use App\Models\CommissionSetting;
use App\Models\Employee;
use App\Models\EmployeeAttendance;
use App\Models\GratuityBalance;
use App\Models\GratuitySetting;
use App\Models\Payroll;
use App\Models\PayrollDetail;
use App\Models\PayslipItem;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GeneratePayrollJob_copy implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $payrollId;
    // item type mapping
    const ITEM_EARNING  = 1;
    const ITEM_DEDUCTION = 2;
    const ITEM_INFO     = 3;

    public function __construct($payrollId)
    {
        $this->payrollId = $payrollId;
        $this->queue = 'payroll_generate_job';
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

        $payroll = Payroll::find($this->payrollId);
        if (!$payroll) return;

        $from = Carbon::parse($payroll->from_date)->startOfDay();
        $to   = Carbon::parse($payroll->to_date)->endOfDay();

        $employees = Employee::where('employee_status_id',1)->get();

        DB::beginTransaction();
        try {
            foreach ($employees as $emp) {

                $basicSalary =  ($emp->basic_salary ?? 0);
                $is_pf = 0;
//                $employee_gratuity_det =0 ;
                $totalDeductions=0;
                $employeeGratuity=0;
                $companyGratuity=0;

                $gratuity = GratuitySetting::where('status', 1)->whereHas('role',function ($q) use ($emp) {
                    $q->where('role_id', $emp->role_id);
                })->first();

                if($gratuity->is_pf)
                {
                    $company_percentage = 0.0;
                    $employee_percentage = 0.0;
                    if($gratuity) {
                        $company_percentage = $gratuity->company_contribution_percentage;
                        $employee_percentage = $gratuity->employee_contribution_percentage;
                    }
                    $employeeGratuity = round($basicSalary * ($employee_percentage / 100), 2);
                    $companyGratuity  = round($basicSalary * ($company_percentage / 100), 2);
//                    $employee_gratuity_det = $employeeGratuity;

                }

                $earned = EmployeeAttendance::where('employee_id', $emp->id)
                    ->whereBetween('attendance_date', [$from->toDateString(), $to->toDateString()])
                    ->whereIn('attendance_status_id',[1,2,3,4,6])
                    ->sum('calculated_salary');

                $totalDeductions = ($basicSalary-$earned) ;

                $commission  = $this->calculateCommission($emp->role_id,$basicSalary);

                $netSalary  = round($earned - $employeeGratuity + $commission, 2);
                $totalDeductions = round($totalDeductions+$employeeGratuity, 2);

                // PayrollDetail object-based
                $detail = new PayrollDetail();
                $detail->payroll_id        = $this->payrollId;
                $detail->employee_id       = $emp->id;
                $detail->basic_salary      = $basicSalary;//50000
                $detail->total_commission  = $commission;//0
                $detail->employee_gratuity = $employeeGratuity;//5000
                $detail->company_gratuity  = $companyGratuity;
                $detail->total_deductions  = $totalDeductions; //25000+5000=30000
                $detail->net_salary        = $netSalary; //20000
                $detail->status_id         = 1;
                $detail->created_at        = now();
                $detail->updated_at        = now();
                $detail->save();



                // Payslip Items
                $this->insertItem($detail->id, self::ITEM_EARNING,  'Attendance-based Basic (earned)', $earned);

                if ($employeeGratuity > 0) {
                    $this->insertItem($detail->id, self::ITEM_DEDUCTION, 'Employee Gratuity Contribution', -$employeeGratuity);
                }
                if ($commission > 0) {
                    $this->insertItem($detail->id, self::ITEM_EARNING,  'Commission', $commission);
                }
                if ($companyGratuity > 0) {
                    $this->insertItem($detail->id, self::ITEM_INFO, 'Company Gratuity Contribution', $companyGratuity);
                }

                $absent_info = EmployeeAttendance::where('employee_id', $emp->id)
                    ->whereBetween('attendance_date', [$from->toDateString(), $to->toDateString()])
                    ->where('attendance_status_id',5);
                $absent_deduct = $absent_info->sum('calculated_salary');
                $absent_count = $absent_info->count();

                // Absent Info
//                $absentInfo = $this->absentSummary($emp->id, $from, $to, $basicSalary);
                if ($absent_deduct > 0) {
                    $this->insertItem($detail->id, self::ITEM_DEDUCTION, "Absent Days: {$absent_count}",$absent_deduct);
                }

                if($employeeGratuity > 0 && $companyGratuity > 0){
                    // Update gratuity balance
                    $this->updateGratuityBalance($emp->id, $payroll->payroll_month, $employeeGratuity, $companyGratuity);
                }

            }

            $payroll->notes = 'Payroll Draft Successfully ready for Approval';
            $payroll->status_id = 2;
            $payroll->updated_at = now();
            $payroll->save();

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            $payroll->notes = 'Generation failed: '.$e->getMessage();
            $payroll->updated_at = now();
            $payroll->save();
//            Log::channel('payroll_log')->info('payroll-generate-job-'.json_encode($e->getMessage()));
            throw $e;
        }
    }

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

    private function calculateCommission(int $emp_role_id, float $basic_salary): float
    {
        $commission_amt = 0.0;
        $commission = CommissionSetting::where('status', 1)->whereHas('role',function ($q) use ($emp_role_id) {
            $q->where('role_id', $emp_role_id);
        })->first();

        if($commission) {
                if($commission->commission_type_id == 1) {
                    $commission_amt = ($basic_salary * $commission->value)/100;
                }else {
                    $commission_amt = $commission->value;
                }
        }
        return round($commission_amt, 2);
    }
    //    private function absentSummary(int $employeeId, Carbon $from, Carbon $to, float $basicSalary): array


//    private function absentSummary(int $employeeId, Carbon $from, Carbon $to, float $basicSalary): array
//    {
//        $absentStatus = AttendanceStatus::whereIn('name',['Absent','ABSENT','absent'])->first();
//        if (!$absentStatus) return ['days'=>0,'amount'=>0.0];
//
//        $days = EmployeeAttendance::where('employee_id',$employeeId)
//            ->whereBetween('attendance_date', [$from->toDateString(), $to->toDateString()])
//            ->where('attendance_status_id',$absentStatus->id)
//            ->count();
//
//        $perDay = $basicSalary / 30;
//        $amount = round($days * $perDay, 2);
//
//        return ['days'=>$days,'amount'=>$amount];
//    }

    private function updateGratuityBalance($employeeId , $month, float $empContr, float $compContr): void
    {
        $existing = GratuityBalance::where('employee_id',$employeeId)->where('month',$month)->first();

        if ($existing) {
            $existing->employee_contribution += $empContr;
            $existing->company_contribution  += $compContr;
            $existing->closing_balance        = $existing->opening_balance + $existing->employee_contribution + $existing->company_contribution;
            $existing->updated_at = now();
            $existing->save();
        } else {
            $prevMonth = Carbon::createFromFormat('Y-m',$month)->subMonth()->format('Y-m');
            $opening   = GratuityBalance::where('employee_id',$employeeId)->where('month',$prevMonth)->value('closing_balance') ?? 0.0;

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

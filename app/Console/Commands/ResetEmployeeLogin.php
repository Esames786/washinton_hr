<?php

namespace App\Console\Commands;

use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ResetEmployeeLogin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'employee:reset-login';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'employee reset login bit based on shift end time plus one hour';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        try {
            $today = now()->toDateString();
            $employees = Employee::with('shift')->where('employee_status_id',1)->get();

            foreach ($employees as $emp) {
                if (!$emp->shift) continue;
                $shiftEnd = Carbon::parse($today.' '.$emp->shift->shift_end);
                // Agar shift end + 1 hour cross ho gaya
                if (now()->greaterThan($shiftEnd->copy()->addHour())) {
                    if ($emp->is_logged_in) {
                        $emp->is_logged_in = 0;
                        $emp->save();
                    }
                }
            }

        } catch (\Throwable $th) {
            Log::channel('job_log')->error([
                'message' => $th->getMessage(),
                'file'    => $th->getFile(),
                'line'    => $th->getLine(),
                'trace'   => $th->getTraceAsString(),
            ]);
        }


//        $this->info("Employee login flags reset after shift end.");
    }
}

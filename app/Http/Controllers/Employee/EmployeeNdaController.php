<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class EmployeeNdaController extends Controller
{
    public function sign(Request $request): JsonResponse
    {
        $request->validate([
            'employee_name'  => 'required|string|max:255',
            'cnic'           => 'required|string|max:20',
            'signature_data' => 'required|string',
            'agreed'         => 'required|in:1',
        ]);

        $employee = Auth::guard('employee')->user();
        if (!$employee || !$employee->nda_required) {
            return response()->json(['success' => false, 'message' => 'NDA not required for this account.'], 403);
        }

        try {
            $sigBase64 = preg_replace('/^data:image\/\w+;base64,/', '', $request->signature_data);
            $sigBinary = base64_decode($sigBase64);
            if (!$sigBinary || strlen($sigBinary) < 100) {
                return response()->json(['success' => false, 'message' => 'Invalid signature. Please draw again.']);
            }

            $pdf = Pdf::loadView('nda.pdf', [
                'employeeName'  => $request->employee_name,
                'cnic'          => $request->cnic,
                'signedDate'    => now()->format('d M Y H:i'),
                'signatureData' => $request->signature_data,
            ])->setPaper('a4', 'portrait');

            $dir      = 'nda_documents';
            $filename = 'nda_emp_' . $employee->id . '_' . now()->format('YmdHis') . '.pdf';
            $relPath  = $dir . '/' . $filename;

            Storage::disk('public')->makeDirectory($dir);
            Storage::disk('public')->put($relPath, $pdf->output());

            // Clear flag on hr_employees
            DB::table('hr_employees')
                ->where('id', $employee->id)
                ->update(['nda_required' => 0]);

            // Also clear on agent's user record (same DB server, same hellotransport_databases)
            if ($employee->agent_id) {
                DB::table('user')
                    ->where('id', $employee->agent_id)
                    ->update([
                        'nda_required'      => 0,
                        'nda_signed_at'     => now(),
                        'nda_document_path' => $relPath,
                    ]);
            }

            return response()->json(['success' => true]);

        } catch (\Throwable $e) {
            Log::error('HR NDA sign failed', ['employee_id' => $employee->id, 'error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Server error. Please try again.'], 500);
        }
    }
}

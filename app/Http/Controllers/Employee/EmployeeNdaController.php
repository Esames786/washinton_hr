<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
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

        // Validate signature canvas data
        $sigBase64 = preg_replace('/^data:image\/\w+;base64,/', '', $request->signature_data);
        $sigBinary = base64_decode($sigBase64);
        if (!$sigBinary || strlen($sigBinary) < 100) {
            return response()->json(['success' => false, 'message' => 'Invalid signature. Please draw again.']);
        }

        $signedAt = now();
        $relPath  = null;

        // Attempt PDF generation — non-blocking (sign succeeds even if PDF fails)
        try {
            // Use barryvdh facade if available, fall back to raw dompdf
            if (class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
                $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('nda.pdf', [
                    'employeeName'  => $request->employee_name,
                    'cnic'          => $request->cnic,
                    'signedDate'    => $signedAt->format('d M Y H:i'),
                    'signatureData' => $request->signature_data,
                ])->setPaper('a4', 'portrait');
                $pdfOutput = $pdf->output();
            } else {
                $html = view('nda.pdf', [
                    'employeeName'  => $request->employee_name,
                    'cnic'          => $request->cnic,
                    'signedDate'    => $signedAt->format('d M Y H:i'),
                    'signatureData' => $request->signature_data,
                ])->render();
                $options = new \Dompdf\Options();
                $options->set('isRemoteEnabled', true);
                $options->set('isHtml5ParserEnabled', true);
                $dompdf = new \Dompdf\Dompdf($options);
                $dompdf->loadHtml($html);
                $dompdf->setPaper('A4', 'portrait');
                $dompdf->render();
                $pdfOutput = $dompdf->output();
            }

            $dir      = 'nda_documents';
            $filename = 'nda_emp_' . $employee->id . '_' . $signedAt->format('YmdHis') . '.pdf';
            $relPath  = $dir . '/' . $filename;

            Storage::disk('public')->makeDirectory($dir);
            Storage::disk('public')->put($relPath, $pdfOutput);

        } catch (\Throwable $e) {
            Log::error('HR NDA PDF generation failed (signature still recorded)', [
                'employee_id' => $employee->id,
                'error'       => $e->getMessage(),
            ]);
            $relPath = null;
        }

        // Clear NDA flags regardless of PDF outcome
        try {
            DB::table('hr_employees')
                ->where('id', $employee->id)
                ->update(['nda_required' => 0]);

            if ($employee->agent_id) {
                DB::table('user')
                    ->where('id', $employee->agent_id)
                    ->update([
                        'nda_required'      => 0,
                        'nda_signed_at'     => $signedAt,
                        'nda_document_path' => $relPath,
                    ]);
            }

        } catch (\Throwable $e) {
            Log::error('HR NDA flag clear failed', ['employee_id' => $employee->id, 'error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Server error saving signature. Please try again.'], 500);
        }

        return response()->json(['success' => true]);
    }
}

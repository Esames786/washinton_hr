<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EmployeeNdaController extends Controller
{
    public function sign(Request $request): JsonResponse
    {
        $request->validate([
            'employee_name'  => 'required|string|max:255',
            'father_name'    => 'required|string|max:255',
            'address'        => 'required|string|max:500',
            'cnic'           => 'required|string|max:20',
            'signature_data' => 'required|string',
            'agreed'         => 'required|in:1',
            'cnic_front'     => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'cnic_back'      => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
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
        $ip       = $request->ip();

        // The NDA copy being signed = the admin's prepared copy, or the shared default template.
        $ndaContent = $employee->nda_content ?? null;
        if (!$ndaContent || trim(strip_tags($ndaContent)) === '') {
            $tpl = DB::table('nda_templates')->where('is_default', 1)->value('content');
            $ndaContent = $tpl ? str_ireplace(['{{COMPANY_NAME}}', '{{COMPANY_LEGAL}}'], 'Crazy Rays Solutions', $tpl) : '';
        }

        // Store CNIC front/back if provided (public/Uploads — web-served, no symlink needed).
        $cnicFrontPath = $this->storeUpload($request, 'cnic_front', $employee->id, $signedAt);
        $cnicBackPath  = $this->storeUpload($request, 'cnic_back', $employee->id, $signedAt);

        // Mirror newly-captured CNIC images into the documents list (#10 Front / #11 Back).
        $this->mirrorCnicDoc($employee->id, 10, $cnicFrontPath, $signedAt);
        $this->mirrorCnicDoc($employee->id, 11, $cnicBackPath, $signedAt);

        // Generate the signed PDF from the admin's HTML + signature block.
        $relPath = null;
        try {
            $html = view('nda.pdf', [
                'ndaContent'    => $ndaContent,
                'brand'         => [],
                'employeeName'  => $request->employee_name,
                'fatherName'    => $request->father_name,
                'address'       => $request->address,
                'cnic'          => $request->cnic,
                'signedDate'    => $signedAt->format('d M Y H:i'),
                'signedIp'      => $ip,
                'signatureData' => $request->signature_data,
                'cnicFrontPath' => $cnicFrontPath ? public_path($cnicFrontPath) : null,
                'cnicBackPath'  => $cnicBackPath ? public_path($cnicBackPath) : null,
            ])->render();

            $pdfOutput = $this->renderPdf($html);

            $dir  = 'Uploads/nda_documents';
            $full = public_path($dir);
            if (!file_exists($full)) {
                mkdir($full, 0755, true);
            }
            $filename = 'nda_emp_' . $employee->id . '_' . $signedAt->format('YmdHis') . '.pdf';
            $relPath  = $dir . '/' . $filename;
            file_put_contents(public_path($relPath), $pdfOutput);
        } catch (\Throwable $e) {
            Log::error('HR NDA PDF generation failed (signature still recorded)', [
                'employee_id' => $employee->id,
                'error'       => $e->getMessage(),
            ]);
            $relPath = null;
        }

        // Persist signed state (signature + IP + CNIC stored in shared DB so the PDF can always
        // be regenerated on demand from either app).
        try {
            DB::table('hr_employees')
                ->where('id', $employee->id)
                ->update([
                    'nda_required'     => 0,
                    'nda_signed_at'    => $signedAt,
                    'nda_document_url' => $relPath ? url($relPath) : null,
                    'nda_content'      => $ndaContent,
                    'nda_signature'    => $request->signature_data,
                    'nda_signed_ip'    => $ip,
                    'nda_cnic_front'   => $cnicFrontPath,
                    'nda_cnic_back'    => $cnicBackPath,
                    'nda_father_name'  => $request->father_name,
                    'nda_address'      => $request->address,
                ]);

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

    /* ───────────────────────── helpers ───────────────────────── */

    private function renderPdf(string $html): string
    {
        if (class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
            return \Barryvdh\DomPDF\Facade\Pdf::loadHTML($html)->setPaper('a4', 'portrait')->output();
        }
        $options = new \Dompdf\Options();
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);
        $dompdf = new \Dompdf\Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        return $dompdf->output();
    }

    private function storeUpload(Request $request, string $field, int $employeeId, $signedAt): ?string
    {
        if (!$request->hasFile($field)) {
            return null;
        }
        $dir  = 'Uploads/nda_cnic';
        $full = public_path($dir);
        if (!file_exists($full)) {
            mkdir($full, 0755, true);
        }
        $file  = $request->file($field);
        $fname = $field . '_emp_' . $employeeId . '_' . $signedAt->format('YmdHis') . '.' . $file->getClientOriginalExtension();
        $file->move($full, $fname);
        return $dir . '/' . $fname;
    }

    private function mirrorCnicDoc(int $employeeId, int $settingId, ?string $relPath, $signedAt): void
    {
        if (!$relPath) {
            return;
        }
        try {
            $exists = DB::table('hr_employee_documents')
                ->where('employee_id', $employeeId)
                ->where('document_setting_id', $settingId)
                ->exists();
            if ($exists) {
                return;
            }
            DB::table('hr_employee_documents')->insert([
                'employee_id'         => $employeeId,
                'document_setting_id' => $settingId,
                'file_path'           => $relPath,
                'file_name'           => basename($relPath),
                'mime_type'           => null,
                'status'              => 0,
                'created_at'          => $signedAt,
                'updated_at'          => $signedAt,
            ]);
        } catch (\Throwable $e) {
            Log::warning('HR NDA mirror CNIC doc failed', ['employee_id' => $employeeId, 'setting' => $settingId, 'error' => $e->getMessage()]);
        }
    }
}

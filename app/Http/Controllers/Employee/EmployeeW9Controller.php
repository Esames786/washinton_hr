<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * The agent's W-9, filled in the HR portal as part of onboarding (client, 23 Oct 2026).
 *
 * The form is forwarded to the agent portal's bridge, which validates it, encrypts the TIN with
 * its own key and renders the PDF exactly as for a W-9 filed there — one store, one format,
 * whether or not the CRM account exists yet. Validation errors come back as-is for the form.
 */
class EmployeeW9Controller extends Controller
{
    private const FIELDS = [
        'legal_name', 'business_name', 'tax_classification', 'llc_tax_class', 'other_classification',
        'exempt_payee_code', 'fatca_code', 'address', 'city', 'state', 'zip', 'account_numbers',
        'tin_type', 'tin', 'signature_data', 'certified',
    ];

    public function submit(Request $request): JsonResponse
    {
        $employee = Auth::guard('employee')->user();

        if (! $employee->w9Applies() || ! $employee->w9Requested()) {
            return response()->json(['success' => false, 'message' => 'No W-9 has been requested from you.'], 422);
        }
        if ($employee->w9Form()) {
            return response()->json(['success' => false, 'message' => 'Your W-9 has already been submitted.'], 409);
        }

        $key = env('HELLOTRANSPORT_BRIDGE_KEY');
        $url = rtrim(env('HELLOTRANSPORT_BRIDGE_URL', 'https://hellotransport.com'), '/') . '/bridge/w9/submit';
        if (! $key) {
            Log::warning('EmployeeW9Controller: HELLOTRANSPORT_BRIDGE_KEY not configured');

            return $this->unavailable();
        }

        try {
            $response = Http::withHeaders(['X-Bridge-Key' => $key, 'Accept' => 'application/json'])
                ->timeout(30)
                ->post($url, array_merge($request->only(self::FIELDS), [
                    'hr_employee_id' => $employee->id,
                    'client_ip'      => $request->ip(),
                ]));
        } catch (\Throwable $e) {
            Log::error('EmployeeW9Controller: bridge call failed', ['employee_id' => $employee->id, 'error' => $e->getMessage()]);

            return $this->unavailable();
        }

        // Success, validation errors (422) and "already submitted" (409) go back to the form as they are.
        if ($response->successful() || in_array($response->status(), [409, 422], true)) {
            return response()->json($response->json() ?? ['success' => false], $response->status());
        }

        Log::error('EmployeeW9Controller: bridge returned an error', [
            'employee_id' => $employee->id, 'status' => $response->status(), 'body' => mb_substr($response->body(), 0, 300),
        ]);

        return $this->unavailable();
    }

    private function unavailable(): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => 'Your W-9 could not be submitted right now. Please try again in a few minutes, or contact HR.',
        ], 503);
    }
}

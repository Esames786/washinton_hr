<?php

namespace App\Support;

use App\Models\Employee;
use App\Models\EmployeeDocument;
use Illuminate\Support\Facades\Http;

/**
 * Serve an hr_employee_documents file that may live on a SIBLING portal's disk.
 * The four hello/CR portals share one database but separate cPanel filesystems:
 * bridge-transferred CR application docs land on the portal HRPORTAL_BASE_URL points
 * at, self-uploads land on the HR domain the subcontractor used, NDA mirrors sit on
 * the agent portals. Local disk first, then probe the brand's portals, then the
 * opposite brand's (records created before the brand split).
 *
 * Used by AdminEmployeeController@docFile and the subcontractor profile document route.
 */
class DocFileServer
{
    public static function respond(EmployeeDocument $document)
    {
        $path = ltrim((string) $document->file_path, '/');
        if ($path === '') {
            abort(404);
        }
        if (preg_match('#^https?://#i', $document->file_path)) {
            return redirect()->away($document->file_path);
        }

        $local = public_path($path);
        if (is_file($local)) {
            return response()->file($local);
        }

        $employee = Employee::find($document->employee_id);
        $isCr = $employee ? $employee->isCrazyrays() : false;
        $bases = $isCr
            ? ['https://hr.crazyrayssolutions.com.pk', 'https://florida.crazyrayssolutions.com.pk']
            : ['https://hr.hellotransport.com', 'https://hellotransport.com'];
        $bases = array_merge($bases, $isCr
            ? ['https://hr.hellotransport.com', 'https://hellotransport.com']
            : ['https://hr.crazyrayssolutions.com.pk', 'https://florida.crazyrayssolutions.com.pk']);

        $currentHost = request()->getHost();
        foreach ($bases as $base) {
            if (stripos($base, $currentHost) !== false) {
                continue; // already checked the local disk
            }
            $url = $base . '/' . str_replace(' ', '%20', $path);
            try {
                $res = Http::timeout(4)->withoutVerifying()->head($url);
                if ($res->successful()) {
                    return redirect()->away($url);
                }
            } catch (\Throwable $e) {
                // portal unreachable — try the next one
            }
        }

        abort(404, 'Document file was not found on any portal.');
    }
}

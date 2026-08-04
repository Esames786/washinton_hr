<?php

namespace App\Support;

use App\Models\Employee;
use Illuminate\Support\Facades\Auth;

/**
 * Central branding resolver for the HR portal.
 *
 * Mirrors washinton_agent/app/Support/Brand.php so both apps brand the SAME person the
 * same way off the shared database. A subcontractor's brand comes from the linked agent
 * account (`user.is_crazyrays`, read via Employee::isCrazyrays()).
 *
 * Use for(): documents/emails ABOUT a person (NDA, contract, notifications).
 * Use current(): portal chrome (logo, navbar, footer) — follows the domain.
 */
class Brand
{
    /**
     * Brand OF A PERSON — their NDA/contract wording and any email addressed to them.
     *
     * The person's own origin wins and PORTAL_BRAND does NOT override it: a Hello
     * subcontractor administered from the CrazyRays HR portal must still get Hello-worded
     * documents. With no person supplied, fall back to the deployment brand.
     */
    public static function for(?Employee $employee): array
    {
        if ($employee) {
            return self::byKey($employee->isCrazyrays() ? 'crazyrays' : config('brands.default', 'hellotransport'));
        }

        $force = config('brands.force');

        return self::byKey($force ?: config('brands.default', 'hellotransport'));
    }

    /**
     * Brand OF THE DOMAIN — portal chrome. PORTAL_BRAND (set on hr.crazyrayssolutions.com.pk)
     * makes the whole domain render CrazyRays, including the login page.
     */
    public static function current(): array
    {
        // 1. Explicit deployment brand wins (PORTAL_BRAND).
        $force = config('brands.force');
        if ($force) {
            return self::byKey($force);
        }

        // 2. Otherwise infer it from the HOST being visited, so each domain brands itself even
        //    when PORTAL_BRAND was never set: hr.crazyrayssolutions.com.pk → CrazyRays,
        //    hr.hellotransport.com → Hello. This is what makes the branding follow the domain.
        $host = self::hostBrandKey();
        if ($host) {
            return self::byKey($host);
        }

        // 3. Fall back to the signed-in person's own brand.
        $employee = Auth::guard('employee')->user();

        return self::for($employee instanceof Employee ? $employee : null);
    }

    /**
     * Brand implied by the current request's hostname, or null when it matches neither domain
     * (e.g. a local/staging host) so the caller can fall back.
     */
    public static function hostBrandKey(): ?string
    {
        try {
            $host = strtolower((string) request()->getHost());
        } catch (\Throwable $e) {
            return null;   // console/queue context — no request
        }

        if ($host === '') {
            return null;
        }

        if (str_contains($host, 'crazyrays')) {
            return 'crazyrays';
        }

        if (str_contains($host, 'hellotransport')) {
            return 'hellotransport';
        }

        return null;
    }

    /**
     * Resolve a brand by its explicit key ('crazyrays' | 'hellotransport').
     */
    public static function byKey(string $key): array
    {
        $brands  = config('brands.brands', []);
        $default = config('brands.default', 'hellotransport');

        $data = $brands[$key] ?? $brands[$default] ?? [];

        return array_merge(['key' => isset($brands[$key]) ? $key : $default], $data);
    }

    /**
     * Convenience: resolve a brand from a boolean CrazyRays flag.
     */
    public static function fromFlag(bool $isCrazyrays): array
    {
        return self::byKey($isCrazyrays ? 'crazyrays' : config('brands.default', 'hellotransport'));
    }

    /**
     * Rebrand a block of HTML/text (contract, NDA, T&C) for the given brand.
     *
     * Supports {{COMPANY_NAME}} / {{COMPANY_LEGAL}} placeholders and rewrites the legacy
     * hard-coded company literals in BOTH directions, so text stored under one brand renders
     * correctly for the other. Kept byte-identical in behaviour to the agent app's version.
     */
    public static function applyTokens(string $html, array $brand): string
    {
        $name  = $brand['name']  ?? 'Hello Transport';
        $legal = $brand['legal'] ?? 'Hello Transport LLC';

        $html = strtr($html, [
            '{{COMPANY_NAME}}'  => $name,
            '{{COMPANY_LEGAL}}' => $legal,
            '{{COMPANY_SITE}}'  => $brand['site']  ?? '',
            '{{COMPANY_EMAIL}}' => $brand['email'] ?? '',
            '{{COMPANY_PHONE}}' => $brand['phone'] ?? '',
        ]);

        // ONE case-insensitive pass (longest alternative first) so replaced text is never
        // re-scanned — sequential replaces would produce "Crazy Rays Solutions Solutions".
        $literals = [
            'hello transport llc'  => $legal,
            'crazy rays solutions' => $legal,
            'crazyrays solutions'  => $legal,
            'hello transport'      => $name,
            'crazy rays'           => $name,
            'crazyrays'            => $name,
        ];

        $pattern = '/' . implode('|', array_map(
            static fn ($needle) => preg_quote($needle, '/'),
            array_keys($literals)
        )) . '/i';

        $replaced = preg_replace_callback(
            $pattern,
            static fn ($m) => $literals[strtolower($m[0])] ?? $m[0],
            $html
        );

        // preg_* returns null on failure — never destroy the content.
        return $replaced ?? $html;
    }
}

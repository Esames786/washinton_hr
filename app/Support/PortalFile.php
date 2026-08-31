<?php

namespace App\Support;

/**
 * URL for an uploaded file (profile pictures etc.) that may live on the SIBLING HR
 * portal's disk — hr.hellotransport.com and hr.crazyrayssolutions.com.pk share one
 * database but separate cPanel filesystems, so a picture an admin uploads on one
 * domain does not exist on the other's disk. Local-first, otherwise link straight to
 * the sibling domain (mirrors washinton_agent's portal_file_url()).
 *
 * For documents that need auth/probing use DocFileServer; this is for public images.
 */
class PortalFile
{
    public static function url($path, string $default = 'assets/images/default_images/profile_image.png'): string
    {
        $path = trim((string) ($path ?? ''));
        if ($path === '') {
            return asset($default);
        }
        if (preg_match('#^https?://#i', $path)) {
            return $path;
        }
        $path = ltrim($path, '/');
        if (is_file(public_path($path))) {
            return asset($path);
        }
        $sibling = (stripos((string) request()->getHost(), 'crazyrays') !== false)
            ? 'https://hr.hellotransport.com'
            : 'https://hr.crazyrayssolutions.com.pk';

        return $sibling . '/' . str_replace(' ', '%20', $path);
    }
}

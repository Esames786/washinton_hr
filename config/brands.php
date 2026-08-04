<?php

/*
|--------------------------------------------------------------------------
| Brand definitions (HR portal)
|--------------------------------------------------------------------------
| Mirror of washinton_agent/config/brands.php so both apps resolve branding
| identically off the SAME shared database.
|
| Source of truth for a person's brand is `user.is_crazyrays` (read through
| App\Models\Employee::isCrazyrays()); PORTAL_BRAND forces the deployment's
| brand for portal chrome on the CrazyRays HR domain.
|
| Resolve via App\Support\Brand::for($employee) / Brand::current().
*/

return [

    'default' => 'hellotransport',

    // Deployment-wide brand override for portal chrome. Set PORTAL_BRAND=crazyrays on
    // hr.crazyrayssolutions.com.pk. Leave EMPTY on hr.hellotransport.com so it stays Hello.
    'force' => env('PORTAL_BRAND'),

    'brands' => [

        'hellotransport' => [
            'name'          => 'Hello Transport',
            'legal'         => 'Hello Transport LLC',
            'site'          => 'https://www.hellotransport.com',
            'login_url'     => 'https://hr.hellotransport.com/login',
            'email'         => 'support@hellotransport.com',
            'contact_email' => 'info@hellotransport.com',
            'phone'         => '1 (844) 474-4721',
            'footer'        => 'Hello Transport. All Rights Reserved.',
            'logo'          => 'assets/images/logo/hello_transport.png',
        ],

        'crazyrays' => [
            'name'          => 'Crazy Rays Solutions',
            'legal'         => 'Crazy Rays Solutions',
            'site'          => 'https://crazyrayssolutions.com.pk',
            'login_url'     => 'https://hr.crazyrayssolutions.com.pk/login',
            'email'         => 'info@crazyrayssolutions.com.pk',
            'contact_email' => 'info@crazyrayssolutions.com.pk',
            'phone'         => '0313-8432343',
            'footer'        => 'Crazy Rays Solutions. All Rights Reserved.',
            'logo'          => 'assets/images/logo/crazyrays.svg',
        ],

    ],
];

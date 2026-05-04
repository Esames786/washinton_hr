<?php

return [
    'shared_key' => env('HR_BRIDGE_SHARED_KEY', ''),
    'bridge_url' => env('BRIDGE_PUBLIC_URL', 'https://bridge.example.com'),
    'defaults' => [
        'employment_type_id' => (int) env('HR_BRIDGE_DEFAULT_EMPLOYMENT_TYPE_ID', 3),
        'employee_status_id' => (int) env('HR_BRIDGE_DEFAULT_EMPLOYEE_STATUS_ID', 7),
    ],
];

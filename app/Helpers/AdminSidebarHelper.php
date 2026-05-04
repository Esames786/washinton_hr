<?php
// app/Helpers/SidebarHelper.php
namespace App\Helpers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

class AdminSidebarHelper
{
    public static function menu()
    {
        return [
            [
                'title' => 'Dashboard',
                'icon'  => 'bi bi-speedometer2',
                'route' => 'admin.dashboard', // single menu
            ],
            [
                'title' => 'Settings',
                'icon'  => 'bi bi-gear',
                'items' => [
                    ['title' => 'Attendance Types', 'route' => 'admin.attendance_types.index', 'iconClass' => 'text-info-main'],
                    ['title' => 'Shift Types', 'route' => 'admin.shift_types.index', 'iconClass' => 'text-info-main'],
                    ['title' => 'Shift Attendance Rule', 'route' => 'admin.shift_attendance_rules.index', 'iconClass' => 'text-danger-main'],
                    ['title' => 'Ticket Types', 'route' => 'admin.ticket_types.index', 'iconClass' => 'text-danger-main'],
                    ['title' => 'Department', 'route' => 'admin.departments.index', 'iconClass' => 'text-info-main'],
                    ['title' => 'Designation', 'route' => 'admin.designations.index', 'iconClass' => 'text-info-main'],
                    ['title' => 'Tax Slabs', 'route' => 'admin.tax_slabs.index', 'iconClass' => 'text-info-main'],
                    ['title' => 'Holidays', 'route' => 'admin.holidays.index', 'iconClass' => 'text-info-main'],
                    ['title' => 'Gratuity Setting', 'route' => 'admin.gratuity_settings.index', 'iconClass' => 'text-primary-600'],
                    ['title' => 'Commission Setting', 'route' => 'admin.commission_settings.index', 'iconClass' => 'text-warning-main'],
                    ['title' => 'Document Setting', 'route' => 'admin.document_settings.index', 'iconClass' => 'text-info-main'],
                    ['title' => 'Daily Activities', 'route' => 'admin.daily_activity_fields.index', 'iconClass' => 'text-danger-main'],
                    ['title' => 'Currency Exchange Rate', 'route' => 'admin.currency_settings.index', 'iconClass' => 'text-info-main'],

                ]
            ],
            [
                'title' => 'User Management',
                'icon'  => 'bi bi-people',
                'items' => [
                    ['title' => 'Roles', 'route' => 'admin.roles.index', 'iconClass' => 'text-primary-600'],
                    ['title' => 'Users', 'route' => 'admin.users.index', 'iconClass' => 'text-warning-main'],
                    ['title' => 'hr_employees', 'route' => 'admin.hr_employees.index', 'iconClass' => 'text-info-main'],
                ]
            ],
            [
                'title' => 'PettyCash',
                'icon'  => 'bi bi-wallet2',
                'items' => [
                    ['title' => 'Opening Balance', 'route' => 'admin.petty_cash.masters.index', 'iconClass' => 'text-primary-600'],
                    ['title' => 'Accounts Head', 'route' => 'admin.petty_cash.heads.index', 'iconClass' => 'text-warning-600'],
                    ['title' => 'Transactions', 'route' => 'admin.petty_cash.transactions.index', 'iconClass' => 'text-warning-600'],
                    ['title' => 'Ledger', 'route' => 'admin.petty_cash.ledger.index', 'iconClass' => 'text-warning-600'],
                ]
            ],

            [
                'title' => 'Payroll Management',
                'icon'  => 'bi bi-cash-stack',
                'items' => [
                    ['title' => 'Generate Payroll', 'route' => 'admin.payroll.index', 'iconClass' => 'text-primary-600'],
                    ['title' => 'Paid Payroll List', 'route' => 'admin.payroll.list', 'iconClass' => 'text-warning-main'],
                    ['title' => 'Employee Payslips', 'route' => 'admin.payroll.payslip.index', 'iconClass' => 'text-info-main'],
                ]
            ],

            [
                'title' => 'Gratuity Management',
                'icon'  => 'bi bi-gift',
                'items' => [
                    ['title' => 'Create Payout', 'route' => 'admin.gratuity_payouts.index', 'iconClass' => 'text-primary-600'],
                    ['title' => 'Paid Payout List', 'route' => 'admin.gratuity_payouts.paid_list', 'iconClass' => 'text-warning-600'],
                ]
            ],

            [
                'title' => 'Employee Management',
                'icon'  => 'bi bi-person-badge',
                'items' => [
                    ['title' => 'Attendance List', 'route' => 'admin.hr_employees.attendance_list', 'iconClass' => 'text-primary-600'],
                    ['title' => 'Break List', 'route' => 'admin.hr_employees.break_list', 'iconClass' => 'text-warning-600'],
                    [
                        'title' => 'Tickets',
                        'route' => 'admin.tickets.index',
                        'iconClass' => 'text-success-600'
                    ],
                    ['title' => 'Daily Activities', 'route' => 'admin.hr_employees.daily_activity_list', 'iconClass' => 'text-primary-600'],
                    ['title' => 'Order List', 'route' => 'admin.hr_employees.order_list', 'iconClass' => 'text-warning-600'],
                    ['title' => 'Monthly Tax', 'route' => 'admin.hr_employees.monthly_tax_list', 'iconClass' => 'text-warning-600'],
                ]
            ],


        ];
    }

}

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
                    ['title' => 'Onboarding Questionnaire', 'route' => 'admin.questionnaire.index', 'iconClass' => 'text-danger-main'],
                    ['title' => 'Daily Activities', 'route' => 'admin.daily_activity_fields.index', 'iconClass' => 'text-danger-main'],
                    ['title' => 'Currency Exchange Rate', 'route' => 'admin.currency_settings.index', 'iconClass' => 'text-info-main'],
                    ['title' => 'Equipment Types', 'route' => 'admin.equipment_types.index', 'iconClass' => 'text-success-main'],
                    ['title' => 'Productivity Rules', 'route' => 'admin.productivity_rules.index', 'iconClass' => 'text-warning-main'],
                    ['title' => 'Leave Types', 'route' => 'admin.leave_types.index', 'iconClass' => 'text-info-main'],

                ]
            ],
            [
                'title' => 'User Management',
                'icon'  => 'bi bi-people',
                'items' => [
                    ['title' => 'Roles', 'route' => 'admin.roles.index', 'iconClass' => 'text-primary-600'],
                    ['title' => 'Users', 'route' => 'admin.users.index', 'iconClass' => 'text-warning-main'],
                    // Employment-split: In-House / On-Site employees only.
                    ['title' => 'On-Site Process', 'route' => 'admin.employees.index', 'iconClass' => 'text-info-main'],
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
                    // Subcontractor payouts now live under Subcontractor Management, so this
                    // group covers in-house payroll only and the entry is not duplicated.
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
                // ── Employment split (client, 8 Oct) ──────────────────────────────────
                // In-house staff and subcontractors are managed separately. Both groups
                // point at the same screens with a `type` filter rather than duplicating
                // them, so a fix to attendance or breaks lands in both at once.
                //
                // Monthly Tax and Gratuity are in-house only; Earning & Payouts is the
                // subcontractor equivalent, since they are paid commission, not salary.
                'title' => 'Subcontractor Management',
                'icon'  => 'bi bi-person-badge',
                'items' => [
                    // Applications from the public site, before they become subcontractors.
                    // Shortlisting one here creates the HR profile; the CRM account comes later.
                    ['title' => 'Campaign Leads', 'route' => 'admin.campaign_leads.index', 'iconClass' => 'text-danger-main'],
                    ['title' => 'Subcontractors', 'route' => 'admin.employees.index', 'params' => ['type' => 'subcontractor'], 'iconClass' => 'text-info-main'],
                    ['title' => 'Attendance List', 'route' => 'admin.employees.attendance_list', 'params' => ['type' => 'subcontractor'], 'iconClass' => 'text-primary-600'],
                    ['title' => 'Break List', 'route' => 'admin.employees.break_list', 'params' => ['type' => 'subcontractor'], 'iconClass' => 'text-warning-600'],
                    ['title' => 'Tickets', 'route' => 'admin.tickets.index', 'params' => ['type' => 'subcontractor'], 'iconClass' => 'text-success-600'],
                    ['title' => 'Daily Activities', 'route' => 'admin.employees.daily_activity_list', 'params' => ['type' => 'subcontractor'], 'iconClass' => 'text-primary-600'],
                    ['title' => 'Order List', 'route' => 'admin.employees.order_list', 'params' => ['type' => 'subcontractor'], 'iconClass' => 'text-warning-600'],
                    ['title' => 'Earning & Payouts', 'route' => 'admin.payroll.payslip.index', 'iconClass' => 'text-success-main'],
                ]
            ],

            [
                'title' => 'In-House Management',
                'icon'  => 'bi bi-building',
                'items' => [
                    ['title' => 'In-House Staff', 'route' => 'admin.employees.index', 'params' => ['type' => 'inhouse'], 'iconClass' => 'text-info-main'],
                    ['title' => 'Attendance List', 'route' => 'admin.employees.attendance_list', 'params' => ['type' => 'inhouse'], 'iconClass' => 'text-primary-600'],
                    ['title' => 'Break List', 'route' => 'admin.employees.break_list', 'params' => ['type' => 'inhouse'], 'iconClass' => 'text-warning-600'],
                    ['title' => 'Tickets', 'route' => 'admin.tickets.index', 'params' => ['type' => 'inhouse'], 'iconClass' => 'text-success-600'],
                    ['title' => 'Daily Activities', 'route' => 'admin.employees.daily_activity_list', 'params' => ['type' => 'inhouse'], 'iconClass' => 'text-primary-600'],
                    ['title' => 'Leads / Orders', 'route' => 'admin.employees.order_list', 'params' => ['type' => 'inhouse'], 'iconClass' => 'text-warning-600'],
                    ['title' => 'Monthly Tax', 'route' => 'admin.employees.monthly_tax_list', 'iconClass' => 'text-warning-600'],
                    // Gratuity is in-house only and already has its own group above, with
                    // both Create Payout and Paid Payout List — not duplicated here.
                ]
            ],


        ];
    }

}

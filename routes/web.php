<?php

use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\Employee\EmployeeBreakController;
use App\Http\Controllers\Employee\EmployeePayslipController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EmployeeSettings\AdminGratuitySettingController;
use App\Http\Controllers\EmployeeSettings\EmployeeDocumentSettingController;
use App\Http\Controllers\EmployeeSettings\EmployeeCommissionSettingController;
use App\Http\Controllers\EmployeeSettings\EmployeeShiftSettingController;
use App\Http\Controllers\EmployeeSettings\DailyActivityController;
use App\Http\Controllers\EmployeeSettings\AdminHolidayController;
use App\Http\Controllers\EmployeeSettings\AdminAttendanceTypeController;
use App\Http\Controllers\EmployeeSettings\AdminDepartmentController;
use App\Http\Controllers\EmployeeSettings\AdminDesignationController;
use App\Http\Controllers\EmployeeSettings\AdminShiftTypeController;
use App\Http\Controllers\EmployeeSettings\AdminTicketTypeController;
use App\Http\Controllers\EmployeeSettings\AdminTaxSlabController;
use App\Http\Controllers\EmployeeSettings\CurrencyRateController;
use App\Http\Controllers\PettyCash\PettyCashMasterController;
use App\Http\Controllers\PettyCash\PettyCashHeadController;
use App\Http\Controllers\PettyCash\PettyCashTransactionController;
use App\Http\Controllers\PettyCash\PettyCashLedgerController;

use App\Http\Controllers\PermissionController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AdminEmployeeController;
use App\Http\Controllers\AdminPayrollController;
use App\Http\Controllers\AdminGratuityPayoutController;
use App\Http\Controllers\AdminPayslipController;
use App\Http\Controllers\AdminTicketController;
use App\Http\Controllers\AdminTicketMessageController;

use App\Http\Controllers\Employee\EmployeeAuthController;
use App\Http\Controllers\Employee\EmployeeDashboardController;
use App\Http\Controllers\Employee\EmployeeAttendanceController;
use App\Http\Controllers\Employee\EmployeeDailyActivityController;
use App\Http\Controllers\Employee\EmployeeTicketController;
use App\Http\Controllers\Employee\EmployeeTicketMessageController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

    Route::get('/',function (){
        return redirect()->route('admin.dashboard');
    });


    Route::prefix('admin')->name('admin.')->group(function () {

            Route::middleware(['guest:admin'])->group(function (){
                Route::get('login', [AdminAuthController::class, 'login'])->name('login');
                Route::post('admin_login', [AdminAuthController::class, 'admin_login'])->name('admin_login');
            });

            Route::middleware(['admin.auth','permission.check:admin'])->group(function () {
                Route::get('not_found',[AdminAuthController::class, 'not_found'])->name('not_found');
                Route::get('/logout', [AdminAuthController::class, 'logout'])->name('logout');
                Route::get('dashboard', [AdminAuthController::class, 'index'])->name('dashboard');

                Route::prefix('permissions')->name('permissions.')->group(function () {
                    Route::get('/{id}', [PermissionController::class, 'index'])->name('index');
                    Route::post('store', [PermissionController::class, 'store'])->name('store');
                    Route::post('assignPermission/{id}', [PermissionController::class, 'assignPermission'])->name('assignPermission');
                });

                Route::resource('users', UserController::class)
                    ->except(['show']);


                Route::get('employees/attendance_list',[AdminEmployeeController::class,'attendance_list'])->name('employees.attendance_list');
                Route::get('employees/break_list',[AdminEmployeeController::class,'break_list'])->name('employees.break_list');
                Route::get('employees/daily_activity_list',[AdminEmployeeController::class,'daily_activity_list'])->name('employees.daily_activity_list');
                Route::get('employees/order_list',[AdminEmployeeController::class,'order_list'])->name('employees.order_list');
                Route::get('employees/order/history/{id}',[AdminEmployeeController::class,'order_history'])->name('employees.order.history');
                Route::get('employees/monthly_tax_list',[AdminEmployeeController::class,'monthly_tax_list'])->name('employees.monthly_tax_list');


                Route::post('employees/change-status', [AdminEmployeeController::class, 'changeStatus'])->name('employees.change-status');
                Route::get('employees/{employee}/documents', [AdminEmployeeController::class, 'getDocuments'])->name('employees.documents');
                Route::post('employees/documents/{document}/verify', [AdminEmployeeController::class, 'verify'])->name('employees.documents.verify');
                Route::post('employees/attach_agent', [AdminEmployeeController::class, 'attach_agent'])->name('employees.attach_agent');
                Route::resource('employees', AdminEmployeeController::class);
                Route::get('employees/show/{id}', [AdminEmployeeController::class, 'show'])->name('employees.show');

                //Roles Route
                Route::resource('roles', RoleController::class);


                Route::prefix('gratuity_payouts')->name('gratuity_payouts.')->group(function () {
                        Route::get('',[AdminGratuityPayoutController::class,'index'])->name('index');
                        Route::get('paid_list',[AdminGratuityPayoutController::class,'paid_list'])->name('paid_list');
                        Route::post('store',[AdminGratuityPayoutController::class,'store'])->name('store');
                        Route::get('/{id}/edit',[AdminGratuityPayoutController::class,'edit'])->name('edit');
                        Route::post('update',[AdminGratuityPayoutController::class,'update'])->name('update');
                        Route::post('approved',[AdminGratuityPayoutController::class,'payout_approved'])->name('approved');
                        Route::post('paid',[AdminGratuityPayoutController::class,'payout_paid'])->name('paid');
                });

                Route::resource('attendance_types',AdminAttendanceTypeController::class)
                    ->except(['show']);

                Route::resource('departments',AdminDepartmentController::class)
                    ->except(['show']);

                Route::resource('designations',AdminDesignationController::class)
                    ->except(['show']);

                Route::resource('shift_types',AdminShiftTypeController::class)
                    ->except(['show']);

                Route::resource('ticket_types', AdminTicketTypeController::class);

                // Gratuity Settings Routes
                Route::resource('gratuity_settings', AdminGratuitySettingController::class)
                    ->except(['show']);

                Route::resource('currency_settings', CurrencyRateController::class)
                    ->except(['show']);


                Route::get('gratuity_settings/assign_roles_index/{id}',[AdminGratuitySettingController::class,'assign_roles_index'])
                    ->name('gratuity_settings.assign_roles_index');
                //assign to role
                Route::post('gratuity_settings/assign_roles/{id}', [AdminGratuitySettingController::class, 'assign_roles'])
                    ->name('gratuity_settings.assign_roles');

                // Document Settings Routes
                Route::resource('document_settings', EmployeeDocumentSettingController::class)
                    ->except(['show']);

                //commission Setting routes
                Route::resource('commission_settings', EmployeeCommissionSettingController::class)
                    ->except(['show']);

                Route::get('commission_settings/assign_roles_index/{id}',[EmployeeCommissionSettingController::class,'assign_roles_index'])
                    ->name('commission_settings.assign_roles_index');
                //assign to role
                Route::post('commission_settings/assign_roles/{id}', [EmployeeCommissionSettingController::class, 'assign_roles'])
                    ->name('commission_settings.assign_roles');


                Route::resource('shift_attendance_rules', EmployeeShiftSettingController::class)
                    ->except(['show']);

                Route::resource('daily_activity_fields', DailyActivityController::class)
                    ->except(['show']);

                Route::get('daily_activity_fields/assign_roles_index/{id}',[DailyActivityController::class,'assign_roles_index'])
                    ->name('daily_activity_fields.assign_roles_index');
                //assign to role
                Route::post('daily_activity_fields/assign_roles/{id}', [DailyActivityController::class, 'assign_roles'])
                    ->name('daily_activity_fields.assign_roles');

                Route::resource('holidays',AdminHolidayController::class)->except(['show']);

                Route::resource('tax_slabs', AdminTaxSlabController::class)
                    ->except(['destroy']);
                Route::prefix('payroll')->name('payroll.')->group(function () {
                    Route::get('',[AdminPayrollController::class,'index'])->name('index');
                    Route::get('list',[AdminPayrollController::class,'list'])->name('list');
                    Route::post('generate',[AdminPayrollController::class,'payroll_generate'])->name('generate');
                    Route::post('approved', [AdminPayrollController::class, 'payroll_approve'])->name('approved');
                     Route::post('paid', [AdminPayrollController::class, 'payroll_paid'])->name('paid');

                    Route::prefix('payslip')->name('payslip.')->group(function () {
                        Route::get('',[AdminPayslipController::class,'index'])->name('index');
                        Route::get('list',[AdminPayslipController::class,'list'])->name('list');
                        Route::get('employee/{id}',[AdminPayslipController::class,'payslip_show'])->name('employee');
                        Route::get('employee/{id}/download',[AdminPayslipController::class,'payslip_download'])->name('employee.download');
                        Route::get('{payroll_id}', [AdminPayslipController::class,'show'])->name('show'); // dynamic
                        Route::post('add_adjustment',[AdminPayslipController::class,'add_adjustment'])->name('add_adjustment');

                    });

                });

                Route::prefix('tickets')->name('tickets.')->group(function () {
                    Route::get('/', [AdminTicketController::class, 'index'])->name('index');
                    Route::get('/create', [AdminTicketController::class, 'create'])->name('create');
                    Route::post('/', [AdminTicketController::class, 'store'])->name('store');
                    Route::post('/{ticket}/status', [AdminTicketController::class, 'changeStatus'])->name('changeStatus');
                    Route::post('/{ticket}/approve',[AdminTicketController::class, 'ticketApprove'])->name('approve');
                    Route::post('/{ticket}/reject',[AdminTicketController::class, 'ticketReject'])->name('reject');

                    // Chat
                    Route::get('/{ticket}/chat', [AdminTicketMessageController::class, 'index'])->name('chat');
                    Route::get('{ticket}/messages/fetch', [AdminTicketMessageController::class, 'fetchMessages'])
                        ->name('messages.fetch');
                    Route::post('/{ticket}/messages', [AdminTicketMessageController::class, 'store'])->name('messages.store');

                });


                Route::prefix('petty_cash')->name('petty_cash.')->group(function () {

                    // Masters
                    Route::resource('masters', PettyCashMasterController::class);

                    // Heads
                    Route::resource('heads', PettyCashHeadController::class);

                    // Transactions
//                    Route::resource('transactions', PettyCashTransactionController::class);
                    Route::prefix('transactions')->name('transactions.')->group(function () {

                        Route::get('/', [PettyCashTransactionController::class, 'index'])->name('index');
                        Route::post('/', [PettyCashTransactionController::class, 'store'])->name('store');
//                        Route::get('{transaction}/edit', [PettyCashTransactionController::class, 'edit'])->name('edit');
//                        Route::put('{transaction}', [PettyCashTransactionController::class, 'update'])->name('update');
                         Route::get('/{id}/print', [PettyCashTransactionController::class, 'printInvoice'])
                            ->name('print');
                        // Approve / Reject via AJAX
                        Route::post('{transaction}/approve', [PettyCashTransactionController::class, 'approveTransaction'])->name('approve');
                        Route::post('{transaction}/reject', [PettyCashTransactionController::class, 'rejectTransaction'])->name('reject');
                        Route::get('/payroll-list/{head_id}', [PettyCashTransactionController::class, 'payroll_list'])->name('payroll_list');

                    });

                    Route::prefix('ledger')->name('ledger.')->group(function (){
                        Route::get('', [PettyCashLedgerController::class, 'index'])
                            ->name('index');
                        Route::get('list', [PettyCashLedgerController::class, 'list'])
                            ->name('list');
                    });

                });

            });

    });


    Route::prefix('employee')->name('employee.')->group(function () {

        Route::post('screenshots', [\App\Http\Controllers\ScreenshotController::class, 'store'])
            ->name('screenshots.store');

        Route::middleware(['guest:employee'])->group(function () {
            Route::get('login', [EmployeeAuthController::class, 'login'])->name('login');
            Route::post('employee_login', [EmployeeAuthController::class, 'employee_login'])->name('employee_login');
        });

        Route::middleware(['employee.auth'])->group(function (){
            Route::get('/logout', [EmployeeAuthController::class, 'logout'])->name('logout');
            Route::get('dashboard', [EmployeeDashboardController::class, 'index'])->name('dashboard');
            Route::get('profile',[EmployeeDashboardController::class,'employee_profile'])->name('profile');
            Route::prefix('attendance')->name('attendance.')->group(function () {
                Route::get('', [EmployeeAttendanceController::class, 'index'])->name('index');
                Route::post('mark', [EmployeeAttendanceController::class, 'markAttendance'])->name('mark');
            });

            Route::prefix('breaks')->name('breaks.')->group(function () {
                Route::get('',[EmployeeBreakController::class,'index'])->name('index');
                Route::post('start', [EmployeeBreakController::class, 'startBreak'])->name('start');
                Route::post('end', [EmployeeBreakController::class, 'endBreak'])->name('end');
            });

            Route::resource('activities', EmployeeDailyActivityController::class)
                ->except(['show','destroy']);

            Route::prefix('tickets')->name('tickets.')->group(function () {
                Route::get('', [EmployeeTicketController::class, 'index'])->name('index');
                Route::post('store', [EmployeeTicketController::class, 'store'])->name('store');

                Route::get('/{ticket}/chat', [EmployeeTicketMessageController::class, 'index'])->name('chat');
                Route::get('{ticket}/messages/fetch', [EmployeeTicketMessageController::class, 'fetchMessages'])
                    ->name('messages.fetch');
                Route::post('/{ticket}/messages', [EmployeeTicketMessageController::class, 'store'])->name('messages.store');

            });

            Route::prefix('payslips')->name('payslips.')->group(function () {
                Route::get('', [EmployeePayslipController::class, 'index'])->name('index');
                Route::get('list', [EmployeePayslipController::class, 'list'])->name('list');
                Route::get('show/{id}',[EmployeePayslipController::class,'payslip_show'])->name('show');
                Route::get('/{id}/download',[EmployeePayslipController::class,'payslip_download'])->name('download');
            });

            Route::prefix('orders')->name('orders.')->group(function () {
                Route::get('today',[EmployeeDashboardController::class,'today_orders'])->name('today_orders');
                Route::get('list',[EmployeeDashboardController::class,'order_list'])->name('list');
                Route::get('history/{id}',[EmployeeDashboardController::class,'order_history'])->name('history');
            });

        });
    });

    Route::prefix('bridge')->group(function () {
        Route::post('/login', [\App\Http\Controllers\Bridge\HrBridgeController::class, 'login']);
        Route::post('/employee/create', [\App\Http\Controllers\Bridge\HrBridgeController::class, 'createEmployee']);
        Route::post('/agent/status', [\App\Http\Controllers\Bridge\HrBridgeController::class, 'agentStatus']);
        Route::post('/agent/login', [\App\Http\Controllers\Bridge\HrBridgeController::class, 'agentLogin']);
        Route::get('/sso/consume', [\App\Http\Controllers\Bridge\HrBridgeController::class, 'consume'])->name('bridge.hr.consume');
    });


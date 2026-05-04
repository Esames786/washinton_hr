<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Payslip - {{ $payrollDetail->employee->full_name }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #333;
        }
        .payslip-container {
            width: 100%;
            margin: 0 auto;
            padding: 15px;
            border: 1px solid #ccc;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
            margin-bottom: 15px;
            /*display: flex;*/
            /*align-items: center;*/
            /*justify-content: space-between;*/
        }
        .header img {
            max-height: 60px;
        }
        .header-text {
            text-align: center;
            flex: 1;
        }
        .header-text h2 {
            margin: 0;
        }
        .employee-info, .salary-info {
            width: 100%;
            margin-bottom: 20px;
            border-collapse: collapse;
        }
        .employee-info th, .salary-info th, .items th {
            padding: 6px 8px;
            border: 1px solid #ccc;
            text-align: left;   /* labels left */
        }
        .employee-info td, .salary-info td, .items td {
            padding: 6px 8px;
            border: 1px solid #ccc;
            text-align: right;  /* values right */
        }
        .salary-info th {
            background: #f5f5f5;
        }
        .net-salary {
            font-weight: bold;
            font-size: 14px;
            background: #e8f4e8;
        }
        .items {
            margin-top: 20px;
        }
        .items table {
            width: 100%;
            border-collapse: collapse;
        }
        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 11px;
            color: #666;
        }
    </style>
</head>
<body>
<div class="payslip-container">

    <div class="header">
        <!-- Left: Company logo -->
        <img src="{{ public_path('assets/images/logo/hello_transport.png') }}" alt="Company Logo" style="max-height:80px; display:block; margin:0 auto;">

        <!-- Center: Company name and payslip title -->
        <div class="header-text">
            <h2>Hello Transport</h2>
            <p><strong>Payslip for {{ \Carbon\Carbon::createFromFormat('Y-m', $payrollDetail->payroll->payroll_month)->format('Y-F') }}</strong></p>
        </div>

        <!-- Right: Blank space to balance flex layout -->
        <div style="width:60px;"></div>
    </div>

    <table class="employee-info">
        <tr>
            <th>Employee Name</th>
            <td>{{ $payrollDetail->employee->full_name }}</td>
            <th>Employee ID</th>
            <td>{{ $payrollDetail->employee->employee_code }}</td>
        </tr>
        <tr>
            <th>Department</th>
            <td>{{ $payrollDetail->employee->department->name ?? '-' }}</td>
            <th>Joining Date</th>
            <td>{{ $payrollDetail->employee->joining_date ?? '-' }}</td>
        </tr>
    </table>

    <table class="salary-info">
        <tr><th>Basic Salary</th><td>{{ number_format($payrollDetail->basic_salary, 2) }}</td></tr>
        <tr><th>Total Commission</th><td>{{ number_format($payrollDetail->total_commission, 2) }}</td></tr>
        <tr><th>Employee Gratuity</th><td>{{ number_format($payrollDetail->employee_gratuity, 2) }}</td></tr>
        <tr><th>Company Gratuity</th><td>{{ number_format($payrollDetail->company_gratuity, 2) }}</td></tr>
        <tr><th>Total Deductions</th><td>{{ number_format($payrollDetail->total_deductions, 2) }}</td></tr>
        <tr class="net-salary"><th>Net Salary</th><td>PKR {{ number_format($payrollDetail->net_salary, 2) }}</td></tr>
    </table>

    <div class="items">
        <h4>Adjustments</h4>
        <table>
            <thead>
            <tr>
                <th>Type</th>
                <th>Remarks</th>
                <th>Amount</th>
            </tr>
            </thead>
            <tbody>
            @forelse($payrollDetail->adjustments as $adj)
                <tr>
                    <td>
                        @if($adj->payslip_item_type_id == 1)
                            Addition
                        @elseif($adj->payslip_item_type_id == 2)
                            Deduction
                        @else
                            Other
                        @endif
                    </td>
                    <td>{{ $adj->remarks ?? '-' }}</td>
                    <td>
                        {{ $adj->payslip_item_type_id == 2 ? '-' : '+' }}
                        {{ number_format($adj->amount, 2) }}
                    </td>
                </tr>
            @empty
                <tr><td colspan="3" style="text-align: center">No Adjustments</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="items">
        <h4>Payslip Items</h4>
        <table>
            <thead>
            <tr>
                <th>Description</th>
                <th>Amount</th>
            </tr>
            </thead>
            <tbody>
            @forelse($payrollDetail->payslipItems as $item)
                <tr>
                    <td>{{ $item->description }}</td>
                    <td>{{ number_format($item->amount, 2) }}</td>
                </tr>
            @empty
                <tr><td colspan="2" style="text-align: center">No Items</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="footer">
        <p>This is a system-generated payslip and does not require a signature.</p>
    </div>

</div>
</body>
</html>

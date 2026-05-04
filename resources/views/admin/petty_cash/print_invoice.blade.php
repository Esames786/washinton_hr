<!DOCTYPE html>
<html>
<head>
    <title>Petty Cash Invoice</title>
    <style>
        body { font-family: Arial, sans-serif; }
        .invoice-box { max-width: 800px; margin: auto; padding: 30px; border: 1px solid #eee; }
        h2 { text-align: center; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        table, th, td { border: 1px solid #ddd; padding: 8px; }
        th { background: #f4f4f4; text-align: left; }
        .text-right { text-align: right; }
    </style>
</head>
<body onload="window.print()">
<div class="invoice-box">
    <div class="logo">
        <img src="{{ asset('assets/images/logo/hello_transport.png') }}" alt="Company Logo" style="max-height:80px; display:block; margin:0 auto;">
    </div>
    <h2>Petty Cash Transaction Invoice</h2>
    <p><strong>Transaction ID:</strong> {{ $txn->id }}</p>
    <p><strong>Date:</strong> {{ $txn->date }}</p>
    <p><strong>Master Account:</strong> {{ $txn->master->title ?? '-' }}</p>
    <p><strong>Head:</strong> {{ $txn->head->name ?? '-' }}</p>
    <p><strong>Description:</strong> {{ $txn->description }}</p>

    <table>
        <tr>
            <th>Entry Type</th>
            <th>Amount</th>
            <th>Status</th>
            <th>Balance</th>
        </tr>
        <tr>
            <td>{{ ucfirst($txn->entry_type) }}</td>
            <td>{{ number_format($txn->amount,2) }}</td>
            <td>{{ ucfirst($txn->status) }}</td>
            <td>{{ number_format($txn->balance,2) }}</td>
        </tr>
    </table>

    <p class="text-right">Generated on {{ now()->format('d-m-Y H:i') }}</p>
</div>
</body>
</html>

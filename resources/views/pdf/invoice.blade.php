<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Invoice - {{ $company->name }}</title>
    <style>
        /* basic CSS dompdf understands */
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            color: #333;
            font-size: 12px;
            line-height: 1.4;
        }
        .container {
            width: 95%;
            margin: 0 auto;
        }
        .header {
            text-align: right;
            margin-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            color: #000;
        }
        .company-details {
            margin-bottom: 30px;
        }
        .company-details h2 {
            margin: 0;
            font-size: 16px;
            font-weight: bold;
        }
        .invoice-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        .invoice-table th,
        .invoice-table td {
            border: 1px solid #ccc;
            padding: 8px;
            text-align: left;
        }
        .invoice-table th {
            background-color: #f4f4f4;
            font-weight: bold;
        }
        .total-row {
            font-weight: bold;
            font-size: 14px;
        }
        .total-row td {
            text-align: right;
        }
        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 10px;
            color: #888;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>INVOICE</h1>
            <p>Date: {{ $invoiceDate }}</p>
        </div>

        <div class="company-details">
            <h2>Bill To:</h2>
            <p>
                <strong>{{ $company->name }}</strong><br>
                {{-- You can add more company details here if they exist in your model --}}
                {{ $company->contact_email ?? '' }}
            </p>
        </div>

        <table class="invoice-table">
            <thead>
                <tr>
                    <th>Employee Name</th>
                    <th>Email</th>
                    <th>Membership Plan</th>
                    <th>Price</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($employees as $employee)
                    <tr>
                        <td>{{ $employee->name }}</td>
                        <td>{{ $employee->email }}</td>
                        <td>{{ $employee->membershipPlan?->name ?? 'N/A' }}</td>
                        <td>${{ number_format($employee->membershipPlan?->price ?? 0, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" style="text-align: center;">No active employees found for this billing period.</td>
                    </tr>
                @endforelse
                
                <!-- Total Row -->
                <tr class="total-row">
                    <td colspan="3">Total</td>
                    <td>${{ number_format($total, 2) }}</td>
                </tr>
            </tbody>
        </table>

        <div class="footer">
            <p>Thank you for your business!</p>
            <p>Fitpass HOPn</p>
        </div>
    </div>
</body>
</html>
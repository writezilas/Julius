<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Share Payment Approved Alert</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background-color: #f4f4f4; }
        .container { max-width: 600px; margin: 0 auto; background-color: white; padding: 30px; border-radius: 5px; }
        .header { text-align: center; border-bottom: 1px solid #eee; padding-bottom: 20px; margin-bottom: 20px; }
        .content { margin: 20px 0; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background-color: #f8f9fa; }
        .button { display: inline-block; padding: 12px 24px; background-color: #dc3545; color: white; text-decoration: none; border-radius: 4px; margin: 20px 0; }
        .footer { margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee; text-align: center; color: #666; }
        .success { color: #28a745; font-weight: bold; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Share Payment Approved Alert</h1>
        </div>
        
        <div class="content">
            <p class="success">A share payment has been successfully approved and processed.</p>
            
            <table>
                <tr><th>User Name</th><td>{{ $user->name }}</td></tr>
                <tr><th>Username</th><td>{{ $user->username }}</td></tr>
                <tr><th>Email</th><td>{{ $user->email }}</td></tr>
                <tr><th>Payment Amount</th><td>${{ number_format($amount, 2) }}</td></tr>
                <tr><th>Transaction ID</th><td>{{ $payment->txs_id ?? 'N/A' }}</td></tr>
                <tr><th>Payment Method</th><td>{{ $payment->payment_method ?? 'N/A' }}</td></tr>
                <tr><th>Approved Date</th><td>{{ $approvedDate }}</td></tr>
                <tr><th>Payment Status</th><td>{{ ucfirst($payment->status ?? 'N/A') }}</td></tr>
            </table>
            
            <p>The payment has been successfully processed and the share transaction is now complete.</p>
            
            <a href="{{ url('/admin/payments') }}" class="button">View Payment Management</a>
        </div>
        
        <div class="footer">
            <p>Best regards,<br>{{ config('app.name') }} Admin System</p>
        </div>
    </div>
</body>
</html>

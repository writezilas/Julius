<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>New Share Purchase Alert</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background-color: #f4f4f4; }
        .container { max-width: 600px; margin: 0 auto; background-color: white; padding: 30px; border-radius: 5px; }
        .header { text-align: center; border-bottom: 1px solid #eee; padding-bottom: 20px; margin-bottom: 20px; }
        .content { margin: 20px 0; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background-color: #f8f9fa; }
        .button { display: inline-block; padding: 12px 24px; background-color: #28a745; color: white; text-decoration: none; border-radius: 4px; margin: 20px 0; }
        .footer { margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee; text-align: center; color: #666; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>New Share Purchase Alert</h1>
        </div>
        
        <div class="content">
            <p>A user has purchased shares on the {{ config('app.name') }} platform.</p>
            
            <table>
                <tr><th>User Name</th><td>{{ $user->name }}</td></tr>
                <tr><th>Username</th><td>{{ $user->username }}</td></tr>
                <tr><th>Email</th><td>{{ $user->email }}</td></tr>
                <tr><th>Trade/Category</th><td>{{ $trade->name ?? 'N/A' }}</td></tr>
                <tr><th>Purchase Amount</th><td>${{ number_format($amount, 2) }}</td></tr>
                <tr><th>Shares Purchased</th><td>{{ number_format($sharesCount) }}</td></tr>
                <tr><th>Ticket Number</th><td>{{ $ticketNo }}</td></tr>
                <tr><th>Purchase Date</th><td>{{ $purchaseDate }}</td></tr>
            </table>
            
            <p><strong>Note:</strong> The user will need to complete payment to finalize this share purchase.</p>
            
            <a href="{{ url('/admin/shares') }}" class="button">View Share Management</a>
        </div>
        
        <div class="footer">
            <p>Best regards,<br>{{ config('app.name') }} Admin System</p>
        </div>
    </div>
</body>
</html>

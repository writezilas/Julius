<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>New User Registration Alert</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background-color: #f4f4f4; }
        .container { max-width: 600px; margin: 0 auto; background-color: white; padding: 30px; border-radius: 5px; }
        .header { text-align: center; border-bottom: 1px solid #eee; padding-bottom: 20px; margin-bottom: 20px; }
        .content { margin: 20px 0; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background-color: #f8f9fa; }
        .button { display: inline-block; padding: 12px 24px; background-color: #007bff; color: white; text-decoration: none; border-radius: 4px; margin: 20px 0; }
        .footer { margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee; text-align: center; color: #666; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>New User Registration Alert</h1>
        </div>
        
        <div class="content">
            <p>A new user has registered on the {{ config('app.name') }} platform.</p>
            
            <table>
                <tr><th>Name</th><td>{{ $user->name }}</td></tr>
                <tr><th>Email</th><td>{{ $user->email }}</td></tr>
                <tr><th>Username</th><td>{{ $user->username }}</td></tr>
                <tr><th>Phone</th><td>{{ $user->phone ?? 'Not provided' }}</td></tr>
                <tr><th>Registration Date</th><td>{{ $registrationDate }}</td></tr>
                <tr><th>Status</th><td>{{ ucfirst($user->status) }}</td></tr>
            </table>
            
            <p>Please review this new user registration and take any necessary actions.</p>
            
            <a href="{{ url('/admin/users') }}" class="button">View Users Management</a>
        </div>
        
        <div class="footer">
            <p>Best regards,<br>{{ config('app.name') }} Admin System</p>
        </div>
    </div>
</body>
</html>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trial Expired</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
            border-radius: 8px 8px 0 0;
        }
        .content {
            background: #f9fafb;
            padding: 30px;
            border-radius: 0 0 8px 8px;
        }
        .alert-box {
            background: #fee2e2;
            border-left: 4px solid #ef4444;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .button {
            display: inline-block;
            background: #667eea;
            color: white;
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 6px;
            margin: 20px 0;
        }
        .footer {
            text-align: center;
            color: #6b7280;
            font-size: 14px;
            margin-top: 30px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>VELOZZ.DIGITAL</h1>
        <p>Your trial has expired</p>
    </div>

    <div class="content">
        <p>Hello <strong>{{ $tenant->admin_name }}</strong>,</p>

        <div class="alert-box">
            <strong>Your trial period has expired and your account has been suspended.</strong>
        </div>

        <p>Thank you for trying VELOZZ.DIGITAL! Your trial period has ended on {{ $tenant->trial_ends_at->format('d/m/Y H:i') }}.</p>

        <p>To continue using VELOZZ.DIGITAL and restore access to your data, please activate a subscription plan.</p>

        <p><strong>What happens now:</strong></p>
        <ul>
            <li>Your account is temporarily suspended</li>
            <li>All your data is safely stored</li>
            <li>You can reactivate anytime by choosing a plan</li>
        </ul>

        <center>
            <a href="{{ config('app.url') }}/app" class="button">Choose a Plan & Reactivate</a>
        </center>

        <p>Need help? Contact our support team and we'll assist you.</p>

        <p>Best regards,<br>
        The VELOZZ.DIGITAL Team</p>
    </div>

    <div class="footer">
        <p>&copy; {{ date('Y') }} VELOZZ.DIGITAL. All rights reserved.</p>
    </div>
</body>
</html>

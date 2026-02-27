<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trial Expiring Soon</title>
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
            background: #fef3c7;
            border-left: 4px solid #f59e0b;
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
        <p>Your trial is expiring soon</p>
    </div>

    <div class="content">
        <p>Hello <strong>{{ $tenant->admin_name }}</strong>,</p>

        <div class="alert-box">
            <strong>Your trial period will expire in {{ $daysRemaining }} {{ Str::plural('day', $daysRemaining) }}.</strong>
        </div>

        <p>We hope you've been enjoying VELOZZ.DIGITAL! To continue using all the features without interruption, please activate a subscription plan.</p>

        <p><strong>Account Details:</strong></p>
        <ul>
            <li>Company: {{ $tenant->name }}</li>
            <li>Trial ends: {{ $tenant->trial_ends_at->format('d/m/Y H:i') }}</li>
        </ul>

        <center>
            <a href="{{ config('app.url') }}/app" class="button">Activate Subscription</a>
        </center>

        <p>If you have any questions, please don't hesitate to contact our support team.</p>

        <p>Best regards,<br>
        The VELOZZ.DIGITAL Team</p>
    </div>

    <div class="footer">
        <p>&copy; {{ date('Y') }} VELOZZ.DIGITAL. All rights reserved.</p>
    </div>
</body>
</html>

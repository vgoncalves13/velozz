<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Deletion Confirmation</title>
    <style>
        body { font-family: sans-serif; max-width: 600px; margin: 80px auto; padding: 0 20px; color: #333; }
        h1 { font-size: 1.5rem; }
        .code { font-family: monospace; background: #f4f4f4; padding: 8px 12px; border-radius: 4px; display: inline-block; }
    </style>
</head>
<body>
    <h1>Data Deletion Request</h1>
    <p>Your data deletion request has been received and processed.</p>
    @if($code)
        <p>Confirmation code: <span class="code">{{ $code }}</span></p>
    @endif
    <p>If you have any questions, please contact our support team.</p>
</body>
</html>

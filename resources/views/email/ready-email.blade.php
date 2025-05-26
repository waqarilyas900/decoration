<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title> 911ERP || Order Ready</title>
<style>
    body {
        font-family: Arial, sans-serif;
        margin: 0;
        padding: 20px;
        background-color: #f4f4f4;
    }
    .container {
        max-width: 600px;
        margin: 0 auto;
        background: #ffffff;
        padding: 20px;
    }
    .header {
        background-color: #007bff;
        color: #ffffff;
        padding: 10px;
        text-align: center;
    }
    .content {
        padding: 20px;
        text-align: center;
    }
    .footer {
        background-color: #eee;
        padding: 10px;
        text-align: center;
        font-size: 12px;
    }
    .button {
        display: inline-block;
        background-color: #007bff;
        color: #ffffff;
        padding: 10px 20px;
        text-decoration: none;
        margin-top: 20px;
        border-radius: 5px;
    }
</style>
</head>
<body>
<div class="container">
    <div class="header">
        <h2>Welcome to ERP Decoration Portal</h2>
    </div>
    <div class="content">
        <p>Hi {{ $order->employee->first_name . ' '.$order->employee->last_name }},</p>
        <p>Order {{ $order->order_number }} is ready in {{ $order->current_location }}.</p>
        {{-- <a href="#" class="button">Get Started</a> --}}
    </div>
    <div class="footer">
        Need help? Contact us at <a href="mailto:uniforms@911erp.com">uniforms@911erp.com</a>
    </div>
</div>
</body>
</html>

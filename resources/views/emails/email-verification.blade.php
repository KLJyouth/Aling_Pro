<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $appName }} - 邮箱验证</title>
    <style>
        body {
            font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .container {
            background-color: #f9f9f9;
            border-radius: 5px;
            padding: 20px;
            border: 1px solid #e0e0e0;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .logo {
            max-width: 150px;
            height: auto;
        }
        h1 {
            color: #333;
            font-size: 24px;
            margin-bottom: 20px;
        }
        .code-container {
            background-color: #f0f0f0;
            padding: 15px;
            border-radius: 5px;
            text-align: center;
            margin: 20px 0;
            border: 1px dashed #ccc;
        }
        .verification-code {
            font-size: 24px;
            font-weight: bold;
            letter-spacing: 5px;
            color: #007bff;
        }
        .button {
            display: inline-block;
            background-color: #007bff;
            color: white !important;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 5px;
            margin-top: 20px;
        }
        .footer {
            margin-top: 30px;
            font-size: 12px;
            color: #777;
            text-align: center;
        }
        .note {
            font-size: 14px;
            color: #666;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <img src="{{ asset("images/logo.png") }}" alt="{{ $appName }}" class="logo">
        </div>
        
        <h1>您好，{{ $name }}！</h1>
        
        <p>感谢您使用 {{ $appName }}。请验证您的电子邮箱地址，以确保您可以接收重要通知并保护您的账户安全。</p>
        
        <p>您的验证码是：</p>
        
        <div class="code-container">
            <div class="verification-code">{{ $code }}</div>
        </div>
        
        <p>请在验证页面输入此验证码完成邮箱验证。验证码有效期为24小时。</p>
        
        <p>或者，您也可以点击下面的按钮直接验证：</p>
        
        <div style="text-align: center;">
            <a href="{{ $verifyUrl }}" class="button">验证邮箱</a>
        </div>
        
        <p class="note">如果您没有请求此验证，请忽略此邮件。</p>
        
        <div class="footer">
            <p>此邮件由系统自动发送，请勿回复。</p>
            <p>&copy; {{ date("Y") }} {{ $appName }}. 保留所有权利。</p>
        </div>
    </div>
</body>
</html>

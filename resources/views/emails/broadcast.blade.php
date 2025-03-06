<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Email Notification</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f8f9fa;
        }
        .container {
            background: #fff;
            padding: 0;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 16px rgba(0,0,0,0.08);
        }
        .header {
            text-align: center;
            padding: 24px;
            background: linear-gradient(135deg, #f5f7fa 0%, #e4eaef 100%);
        }
        .logo {
            max-width: 140px;
            height: auto;
        }
        .banner {
            width: 100%;
            max-height: 200px;
            object-fit: cover;
            display: block;
        }
        .banner-container {
            position: relative;
            background-color: #f0f4f8;
            text-align: center;
        }
        .content {
            text-align: center;
            padding: 30px;
            color: #444;
        }
        .footer {
            background-color: #f8f9fa;
            padding: 20px;
            font-size: 12px;
            color: #777;
            text-align: center;
        }
        .divider {
            height: 1px;
            background: #eee;
            margin: 0;
        }
        a {
            color: #3490dc;
            text-decoration: none;
            font-weight: 500;
        }
        a:hover {
            text-decoration: underline;
        }
        .social-links {
            margin: 15px 0;
        }
        .social-links a {
            display: inline-block;
            margin: 0 8px;
            color: #666;
        }
        .btn {
            display: inline-block;
            background-color: #3490dc;
            color: white;
            padding: 10px 20px;
            border-radius: 4px;
            margin-top: 10px;
            font-weight: bold;
            text-decoration: none;
        }
        .btn:hover {
            background-color: #2779bd;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <img src="https://cellministry.tv/pfcc/assets/logo.png" alt="Company Logo" class="logo">
        </div>
        
        @if(isset($bannerImage))
            <div class="banner-container">
                <img src="{{ $bannerImage }}" alt="Banner" class="banner">
            </div>
        @endif
        
        <div class="content" style="white-space: pre-wrap;">
            {!! $messageContent !!}
        </div>
        
        <div class="divider"></div>
        
        <div class="footer">
            <div class="social-links">
                <a href="#">Facebook</a> • 
                <a href="#">Twitter</a> • 
                <a href="#">Instagram</a> • 
                <a href="#">LinkedIn</a>
            </div>
            
            @if(isset($metadata['unsubscribe_link']))
                <p>
                    If you'd like to unsubscribe, <a href="{{ $metadata['unsubscribe_link'] }}">click here</a>
                </p>
            @endif
            
            <p>&copy; {{ date('Y') }} Your Company Name. All rights reserved.</p>
        </div>
    </div>
</body>
</html> 
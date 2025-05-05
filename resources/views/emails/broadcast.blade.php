<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Email Notification</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            line-height: 1.6;
            color: #1a1a1a;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f4f4f5;
        }
        .container {
            background: #ffffff;
            padding: 0;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }
        .header {
            padding: 32px;
            background: #ffffff;
            border-bottom: 1px solid #e5e7eb;
            text-align: center;
        }
        .logo {
            max-width: 120px;
            height: auto;
            display: inline-block;
        }
        .banner {
            width: 100%;
            height: auto;
            display: block;
        }
        .banner-container {
            background-color: #f8fafc;
            max-height: none;
            line-height: 0;
        }
        .content {
            padding: 32px;
            color: #374151;
        }
        .greeting {
            font-size: 16px;
            color: black;
            margin-bottom: 16px;
        }
        .message {
            font-size: 15px;
            line-height: 1.7;
            color: #1f2937;
            white-space: pre-line;
            text-align: left;
            margin: 0;
            padding: 0;
        }
        .message p {
            margin: 0 0 16px 0;
        }
        .message p:last-child {
            margin-bottom: 0;
        }
        .footer {
            background-color: #f8fafc;
            padding: 24px 32px;
            font-size: 13px;
            color: #6b7280;
            border-top: 1px solid #e5e7eb;
        }
        .social-links {
            margin: 16px 0;
        }
        .social-links a {
            color: #4b5563;
            text-decoration: none;
            margin-right: 16px;
            font-weight: 500;
        }
        .social-links a:hover {
            color: #2563eb;
        }
        .btn {
            display: inline-block;
            background-color: #2563eb;
            color: white;
            padding: 12px 24px;
            border-radius: 6px;
            margin-top: 24px;
            font-weight: 500;
            text-decoration: none;
            font-size: 14px;
        }
        .btn:hover {
            background-color: #1d4ed8;
        }
        .copyright {
            margin-top: 16px;
            font-size: 12px;
            color: #9ca3af;
        }
        @media (max-width: 600px) {
            body {
                padding: 12px;
            }
            .container {
                border-radius: 12px;
            }
            .header, .content, .footer {
                padding: 24px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <img src="https://cellministry.tv/pfcc/assets/logo.png" alt="Company Logo" class="logo">
        </div>

        @if(isset($messageContent['bannerImage']))
            <div>
                <img src="{{ $messageContent['bannerImage'] }}" style="width: 100%; height: auto;" alt="">
            </div>
        @endif

        <div class="content">
            <div class="greeting">
                Dear {{ $messageContent['name'] }},
            </div>
            
            <div class="message">
                {!! $messageContent['message'] !!}
            </div>
        </div>
        
        <div class="footer">
            <!-- <div class="social-links">
                <a href="https://www.facebook.com/share/19rVXhK1Zu/">Facebook</a>
                <a href="https://x.com/cellseverywhere?t=mcwZw3-RhChyDuim-3OIug&s=09">X</a>
                <a href="https://www.instagram.com/p/DGzxv1KNfcY/?igsh=em9iOHg5b2xkcWk=">Instagram</a>
                <a href="https://vm.tiktok.com/ZMBe751SN/">TikTok</a>
            </div> -->
            
            <div class="copyright">
                &copy; {{ date('Y') }} Love World Cell Ministry. All rights reserved.
            </div>
        </div>
    </div>
</body>
</html> 
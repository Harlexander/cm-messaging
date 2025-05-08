<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Email Notification</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            background-color: #f4f4f5;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
        }
        table {
            border-spacing: 0;
            border-collapse: collapse;
            mso-table-lspace: 0pt;
            mso-table-rspace: 0pt;
        }
        td {
            padding: 0;
        }
        img {
            border: 0;
            height: auto;
            line-height: 100%;
            outline: none;
            text-decoration: none;
            -ms-interpolation-mode: bicubic;
        }
        .wrapper {
            width: 100%;
            table-layout: fixed;
            background-color: #f4f4f5;
            padding: 20px 0;
        }
        .container {
            max-width: 500px;
            background-color: #ffffff;
            margin: 0 auto;
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
        }
        .content {
            padding: 32px;
            color: #374151;
            line-height: 1.7;
        }
        .message {
            font-size: 15px;
            color: #1f2937;
            text-align: left;
        }
        .footer {
            background-color: #f8fafc;
            padding: 24px 32px;
            font-size: 13px;
            color: #6b7280;
            border-top: 1px solid #e5e7eb;
        }
        .copyright {
            font-size: 12px;
            color: #9ca3af;
        }
        @media screen and (max-width: 600px) {
            .container {
                width: 100% !important;
                max-width: 100% !important;
            }
            .content, .header, .footer {
                padding: 24px !important;
            }
        }
    </style>
</head>
<body>
    <table class="wrapper" width="100%" cellpadding="0" cellspacing="0" role="presentation">
        <tr>
            <td align="center">
                <table class="container" width="500" cellpadding="0" cellspacing="0" role="presentation">
                    <tr>
                        <td class="header">
                            <img src="https://cellministry.tv/pfcc/assets/logo.png" alt="Company Logo" class="logo">
                        </td>
                    </tr>
                    
                    @if(isset($messageContent['bannerImage']))
                    <tr>
                        <td>
                            <img src="{{ $messageContent['bannerImage'] }}" width="500" style="width: 100%;" alt="">
                        </td>
                    </tr>
                    @endif
                    
                    <tr>
                        <td class="content">
                            <table width="100%" cellpadding="0" cellspacing="0" role="presentation">
                                <tr>
                                    <td class="message">
                                        {!! $messageContent['message'] !!}
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    
                    <tr>
                        <td class="footer">
                            <table width="100%" cellpadding="0" cellspacing="0" role="presentation">
                                <tr>
                                    <td class="copyright" align="center">
                                        &copy; {{ date('Y') }} Love World Cell Ministry. All rights reserved.
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html> 
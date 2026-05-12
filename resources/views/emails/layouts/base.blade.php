<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $subject ?? 'Efiewura Notification' }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f4f4f4;
        }
        
        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px 20px;
            text-align: center;
        }
        
        .header h1 {
            font-size: 28px;
            margin-bottom: 10px;
        }
        
        .header p {
            font-size: 16px;
            opacity: 0.9;
        }
        
        .content {
            padding: 40px 30px;
        }
        
        .notification-type {
            display: inline-block;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 20px;
        }
        
        .type-high { background-color: #fee; color: #c53030; }
        .type-medium { background-color: #fff3cd; color: #856404; }
        .type-low { background-color: #d1ecf1; color: #0c5460; }
        .type-urgent { background-color: #f8d7da; color: #721c24; }
        
        .message-title {
            font-size: 24px;
            color: #2d3748;
            margin-bottom: 20px;
            font-weight: 600;
        }
        
        .message-content {
            font-size: 16px;
            line-height: 1.8;
            color: #4a5568;
            margin-bottom: 30px;
        }
        
        .details-box {
            background-color: #f7fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        
        .details-box h3 {
            color: #2d3748;
            margin-bottom: 15px;
            font-size: 18px;
        }
        
        .detail-item {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .detail-item:last-child {
            border-bottom: none;
        }
        
        .detail-label {
            font-weight: 600;
            color: #4a5568;
        }
        
        .detail-value {
            color: #2d3748;
        }
        
        .cta-button {
            display: inline-block;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px 30px;
            text-decoration: none;
            border-radius: 25px;
            font-weight: 600;
            margin: 20px 0;
            transition: transform 0.2s;
        }
        
        .cta-button:hover {
            transform: translateY(-2px);
        }
        
        .footer {
            background-color: #2d3748;
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .footer-links {
            margin: 20px 0;
        }
        
        .footer-links a {
            color: #a0aec0;
            text-decoration: none;
            margin: 0 15px;
        }
        
        .footer-links a:hover {
            color: white;
        }
        
        .social-links {
            margin: 20px 0;
        }
        
        .social-links a {
            display: inline-block;
            margin: 0 10px;
            color: #a0aec0;
            text-decoration: none;
        }
        
        .unsubscribe {
            font-size: 12px;
            color: #a0aec0;
            margin-top: 20px;
        }
        
        .unsubscribe a {
            color: #a0aec0;
        }
        
        @media (max-width: 600px) {
            .email-container {
                margin: 0;
                box-shadow: none;
            }
            
            .content {
                padding: 20px;
            }
            
            .header {
                padding: 20px;
            }
            
            .header h1 {
                font-size: 24px;
            }
            
            .detail-item {
                flex-direction: column;
            }
            
            .detail-label {
                margin-bottom: 5px;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <!-- Header -->
        <div class="header">
            <h1>Efiewura</h1>
            <p>Ghana's Premier Property Management Platform</p>
        </div>
        
        <!-- Content -->
        <div class="content">
            @yield('content')
        </div>
        
        <!-- Footer -->
        <div class="footer">
            <h3>Efiewura Property Management</h3>
            <p>Making property rental simple and secure across Ghana</p>
            
            <div class="footer-links">
                <a href="{{ config('app.url') }}">Visit Website</a>
                <a href="{{ config('app.url') }}/support">Support</a>
                <a href="{{ config('app.url') }}/privacy">Privacy Policy</a>
            </div>
            
            <div class="social-links">
                <a href="#">Facebook</a>
                <a href="#">Twitter</a>
                <a href="#">LinkedIn</a>
                <a href="#">Instagram</a>
            </div>
            
            <div class="unsubscribe">
                <p>You received this email because you have an account with Efiewura.</p>
                <p><a href="#">Manage email preferences</a> | <a href="#">Unsubscribe</a></p>
            </div>
        </div>
    </div>
</body>
</html>

<!DOCTYPE html>
<html>
<head>
    <title>Contact Form Submission</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            color: #333;
            line-height: 1.6;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 80%;
            margin: 0 auto;
            background: #fff;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            border-radius: 5px;
            margin-top: 20px;
        }
        h1 {
            background: #82CF45;
            color: white;
            padding: 10px;
            border-radius: 5px;
            text-align: center;
        }
        p {
            font-size: 14px;
            margin: 10px 0;
        }
        .label {
            font-weight: bold;
            color: #555;
        }
        .content {
            margin: 10px 0;
            padding: 10px;
            background: #f9f9f9;
            border-left: 5px solid #82CF45;
            border-radius: 3px;
        }
        .footer {
            text-align: center;
            margin-top: 20px;
            color: #aaa;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Contact Form Submission</h1>
        <p class="label">Reference:</p>
        <div class="content">{{ $details['reference'] }}</div>
        <p class="label">Trip:</p>
        <div class="content">{{ $details['trip'] }}</div>
        <p class="label">Email:</p>
        <div class="content">{{ $details['email'] }}</div>
        <p class="label">Subject:</p>
        <div class="content">{{ $details['subject'] }}</div>
        <p class="label">Message:</p>
        <div class="content">{{ $details['message'] }}</div>
        <div class="footer">
            <p>&copy; {{ date('Y') }} VibeAdventures. All rights reserved.</p>
        </div>
    </div>
</body>
</html>

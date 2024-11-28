<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Complete Your Booking</title>
  <style>
    body {
      margin: 0;
      padding: 0;
      font-family: Arial, sans-serif;
      background-color: #f9f9f9;
      color: #333;
    }
    .email-container {
      max-width: 600px;
      margin: 20px auto;
      background-color: #ffffff;
      border: 1px solid #ddd;
      border-radius: 8px;
      overflow: hidden;
    }
    .email-header {
      background-color: #82CF45;
      color: #ffffff;
      text-align: center;
      padding: 20px;
    }
    .email-header h1 {
      margin: 0;
      font-size: 24px;
    }
    .email-body {
      padding: 20px;
    }
    .email-body p {
      font-size: 16px;
      line-height: 1.5;
      margin: 15px 0;
    }
    .cta-button {
      display: inline-block;
      background-color: #82CF45;
      color: #ffffff;
      text-decoration: none;
      padding: 12px 20px;
      font-size: 16px;
      border-radius: 5px;
      text-align: center;
    }
    .cta-container {
      text-align: center;
      margin: 20px 0;
    }
    .email-footer {
      background-color: #f1f1f1;
      text-align: center;
      padding: 15px;
    }
    .email-footer p {
      font-size: 14px;
      color: #666;
      margin: 5px 0;
    }
    .email-footer a {
      color: #007bff;
      text-decoration: none;
    }
  </style>
</head>
<body>
  <div class="email-container">
    <!-- Header -->
    <div class="email-header">
      <h1>Still Thinking About It?</h1>
    </div>
    
    <!-- Body -->
    <div class="email-body">
      <p>Hi {{ $tour['userName'] }},</p>
      <p>
        We noticed you left some exciting plans behind. Your booking for <strong>{{ $tour['name'] }}</strong> is still waiting! Don't miss the chance to confirm your spot and create unforgettable memories.
      </p>
      <p>
        Click the button below to complete your booking. We’re here to help if you have any questions!
      </p>
      
      <!-- Call-to-action Button -->
      <div class="cta-container">
        <a href="{{ $tour['link'] }}" class="cta-button">Complete Your Booking</a>
      </div>
      
      <p style="text-align: center;">
        Don’t wait too long—availability is limited!
      </p>
    </div>
    
    <!-- Footer -->
    <div class="email-footer">
        <p>&copy; {{ date('Y') }} VibeAdventures. All rights reserved.</p>
    </div>
  </div>
</body>
</html>

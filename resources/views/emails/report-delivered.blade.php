<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report Delivered - BeanTrack</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            padding: 20px 0;
            border-bottom: 2px solid #8B4513;
            margin-bottom: 30px;
        }
        .header h1 {
            color: #8B4513;
            margin: 0;
            font-size: 24px;
        }
        .logo {
            font-size: 32px;
            color: #8B4513;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .content {
            padding: 20px 0;
        }
        .greeting {
            font-size: 18px;
            color: #8B4513;
            margin-bottom: 20px;
        }
        .report-details {
            background-color: #f9f9f9;
            border-left: 4px solid #8B4513;
            padding: 20px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .report-details h3 {
            color: #8B4513;
            margin-top: 0;
            font-size: 20px;
        }
        .detail-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        .detail-row:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }
        .detail-label {
            font-weight: bold;
            color: #555;
        }
        .detail-value {
            color: #333;
        }
        .download-section {
            background-color: #8B4513;
            color: white;
            padding: 20px;
            border-radius: 6px;
            text-align: center;
            margin: 30px 0;
        }
        .download-section h3 {
            margin-top: 0;
            color: white;
        }
        .download-button {
            display: inline-block;
            background-color: #CD853F;
            color: white;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 6px;
            font-weight: bold;
            margin: 10px 0;
            transition: background-color 0.3s;
        }
        .download-button:hover {
            background-color: #A0522D;
        }
        .footer {
            text-align: center;
            padding: 20px 0;
            border-top: 1px solid #eee;
            margin-top: 30px;
            color: #666;
            font-size: 14px;
        }
        .footer a {
            color: #8B4513;
            text-decoration: none;
        }
        .attachment-notice {
            background-color: #e8f5e8;
            border: 1px solid #c3e6c3;
            padding: 15px;
            border-radius: 4px;
            margin: 20px 0;
        }
        .attachment-notice i {
            color: #28a745;
            margin-right: 8px;
        }
        @media (max-width: 600px) {
            .container {
                padding: 10px;
            }
            .detail-row {
                flex-direction: column;
                gap: 5px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">☕ BeanTrack</div>
            <h1>Report Delivered</h1>
        </div>

        <div class="content">
            <div class="greeting">
                Hello {{ $recipient->name ?? 'Valued User' }},
            </div>

            <p>Your scheduled report has been successfully generated and is ready for review.</p>

            <div class="report-details">
                <h3>📊 Report Details</h3>
                <div class="detail-row">
                    <span class="detail-label">Report Name:</span>
                    <span class="detail-value">{{ $reportName }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Generated On:</span>
                    <span class="detail-value">{{ $generatedAt }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Format:</span>
                    <span class="detail-value">{{ $format }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">File Size:</span>
                    <span class="detail-value">{{ $fileSize }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Report Type:</span>
                    <span class="detail-value">{{ ucfirst($report->type ?? 'Scheduled') }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Frequency:</span>
                    <span class="detail-value">{{ ucfirst($report->frequency ?? 'N/A') }}</span>
                </div>
            </div>

            <div class="attachment-notice">
                <i>📎</i> <strong>Report Attached:</strong> The report file is attached to this email for your convenience.
            </div>

            <div class="download-section">
                <h3>📥 Access Your Report</h3>
                <p>Your report is attached to this email. You can also access it through your BeanTrack dashboard.</p>
                <a href="{{ url('/reports') }}" class="download-button">View in Dashboard</a>
            </div>

            <p>If you have any questions about this report or need assistance, please don't hesitate to contact our support team.</p>

            <p>Best regards,<br>
            <strong>The BeanTrack Team</strong></p>
        </div>

        <div class="footer">
            <p>This is an automated message from BeanTrack. Please do not reply to this email.</p>
            <p>
                <a href="{{ url('/') }}">Visit BeanTrack</a> | 
                <a href="{{ url('/reports') }}">Manage Reports</a> | 
                <a href="{{ url('/support') }}">Support</a>
            </p>
        </div>
    </div>
</body>
</html>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Question Status Update</title>
    <style>
        body { font-family: 'Noto Sans', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f4f6f9; margin: 0; padding: 20px; color: #333; }
        .container { max-width: 600px; margin: 0 auto; background: #fff; border-radius: 10px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,.08); border: 1px solid #e2e8f0; }
        .header { background: linear-gradient(135deg, #991b1b, #7f1d1d); color: #fff; padding: 25px 30px; }
        .header h2 { margin: 0; font-size: 1.25rem; }
        .body { padding: 25px 30px; line-height: 1.7; color: #333; }
        .reason-box { background: #fef2f2; border: 1px solid #fecaca; border-radius: 8px; padding: 16px; margin: 18px 0; }
        .reason-title { font-weight: bold; color: #991b1b; margin-bottom: 6px; font-size: 0.95rem; }
        .reason-text { color: #4b5563; font-size: 0.9rem; margin: 0; }
        .footer { background: #f8fafc; padding: 18px 30px; font-size: .8rem; color: #888; text-align: center; border-top: 1px solid #eee; }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h2>Update Regarding Your Submitted Islamic Question</h2>
        <p style="margin:.3rem 0 0;opacity:.85;font-size:.9rem;">Darul Ifta Kauzariyya</p>
    </div>
    <div class="body">
        <p>Dear <strong>{{ $question->user?->name ?? 'Questioner' }}</strong>,</p>
        <p>Thank you for reaching out to Darul Ifta Kauzariyya. We are writing to update you regarding your inquiry (Question ID: <strong>{{ $question->question_id }}</strong>).</p>
        
        <div class="reason-box">
            <div class="reason-title">Status: Rejected / Returned</div>
            <p class="reason-text"><strong>Reason:</strong> {{ $reason }}</p>
            @if(!empty($explanation))
                <p class="reason-text" style="margin-top: 8px;"><strong>Details:</strong> {{ $explanation }}</p>
            @endif
        </div>

        <p style="font-size: 0.9rem; color: #6b7280;">If you wish to submit a revised or clarified question, you may log in to your account on the portal and submit a new inquiry with the required details.</p>
        <p style="margin-top:20px;color:#888;font-size:.85rem;">Jazakumullahu Khayran for your understanding.</p>
    </div>
    <div class="footer">© {{ date('Y') }} Darul Ifta Kauzariyya. All rights reserved.</div>
</div>
</body>
</html>


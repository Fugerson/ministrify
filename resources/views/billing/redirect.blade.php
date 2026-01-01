<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Перенаправлення на оплату...</title>
    <style>
        body {
            font-family: system-ui, -apple-system, sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
            background: #f3f4f6;
        }
        .container {
            text-align: center;
            padding: 2rem;
        }
        .spinner {
            width: 48px;
            height: 48px;
            border: 4px solid #e5e7eb;
            border-top-color: #3b82f6;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto 1rem;
        }
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        h1 {
            font-size: 1.25rem;
            color: #1f2937;
            margin-bottom: 0.5rem;
        }
        p {
            color: #6b7280;
            margin: 0;
        }
        noscript {
            display: block;
            margin-top: 1rem;
        }
        .btn {
            display: inline-block;
            padding: 0.75rem 1.5rem;
            background: #3b82f6;
            color: white;
            text-decoration: none;
            border-radius: 0.5rem;
            border: none;
            cursor: pointer;
            font-size: 1rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="spinner"></div>
        <h1>Перенаправлення на LiqPay</h1>
        <p>Зачекайте, будь ласка...</p>

        <form id="liqpay-form" method="POST" action="{{ $checkoutUrl }}" style="display: none;">
            <input type="hidden" name="data" value="{{ $formData['data'] }}">
            <input type="hidden" name="signature" value="{{ $formData['signature'] }}">
        </form>

        <noscript>
            <p>JavaScript вимкнено. Натисніть кнопку для продовження:</p>
            <form method="POST" action="{{ $checkoutUrl }}">
                <input type="hidden" name="data" value="{{ $formData['data'] }}">
                <input type="hidden" name="signature" value="{{ $formData['signature'] }}">
                <button type="submit" class="btn">Перейти до оплати</button>
            </form>
        </noscript>
    </div>

    <script>
        document.getElementById('liqpay-form').submit();
    </script>
</body>
</html>

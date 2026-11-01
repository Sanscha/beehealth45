<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In | SeatSync</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-base: #f8fafc;
            --bg-card: #ffffff;
            --primary: #2563eb;
            --primary-hover: #1d4ed8;
            --text-primary: #0f172a;
            --text-secondary: #475569;
            --text-muted: #64748b;
            --border: #e2e8f0;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            background-color: var(--bg-base);
            color: var(--text-primary);
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .login-card {
            width: 100%;
            max-width: 400px;
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 36px 32px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -2px rgba(0, 0, 0, 0.05);
        }
        .brand-header {
            text-align: center;
            margin-bottom: 28px;
        }
        .brand-badge {
            width: 44px;
            height: 44px;
            margin: 0 auto 12px;
            background: var(--primary);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            font-weight: 600;
            color: white;
        }
        .brand-header h1 {
            font-size: 20px;
            font-weight: 600;
            color: var(--text-primary);
            letter-spacing: -0.3px;
        }
        .brand-header p {
            color: var(--text-muted);
            font-size: 13px;
            margin-top: 2px;
        }
        .alert {
            padding: 10px 14px;
            border-radius: 6px;
            font-size: 13px;
            margin-bottom: 18px;
            background: #fef2f2;
            color: #b91c1c;
            border: 1px solid #fecaca;
        }
        .form-group {
            margin-bottom: 16px;
        }
        .form-group label {
            display: block;
            font-size: 12.5px;
            font-weight: 500;
            margin-bottom: 6px;
            color: var(--text-secondary);
        }
        .form-control {
            width: 100%;
            padding: 9px 12px;
            background: #ffffff;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            color: var(--text-primary);
            font-size: 13.5px;
            outline: none;
            font-family: inherit;
            transition: all 0.15s;
        }
        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }
        .btn-submit {
            width: 100%;
            padding: 10px;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: background 0.15s;
            margin-top: 6px;
            font-family: inherit;
        }
        .btn-submit:hover {
            background: var(--primary-hover);
        }
        .demo-credentials {
            margin-top: 24px;
            padding: 14px;
            border-radius: 8px;
            background: #f8fafc;
            border: 1px solid var(--border);
            font-size: 12px;
            color: var(--text-secondary);
        }
        .demo-credentials strong {
            color: var(--text-primary);
            display: block;
            margin-bottom: 8px;
            font-size: 12px;
            font-weight: 600;
        }
        .demo-code {
            display: flex;
            justify-content: space-between;
            font-family: monospace;
            padding: 6px 8px;
            border-radius: 4px;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            cursor: pointer;
            transition: all 0.15s;
        }
        .demo-code:hover {
            border-color: var(--primary);
            background: #eff6ff;
        }
    </style>
</head>
<body>

    <div class="login-card">
        <div class="brand-header">
            <div class="brand-badge">S</div>
            <h1>SeatSync Admin</h1>
            <p>Staff &amp; Administrator Portal</p>
        </div>

        @if(session('error'))
            <div class="alert">
                {{ session('error') }}
            </div>
        @endif

        <form action="{{ route('admin.login') }}" method="POST">
            @csrf
            <div class="form-group">
                <label for="email">Email</label>
                <input type="text" id="email" name="email" class="form-control" placeholder="admin@library.com" value="{{ old('email', 'admin@library.com') }}" required autofocus>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" class="form-control" placeholder="••••••••" value="admin123" required>
            </div>

            <button type="submit" class="btn-submit">Sign In &rarr;</button>
        </form>

        <div class="demo-credentials">
            <strong>Default Credentials (click to fill):</strong>
            <div class="demo-code" onclick="fillCredentials('admin@library.com', 'admin123')">
                <span style="color:#2563eb; font-weight:500;">Admin: admin@library.com</span>
                <span>admin123</span>
            </div>
            <div class="demo-code" style="margin-top:6px;" onclick="fillCredentials('staff@library.com', 'staff123')">
                <span style="color:#475569; font-weight:500;">Staff: staff@library.com</span>
                <span>staff123</span>
            </div>
        </div>
    </div>

    <script>
        function fillCredentials(email, pass) {
            document.getElementById('email').value = email;
            document.getElementById('password').value = pass;
        }
    </script>

</body>
</html>

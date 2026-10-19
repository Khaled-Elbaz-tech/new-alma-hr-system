<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - ALMA HR System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --alma: #8B251E;
            --alma-dark: #5F1812;
            --alma-darker: #47100B;
            --alma-hover: #701C16;
            --slate: #64748B;
            --gray-500: #6B7280;
            --gray-300: #D1D5DB;
        }
        * { box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            min-height: 100vh;
            display: flex;
        }
        /* --- Brand panel --- */
        .login-brand {
            flex: 0 0 38%;
            background: linear-gradient(150deg, var(--alma-darker) 0%, var(--alma-dark) 55%, var(--alma) 100%);
            color: #fff;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 48px 40px;
            position: relative;
            overflow: hidden;
        }
        .login-brand::before {
            content: "";
            position: absolute;
            inset: 0;
            background:
                radial-gradient(circle at 85% 15%, rgba(255,255,255,.12), transparent 45%),
                radial-gradient(circle at 10% 90%, rgba(255,255,255,.07), transparent 40%);
        }
        .login-brand .logo-pill {
            background: #fff;
            border-radius: 14px;
            padding: 10px 18px;
            box-shadow: 0 18px 45px rgba(0,0,0,.35);
            position: relative; z-index: 1;
            display: inline-block;
            align-self: flex-start;
            max-width: 100%;
        }
        .login-brand .logo-pill img { display: block; }
        .login-brand h1 { font-size: 34px; margin: 26px 0 6px; position: relative; z-index: 1; font-weight: 800; }
        .login-brand .tagline { color: #E2E8F0; letter-spacing: 5px; font-size: 12px; font-weight: 600; position: relative; z-index: 1; }
        .login-brand p.desc { margin-top: 22px; line-height: 1.9; color: #F1F5F9; max-width: 460px; position: relative; z-index: 1; font-size: 15px; }
        .login-brand ul { margin-top: 26px; list-style: none; padding: 0; position: relative; z-index: 1; }
        .login-brand li { display: flex; align-items: center; gap: 12px; margin: 13px 0; color: #F8FAFC; font-size: 14.5px; }
        .login-brand li .dot {
            width: 30px; height: 30px; flex: 0 0 30px;
            border-radius: 50%;
            background: rgba(255,255,255,.14);
            border: 1px solid rgba(255,255,255,.5);
            display: flex; align-items: center; justify-content: center;
            color: #fff; font-size: 14px; font-weight: 700;
        }
        /* --- Form side --- */
        .login-form-side {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #F8FAFC;
            padding: 40px 24px;
        }
        .login-card {
            background: #fff;
            width: 100%;
            max-width: 400px;
            border-radius: 20px;
            padding: 40px 38px;
            box-shadow: 0 24px 60px rgba(66,16,13,.12);
            border-top: 5px solid var(--alma);
        }
        .login-card h2 { font-size: 22px; margin-bottom: 6px; font-weight: 800; color: #1F2937; }
        .login-card .sub { color: var(--gray-500); font-size: 13.5px; margin-bottom: 26px; }
        .form-label { font-weight: 600; font-size: 13.5px; color: #374151; margin-bottom: 6px; display: block; }
        .form-control {
            border-radius: 10px;
            padding: 12px 14px;
            border: 1px solid var(--gray-300);
            font-size: 14px;
            width: 100%;
        }
        .form-control:focus {
            border-color: var(--alma);
            box-shadow: 0 0 0 3px rgba(139,37,30,.12);
            outline: none;
        }
        .btn-login {
            background: var(--alma);
            color: #fff;
            border: none;
            padding: 13px;
            width: 100%;
            border-radius: 10px;
            font-weight: 700;
            font-size: 15px;
            cursor: pointer;
            transition: background .15s ease;
            margin-top: 8px;
        }
        .btn-login:hover { background: var(--alma-hover); color: #fff; }
        .login-foot {
            margin-top: 22px;
            text-align: center;
            font-size: 12px;
            color: var(--gray-500);
        }
        .alert { border-radius: 10px; font-size: 13.5px; }
        @media (max-width: 860px) {
            body { flex-direction: column; }
            .login-brand { padding: 40px 28px; }
            .login-brand h1 { font-size: 26px; }
            .login-brand ul { display: none; }
        }
    </style>
</head>
<body>
    <div class="login-brand">
        <div class="logo-pill">
            @include('partials.alma-logo', ['height' => 58])
        </div>
        <h1>HR Management System</h1>
        <div class="tagline">MORE THAN A JOURNEY</div>
        <p class="desc">Alma's platform for leave and employee management — automatic balance calculation, multi-stage approvals, and full reporting, all in your company's identity.</p>
        <ul>
            <li><span class="dot">✓</span> Automatic balance from hire date (1.75 days / month)</li>
            <li><span class="dot">✓</span> Approval flow: HR → Manager → Final</li>
            <li><span class="dot">✓</span> Automatic yearly carry-forward</li>
            <li><span class="dot">✓</span> Balance & leave reports with CSV export</li>
        </ul>
    </div>
    <div class="login-form-side">
        <div class="login-card">
            <h2>Sign In</h2>
            <div class="sub">Welcome to Alma HR</div>
            @if($errors->any())
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle me-2"></i>{{ $errors->first() }}
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                </div>
            @endif
            <form method="POST" action="{{ route('login') }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Email Address</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email') }}" required autofocus placeholder="you@company.com">
                </div>
                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" required placeholder="••••••••">
                </div>
                <button type="submit" class="btn-login">
                    <i class="fas fa-sign-in-alt me-2"></i>Sign In
                </button>
            </form>
            <div class="login-foot">
                <i class="fas fa-lock me-1"></i> Protected — max 5 login attempts per minute
            </div>
        </div>
    </div>
</body>
</html>

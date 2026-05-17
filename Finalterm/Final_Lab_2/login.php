<?php
session_start();
require_once 'config/db.php';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

$error     = '';
$email_val = '';

// Pre-fill email from cookie if available
if (isset($_COOKIE['remembered_email'])) {
    $email_val = htmlspecialchars($_COOKIE['remembered_email']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);

    $email_val = htmlspecialchars($email);

    if (empty($email) || empty($password)) {
        $error = 'Please enter your email and password.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        $conn = getDBConnection();
        $stmt = $conn->prepare('SELECT id, name, email, password FROM users WHERE email = ?');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            if (password_verify($password, $user['password'])) {
                // Regenerate session ID to prevent fixation
                session_regenerate_id(true);

                $_SESSION['user_id']   = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email']= $user['email'];

                // Set "remembered email" cookie (30 days)
                if ($remember) {
                    setcookie('remembered_email', $email, time() + (30 * 24 * 3600), '/', '', false, true);
                } else {
                    // Clear cookie if not remembering
                    setcookie('remembered_email', '', time() - 3600, '/');
                }

                // Set last login time cookie (365 days, readable)
                $lastLogin = date('Y-m-d H:i:s');
                setcookie('last_login', $lastLogin, time() + (365 * 24 * 3600), '/', '', false, true);

                header('Location: dashboard.php');
                exit;
            } else {
                $error = 'Incorrect email or password. Please try again.';
            }
        } else {
            // Use same generic message to prevent email enumeration
            $error = 'Incorrect email or password. Please try again.';
        }

        $stmt->close();
        $conn->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In — Luminary</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,600;1,300&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --ink: #0f0e0d;
            --paper: #f7f4ef;
            --cream: #ede8df;
            --gold: #b8962e;
            --gold-light: #d4af5a;
            --muted: #7a7268;
            --error: #c0392b;
            --border: #d5cfc5;
        }

        body {
            font-family: 'DM Sans', sans-serif;
            background-color: var(--paper);
            color: var(--ink);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
        }

        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background:
                radial-gradient(ellipse 80% 60% at 90% 90%, rgba(184,150,46,0.07) 0%, transparent 60%),
                radial-gradient(ellipse 60% 80% at 10% 10%, rgba(184,150,46,0.05) 0%, transparent 60%);
            pointer-events: none;
        }

        .container {
            width: 100%;
            max-width: 420px;
            animation: fadeUp 0.6s ease both;
        }

        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(24px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .brand {
            text-align: center;
            margin-bottom: 2.5rem;
        }

        .brand-mark {
            width: 48px;
            height: 48px;
            margin: 0 auto 1rem;
            border: 1.5px solid var(--gold);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .brand h1 {
            font-family: 'Cormorant Garamond', serif;
            font-size: 2rem;
            font-weight: 300;
            letter-spacing: 0.12em;
            text-transform: uppercase;
        }

        .brand p { font-size: 0.8rem; color: var(--muted); letter-spacing: 0.06em; margin-top: 0.25rem; }

        .card {
            background: #fff;
            border: 1px solid var(--border);
            border-radius: 2px;
            padding: 2.5rem;
            box-shadow: 0 2px 40px rgba(15,14,13,0.06);
        }

        .card-title { font-family: 'Cormorant Garamond', serif; font-size: 1.4rem; font-weight: 400; margin-bottom: 0.25rem; }
        .card-sub { font-size: 0.8rem; color: var(--muted); margin-bottom: 2rem; }

        .cookie-notice {
            background: linear-gradient(135deg, #fffbf0, #fef9ec);
            border: 1px solid #e8d99a;
            border-radius: 2px;
            padding: 0.75rem 1rem;
            font-size: 0.78rem;
            color: #7a6520;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .alert {
            padding: 0.85rem 1rem;
            border-radius: 2px;
            font-size: 0.82rem;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: flex-start;
            gap: 0.6rem;
            line-height: 1.5;
        }

        .alert-error { background: #fdf2f2; border: 1px solid #f5c6c6; color: var(--error); }

        .field { margin-bottom: 1.25rem; }

        .field label {
            display: block;
            font-size: 0.72rem;
            font-weight: 500;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            color: var(--muted);
            margin-bottom: 0.5rem;
        }

        .input-wrap { position: relative; }

        .field input[type="email"],
        .field input[type="password"],
        .field input[type="text"] {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid var(--border);
            border-radius: 2px;
            font-family: 'DM Sans', sans-serif;
            font-size: 0.9rem;
            color: var(--ink);
            background: var(--paper);
            transition: border-color 0.2s, box-shadow 0.2s;
            outline: none;
        }

        .field input:focus {
            border-color: var(--gold);
            box-shadow: 0 0 0 3px rgba(184,150,46,0.1);
            background: #fff;
        }

        .show-pw {
            position: absolute;
            right: 0.75rem;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            color: var(--muted);
            padding: 0.25rem;
            display: flex;
            align-items: center;
        }

        .show-pw:hover { color: var(--gold); }

        .remember-row {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 1.5rem;
        }

        .remember-row input[type="checkbox"] {
            accent-color: var(--gold);
            width: 15px;
            height: 15px;
            cursor: pointer;
        }

        .remember-row label {
            font-size: 0.82rem;
            color: var(--muted);
            cursor: pointer;
        }

        .btn {
            width: 100%;
            padding: 0.85rem;
            background: var(--ink);
            color: #fff;
            border: none;
            border-radius: 2px;
            font-family: 'DM Sans', sans-serif;
            font-size: 0.8rem;
            font-weight: 500;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            cursor: pointer;
            transition: background 0.2s, transform 0.1s;
        }

        .btn:hover { background: var(--gold); }
        .btn:active { transform: scale(0.99); }

        .divider {
            text-align: center;
            font-size: 0.75rem;
            color: var(--muted);
            margin: 1.5rem 0;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .divider::before, .divider::after { content: ''; flex: 1; height: 1px; background: var(--border); }

        .link-row { text-align: center; font-size: 0.82rem; color: var(--muted); }
        .link-row a { color: var(--gold); text-decoration: none; font-weight: 500; }
        .link-row a:hover { text-decoration: underline; }
    </style>
</head>
<body>
<div class="container">
    <div class="brand">
        <div class="brand-mark">
            <svg viewBox="0 0 24 24" fill="none" stroke="#b8962e" stroke-width="1.5" width="22" height="22">
                <path d="M12 2L15.09 8.26L22 9.27L17 14.14L18.18 21.02L12 17.77L5.82 21.02L7 14.14L2 9.27L8.91 8.26L12 2Z"/>
            </svg>
        </div>
        <h1>Luminary</h1>
        <p>Secure Authentication System</p>
    </div>

    <div class="card">
        <div class="card-title">Welcome back</div>
        <div class="card-sub">Sign in to your account to continue.</div>

        <?php if (!empty($email_val) && isset($_COOKIE['remembered_email'])): ?>
        <div class="cookie-notice">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 8v4l3 3"/></svg>
            Email restored from your saved cookie.
        </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-error">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="flex-shrink:0;margin-top:1px"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                <?= $error ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="login.php" novalidate>
            <div class="field">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" value="<?= $email_val ?>" placeholder="jane@example.com" autocomplete="email" required>
            </div>
            <div class="field">
                <label for="password">Password</label>
                <div class="input-wrap">
                    <input type="password" id="password" name="password" placeholder="Your password" autocomplete="current-password" required>
                    <button type="button" class="show-pw" id="togglePw" aria-label="Show password">
                        <svg id="eyeIcon" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
                        </svg>
                    </button>
                </div>
            </div>

            <div class="remember-row">
                <input type="checkbox" id="remember" name="remember" <?= isset($_COOKIE['remembered_email']) ? 'checked' : '' ?>>
                <label for="remember">Remember my email for next time</label>
            </div>

            <button type="submit" class="btn">Sign In</button>
        </form>

        <div class="divider">or</div>
        <div class="link-row">Don't have an account? <a href="register.php">Create one</a></div>
    </div>
</div>

<script>
const toggleBtn = document.getElementById('togglePw');
const pwField   = document.getElementById('password');
toggleBtn.addEventListener('click', () => {
    const isHidden = pwField.type === 'password';
    pwField.type = isHidden ? 'text' : 'password';
    document.getElementById('eyeIcon').innerHTML = isHidden
        ? '<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/><line x1="1" y1="1" x2="23" y2="23"/>'
        : '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>';
});
</script>
</body>
</html>

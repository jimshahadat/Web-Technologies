<?php
session_start();
require_once 'config/db.php';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
$success = '';
$name_val = '';
$email_val = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim($_POST['name'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';

    $name_val  = htmlspecialchars($name);
    $email_val = htmlspecialchars($email);

    // Validation
    if (empty($name) || empty($email) || empty($password) || empty($confirm)) {
        $error = 'All fields are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters long.';
    } elseif (!preg_match('/[A-Z]/', $password) || !preg_match('/[0-9]/', $password)) {
        $error = 'Password must contain at least one uppercase letter and one number.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        $conn = getDBConnection();

        // Check if email already exists
        $stmt = $conn->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $error = 'An account with this email already exists.';
        } else {
            // Hash password and insert user
            $hashed = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
            $stmt->close();

            $stmt = $conn->prepare('INSERT INTO users (name, email, password) VALUES (?, ?, ?)');
            $stmt->bind_param('sss', $name, $email, $hashed);

            if ($stmt->execute()) {
                $success = 'Account created successfully! You can now log in.';
                $name_val  = '';
                $email_val = '';
            } else {
                $error = 'Registration failed. Please try again.';
            }
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
    <title>Create Account — Luminary</title>
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
            --success: #2d6a4f;
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
                radial-gradient(ellipse 80% 60% at 10% 90%, rgba(184,150,46,0.08) 0%, transparent 60%),
                radial-gradient(ellipse 60% 80% at 90% 10%, rgba(184,150,46,0.06) 0%, transparent 60%);
            pointer-events: none;
        }

        .container {
            width: 100%;
            max-width: 480px;
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

        .brand-mark svg { width: 22px; height: 22px; }

        .brand h1 {
            font-family: 'Cormorant Garamond', serif;
            font-size: 2rem;
            font-weight: 300;
            letter-spacing: 0.12em;
            color: var(--ink);
            text-transform: uppercase;
        }

        .brand p {
            font-size: 0.8rem;
            color: var(--muted);
            letter-spacing: 0.06em;
            margin-top: 0.25rem;
        }

        .card {
            background: #fff;
            border: 1px solid var(--border);
            border-radius: 2px;
            padding: 2.5rem 2.5rem;
            box-shadow: 0 2px 40px rgba(15,14,13,0.06);
        }

        .card-title {
            font-family: 'Cormorant Garamond', serif;
            font-size: 1.4rem;
            font-weight: 400;
            margin-bottom: 0.25rem;
        }

        .card-sub {
            font-size: 0.8rem;
            color: var(--muted);
            margin-bottom: 2rem;
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

        .alert-error {
            background: #fdf2f2;
            border: 1px solid #f5c6c6;
            color: var(--error);
        }

        .alert-success {
            background: #f0f8f4;
            border: 1px solid #a8d5bb;
            color: var(--success);
        }

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

        .field input {
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

        .password-hint {
            font-size: 0.72rem;
            color: var(--muted);
            margin-top: 0.4rem;
        }

        .strength-bar {
            height: 3px;
            background: var(--cream);
            border-radius: 2px;
            margin-top: 0.5rem;
            overflow: hidden;
        }

        .strength-fill {
            height: 100%;
            border-radius: 2px;
            transition: width 0.3s, background 0.3s;
            width: 0%;
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
            margin-top: 0.75rem;
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

        .divider::before, .divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: var(--border);
        }

        .link-row {
            text-align: center;
            font-size: 0.82rem;
            color: var(--muted);
        }

        .link-row a {
            color: var(--gold);
            text-decoration: none;
            font-weight: 500;
        }

        .link-row a:hover { text-decoration: underline; }
    </style>
</head>
<body>
<div class="container">
    <div class="brand">
        <div class="brand-mark">
            <svg viewBox="0 0 24 24" fill="none" stroke="#b8962e" stroke-width="1.5">
                <path d="M12 2L15.09 8.26L22 9.27L17 14.14L18.18 21.02L12 17.77L5.82 21.02L7 14.14L2 9.27L8.91 8.26L12 2Z"/>
            </svg>
        </div>
        <h1>Luminary</h1>
        <p>Secure Authentication System</p>
    </div>

    <div class="card">
        <div class="card-title">Create your account</div>
        <div class="card-sub">Join us — it only takes a moment.</div>

        <?php if ($error): ?>
            <div class="alert alert-error">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="flex-shrink:0;margin-top:1px"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                <?= $error ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="flex-shrink:0;margin-top:1px"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                <?= $success ?> <a href="login.php" style="color:var(--success);font-weight:600;">Sign in →</a>
            </div>
        <?php endif; ?>

        <form method="POST" action="register.php" novalidate>
            <div class="field">
                <label for="name">Full Name</label>
                <input type="text" id="name" name="name" value="<?= $name_val ?>" placeholder="Jane Doe" autocomplete="name" required>
            </div>
            <div class="field">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" value="<?= $email_val ?>" placeholder="jane@example.com" autocomplete="email" required>
            </div>
            <div class="field">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Min. 8 chars, 1 uppercase, 1 number" autocomplete="new-password" required>
                <div class="strength-bar"><div class="strength-fill" id="strengthFill"></div></div>
                <div class="password-hint" id="strengthLabel">Enter a password to see its strength</div>
            </div>
            <div class="field">
                <label for="confirm_password">Confirm Password</label>
                <input type="password" id="confirm_password" name="confirm_password" placeholder="Re-enter your password" autocomplete="new-password" required>
            </div>
            <button type="submit" class="btn">Create Account</button>
        </form>

        <div class="divider">or</div>
        <div class="link-row">Already have an account? <a href="login.php">Sign in</a></div>
    </div>
</div>

<script>
const pwInput = document.getElementById('password');
const fill    = document.getElementById('strengthFill');
const label   = document.getElementById('strengthLabel');

pwInput.addEventListener('input', () => {
    const v = pwInput.value;
    let score = 0;
    if (v.length >= 8)  score++;
    if (v.length >= 12) score++;
    if (/[A-Z]/.test(v)) score++;
    if (/[0-9]/.test(v)) score++;
    if (/[^A-Za-z0-9]/.test(v)) score++;

    const levels = [
        { pct: '0%',   color: 'transparent', text: 'Enter a password to see its strength' },
        { pct: '25%',  color: '#e74c3c',      text: 'Weak' },
        { pct: '50%',  color: '#e67e22',      text: 'Fair' },
        { pct: '75%',  color: '#f1c40f',      text: 'Good' },
        { pct: '90%',  color: '#27ae60',      text: 'Strong' },
        { pct: '100%', color: '#1e8449',      text: 'Very strong' },
    ];
    const l = levels[Math.min(score, 5)];
    fill.style.width    = l.pct;
    fill.style.background = l.color;
    label.textContent   = l.text;
});
</script>
</body>
</html>

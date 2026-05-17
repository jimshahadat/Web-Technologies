<?php
session_start();

// Protect this page — redirect to login if no session
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_name  = htmlspecialchars($_SESSION['user_name']);
$user_email = htmlspecialchars($_SESSION['user_email']);

// Read last login cookie
$last_login = '';
if (isset($_COOKIE['last_login'])) {
    $ts = strtotime($_COOKIE['last_login']);
    if ($ts) {
        $last_login = date('F j, Y \a\t g:i A', $ts);
    }
}

// Greeting based on time of day (UTC)
$hour = (int) gmdate('G');
if ($hour < 12)      $greeting = 'Good morning';
elseif ($hour < 17)  $greeting = 'Good afternoon';
else                 $greeting = 'Good evening';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard — Luminary</title>
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
            --border: #d5cfc5;
            --card-bg: #ffffff;
        }

        body {
            font-family: 'DM Sans', sans-serif;
            background-color: var(--paper);
            color: var(--ink);
            min-height: 100vh;
        }

        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background:
                radial-gradient(ellipse 70% 50% at 0% 100%, rgba(184,150,46,0.07) 0%, transparent 60%),
                radial-gradient(ellipse 50% 60% at 100% 0%, rgba(184,150,46,0.05) 0%, transparent 60%);
            pointer-events: none;
            z-index: 0;
        }

        /* ── NAV ── */
        nav {
            position: sticky;
            top: 0;
            z-index: 100;
            background: rgba(247,244,239,0.92);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid var(--border);
            padding: 0 2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            height: 64px;
        }

        .nav-brand {
            display: flex;
            align-items: center;
            gap: 0.6rem;
            text-decoration: none;
            color: var(--ink);
        }

        .nav-brand-icon {
            width: 32px;
            height: 32px;
            border: 1px solid var(--gold);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .nav-brand span {
            font-family: 'Cormorant Garamond', serif;
            font-size: 1.2rem;
            font-weight: 300;
            letter-spacing: 0.12em;
            text-transform: uppercase;
        }

        .nav-right {
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }

        .nav-email {
            font-size: 0.78rem;
            color: var(--muted);
            display: none;
        }

        @media (min-width: 600px) { .nav-email { display: block; } }

        .avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: var(--ink);
            color: #fff;
            font-size: 0.85rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            justify-content: center;
            letter-spacing: 0.05em;
        }

        .logout-btn {
            display: flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.45rem 1rem;
            border: 1px solid var(--border);
            border-radius: 2px;
            background: transparent;
            font-family: 'DM Sans', sans-serif;
            font-size: 0.75rem;
            font-weight: 500;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: var(--muted);
            cursor: pointer;
            text-decoration: none;
            transition: border-color 0.2s, color 0.2s, background 0.2s;
        }

        .logout-btn:hover {
            border-color: #c0392b;
            color: #c0392b;
            background: #fdf2f2;
        }

        /* ── MAIN ── */
        main {
            position: relative;
            z-index: 1;
            max-width: 960px;
            margin: 0 auto;
            padding: 3rem 1.5rem 4rem;
        }

        /* ── HERO ── */
        .hero {
            margin-bottom: 3rem;
            animation: fadeUp 0.6s ease both;
        }

        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(20px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .hero-eyebrow {
            font-size: 0.72rem;
            letter-spacing: 0.2em;
            text-transform: uppercase;
            color: var(--gold);
            font-weight: 500;
            margin-bottom: 0.75rem;
        }

        .hero h2 {
            font-family: 'Cormorant Garamond', serif;
            font-size: clamp(2rem, 5vw, 3rem);
            font-weight: 300;
            line-height: 1.15;
            margin-bottom: 0.75rem;
        }

        .hero h2 em {
            font-style: italic;
            color: var(--gold);
        }

        .hero-sub {
            font-size: 0.88rem;
            color: var(--muted);
            line-height: 1.7;
            max-width: 480px;
        }

        /* ── GRID ── */
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 1.25rem;
            margin-bottom: 1.5rem;
        }

        .card {
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: 2px;
            padding: 1.75rem;
            box-shadow: 0 1px 20px rgba(15,14,13,0.04);
            animation: fadeUp 0.6s ease both;
        }

        .card:nth-child(2) { animation-delay: 0.07s; }
        .card:nth-child(3) { animation-delay: 0.14s; }
        .card:nth-child(4) { animation-delay: 0.21s; }

        .card-icon {
            width: 40px;
            height: 40px;
            border: 1px solid var(--border);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1rem;
            background: var(--paper);
        }

        .card-label {
            font-size: 0.7rem;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: var(--muted);
            margin-bottom: 0.4rem;
        }

        .card-value {
            font-family: 'Cormorant Garamond', serif;
            font-size: 1.6rem;
            font-weight: 400;
            color: var(--ink);
        }

        .card-value.small { font-size: 1rem; font-family: 'DM Sans', sans-serif; font-weight: 400; word-break: break-all; }

        .card-desc {
            font-size: 0.78rem;
            color: var(--muted);
            margin-top: 0.4rem;
            line-height: 1.5;
        }

        /* ── SESSION INFO BLOCK ── */
        .session-block {
            background: linear-gradient(135deg, #0f0e0d 0%, #2a2724 100%);
            border-radius: 2px;
            padding: 2rem 2rem;
            color: #fff;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            animation: fadeUp 0.6s ease both;
            animation-delay: 0.28s;
        }

        .session-block .label {
            font-size: 0.7rem;
            letter-spacing: 0.15em;
            text-transform: uppercase;
            color: rgba(255,255,255,0.5);
            margin-bottom: 0.3rem;
        }

        .session-block .val {
            font-family: 'Cormorant Garamond', serif;
            font-size: 1.2rem;
            color: var(--gold-light);
        }

        .session-meta { display: flex; flex-wrap: wrap; gap: 2rem; }

        .badge {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.3rem 0.75rem;
            background: rgba(184,150,46,0.15);
            border: 1px solid rgba(184,150,46,0.35);
            border-radius: 999px;
            font-size: 0.72rem;
            color: var(--gold-light);
            letter-spacing: 0.06em;
        }

        .badge::before {
            content: '';
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: #4ade80;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50%       { opacity: 0.4; }
        }
    </style>
</head>
<body>

<nav>
    <a class="nav-brand" href="dashboard.php">
        <div class="nav-brand-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="#b8962e" stroke-width="1.5" width="16" height="16">
                <path d="M12 2L15.09 8.26L22 9.27L17 14.14L18.18 21.02L12 17.77L5.82 21.02L7 14.14L2 9.27L8.91 8.26L12 2Z"/>
            </svg>
        </div>
        <span>Luminary</span>
    </a>
    <div class="nav-right">
        <span class="nav-email"><?= $user_email ?></span>
        <div class="avatar"><?= strtoupper(substr($_SESSION['user_name'], 0, 2)) ?></div>
        <a href="logout.php" class="logout-btn">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/>
            </svg>
            Sign Out
        </a>
    </div>
</nav>

<main>
    <div class="hero">
        <div class="hero-eyebrow">Dashboard</div>
        <h2><?= $greeting ?>, <em><?= $user_name ?></em>.</h2>
        <p class="hero-sub">You are securely signed in. Your session is active and all your data is protected.</p>
    </div>

    <div class="grid">
        <!-- Account Info -->
        <div class="card">
            <div class="card-icon">
                <svg width="18" height="18" fill="none" stroke="var(--gold)" stroke-width="1.5" viewBox="0 0 24 24">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>
                </svg>
            </div>
            <div class="card-label">Signed in as</div>
            <div class="card-value"><?= $user_name ?></div>
            <div class="card-desc small"><?= $user_email ?></div>
        </div>

        <!-- Session Status -->
        <div class="card">
            <div class="card-icon">
                <svg width="18" height="18" fill="none" stroke="var(--gold)" stroke-width="1.5" viewBox="0 0 24 24">
                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                </svg>
            </div>
            <div class="card-label">Session</div>
            <div class="card-value">Active</div>
            <div class="card-desc">PHP session is running securely. You will remain logged in until you sign out or close the browser.</div>
        </div>

        <!-- Last Login -->
        <div class="card">
            <div class="card-icon">
                <svg width="18" height="18" fill="none" stroke="var(--gold)" stroke-width="1.5" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
                </svg>
            </div>
            <div class="card-label">Previous Login</div>
            <?php if ($last_login): ?>
                <div class="card-value small"><?= htmlspecialchars($last_login) ?></div>
                <div class="card-desc">Retrieved from your browser cookie (1-year expiry).</div>
            <?php else: ?>
                <div class="card-value">First visit</div>
                <div class="card-desc">No previous login cookie was found on this device.</div>
            <?php endif; ?>
        </div>

        <!-- Cookie Info -->
        <div class="card">
            <div class="card-icon">
                <svg width="18" height="18" fill="none" stroke="var(--gold)" stroke-width="1.5" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
                </svg>
            </div>
            <div class="card-label">Cookies Active</div>
            <div class="card-value"><?= isset($_COOKIE['remembered_email']) ? '2' : '1' ?></div>
            <div class="card-desc">
                <?= isset($_COOKIE['remembered_email']) ? '<strong>last_login</strong> &amp; <strong>remembered_email</strong> cookies are set.' : '<strong>last_login</strong> cookie is set.' ?>
                These enhance your login experience.
            </div>
        </div>
    </div>

    <!-- Session block -->
    <div class="session-block">
        <div>
            <div class="label">Current session</div>
            <div class="val"><?= session_id() ?></div>
        </div>
        <div class="session-meta">
            <div>
                <div class="label">User ID</div>
                <div class="val">#<?= (int) $_SESSION['user_id'] ?></div>
            </div>
            <div>
                <div class="label">Status</div>
                <span class="badge">Authenticated</span>
            </div>
        </div>
    </div>
</main>

</body>
</html>

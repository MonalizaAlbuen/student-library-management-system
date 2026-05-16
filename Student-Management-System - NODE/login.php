<?php
session_start();

if (isset($_SESSION['user'])) {
    header('Location: ./index.php');
    exit;
}

$apiBase = "http://localhost:3000";
$error   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $error = 'Please enter both email and password.';
    } else {
        $payload = json_encode(['email' => $email, 'password' => $password]);

        $ch = curl_init("$apiBase/login");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST,           true);
        curl_setopt($ch, CURLOPT_POSTFIELDS,     $payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER,     ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_TIMEOUT,        5);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
        $response = curl_exec($ch);
        $curlErr  = curl_error($ch);
        curl_close($ch);

        if ($curlErr || $response === false) {
            $error = 'Cannot reach the authentication server. Please try again.';
        } else {
            $data = json_decode($response, true);

            if (isset($data['status']) && $data['status'] === 'success') {
                session_regenerate_id(true);
                $_SESSION['user'] = $data['data']['user']['email'];
                $_SESSION['role'] = $data['data']['user']['role'];
                header('Location: ./index.php');
                exit;
            } else {
                $error = $data['message'] ?? 'Invalid email or password. Please try again.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Library Management System — Admin Login</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Georgia:ital,wght@0,400;0,700;1,400&family=Segoe+UI:wght@400;600;700&display=swap" rel="stylesheet">
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    :root {
      --navy:        #0D1B3E;
      --gold:        #E8DEBB;
      --gold-muted:  #AAA078;
      --gold-dim:    #464631;
      --gold-accent: rgba(232,222,187,0.5);
      --wheat:       #F5DEB3;
      --input-bg:    #EBE8E0;
      --label-text:  #000000;
      --sub-text:    #000000;
      --danger:      #C00000;
      --danger-border: #C88C82;
      --btn-clear-bg:#FFFF00;
      --btn-clear-fg:#504637;
      --form-bg:     #F5F3EE;
    }

    html, body {
      height: 100%;
      font-family: 'Segoe UI', Tahoma, Geneva, sans-serif;
      background: var(--form-bg);
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .login-shell {
      width: 960px;
      height: 580px;
      display: flex;
      border: 1px solid #aaa;
      box-shadow: 0 8px 40px rgba(13,27,62,0.18);
      overflow: hidden;
    }

    /* ── LEFT PANEL ── */
    .panel-left {
      width: 350px;
      min-width: 350px;
      height: 580px;
      background: var(--navy);
      position: relative;
      display: flex;
      flex-direction: column;
      padding: 0;
    }

    .left-title {
      font-family: Georgia, 'Times New Roman', serif;
      font-size: 28px;
      font-weight: bold;
      color: var(--gold);
      position: absolute;
      top: 151px;
      left: 30px;
      width: 290px;
      line-height: 1.15;
    }

    .left-subtitle {
      font-family: 'Segoe UI', sans-serif;
      font-size: 13.8px;
      color: var(--gold-muted);
      position: absolute;
      top: 220px;
      left: 24px;
      width: 290px;
    }

    .left-divider {
      position: absolute;
      top: 280px;
      left: 30px;
      width: 56px;
      height: 2px;
      background: var(--gold-accent);
    }

    .left-quote {
      font-family: Georgia, 'Times New Roman', serif;
      font-size: 10.8px;
      font-style: italic;
      color: var(--gold-dim);
      position: absolute;
      top: 298px;
      left: 30px;
      width: 290px;
      line-height: 1.6;
    }

    .left-version {
      font-family: 'Segoe UI', sans-serif;
      font-size: 7.5px;
      color: #46432D;
      position: absolute;
      top: 546px;
      left: 30px;
      width: 290px;
    }

    /* ── RIGHT PANEL ── */
    .panel-right {
      flex: 1;
      height: 580px;
      background: var(--wheat);
      position: relative;
    }

    .right-welcome {
      font-family: Georgia, 'Times New Roman', serif;
      font-size: 22px;
      font-weight: bold;
      color: var(--navy);
      position: absolute;
      top: 64px;
      left: 65px;
      width: 480px;
    }

    .right-welcome-sub {
      font-family: 'Segoe UI', sans-serif;
      font-size: 10px;
      color: var(--sub-text);
      position: absolute;
      top: 130px;
      left: 65px;
      width: 480px;
    }

    .admin-badge {
      position: absolute;
      top: 165px;
      left: 65px;
      width: 80px;
      height: 24px;
      background: var(--navy);
      display: flex;
      align-items: center;
      justify-content: center;
    }
    .admin-badge span {
      font-family: 'Segoe UI', sans-serif;
      font-size: 7.5px;
      font-weight: bold;
      color: var(--gold);
      letter-spacing: 0.8px;
    }

    .field-label {
      font-family: 'Segoe UI', sans-serif;
      font-size: 10px;
      font-weight: bold;
      color: var(--label-text);
      position: absolute;
      width: 480px;
      left: 65px;
    }
    .label-username { top: 205px; }
    .label-password  { top: 285px; }

    .field-username {
      position: absolute;
      top: 224px;
      left: 65px;
      width: 480px;
      height: 42px;
    }

    .field-password-wrap {
      position: absolute;
      top: 304px;
      left: 65px;
      width: 480px;
      height: 42px;
      display: flex;
    }
    .field-password {
      flex: 1;
      width: 438px;
    }

    .field-username,
    .field-password {
      background: var(--input-bg);
      border: 1px solid #ccc;
      font-family: 'Segoe UI', sans-serif;
      font-size: 13px;
      color: var(--navy);
      padding: 0 14px;
      outline: none;
      transition: border-color 0.2s;
    }
    .field-username:focus,
    .field-password:focus {
      border-color: var(--navy);
    }

    .btn-show-pw {
      width: 42px;
      height: 42px;
      background: var(--input-bg);
      border: 1px solid #ccc;
      border-left: none;
      cursor: pointer;
      font-size: 18px;
      display: flex;
      align-items: center;
      justify-content: center;
      user-select: none;
      transition: background 0.15s;
    }
    .btn-show-pw:hover { background: #ddd; }

    .lbl-forgot {
      font-family: 'Segoe UI', sans-serif;
      font-size: 11px;
      color: var(--sub-text);
      position: absolute;
      top: 358px;
      left: 65px;
      width: 480px;
    }

    .error-msg {
      font-family: 'Segoe UI', sans-serif;
      font-size: 11px;
      color: var(--danger);
      position: absolute;
      top: 380px;
      left: 65px;
      width: 480px;
    }

    .btn-signin {
      position: absolute;
      top: 408px;
      left: 65px;
      width: 210px;
      height: 56px;
      background: var(--navy);
      color: var(--gold);
      border: none;
      font-family: 'Segoe UI', sans-serif;
      font-size: 13px;
      font-weight: bold;
      cursor: pointer;
      letter-spacing: 0.5px;
      transition: background 0.2s, color 0.2s;
    }
    .btn-signin:hover { background: #162456; }

    .btn-clear {
      position: absolute;
      top: 408px;
      left: 290px;
      width: 130px;
      height: 56px;
      background: var(--btn-clear-bg);
      color: var(--btn-clear-fg);
      border: 1px solid #C8C3B4;
      font-family: 'Segoe UI', sans-serif;
      font-size: 13px;
      font-weight: bold;
      cursor: pointer;
      transition: background 0.2s;
    }
    .btn-clear:hover { background: #e6e600; }

    .btn-exit {
      position: absolute;
      top: 408px;
      left: 435px;
      width: 130px;
      height: 56px;
      background: var(--danger);
      color: #fff;
      border: 1px solid var(--danger-border);
      font-family: 'Segoe UI', sans-serif;
      font-size: 13px;
      font-weight: bold;
      cursor: pointer;
      transition: background 0.2s;
    }
    .btn-exit:hover { background: #a00000; }

    @media (max-width: 980px) {
      html, body { align-items: flex-start; padding: 20px; }
      .login-shell {
        width: 100%;
        height: auto;
        flex-direction: column;
      }
      .panel-left {
        width: 100%;
        min-width: unset;
        height: 180px;
      }
      .left-title    { top: 30px; font-size: 22px; }
      .left-subtitle { top: 68px; }
      .left-divider  { top: 100px; }
      .left-quote, .left-version { display: none; }
      .panel-right   { height: auto; min-height: 420px; }
    }
  </style>
</head>
<body>

<div class="login-shell">

  <!-- LEFT PANEL -->
  <div class="panel-left">
    <span class="left-title">Student</span>
    <span class="left-subtitle">Management System</span>
    <div class="left-divider"></div>
    <p class="left-quote">
      "An effective student management system helps schools organize student records,
       and manage information efficiently."
    </p>
    <span class="left-version">v2.0.0 &nbsp;·&nbsp; Admin Console</span>
  </div>

  <!-- RIGHT PANEL -->
  <div class="panel-right">

    <span class="right-welcome">Welcome back</span>
    <span class="right-welcome-sub">Sign in to access the admin dashboard.</span>

    <div class="admin-badge"><span>ADMIN</span></div>

    <form method="POST" action="">

      <!-- Email / Username -->
      <span class="field-label label-username">USERNAME OR EMAIL</span>
      <input
        type="text"
        name="username"
        class="field-username"
        value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
        autocomplete="username"
        tabindex="1"
      >

      <!-- Password -->
      <span class="field-label label-password">PASSWORD</span>
      <div class="field-password-wrap">
        <input
          type="password"
          name="password"
          id="passwordInput"
          class="field-password"
          autocomplete="current-password"
          tabindex="2"
        >
        <button
          type="button"
          class="btn-show-pw"
          id="btnShowPw"
          title="Show / hide password"
          tabindex="6"
        >👁</button>
      </div>

      <span class="lbl-forgot">Forgot password? Contact your system administrator.</span>

      <?php if ($error): ?>
        <span class="error-msg"><?= htmlspecialchars($error) ?></span>
      <?php endif; ?>

      <button type="submit" class="btn-signin" tabindex="3">Sign In</button>
      <button type="button" class="btn-clear"  tabindex="4" onclick="clearForm()">Clear</button>
      <button type="button" class="btn-exit"   tabindex="5" onclick="window.close()">Exit</button>

    </form>

  </div>

</div>

<script>
  const pwInput  = document.getElementById('passwordInput');
  const eyeBtn   = document.getElementById('btnShowPw');
  let   pwHidden = true;

  eyeBtn.addEventListener('click', function () {
    pwHidden = !pwHidden;
    pwInput.type       = pwHidden ? 'password' : 'text';
    eyeBtn.textContent = pwHidden ? '👁' : '🙈';
  });

  function clearForm() {
    document.querySelector('input[name="username"]').value = '';
    document.querySelector('input[name="password"]').value = '';
    pwHidden           = true;
    pwInput.type       = 'password';
    eyeBtn.textContent = '👁';
  }
</script>

</body>
</html>
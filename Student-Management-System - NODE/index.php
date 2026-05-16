<?php
session_start();

if (!isset($_SESSION['user'])) {
    header('Location: ./login.php');
    exit;
}

$apiBase = "http://localhost:3000";

function apiGet(string $url): ?array {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT,        5);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
    $response = curl_exec($ch);
    curl_close($ch);
    if (!$response) return null;
    return json_decode($response, true);
}

$studentsRes = apiGet("$apiBase/students-count");
$parentsRes  = apiGet("$apiBase/parents-count");

$totalStudents = $studentsRes['data']['total'] ?? 0;
$totalParents  = $parentsRes['data']['total']  ?? 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>Dashboard — Student Management System</title>
  <link rel="icon" href="../img/favicon2.png">
  <?php include_once 'header.php'; ?>
  <style>
    :root {
      --navy:        #0D1B3E;
      --navy-hover:  #162456;
      --gold:        #E8DEBB;
      --gold-muted:  #AAA078;
      --gold-dim:    #46432D;
      --gold-accent: rgba(232,222,187,0.12);
      --gold-border: rgba(232,222,187,0.22);
      --wheat-light: #FAF6ED;
      --page-bg:     #F5F3EE;
    }

    body { background: var(--page-bg) !important; font-family: 'Segoe UI', Tahoma, sans-serif; }

    /* ── Page header ── */
    .pm-page-header {
      background: var(--navy);
      padding: 18px 28px;
      margin-bottom: 24px;
      display: flex;
      align-items: center;
      justify-content: space-between;
    }
    .pm-page-header h1 {
      font-family: Georgia, serif;
      font-size: 28px; /* increased */
      font-weight: bold;
      color: var(--gold);
      margin: 0;
    }
    .pm-breadcrumb {
      font-size: 14px; /* increased */
      color: var(--gold-muted);
      letter-spacing: 0.6px;
      text-transform: uppercase;
      margin-bottom: 4px;
    }
    .pm-badge {
      background: var(--gold-accent);
      border: 1px solid var(--gold-border);
      color: var(--gold-muted);
      font-size: 12px; /* increased */
      font-weight: bold;
      letter-spacing: 0.8px;
      padding: 3px 10px;
      text-transform: uppercase;
    }

    /* ── Welcome panel ── */
    .dash-welcome {
      background: #fff;
      border: 1px solid #ddd;
      box-shadow: 0 2px 8px rgba(13,27,62,0.07);
      margin-bottom: 24px;
    }
    .dash-welcome-header {
      background: var(--navy);
      padding: 12px 18px;
      display: flex;
      align-items: center;
      justify-content: space-between;
    }
    .dash-welcome-header h2 {
      font-family: Georgia, serif;
      font-size: 20px; /* increased */
      font-weight: bold;
      color: var(--gold);
      margin: 0;
    }
    .dash-welcome-body {
      padding: 20px 18px;
      background: var(--wheat-light);
    }
    .dash-welcome-body p {
      font-size: 16px; /* increased */
      color: var(--gold-dim);
      margin: 0;
      line-height: 1.7;
    }
    .pm-accent-line {
      width: 40px;
      height: 2px;
      background: rgba(232,222,187,0.5);
      margin: 6px 0 14px;
    }

    /* ── Stat cards ── */
    .dash-card {
      background: #fff;
      border: 1px solid #ddd;
      box-shadow: 0 2px 8px rgba(13,27,62,0.07);
      margin-bottom: 20px;
      overflow: hidden;
    }
    .dash-card-header {
      background: var(--navy);
      padding: 10px 20px;
      display: flex;
      align-items: center;
      justify-content: space-between;
    }
    .dash-card-label {
      font-family: 'Segoe UI', sans-serif;
      font-size: 12px; /* increased */
      font-weight: bold;
      color: var(--gold-muted);
      letter-spacing: 1px;
      text-transform: uppercase;
    }
    .dash-card-header i {
      color: var(--gold-muted);
      font-size: 20px; /* increased */
    }
    .dash-card-body {
      background: var(--wheat-light);
      padding: 28px 24px;
      display: flex;
      align-items: center;
      gap: 20px;
    }
    .dash-card-icon {
      width: 64px;
      height: 64px;
      background: var(--navy);
      display: flex;
      align-items: center;
      justify-content: center;
      flex-shrink: 0;
    }
    .dash-card-icon i {
      font-size: 36px; /* increased */
      color: var(--gold-muted);
    }
    .dash-card-count {
      font-family: Georgia, serif;
      font-size: 64px; /* increased */
      font-weight: bold;
      color: var(--navy);
      line-height: 1;
    }
    .dash-card-sublabel {
      font-size: 14px; /* increased */
      font-weight: 600;
      color: var(--gold-dim);
      letter-spacing: 0.3px;
      margin-top: 6px;
    }
  </style>
</head>

<body class="nav-md">
<div class="container body">
<div class="main_container">

  <div class="col-md-3 left_col">
    <?php include_once 'sidebar.php'; ?>
  </div>

  <?php include_once 'nav-menu.php'; ?>

  <div class="right_col" role="main">

    <div class="pm-page-header">
      <div>
        <div class="pm-breadcrumb">Student Management System &nbsp;›&nbsp; Dashboard</div>
        <h1>Dashboard</h1>
      </div>
      <div class="pm-badge"><?= htmlspecialchars($_SESSION['role'] ?? 'USER') ?></div>
    </div>

    <div class="dash-welcome">
      <div class="dash-welcome-header">
        <h2>Overview</h2>
        <i class="fa fa-bar-chart" style="color:var(--gold-muted);font-size:18px;"></i>
      </div>
      <div class="dash-welcome-body">
        <div class="pm-accent-line"></div>
        <p>Welcome back, <strong><?= htmlspecialchars($_SESSION['role'] ?? 'User') ?></strong>. Here is a summary of the current records in the system.</p>
      </div>
    </div>

    <div class="row">

      <div class="col-md-4 col-sm-6 col-xs-12">
        <div class="dash-card">
          <div class="dash-card-header">
            <span class="dash-card-label">Total Students</span>
            <i class="fa fa-user"></i>
          </div>
          <div class="dash-card-body">
            <div class="dash-card-icon">
              <i class="fa fa-user"></i>
            </div>
            <div>
              <div class="dash-card-count"><?= (int)$totalStudents ?></div>
              <div class="dash-card-sublabel">Enrolled Students</div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-md-4 col-sm-6 col-xs-12">
        <div class="dash-card">
          <div class="dash-card-header">
            <span class="dash-card-label">Registered Parents</span>
            <i class="fa fa-users"></i>
          </div>
          <div class="dash-card-body">
            <div class="dash-card-icon">
              <i class="fa fa-users"></i>
            </div>
            <div>
              <div class="dash-card-count"><?= (int)$totalParents ?></div>
              <div class="dash-card-sublabel">Registered Parents</div>
            </div>
          </div>
        </div>
      </div>

    </div>

  </div>

  <footer>
    <div class="pull-right">
      Gentelella - Bootstrap Admin Template by <a href="https://colorlib.com">Colorlib</a>
    </div>
    <div class="clearfix"></div>
  </footer>

</div>
</div>

<?php include_once 'footer.php'; ?>
</body>
</html>
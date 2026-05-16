<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['role'] != 'Teacher') {
    header('Location:./logout.php');
    exit;
}

$apiBase = "http://localhost:3000";

function apiGet($url) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $response = curl_exec($ch);
    curl_close($ch);
    return json_decode($response, true);
}

function apiPost($url, $data) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $response = curl_exec($ch);
    curl_close($ch);
    return json_decode($response, true);
}

function h($v) {
    return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
}

$pid = $fname = $lname = $nic = $gender = $address = $email = $contact = $occupation = '';
$isUpdate = isset($_GET['update']);
$errorMsg = '';

if ($isUpdate) {
    $pid = $_GET['update'];

    $parentData = apiGet("$apiBase/parents/$pid");

    if (isset($parentData['status']) && $parentData['status'] === 'success') {
        $row = $parentData['data'];

        $pid        = $row['pid'] ?? $pid;
        $fname      = $row['fname'] ?? '';
        $lname      = $row['lname'] ?? '';
        $nic        = $row['nic'] ?? '';
        $gender     = $row['gender'] ?? '';
        $address    = $row['address'] ?? '';
        $email      = $row['email'] ?? '';
        $contact    = $row['contact'] ?? '';
        $occupation = $row['job'] ?? '';
    } else {
        $errorMsg = "Parent not found.";
    }
}

if (isset($_POST['submit'])) {
    $fname      = trim($_POST['fname']);
    $lname      = trim($_POST['lname']);
    $nic        = trim($_POST['nic']);
    $gender     = $_POST['gender'] ?? '';
    $address    = trim($_POST['address']);
    $email      = trim($_POST['email']);
    $contact    = trim($_POST['contact']);
    $occupation = trim($_POST['occupation']);

    if ($isUpdate) {
        $pid = $_POST['pid'];

        $response = apiPost("$apiBase/parents/update", [
            'pid' => $pid,
            'fname' => $fname,
            'lname' => $lname,
            'nic' => $nic,
            'gender' => $gender,
            'address' => $address,
            'email' => $email,
            'contact' => $contact,
            'occupation' => $occupation
        ]);
    } else {
        $response = apiPost("$apiBase/parents", [
            'fname' => $fname,
            'lname' => $lname,
            'nic' => $nic,
            'gender' => $gender,
            'address' => $address,
            'email' => $email,
            'contact' => $contact,
            'occupation' => $occupation
        ]);
    }

    if (isset($response['status']) && $response['status'] === 'success') {
        header("Location: parent.php?page=1");
        exit;
    } else {
        $errorMsg = "Error: " . ($response['message'] ?? 'Unknown error');
    }
}

$limit = 10;
$page  = max(1, (int)($_GET['page'] ?? 1));

$parentsData = apiGet("$apiBase/parents?page=$page&limit=$limit");

$parents      = $parentsData['data']['rows'] ?? [];
$totalRecords = $parentsData['data']['total'] ?? 0;
$totalPages   = max(1, (int)ceil($totalRecords / $limit));
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Parent Management</title>
<link rel="icon" href="../img/favicon2.png">
<?php include_once 'header.php'; ?>

<style>
:root {
  --navy:#0D1B3E;
  --navy-hover:#162456;
  --input-bg:#F8F9FB;
  --input-border:#dcdfe6;
  --page-bg:#F4F6F9;
}

body {
  background: var(--page-bg)!important;
  font-family:'Segoe UI',Tahoma,sans-serif;
}

.pm-page-header h1 { font-size:18px; }
.pm-page-header .pm-breadcrumb { font-size:11px; }
.pm-badge { font-size:9px; }

.pm-panel {
  border-radius:6px;
  box-shadow:0 2px 6px rgba(0,0,0,0.05);
  border:none;
  overflow:hidden;
}

.pm-panel-header {
  padding:14px 16px;
  background:#fff;
  border-bottom:1px solid #eee;
}

.pm-panel-header h2 {
  font-size:14px;
  color:var(--navy);
  margin:0;
}

.pm-panel-body {
  padding:18px;
  background:#fff;
}

.pm-form-group {
  margin-bottom:12px;
}

.pm-form-group label {
  font-size:11.5px;
  font-weight:600;
  color:#555;
  margin-bottom:3px;
  display:block;
}

.pm-form-group input,
.pm-form-group select,
.pm-form-group textarea {
  width:100%;
  height:34px;
  font-size:12.5px;
  padding:0 9px;
  border:1px solid var(--input-border);
  background:var(--input-bg);
  border-radius:4px;
  transition:0.2s;
}

.pm-form-group input:focus,
.pm-form-group select:focus,
.pm-form-group textarea:focus {
  border-color:var(--navy);
  box-shadow:0 0 0 1px rgba(13,27,62,0.1);
  outline:none;
}

.pm-form-group textarea {
  height:75px;
  padding:7px 9px;
}

.pm-radio-group {
  display:flex;
  gap:12px;
}

.pm-radio-group label {
  font-size:11.5px;
  display:flex;
  align-items:center;
  gap:4px;
}

.pm-radio-group input[type="radio"] {
  transform:scale(0.7);
  accent-color:var(--navy);
  cursor:pointer;
}

.pm-btn {
  font-size:12px;
  padding:7px 14px;
  border-radius:4px;
  border:none;
  cursor:pointer;
  text-decoration:none;
  display:inline-block;
}

.pm-btn-primary {
  background:var(--navy);
  color:#fff;
}

.pm-btn-primary:hover {
  background:var(--navy-hover);
  color:#fff;
}

.pm-btn-cancel {
  background:#ddd;
  color:#222;
  margin-left:5px;
}

.pm-btn-edit {
  background:var(--navy);
  color:#fff;
  font-size:9.5px;
  padding:3px 7px;
}

.pm-alert {
  font-size:11px;
  padding:7px;
  border-radius:4px;
  margin-bottom:10px;
}

.pm-alert-danger {
  background:#fff0f0;
  color:#b00020;
  border-left:3px solid #b00020;
}

.pm-table-wrap {
  overflow-x:auto;
}

.pm-table {
  width:100%;
  border-collapse:collapse;
  font-size:10.5px;
}

.pm-table thead {
  background:#f5f6f8;
}

.pm-table thead th {
  font-size:8.5px;
  padding:8px;
  text-transform:uppercase;
  color:#666;
}

.pm-table tbody tr {
  border-bottom:1px solid #eee;
}

.pm-table tbody tr:hover {
  background:#f9fafc;
}

.pm-table tbody td {
  padding:8px;
}

.pm-id-badge {
  background:#eef1f7;
  padding:2px 5px;
  font-size:9px;
  border-radius:3px;
}

.pm-pagination {
  margin-top:14px;
  display:flex;
  gap:4px;
  flex-wrap:wrap;
}

.pm-pagination a,
.pm-pagination span {
  font-size:10px;
  padding:4px 8px;
  border:1px solid #ddd;
  border-radius:4px;
  text-decoration:none;
  color:var(--navy);
  background:#fff;
}

.pm-pagination a:hover,
.pm-page-active {
  background:var(--navy)!important;
  color:#fff!important;
}

.pm-page-disabled {
  color:#aaa!important;
  background:#f3f3f3!important;
  pointer-events:none;
}

.pm-record-count {
  font-size:9.5px;
  margin-top:8px;
  color:#666;
}

.pm-accent-line {
  width:40px;
  height:2px;
  background:var(--navy);
  margin-bottom:12px;
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
        <div class="pm-breadcrumb">Student Management System &nbsp;›&nbsp; Parent Management</div>
        <h1>Parent Management</h1>
      </div>
      <div class="pm-badge">TEACHER VIEW</div>
    </div>

    <div class="row">

      <div class="col-md-3">
        <div class="pm-panel">

          <div class="pm-panel-header">
            <h2><?= $isUpdate ? 'Update Parent' : 'Add Parent' ?></h2>
          </div>

          <div class="pm-panel-body">
            <div class="pm-accent-line"></div>

            <?php if ($errorMsg): ?>
              <div class="pm-alert pm-alert-danger"><?= h($errorMsg) ?></div>
            <?php endif; ?>

            <form method="POST">

              <?php if ($isUpdate): ?>
                <input type="hidden" name="pid" value="<?= h($pid) ?>">
              <?php endif; ?>

              <div class="pm-form-group">
                <label>First Name</label>
                <input name="fname" type="text" required value="<?= h($fname) ?>">
              </div>

              <div class="pm-form-group">
                <label>Last Name</label>
                <input name="lname" type="text" required value="<?= h($lname) ?>">
              </div>

              <div class="pm-form-group">
                <label>NIC</label>
                <input name="nic" type="text" value="<?= h($nic) ?>">
              </div>

              <div class="pm-form-group">
                <label>Gender</label>
                <div class="pm-radio-group">
                  <label>
                    <input type="radio" name="gender" value="Male" <?= ($gender === 'Male') ? 'checked' : '' ?>> Male
                  </label>
                  <label>
                    <input type="radio" name="gender" value="Female" <?= ($gender === 'Female') ? 'checked' : '' ?>> Female
                  </label>
                </div>
              </div>

              <div class="pm-form-group">
                <label>Email</label>
                <input name="email" type="email" required value="<?= h($email) ?>">
              </div>

              <div class="pm-form-group">
                <label>Contact</label>
                <input name="contact" type="text" value="<?= h($contact) ?>">
              </div>

              <div class="pm-form-group">
                <label>Occupation / Job</label>
                <input name="occupation" type="text" value="<?= h($occupation) ?>">
              </div>

              <div class="pm-form-group">
                <label>Address</label>
                <textarea name="address" rows="3"><?= h($address) ?></textarea>
              </div>

              <button type="submit" name="submit" class="pm-btn pm-btn-primary">
                <?= $isUpdate ? 'Update Parent' : 'Add Parent' ?>
              </button>

              <?php if ($isUpdate): ?>
                <a href="parent.php" class="pm-btn pm-btn-cancel">Cancel</a>
              <?php endif; ?>

            </form>
          </div>
        </div>
      </div>

      <div class="col-md-9">
        <div class="pm-panel">

          <div class="pm-panel-header">
            <h2>All Parents</h2>
          </div>

          <div class="pm-panel-body">
            <div class="pm-accent-line"></div>

            <div class="pm-table-wrap">
              <table class="pm-table">
                <thead>
                  <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Contact</th>
                    <th>Job</th>
                    <th>Email</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if (empty($parents)): ?>
                    <tr>
                      <td colspan="6">No parents found.</td>
                    </tr>
                  <?php else: ?>
                    <?php foreach ($parents as $p): ?>
                      <tr>
                        <td><span class="pm-id-badge"><?= h($p['pid'] ?? '') ?></span></td>
                        <td><?= h(($p['fname'] ?? '') . ' ' . ($p['lname'] ?? '')) ?></td>
                        <td><?= h($p['contact'] ?? '') ?></td>
                        <td><?= h($p['job'] ?? '') ?></td>
                        <td><?= h($p['email'] ?? '') ?></td>
                        <td>
                          <a href="parent.php?update=<?= urlencode($p['pid'] ?? '') ?>&page=<?= $page ?>" class="pm-btn pm-btn-edit">
                            Edit
                          </a>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>

            <?php if ($totalPages > 1): ?>
              <div class="pm-pagination">
                <?php if ($page <= 1): ?>
                  <span class="pm-page-disabled">← Prev</span>
                <?php else: ?>
                  <a href="?page=<?= $page - 1 ?>">← Prev</a>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                  <?php if ($i == $page): ?>
                    <span class="pm-page-active"><?= $i ?></span>
                  <?php else: ?>
                    <a href="?page=<?= $i ?>"><?= $i ?></a>
                  <?php endif; ?>
                <?php endfor; ?>

                <?php if ($page >= $totalPages): ?>
                  <span class="pm-page-disabled">Next →</span>
                <?php else: ?>
                  <a href="?page=<?= $page + 1 ?>">Next →</a>
                <?php endif; ?>
              </div>
            <?php endif; ?>

            <div class="pm-record-count">
              Showing page <?= $page ?> of <?= $totalPages ?> &nbsp;·&nbsp; <?= $totalRecords ?> total records
            </div>

          </div>
        </div>
      </div>

    </div>
  </div>

  <footer>
    <div class="pull-right">
      <a href="https://colorlib.com">Colorlib</a>
    </div>
    <div class="clearfix"></div>
  </footer>

  <?php include_once 'footer.php'; ?>

</div>
</div>
</body>
</html>
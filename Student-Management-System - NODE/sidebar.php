<?php
$role     = $_SESSION['role'] ?? '';
$hasMenu  = ($role === 'Teacher' || $role === 'Parent');
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<style>
  :root {
    --navy:         #0D1B3E;
    --navy-hover:   #162456;
    --navy-active:  #1a2d6b;
    --gold:         #E8DEBB;
    --gold-muted:   #AAA078;
    --gold-dim:     #46432D;
    --gold-accent:  rgba(232,222,187,0.15);
    --gold-divider: rgba(232,222,187,0.25);
  }

  /* ── Override Gentelella sidebar base ── */
  .nav_title,
  .left_col {
    background: var(--navy) !important;
  }

  /* Force sidebar to stay in its lane */
  .left_col {
    position: fixed !important;
    top: 0;
    left: 0;
    width: 230px !important;
    height: 100vh !important;
    overflow-y: auto;
    overflow-x: hidden;
    z-index: 1000;
    display: flex;
    flex-direction: column;
    font-family: 'Segoe UI', Tahoma, Geneva, sans-serif;
  }

  /* Push main content area so it doesn't go under sidebar */
  .right_col {
    margin-left: 230px !important;
  }

  /* ── Site title bar ── */
  .nav_title {
    border-bottom: 1px solid var(--gold-divider) !important;
    height: 57px;
    display: flex;
    align-items: center;
    flex-shrink: 0;
  }
  .nav_title .site_title {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 0 20px;
    text-decoration: none !important;
    color: var(--gold) !important;
    font-family: Georgia, serif;
    font-size: 14px;
    font-weight: bold;
    white-space: nowrap;
  }
  .nav_title .site_title i {
    color: var(--gold-muted);
    font-size: 18px;
  }

  /* ── Profile block ── */
  .left_col .profile {
    background: rgba(0,0,0,0.18) !important;
    border-bottom: 1px solid var(--gold-divider) !important;
    padding: 16px 20px !important;
    display: flex !important;
    align-items: center !important;
    gap: 12px;
    flex-shrink: 0;
  }
  .left_col .profile_pic .profile_img {
    width: 46px;
    height: 46px;
    border-radius: 50%;
    border: 2px solid var(--gold-muted);
    object-fit: cover;
  }
  .left_col .profile_info {
    overflow: hidden;
    flex: 1;
  }
  .left_col .profile_info span {
    display: block;
    font-size: 9px;
    font-weight: bold;
    color: var(--gold-muted) !important;
    letter-spacing: 0.8px;
    text-transform: uppercase;
    margin-bottom: 2px;
  }
  .left_col .profile_info h2 {
    font-family: Georgia, serif;
    font-size: 13px !important;
    font-weight: bold;
    color: var(--gold) !important;
    margin: 0 !important;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }
  .profile-role-badge {
    display: inline-block;
    margin-top: 4px;
    padding: 2px 8px;
    background: var(--gold-accent);
    border: 1px solid var(--gold-divider);
    font-size: 7.5px;
    font-weight: bold;
    color: var(--gold-muted);
    letter-spacing: 0.8px;
    text-transform: uppercase;
  }

  /* ── Menu sections ── */
  .left_col .menu_section {
    border-bottom: 1px solid var(--gold-divider) !important;
    padding: 0 !important;
    margin: 0 !important;
  }
  .left_col .menu_section:last-child {
    border-bottom: none !important;
  }
  .left_col .menu_section > h3 {
    font-size: 10px !important;
    font-weight: bold !important;
    color: var(--gold-dim) !important;
    letter-spacing: 1.2px !important;
    text-transform: uppercase !important;
    padding: 18px 20px 8px !important;
    margin: 0 !important;
    background: transparent !important;
    border: none !important;
  }

  /* ── Nav links ── */
  .left_col .nav.side-menu > li > a,
  .left_col .nav.child_menu > li > a {
    font-family: 'Segoe UI', sans-serif !important;
    font-size: 13px !important;
    color: var(--gold-muted) !important;
    background: transparent !important;
    padding: 14px 20px !important;
    border: none !important;
    border-left: 3px solid transparent !important;
    display: flex !important;
    align-items: center !important;
    gap: 12px;
    transition: background 0.15s, color 0.15s;
  }
  .left_col .nav.side-menu > li > a:hover,
  .left_col .nav.child_menu > li > a:hover {
    background: var(--gold-accent) !important;
    color: var(--gold) !important;
  }
  .left_col .nav.side-menu > li.active > a,
  .left_col .nav.child_menu > li.active > a {
    background: var(--navy-active) !important;
    color: var(--gold) !important;
    border-left: 3px solid var(--gold) !important;
  }

  /* Icons */
  .left_col .nav li a i {
    color: var(--gold-muted) !important;
    font-size: 16px;
    width: 20px;
    text-align: center;
    flex-shrink: 0;
  }
  .left_col .nav li a:hover i,
  .left_col .nav li.active a i {
    color: var(--gold) !important;
  }

  /* Chevron */
  .left_col .nav.side-menu > li > a .fa-chevron-down {
    margin-left: auto !important;
    font-size: 11px;
    color: var(--gold-dim);
    transition: transform 0.2s;
  }
  .left_col .nav.side-menu > li.active > a .fa-chevron-down {
    transform: rotate(180deg);
    color: var(--gold-muted);
  }

  /* Child menu */
  .left_col .nav.child_menu {
    background: rgba(0,0,0,0.15) !important;
    border-left: 2px solid var(--gold-divider) !important;
    margin-left: 20px !important;
    padding: 0 !important;
  }
  .left_col .nav.child_menu > li > a {
    padding: 12px 16px !important;
    font-size: 12px !important;
  }

  /* ── Sidebar footer ── */
  .left_col .sidebar-footer {
    margin-top: auto;
    border-top: 1px solid var(--gold-divider);
    background: rgba(0,0,0,0.20);
    display: flex;
    justify-content: space-around;
    align-items: center;
    padding: 14px 0;
    flex-shrink: 0;
  }
  .left_col .sidebar-footer a {
    color: var(--gold-dim) !important;
    font-size: 20px;
    padding: 8px 12px;
    border-radius: 2px;
    transition: color 0.15s, background 0.15s;
    cursor: pointer;
    text-decoration: none !important;
  }
  .left_col .sidebar-footer a:hover {
    color: var(--gold) !important;
    background: var(--gold-accent);
  }

  /* ── Scrollbar styling ── */
  .left_col::-webkit-scrollbar { width: 4px; }
  .left_col::-webkit-scrollbar-track { background: var(--navy); }
  .left_col::-webkit-scrollbar-thumb { background: var(--gold-dim); border-radius: 2px; }
</style>

<!-- ── Site title ── -->
<div class="navbar nav_title" style="border:0;">
  <a href="index.php" class="site_title">
    <i class="fa fa-graduation-cap"></i>
    <span>Student Management</span>
  </a>
</div>

<div class="clearfix"></div>

<!-- ── Profile ── -->
<div class="profile clearfix">
  <div class="profile_pic">
    <img src="mona.jpg" alt="Profile" class="img-circle profile_img">
  </div>
  <div class="profile_info">
    <span>Welcome,</span>
    <h2><?= htmlspecialchars($role ?: 'User') ?></h2>
    <?php if (!empty($role)): ?>
      <div class="profile-role-badge"><?= htmlspecialchars($role) ?></div>
    <?php endif; ?>
  </div>
</div>

<!-- ── Menu ── -->
<div id="sidebar-menu" class="main_menu_side hidden-print main_menu">

  <div class="menu_section">
    <h3>General</h3>
    <ul class="nav side-menu">

      <li class="<?= $currentPage === 'index.php' ? 'active' : '' ?>">
        <a href="./index.php">
          <i class="fa fa-home"></i> Dashboard
        </a>
      </li>

      <?php if ($hasMenu): ?>
      <li class="<?= in_array($currentPage, ['student.php','parent.php','student-par.php']) ? 'active' : '' ?>">
        <a href="javascript:void(0)">
          <i class="fa fa-windows"></i>
          Menu
          <span class="fa fa-chevron-down"></span>
        </a>
        <ul class="nav child_menu" <?= in_array($currentPage, ['student.php','parent.php','student-par.php']) ? 'style="display:block"' : '' ?>>
          <?php if ($role === 'Teacher'): ?>
            <li class="<?= $currentPage === 'student.php' ? 'active' : '' ?>">
              <a href="./student.php"><i class="fa fa-user"></i> Student</a>
            </li>
            <li class="<?= $currentPage === 'parent.php' ? 'active' : '' ?>">
              <a href="./parent.php"><i class="fa fa-users"></i> Parents</a>
            </li>
          <?php elseif ($role === 'Parent'): ?>
            <li class="<?= $currentPage === 'student-par.php' ? 'active' : '' ?>">
              <a href="./student-par.php"><i class="fa fa-user"></i> Student</a>
            </li>
          <?php endif; ?>
        </ul>
      </li>
      <?php endif; ?>

    </ul>
  </div>

  <div class="menu_section">
    <h3>User</h3>
    <ul class="nav side-menu">
      <li>
        <a href="logout.php">
          <i class="fa fa-power-off"></i> Logout
        </a>
      </li>
    </ul>
  </div>

</div>

<!-- ── Sidebar footer ── -->
<div class="sidebar-footer hidden-small">
  <a data-toggle="tooltip" title="Settings">
    <span class="glyphicon glyphicon-cog"></span>
  </a>
  <a data-toggle="tooltip" title="Full Screen">
    <span class="glyphicon glyphicon-fullscreen"></span>
  </a>
  <a data-toggle="tooltip" title="Lock">
    <span class="glyphicon glyphicon-eye-close"></span>
  </a>
  <a data-toggle="tooltip" title="Logout" href="logout.php">
    <span class="glyphicon glyphicon-off"></span>
  </a>
</div>
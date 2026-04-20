<?php
require_once __DIR__ . '/../config/session.php';
$flash = getFlash();
// Base URL works from both / and /admin/ subdirectory
$base = '/municipality-complaint-system/';
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
  <div class="container-fluid">
    <a class="navbar-brand fw-bold" href="<?= !isLoggedIn() ? $base.'index.php' : (isset($_SESSION['role']) && in_array($_SESSION['role'], ['admin','staff']) ? $base.'admin/admin_dashboard.php' : $base.'dashboard.php') ?>">
      <i class="bi bi-building me-2"></i>Municipality Portal
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMain">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navMain">
      <?php if (isLoggedIn()): ?>
      <ul class="navbar-nav ms-auto align-items-lg-center">
        <?php if ($_SESSION['role'] === 'citizen'): ?>
        <li class="nav-item"><a class="nav-link" href="<?= $base ?>dashboard.php"><i class="bi bi-speedometer2 me-1"></i>Dashboard</a></li>
        <li class="nav-item"><a class="nav-link" href="<?= $base ?>submit_complaint.php"><i class="bi bi-plus-circle me-1"></i>Submit Complaint</a></li>
        <li class="nav-item"><a class="nav-link" href="<?= $base ?>view_complaints.php"><i class="bi bi-list-ul me-1"></i>My Complaints</a></li>
        <?php endif; ?>
        <li class="nav-item ms-lg-2">
          <a class="btn btn-outline-light btn-sm" href="<?= $base ?>logout.php"><i class="bi bi-box-arrow-right me-1"></i>Logout (<?= htmlspecialchars($_SESSION['name']) ?>)</a>
        </li>
      </ul>
      <?php else: ?>
      <ul class="navbar-nav ms-auto">
        <li class="nav-item"><a class="nav-link" href="<?= $base ?>login.php">Login</a></li>
        <li class="nav-item"><a class="nav-link" href="<?= $base ?>register.php">Register</a></li>
      </ul>
      <?php endif; ?>
    </div>
  </div>
</nav>
<?php if ($flash): ?>
<div class="alert alert-<?= $flash['type'] === 'success' ? 'success' : 'danger' ?> alert-dismissible fade show mb-0 rounded-0" role="alert">
  <?= htmlspecialchars($flash['msg']) ?>
  <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

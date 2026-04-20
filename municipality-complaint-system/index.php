<?php
require_once 'config/session.php';
if (isLoggedIn()) { header('Location: /municipality-complaint-system/dashboard.php'); exit; }
$pageTitle = 'Welcome';
require_once 'components/head.php';
?>

<?php require_once 'components/navbar.php'; ?>

<section class="hero text-center">
  <div class="container">
    <i class="bi bi-building display-3 mb-3 d-block"></i>
    <h1 class="display-5 fw-bold">Public Complaint Tracking System</h1>
    <p class="lead mt-3 mb-4 opacity-75">Submit, track, and resolve municipal complaints transparently and efficiently.</p>
    <a href="register.php" class="btn btn-light btn-lg me-2 fw-semibold">Get Started</a>
    <a href="login.php" class="btn btn-outline-light btn-lg">Login</a>
  </div>
</section>

<main class="container py-5">
  <div class="row g-4 text-center">
    <div class="col-md-4">
      <div class="card h-100 p-4">
        <i class="bi bi-pencil-square display-5 text-primary mb-3"></i>
        <h5 class="fw-bold">Submit a Complaint</h5>
        <p class="text-muted small">Report road damage, garbage, water leaks, street lighting issues, and more — with photo evidence.</p>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card h-100 p-4">
        <i class="bi bi-search display-5 text-warning mb-3"></i>
        <h5 class="fw-bold">Track Progress</h5>
        <p class="text-muted small">Get a unique tracking ID and monitor your complaint status in real time from submission to resolution.</p>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card h-100 p-4">
        <i class="bi bi-check2-circle display-5 text-success mb-3"></i>
        <h5 class="fw-bold">Faster Resolution</h5>
        <p class="text-muted small">Complaints are assigned to relevant departments ensuring accountability and faster service delivery.</p>
      </div>
    </div>
  </div>
</main>

<?php require_once 'components/footer.php'; ?>

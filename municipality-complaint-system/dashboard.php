<?php
require_once 'config/session.php';
require_once 'config/db.php';
requireLogin();

// Admin and staff have no business on the citizen dashboard
if (in_array($_SESSION['role'], ['admin', 'staff'])) {
    header('Location: /municipality-complaint-system/admin/admin_dashboard.php');
    exit;
}

$pdo = getConnection();
$uid = $_SESSION['user_id'];

// Stats
$total    = $pdo->prepare("SELECT COUNT(*) FROM complaints WHERE user_id = ?");
$total->execute([$uid]); $total = $total->fetchColumn();

$pending  = $pdo->prepare("SELECT COUNT(*) FROM complaints WHERE user_id = ? AND status = 'Pending'");
$pending->execute([$uid]); $pending = $pending->fetchColumn();

$inprog   = $pdo->prepare("SELECT COUNT(*) FROM complaints WHERE user_id = ? AND status = 'In Progress'");
$inprog->execute([$uid]); $inprog = $inprog->fetchColumn();

$resolved = $pdo->prepare("SELECT COUNT(*) FROM complaints WHERE user_id = ? AND status = 'Resolved'");
$resolved->execute([$uid]); $resolved = $resolved->fetchColumn();

// Recent 5
$recent = $pdo->prepare("SELECT * FROM complaints WHERE user_id = ? ORDER BY date_submitted DESC LIMIT 5");
$recent->execute([$uid]);
$recentComplaints = $recent->fetchAll();

$pageTitle = 'Dashboard';
require_once 'components/head.php';
?>
<?php require_once 'components/navbar.php'; ?>

<main class="container py-4">
  <h4 class="fw-bold mb-4">Welcome, <?= htmlspecialchars($_SESSION['name']) ?> 👋</h4>

  <!-- Stat Cards -->
  <div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
      <div class="stat-card" style="background:#1a73e8">
        <i class="bi bi-clipboard-data stat-icon"></i>
        <div><div class="stat-value"><?= $total ?></div><div class="stat-label">Total</div></div>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="stat-card" style="background:#fd7e14">
        <i class="bi bi-hourglass-split stat-icon"></i>
        <div><div class="stat-value"><?= $pending ?></div><div class="stat-label">Pending</div></div>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="stat-card" style="background:#0dcaf0">
        <i class="bi bi-arrow-repeat stat-icon"></i>
        <div><div class="stat-value"><?= $inprog ?></div><div class="stat-label">In Progress</div></div>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="stat-card" style="background:#198754">
        <i class="bi bi-check-circle stat-icon"></i>
        <div><div class="stat-value"><?= $resolved ?></div><div class="stat-label">Resolved</div></div>
      </div>
    </div>
  </div>

  <!-- Quick Actions -->
  <div class="row g-3 mb-4">
    <?php if ($_SESSION['role'] === 'citizen'): ?>
    <div class="col-auto">
      <a href="submit_complaint.php" class="btn btn-primary"><i class="bi bi-plus-circle me-1"></i>New Complaint</a>
    </div>
    <div class="col-auto">
      <a href="view_complaints.php" class="btn btn-outline-secondary"><i class="bi bi-list-ul me-1"></i>View All</a>
    </div>
    <?php else: ?>
    <div class="col-auto">
      <a href="admin/admin_dashboard.php" class="btn btn-primary"><i class="bi bi-shield-check me-1"></i>Go to Admin Panel</a>
    </div>
    <?php endif; ?>
  </div>

  <!-- Recent Complaints -->
  <div class="card">
    <div class="card-header bg-white">Recent Complaints</div>
    <div class="table-responsive">
      <table class="table mb-0">
        <thead><tr>
          <th>#ID</th><th>Category</th><th>Location</th><th>Status</th><th>Date</th><th></th>
        </tr></thead>
        <tbody>
        <?php if ($recentComplaints): ?>
          <?php foreach ($recentComplaints as $c): ?>
          <tr>
            <td>#<?= $c['complaint_id'] ?></td>
            <td><?= htmlspecialchars($c['category']) ?></td>
            <td><?= htmlspecialchars($c['location']) ?></td>
            <td><?php
              $map = ['Pending'=>'warning','In Progress'=>'info','Resolved'=>'success','Rejected'=>'danger'];
              $cls = $map[$c['status']] ?? 'secondary';
            ?><span class="badge bg-<?= $cls ?>"><?= $c['status'] ?></span></td>
            <td><?= date('d M Y', strtotime($c['date_submitted'])) ?></td>
            <td><a href="view_complaints.php?id=<?= $c['complaint_id'] ?>" class="btn btn-sm btn-outline-primary">View</a></td>
          </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr><td colspan="6" class="text-center text-muted py-4">No complaints submitted yet.</td></tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</main>

<?php require_once 'components/footer.php'; ?>

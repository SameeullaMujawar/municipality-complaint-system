<?php
require_once '../config/session.php';
require_once '../config/db.php';
requireStaffOrAdmin();

$pdo = getConnection();

// Summary by status
$byStatus = $pdo->query(
    "SELECT status, COUNT(*) AS total FROM complaints GROUP BY status ORDER BY total DESC"
)->fetchAll();

// Summary by category
$byCat = $pdo->query(
    "SELECT category, COUNT(*) AS total FROM complaints GROUP BY category ORDER BY total DESC"
)->fetchAll();

// Summary by department
$byDept = $pdo->query(
    "SELECT d.department_name, COUNT(ca.complaint_id) AS total
     FROM departments d
     LEFT JOIN complaint_assignments ca ON d.department_id = ca.department_id
     GROUP BY d.department_id ORDER BY total DESC"
)->fetchAll();

// Recent 10 resolved
$resolved = $pdo->query(
    "SELECT c.complaint_id, c.category, c.location, u.name AS citizen, c.date_submitted
     FROM complaints c JOIN users u ON c.user_id = u.user_id
     WHERE c.status = 'Resolved'
     ORDER BY c.date_submitted DESC LIMIT 10"
)->fetchAll();

$pageTitle = 'Reports';
$assetBase = '../';
require_once '../components/head.php';
?>
<?php require_once '../components/navbar.php'; ?>

<main class="container py-4">
  <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
    <a href="admin_dashboard.php" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Back to Dashboard</a>
    <a href="report_pdf.php" target="_blank" class="btn btn-sm btn-danger"><i class="bi bi-file-earmark-pdf me-1"></i>Download PDF</a>
  </div>
  <h4 class="fw-bold mb-4"><i class="bi bi-file-earmark-bar-graph me-2 text-primary"></i>Reports Overview</h4>

  <div class="row g-4">
    <!-- By Status -->
    <div class="col-md-4">
      <div class="card h-100">
        <div class="card-header bg-white fw-semibold">By Status</div>
        <div class="table-responsive">
          <table class="table mb-0">
            <thead><tr><th>Status</th><th>Count</th></tr></thead>
            <tbody>
            <?php $map=['Pending'=>'warning','In Progress'=>'info','Resolved'=>'success','Rejected'=>'danger']; ?>
            <?php foreach ($byStatus as $r): ?>
            <tr>
              <td><span class="badge bg-<?= $map[$r['status']]??'secondary' ?>"><?= $r['status'] ?></span></td>
              <td><?= $r['total'] ?></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- By Category -->
    <div class="col-md-4">
      <div class="card h-100">
        <div class="card-header bg-white fw-semibold">By Category</div>
        <div class="table-responsive">
          <table class="table mb-0">
            <thead><tr><th>Category</th><th>Count</th></tr></thead>
            <tbody>
            <?php foreach ($byCat as $r): ?>
            <tr><td><?= htmlspecialchars($r['category']) ?></td><td><?= $r['total'] ?></td></tr>
            <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- By Department -->
    <div class="col-md-4">
      <div class="card h-100">
        <div class="card-header bg-white fw-semibold">By Department</div>
        <div class="table-responsive">
          <table class="table mb-0">
            <thead><tr><th>Department</th><th>Assigned</th></tr></thead>
            <tbody>
            <?php foreach ($byDept as $r): ?>
            <tr><td><?= htmlspecialchars($r['department_name']) ?></td><td><?= $r['total'] ?></td></tr>
            <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <!-- Recently Resolved -->
  <div class="card mt-4">
    <div class="card-header bg-white fw-semibold">Recently Resolved (Last 10)</div>
    <div class="table-responsive">
      <table class="table mb-0">
        <thead><tr><th>#ID</th><th>Category</th><th>Location</th><th>Citizen</th><th>Date</th></tr></thead>
        <tbody>
        <?php foreach ($resolved as $r): ?>
        <tr>
          <td>#<?= $r['complaint_id'] ?></td>
          <td><?= htmlspecialchars($r['category']) ?></td>
          <td><?= htmlspecialchars($r['location']) ?></td>
          <td><?= htmlspecialchars($r['citizen']) ?></td>
          <td><?= date('d M Y', strtotime($r['date_submitted'])) ?></td>
        </tr>
        <?php endforeach; ?>
        <?php if (!$resolved): ?>
        <tr><td colspan="5" class="text-center text-muted py-3">No resolved complaints yet.</td></tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</main>

<?php $assetBase='../'; require_once '../components/footer.php'; ?>

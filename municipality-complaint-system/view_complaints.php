<?php
require_once 'config/session.php';
require_once 'config/db.php';
requireLogin();
if (in_array($_SESSION['role'], ['admin', 'staff'])) {
    header('Location: /municipality-complaint-system/admin/admin_dashboard.php'); exit;
}

$pdo = getConnection();
$uid = $_SESSION['user_id'];

// Single complaint detail view
$detail = null;
if (isset($_GET['id'])) {
    $stmt = $pdo->prepare("SELECT c.*, d.department_name FROM complaints c
        LEFT JOIN complaint_assignments ca ON c.complaint_id = ca.complaint_id
        LEFT JOIN departments d ON ca.department_id = d.department_id
        WHERE c.complaint_id = ? AND c.user_id = ?");
    $stmt->execute([(int)$_GET['id'], $uid]);
    $detail = $stmt->fetch();
}

// Filter
$statusFilter = $_GET['status'] ?? '';
$where  = "WHERE c.user_id = ?";
$params = [$uid];
if ($statusFilter) { $where .= " AND c.status = ?"; $params[] = $statusFilter; }

$stmt = $pdo->prepare("SELECT c.*, d.department_name FROM complaints c
    LEFT JOIN complaint_assignments ca ON c.complaint_id = ca.complaint_id
    LEFT JOIN departments d ON ca.department_id = d.department_id
    $where ORDER BY c.date_submitted DESC");
$stmt->execute($params);
$complaints = $stmt->fetchAll();

$pageTitle = 'My Complaints';
require_once 'components/head.php';
?>
<?php require_once 'components/navbar.php'; ?>

<main class="container py-4">
  <?php if ($detail): ?>
  <!-- Detail Panel -->
  <div class="mb-3"><a href="view_complaints.php" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Back</a></div>
  <div class="card p-4 mb-4">
    <h5 class="fw-bold mb-3">Complaint #<?= $detail['complaint_id'] ?></h5>
    <div class="row g-3">
      <div class="col-md-6">
        <label class="text-muted small">Category</label>
        <p class="fw-semibold mb-1"><?= htmlspecialchars($detail['category']) ?></p>
      </div>
      <div class="col-md-6">
        <label class="text-muted small">Status</label>
        <?php $map=['Pending'=>'warning','In Progress'=>'info','Resolved'=>'success','Rejected'=>'danger']; ?>
        <p><span class="badge bg-<?= $map[$detail['status']] ?? 'secondary' ?> fs-6"><?= $detail['status'] ?></span></p>
      </div>
      <div class="col-12">
        <label class="text-muted small">Description</label>
        <p><?= nl2br(htmlspecialchars($detail['description'])) ?></p>
      </div>
      <div class="col-md-6">
        <label class="text-muted small">Location</label>
        <p><?= htmlspecialchars($detail['location']) ?></p>
      </div>
      <div class="col-md-6">
        <label class="text-muted small">Submitted On</label>
        <p><?= date('d M Y, h:i A', strtotime($detail['date_submitted'])) ?></p>
      </div>
      <?php if ($detail['department_name']): ?>
      <div class="col-md-6">
        <label class="text-muted small">Assigned To</label>
        <p><?= htmlspecialchars($detail['department_name']) ?></p>
      </div>
      <?php endif; ?>
      <?php if ($detail['image_path']): ?>
      <div class="col-12">
        <label class="text-muted small">Attached Image</label><br>
        <img src="<?= htmlspecialchars($detail['image_path']) ?>" class="img-thumbnail mt-1" style="max-height:220px">
      </div>
      <?php endif; ?>
    </div>
  </div>
  <?php endif; ?>

  <!-- List -->
  <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
    <h4 class="fw-bold mb-0">My Complaints</h4>
    <div class="d-flex gap-2 align-items-center">
      <form method="GET" class="d-flex gap-2">
        <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
          <option value="">All Status</option>
          <?php foreach (['Pending','In Progress','Resolved','Rejected'] as $s): ?>
          <option value="<?= $s ?>" <?= $statusFilter === $s ? 'selected' : '' ?>><?= $s ?></option>
          <?php endforeach; ?>
        </select>
      </form>
      <a href="submit_complaint.php" class="btn btn-primary btn-sm"><i class="bi bi-plus-circle me-1"></i>New</a>
    </div>
  </div>

  <div class="card">
    <div class="table-responsive">
      <table class="table mb-0">
        <thead><tr>
          <th>#ID</th><th>Category</th><th>Location</th><th>Department</th><th>Status</th><th>Date</th><th></th>
        </tr></thead>
        <tbody>
        <?php if ($complaints): ?>
          <?php foreach ($complaints as $c): $cls=$map[$c['status']]??'secondary'; ?>
          <tr>
            <td>#<?= $c['complaint_id'] ?></td>
            <td><?= htmlspecialchars($c['category']) ?></td>
            <td><?= htmlspecialchars($c['location']) ?></td>
            <td><?= $c['department_name'] ? htmlspecialchars($c['department_name']) : '<span class="text-muted">Unassigned</span>' ?></td>
            <td><span class="badge bg-<?= $cls ?>"><?= $c['status'] ?></span></td>
            <td><?= date('d M Y', strtotime($c['date_submitted'])) ?></td>
            <td><a href="?id=<?= $c['complaint_id'] ?>" class="btn btn-sm btn-outline-primary">View</a></td>
          </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr><td colspan="7" class="text-center text-muted py-4">No complaints found.</td></tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</main>

<?php require_once 'components/footer.php'; ?>

<?php
require_once '../config/session.php';
require_once '../config/db.php';
requireStaffOrAdmin();

$pdo = getConnection();
$id  = (int)($_GET['id'] ?? 0);

if (!$id) { header('Location: /municipality-complaint-system/admin/admin_dashboard.php'); exit; }

$stmt = $pdo->prepare(
    "SELECT c.*, u.name AS citizen_name,
            ca.department_id AS assigned_dept, ca.notes AS assign_notes
     FROM complaints c
     JOIN users u ON c.user_id = u.user_id
     LEFT JOIN complaint_assignments ca ON c.complaint_id = ca.complaint_id
     WHERE c.complaint_id = ?"
);
$stmt->execute([$id]);
$c = $stmt->fetch();
if (!$c) { header('Location: /municipality-complaint-system/admin/admin_dashboard.php'); exit; }

$depts = $pdo->query("SELECT * FROM departments ORDER BY department_name")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newStatus = $_POST['status']      ?? $c['status'];
    $deptId    = $_POST['department']  ? (int)$_POST['department'] : null;
    $notes     = trim($_POST['notes']  ?? '');

    // Update status
    $upd = $pdo->prepare("UPDATE complaints SET status = ? WHERE complaint_id = ?");
    $upd->execute([$newStatus, $id]);

    // Upsert assignment
    if ($deptId) {
        $exists = $pdo->prepare("SELECT assignment_id FROM complaint_assignments WHERE complaint_id = ?");
        $exists->execute([$id]);
        if ($exists->fetch()) {
            $pdo->prepare("UPDATE complaint_assignments SET department_id=?, notes=? WHERE complaint_id=?")
                ->execute([$deptId, $notes, $id]);
        } else {
            $pdo->prepare("INSERT INTO complaint_assignments (complaint_id, department_id, notes) VALUES (?,?,?)")
                ->execute([$id, $deptId, $notes]);
        }
    }

    flashMessage("Complaint #$id updated successfully.");
    header('Location: /municipality-complaint-system/admin/admin_dashboard.php');
    exit;
}

$pageTitle  = "Manage Complaint #$id";
$assetBase  = '../';
require_once '../components/head.php';
?>
<?php require_once '../components/navbar.php'; ?>

<main class="container py-4" style="max-width:720px">
  <div class="mb-3"><a href="admin_dashboard.php" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Back</a></div>
  <h4 class="fw-bold mb-4">Manage Complaint #<?= $id ?></h4>

  <!-- Complaint Info -->
  <div class="card p-4 mb-4">
    <div class="row g-3">
      <div class="col-md-6">
        <p class="text-muted small mb-0">Citizen</p>
        <p class="fw-semibold"><?= htmlspecialchars($c['citizen_name']) ?></p>
      </div>
      <div class="col-md-6">
        <p class="text-muted small mb-0">Category</p>
        <p class="fw-semibold"><?= htmlspecialchars($c['category']) ?></p>
      </div>
      <div class="col-12">
        <p class="text-muted small mb-0">Description</p>
        <p><?= nl2br(htmlspecialchars($c['description'])) ?></p>
      </div>
      <div class="col-md-6">
        <p class="text-muted small mb-0">Location</p>
        <p><?= htmlspecialchars($c['location']) ?></p>
      </div>
      <div class="col-md-6">
        <p class="text-muted small mb-0">Submitted</p>
        <p><?= date('d M Y, h:i A', strtotime($c['date_submitted'])) ?></p>
      </div>
      <?php if ($c['image_path']): ?>
      <div class="col-12">
        <p class="text-muted small mb-1">Attached Image</p>
        <img src="../<?= htmlspecialchars($c['image_path']) ?>" class="img-thumbnail" style="max-height:180px">
      </div>
      <?php endif; ?>
    </div>
  </div>

  <!-- Update Form -->
  <div class="card p-4">
    <h6 class="fw-bold mb-3">Update Status & Assignment</h6>
    <form method="POST" novalidate>
      <div class="mb-3">
        <label class="form-label fw-semibold">Status</label>
        <select name="status" class="form-select">
          <?php foreach (['Pending','In Progress','Resolved','Rejected'] as $s): ?>
          <option value="<?= $s ?>" <?= $c['status']===$s?'selected':'' ?>><?= $s ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="mb-3">
        <label class="form-label fw-semibold">Assign to Department</label>
        <select name="department" class="form-select">
          <option value="">-- Unassigned --</option>
          <?php foreach ($depts as $d): ?>
          <option value="<?= $d['department_id'] ?>" <?= $c['assigned_dept']==$d['department_id']?'selected':'' ?>><?= htmlspecialchars($d['department_name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="mb-4">
        <label class="form-label fw-semibold">Internal Notes</label>
        <textarea name="notes" class="form-control" rows="3" placeholder="Optional notes for internal tracking..."><?= htmlspecialchars($c['assign_notes'] ?? '') ?></textarea>
      </div>
      <button class="btn btn-primary w-100 fw-semibold">Save Changes</button>
    </form>
  </div>
</main>

<?php $assetBase='../'; require_once '../components/footer.php'; ?>

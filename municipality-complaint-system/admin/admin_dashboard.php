<?php
require_once '../config/session.php';
require_once '../config/db.php';
requireStaffOrAdmin();

$pdo = getConnection();

// Stats
function countBy($pdo, $col, $val) {
    $s = $pdo->prepare("SELECT COUNT(*) FROM complaints WHERE $col = ?");
    $s->execute([$val]); return $s->fetchColumn();
}
$stats = [
    'total'      => $pdo->query("SELECT COUNT(*) FROM complaints")->fetchColumn(),
    'pending'    => countBy($pdo, 'status', 'Pending'),
    'inprogress' => countBy($pdo, 'status', 'In Progress'),
    'resolved'   => countBy($pdo, 'status', 'Resolved'),
    'rejected'   => countBy($pdo, 'status', 'Rejected'),
];

// Filters
$statusFilter   = $_GET['status']   ?? '';
$categoryFilter = $_GET['category'] ?? '';
$where  = "WHERE 1=1";
$params = [];
if ($statusFilter)   { $where .= " AND c.status = ?";   $params[] = $statusFilter; }
if ($categoryFilter) { $where .= " AND c.category = ?"; $params[] = $categoryFilter; }

$stmt = $pdo->prepare(
    "SELECT c.*, u.name AS citizen_name, d.department_name
     FROM complaints c
     JOIN users u ON c.user_id = u.user_id
     LEFT JOIN complaint_assignments ca ON c.complaint_id = ca.complaint_id
     LEFT JOIN departments d ON ca.department_id = d.department_id
     $where ORDER BY c.date_submitted DESC"
);
$stmt->execute($params);
$complaints = $stmt->fetchAll();

// Departments for assignment dropdown
$depts = $pdo->query("SELECT * FROM departments ORDER BY department_name")->fetchAll();

$pageTitle = 'Admin Dashboard';
$assetBase = '../';
require_once '../components/head.php';
?>
<?php require_once '../components/navbar.php'; ?>

<div class="container-fluid">
  <div class="row">
    <!-- Sidebar -->
    <div class="col-md-2 sidebar py-3 d-none d-md-block">
      <p class="text-secondary small px-3 mb-2 text-uppercase fw-bold">Menu</p>
      <nav class="nav flex-column px-2">
        <a class="nav-link active" href="admin_dashboard.php"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a>
        <a class="nav-link" href="admin_dashboard.php?status=Pending"><i class="bi bi-hourglass me-2"></i>Pending</a>
        <a class="nav-link" href="admin_dashboard.php?status=In+Progress"><i class="bi bi-arrow-repeat me-2"></i>In Progress</a>
        <a class="nav-link" href="admin_dashboard.php?status=Resolved"><i class="bi bi-check-circle me-2"></i>Resolved</a>
        <a class="nav-link" href="report.php"><i class="bi bi-file-earmark-bar-graph me-2"></i>Reports</a>
        <?php if ($_SESSION['role'] === 'admin'): ?>
        <a class="nav-link" href="manage_users.php"><i class="bi bi-people me-2"></i>Users</a>
        <?php endif; ?>
      </nav>
    </div>

    <!-- Main Content -->
    <div class="col-md-10 py-4 px-4">
      <h4 class="fw-bold mb-4">Complaint Management</h4>

      <!-- Stats -->
      <div class="row g-3 mb-4">
        <?php
        $statData = [
          ['Total','bi-clipboard-data','#1a73e8',$stats['total']],
          ['Pending','bi-hourglass','#fd7e14',$stats['pending']],
          ['In Progress','bi-arrow-repeat','#0dcaf0',$stats['inprogress']],
          ['Resolved','bi-check-circle','#198754',$stats['resolved']],
          ['Rejected','bi-x-circle','#dc3545',$stats['rejected']],
        ];
        foreach ($statData as [$label, $icon, $color, $val]):
        ?>
        <div class="col-6 col-md">
          <div class="stat-card" style="background:<?= $color ?>">
            <i class="bi <?= $icon ?> stat-icon"></i>
            <div><div class="stat-value"><?= $val ?></div><div class="stat-label"><?= $label ?></div></div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>

      <!-- Filters -->
      <form method="GET" class="row g-2 mb-3">
        <div class="col-auto">
          <select name="status" class="form-select form-select-sm">
            <option value="">All Status</option>
            <?php foreach (['Pending','In Progress','Resolved','Rejected'] as $s): ?>
            <option value="<?= $s ?>" <?= $statusFilter===$s?'selected':'' ?>><?= $s ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-auto">
          <select name="category" class="form-select form-select-sm">
            <option value="">All Categories</option>
            <?php foreach (['Garbage','Road Damage','Water Leakage','Street Light','Other'] as $cat): ?>
            <option value="<?= $cat ?>" <?= $categoryFilter===$cat?'selected':'' ?>><?= $cat ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-auto">
          <button class="btn btn-primary btn-sm">Filter</button>
          <a href="admin_dashboard.php" class="btn btn-outline-secondary btn-sm ms-1">Reset</a>
        </div>
      </form>

      <!-- Table -->
      <div class="card">
        <div class="table-responsive">
          <table class="table mb-0">
            <thead><tr>
              <th>#ID</th><th>Citizen</th><th>Category</th><th>Location</th>
              <th>Department</th><th>Status</th><th>Date</th><th>Actions</th>
            </tr></thead>
            <tbody>
            <?php $map=['Pending'=>'warning','In Progress'=>'info','Resolved'=>'success','Rejected'=>'danger']; ?>
            <?php if ($complaints): ?>
              <?php foreach ($complaints as $c): ?>
              <tr>
                <td>#<?= $c['complaint_id'] ?></td>
                <td><?= htmlspecialchars($c['citizen_name']) ?></td>
                <td><?= htmlspecialchars($c['category']) ?></td>
                <td><?= htmlspecialchars($c['location']) ?></td>
                <td><?= $c['department_name'] ? htmlspecialchars($c['department_name']) : '<span class="text-muted">—</span>' ?></td>
                <td><span class="badge bg-<?= $map[$c['status']]??'secondary' ?>"><?= $c['status'] ?></span></td>
                <td><?= date('d M Y', strtotime($c['date_submitted'])) ?></td>
                <td>
                  <a href="update_complaint.php?id=<?= $c['complaint_id'] ?>" class="btn btn-sm btn-warning">Manage</a>
                </td>
              </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr><td colspan="8" class="text-center text-muted py-4">No complaints found.</td></tr>
            <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<?php $assetBase='../'; require_once '../components/footer.php'; ?>

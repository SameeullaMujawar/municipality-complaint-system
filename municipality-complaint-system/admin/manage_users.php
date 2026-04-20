<?php
require_once '../config/session.php';
require_once '../config/db.php';
requireAdmin();

$pdo = getConnection();

// Handle role update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id'], $_POST['role'])) {
    $targetId = (int)$_POST['user_id'];
    $newRole  = $_POST['role'];
    if (in_array($newRole, ['citizen','staff','admin']) && $targetId !== (int)$_SESSION['user_id']) {
        $pdo->prepare("UPDATE users SET role = ? WHERE user_id = ?")->execute([$newRole, $targetId]);
        flashMessage("User role updated.");
    }
    header('Location: /municipality-complaint-system/admin/manage_users.php');
    exit;
}

// Handle delete
if (isset($_GET['delete'])) {
    $targetId = (int)$_GET['delete'];
    if ($targetId !== (int)$_SESSION['user_id']) {
        $pdo->prepare("DELETE FROM users WHERE user_id = ?")->execute([$targetId]);
        flashMessage("User deleted.");
    }
    header('Location: /municipality-complaint-system/admin/manage_users.php');
    exit;
}

$users = $pdo->query("SELECT u.*, COUNT(c.complaint_id) AS complaint_count
    FROM users u LEFT JOIN complaints c ON u.user_id = c.user_id
    WHERE u.role != 'admin'
    GROUP BY u.user_id ORDER BY u.created_at DESC")->fetchAll();

$pageTitle = 'Manage Users';
$assetBase = '../';
require_once '../components/head.php';
?>
<?php require_once '../components/navbar.php'; ?>

<main class="container py-4">
  <div class="mb-3"><a href="admin_dashboard.php" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Back to Dashboard</a></div>
  <h4 class="fw-bold mb-4"><i class="bi bi-people me-2 text-primary"></i>Manage Users</h4>

  <div class="card">
    <div class="table-responsive">
      <table class="table mb-0">
        <thead><tr>
          <th>#</th><th>Name</th><th>Email</th><th>Role</th><th>Complaints</th><th>Joined</th><th>Actions</th>
        </tr></thead>
        <tbody>
        <?php if ($users): ?>
        <?php foreach ($users as $u): ?>
        <tr>
          <td><?= $u['user_id'] ?></td>
          <td><?= htmlspecialchars($u['name']) ?></td>
          <td><?= htmlspecialchars($u['email']) ?></td>
          <td>
            <form method="POST" class="d-inline">
              <input type="hidden" name="user_id" value="<?= $u['user_id'] ?>">
              <select name="role" class="form-select form-select-sm d-inline w-auto" onchange="this.form.submit()">
                <?php foreach (['citizen','staff'] as $r): ?>
                <option value="<?= $r ?>" <?= $u['role']===$r?'selected':'' ?>><?= ucfirst($r) ?></option>
                <?php endforeach; ?>
              </select>
            </form>
          </td>
          <td><?= $u['complaint_count'] ?></td>
          <td><?= date('d M Y', strtotime($u['created_at'])) ?></td>
          <td>
            <a href="?delete=<?= $u['user_id'] ?>"
               class="btn btn-sm btn-outline-danger"
               data-confirm="Delete this user and all their complaints?">
              <i class="bi bi-trash"></i>
            </a>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php else: ?>
        <tr><td colspan="7" class="text-center text-muted py-4">No users registered yet.</td></tr>
        <?php endif; ?>
      </table>
    </div>
  </div>
</main>

<?php $assetBase='../'; require_once '../components/footer.php'; ?>

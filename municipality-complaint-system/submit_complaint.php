<?php
require_once 'config/session.php';
require_once 'config/db.php';
requireLogin();
if (in_array($_SESSION['role'], ['admin', 'staff'])) {
    header('Location: /municipality-complaint-system/admin/admin_dashboard.php'); exit;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category    = $_POST['category']    ?? '';
    $description = trim($_POST['description'] ?? '');
    $location    = trim($_POST['location']    ?? '');

    $validCats = ['Garbage','Road Damage','Water Leakage','Street Light','Other'];
    if (!in_array($category, $validCats)) $errors[] = 'Select a valid category.';
    if (!$description)                    $errors[] = 'Description is required.';
    if (!$location)                       $errors[] = 'Location is required.';

    $imagePath = null;
    if (!empty($_FILES['complaint_image']['name'])) {
        $file    = $_FILES['complaint_image'];
        $allowed = ['image/jpeg','image/png','image/gif','image/webp'];
        $maxSize = 2 * 1024 * 1024; // 2 MB

        if (!in_array($file['type'], $allowed)) {
            $errors[] = 'Only JPG, PNG, GIF, WEBP images allowed.';
        } elseif ($file['size'] > $maxSize) {
            $errors[] = 'Image must be under 2 MB.';
        } else {
            $ext       = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename  = uniqid('img_', true) . '.' . $ext;
            $dest      = __DIR__ . '/uploads/' . $filename;
            if (move_uploaded_file($file['tmp_name'], $dest)) {
                $imagePath = 'uploads/' . $filename;
            } else {
                $errors[] = 'Failed to upload image.';
            }
        }
    }

    if (!$errors) {
        $pdo  = getConnection();
        $stmt = $pdo->prepare(
            "INSERT INTO complaints (user_id, category, description, location, image_path, status)
             VALUES (?, ?, ?, ?, ?, 'Pending')"
        );
        $stmt->execute([$_SESSION['user_id'], $category, $description, $location, $imagePath]);
        $newId = $pdo->lastInsertId();
        flashMessage("Complaint #$newId submitted successfully! Track it below.");
        header('Location: /municipality-complaint-system/view_complaints.php');
        exit;
    }
}

$pageTitle = 'Submit Complaint';
require_once 'components/head.php';
?>
<?php require_once 'components/navbar.php'; ?>

<main class="container py-4" style="max-width:680px">
  <h4 class="fw-bold mb-4"><i class="bi bi-plus-circle me-2 text-primary"></i>Submit a Complaint</h4>

  <?php if ($errors): ?>
  <div class="alert alert-danger small">
    <?php foreach ($errors as $e): ?><div><?= htmlspecialchars($e) ?></div><?php endforeach; ?>
  </div>
  <?php endif; ?>

  <div class="card p-4">
    <form method="POST" enctype="multipart/form-data" novalidate>
      <div class="mb-3">
        <label class="form-label fw-semibold">Complaint Category <span class="text-danger">*</span></label>
        <select class="form-select" name="category" required>
          <option value="">-- Select Category --</option>
          <?php foreach (['Garbage','Road Damage','Water Leakage','Street Light','Other'] as $cat): ?>
          <option value="<?= $cat ?>" <?= (($_POST['category'] ?? '') === $cat) ? 'selected' : '' ?>><?= $cat ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="mb-3">
        <label class="form-label fw-semibold">Description <span class="text-danger">*</span></label>
        <textarea class="form-control" name="description" rows="4" placeholder="Describe the issue in detail..." required><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
      </div>
      <div class="mb-3">
        <label class="form-label fw-semibold">Location / Area <span class="text-danger">*</span></label>
        <input type="text" class="form-control" name="location" placeholder="e.g. Ward 12, MG Road near bus stop" value="<?= htmlspecialchars($_POST['location'] ?? '') ?>" required>
      </div>
      <div class="mb-4">
        <label class="form-label fw-semibold">Attach Photo <span class="text-muted small">(optional, max 2 MB)</span></label>
        <input type="file" class="form-control" id="complaint_image" name="complaint_image" accept="image/*">
        <img id="image-preview" src="" class="mt-2 d-none img-thumbnail" style="max-height:160px">
      </div>
      <button type="submit" class="btn btn-primary w-100 fw-semibold">
        <i class="bi bi-send me-1"></i> Submit Complaint
      </button>
    </form>
  </div>
</main>

<?php require_once 'components/footer.php'; ?>

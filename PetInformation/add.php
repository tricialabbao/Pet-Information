<?php
require_once 'db.php';
$pdo = getConnection();

$errors = [];
$values = ['name'=>'','species'=>'','breed'=>'','age'=>'','owner_name'=>'','description'=>''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $values['name']        = trim($_POST['name']        ?? '');
    $values['species']     = trim($_POST['species']     ?? '');
    $values['breed']       = trim($_POST['breed']       ?? '');
    $values['age']         = trim($_POST['age']         ?? '');
    $values['owner_name']  = trim($_POST['owner_name']  ?? '');
    $values['description'] = trim($_POST['description'] ?? '');

    if (empty($values['name']))       $errors['name']       = 'Pet name is required.';
    if (empty($values['species']))    $errors['species']    = 'Species is required.';
    if (empty($values['breed']))      $errors['breed']      = 'Breed is required.';
    if (empty($values['age']))        $errors['age']        = 'Age is required.';
    elseif (!is_numeric($values['age']) || $values['age'] < 0 || $values['age'] > 100)
                                      $errors['age']        = 'Enter a valid age (0–100).';
    if (empty($values['owner_name'])) $errors['owner_name'] = 'Owner name is required.';

    $imageName = null;
    if (!empty($_FILES['image']['name'])) {
        $allowed = ['image/jpeg','image/png','image/gif','image/webp'];
        $maxSize = 3 * 1024 * 1024;
        if (!in_array($_FILES['image']['type'], $allowed)) {
            $errors['image'] = 'Only JPG, PNG, GIF, or WEBP allowed.';
        } elseif ($_FILES['image']['size'] > $maxSize) {
            $errors['image'] = 'Image must be under 3 MB.';
        } else {
            $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $imageName = uniqid('pet_', true) . '.' . $ext;
            if (!is_dir('uploads/')) mkdir('uploads/', 0755, true);
            if (!move_uploaded_file($_FILES['image']['tmp_name'], 'uploads/' . $imageName)) {
                $errors['image'] = 'Upload failed. Check folder permissions.';
                $imageName = null;
            }
        }
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare("INSERT INTO pets (name,species,breed,age,owner_name,description,image) VALUES(?,?,?,?,?,?,?)");
        $stmt->execute([$values['name'],$values['species'],$values['breed'],$values['age'],$values['owner_name'],$values['description'],$imageName]);
        header("Location: index.php?msg=added");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Pet – PawRegistry</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    <style>
        :root { --primary:#1d4ed8; --accent:#1e40af; --light-bg:#eff6ff; --radius:16px; }
body { background:var(--light-bg); font-family:'Nunito',sans-serif; }
.page-header {
    background: linear-gradient(135deg,#1d4ed8,#1e3a8a);
    color:#fff; padding:2rem 0 1.8rem;
    clip-path: polygon(0 0,100% 0,100% 88%,0 100%);
    margin-bottom:2.5rem;
}
.page-header h1 { font-family:'Playfair Display',serif; font-size:2rem; }
.form-card {
    background:#fff; border-radius:var(--radius);
    box-shadow:0 6px 32px rgba(0,0,0,.1); padding:2.5rem;
    max-width:720px; margin:0 auto;
}
.form-label { font-weight:700; font-size:.9rem; }
.form-control, .form-select {
    border-radius:10px; border:2px solid #e5e7eb; padding:.6rem 1rem; transition:border-color .2s;
}
.form-control:focus, .form-select:focus { border-color:#1d4ed8; box-shadow:0 0 0 .2rem rgba(29,78,216,.2); }
.form-control.is-invalid { border-color:#dc3545; }
.btn-submit {
    background:#1e40af; color:#fff; border:none; border-radius:50px;
    padding:.65rem 2.2rem; font-weight:800; font-size:1rem;
}
.btn-submit:hover { background:#1e3a8a; color:#fff; }
.btn-back { background:#f3f4f6; color:#374151; border:none; border-radius:50px; padding:.65rem 1.8rem; font-weight:700; }
.img-preview-wrap {
    width:100%; height:180px; border-radius:12px;
    background:linear-gradient(135deg,#dbeafe,#bfdbfe);
    display:flex; align-items:center; justify-content:center;
    overflow:hidden; margin-top:.5rem; border:2px dashed #3b82f6;
}
.img-preview-wrap img { width:100%; height:100%; object-fit:cover; }
.img-preview-wrap .placeholder-icon { font-size:3.5rem; opacity:.35; }
.required-star { color:#1d4ed8; }
    </style>
</head>
<body>
<div class="page-header">
    <div class="container">
        <h1><i class="bi bi-plus-circle-fill me-2"></i>Add New Pet</h1>
        <p class="mb-0 opacity-75">Fill in the details below to register a new pet.</p>
    </div>
</div>

<div class="container pb-5">
    <div class="form-card">
        <?php if (!empty($errors)): ?>
        <div class="alert alert-danger d-flex align-items-start gap-2 rounded-3">
            <i class="bi bi-exclamation-triangle-fill mt-1"></i>
            <div><strong>Please fix the following:</strong>
                <ul class="mb-0 mt-1">
                    <?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?>
                </ul>
            </div>
        </div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data" novalidate>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Pet Name <span class="required-star">*</span></label>
                    <input type="text" name="name" class="form-control <?= isset($errors['name'])?'is-invalid':'' ?>"
                           placeholder="e.g. Buddy" maxlength="100" value="<?= htmlspecialchars($values['name']) ?>">
                    <?php if (isset($errors['name'])): ?><div class="invalid-feedback"><?= htmlspecialchars($errors['name']) ?></div><?php endif; ?>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Species <span class="required-star">*</span></label>
                    <select name="species" class="form-select <?= isset($errors['species'])?'is-invalid':'' ?>">
                        <option value="">-- Select species --</option>
                        <?php foreach (['Dog','Cat','Bird','Rabbit','Fish','Reptile','Hamster','Other'] as $s): ?>
                        <option value="<?= $s ?>" <?= $values['species']===$s?'selected':'' ?>><?= $s ?></option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (isset($errors['species'])): ?><div class="invalid-feedback"><?= htmlspecialchars($errors['species']) ?></div><?php endif; ?>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Breed <span class="required-star">*</span></label>
                    <input type="text" name="breed" class="form-control <?= isset($errors['breed'])?'is-invalid':'' ?>"
                           placeholder="e.g. Golden Retriever" maxlength="100" value="<?= htmlspecialchars($values['breed']) ?>">
                    <?php if (isset($errors['breed'])): ?><div class="invalid-feedback"><?= htmlspecialchars($errors['breed']) ?></div><?php endif; ?>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Age (years) <span class="required-star">*</span></label>
                    <input type="number" name="age" step="0.1" min="0" max="100"
                           class="form-control <?= isset($errors['age'])?'is-invalid':'' ?>"
                           placeholder="e.g. 3.5" value="<?= htmlspecialchars($values['age']) ?>">
                    <?php if (isset($errors['age'])): ?><div class="invalid-feedback"><?= htmlspecialchars($errors['age']) ?></div><?php endif; ?>
                </div>
                <div class="col-12">
                    <label class="form-label">Owner Name <span class="required-star">*</span></label>
                    <input type="text" name="owner_name" class="form-control <?= isset($errors['owner_name'])?'is-invalid':'' ?>"
                           placeholder="e.g. Juan dela Cruz" maxlength="100" value="<?= htmlspecialchars($values['owner_name']) ?>">
                    <?php if (isset($errors['owner_name'])): ?><div class="invalid-feedback"><?= htmlspecialchars($errors['owner_name']) ?></div><?php endif; ?>
                </div>
                <div class="col-12">
                    <label class="form-label">Description <span class="text-muted fw-normal">(optional)</span></label>
                    <textarea name="description" rows="3" class="form-control"
                              placeholder="Any notes about this pet…"><?= htmlspecialchars($values['description']) ?></textarea>
                </div>
                <div class="col-12">
                    <label class="form-label">Pet Photo <span class="text-muted fw-normal">(optional – JPG/PNG/GIF/WEBP, max 3 MB)</span></label>
                    <input type="file" name="image" id="imageInput"
                           class="form-control <?= isset($errors['image'])?'is-invalid':'' ?>" accept="image/*">
                    <?php if (isset($errors['image'])): ?><div class="invalid-feedback"><?= htmlspecialchars($errors['image']) ?></div><?php endif; ?>
                    <div class="img-preview-wrap" id="previewWrap">
                        <i class="bi bi-image placeholder-icon"></i>
                    </div>
                </div>
            </div>
            <hr class="my-4">
            <div class="d-flex gap-3 justify-content-end">
                <a href="index.php" class="btn btn-back"><i class="bi bi-arrow-left me-1"></i> Back</a>
                <button type="submit" class="btn btn-submit"><i class="bi bi-save2-fill me-1"></i> Save Pet</button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.getElementById('imageInput').addEventListener('change', function () {
        const wrap = document.getElementById('previewWrap');
        wrap.innerHTML = '';
        if (this.files && this.files[0]) {
            const img = document.createElement('img');
            img.src = URL.createObjectURL(this.files[0]);
            wrap.appendChild(img);
        } else {
            wrap.innerHTML = '<i class="bi bi-image placeholder-icon"></i>';
        }
    });
</script>
</body>
</html>
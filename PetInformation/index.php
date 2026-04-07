<?php
require_once 'db.php';
$pdo = getConnection();

if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = (int) $_GET['delete'];
    $stmt = $pdo->prepare("SELECT image FROM pets WHERE id = ?");
    $stmt->execute([$id]);
    $pet = $stmt->fetch();
    if ($pet && $pet['image'] && file_exists('uploads/' . $pet['image'])) {
        unlink('uploads/' . $pet['image']);
    }
    $stmt = $pdo->prepare("DELETE FROM pets WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: index.php?msg=deleted");
    exit;
}

$search = trim($_GET['search'] ?? '');
if ($search !== '') {
    $stmt = $pdo->prepare("SELECT * FROM pets WHERE name LIKE ? OR species LIKE ? OR breed LIKE ? OR owner_name LIKE ? ORDER BY created_at DESC");
    $like = "%$search%";
    $stmt->execute([$like, $like, $like, $like]);
} else {
    $stmt = $pdo->query("SELECT * FROM pets ORDER BY created_at DESC");
}
$pets = $stmt->fetchAll();

$msg = $_GET['msg'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🐾 PawRegistry – Pets CRUD</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    <style>
        :root {
    --primary: #1d4ed8;
    --secondary: #3b82f6;
    --accent: #1e40af;
    --dark: #1e1b4b;
    --light-bg: #eff6ff;
    --radius: 16px;
}
body { background-color: var(--light-bg); font-family: 'Nunito', sans-serif; color: var(--dark); }
.navbar-custom {
    background: linear-gradient(135deg, #1d4ed8, #1e3a8a);
    box-shadow: 0 4px 20px rgba(29,78,216,.35);
}
.navbar-brand { font-family: 'Playfair Display', serif; font-size: 1.6rem; color: #fff !important; }
.hero-strip {
    background: linear-gradient(135deg, #1d4ed8 0%, #3b82f6 100%);
    color: #fff; padding: 3rem 0 2.5rem; margin-bottom: 2.5rem;
    clip-path: polygon(0 0, 100% 0, 100% 85%, 0 100%);
}
.hero-strip h1 { font-family: 'Playfair Display', serif; font-size: 2.8rem; }
.pet-card {
    border: none; border-radius: var(--radius);
    box-shadow: 0 4px 18px rgba(0,0,0,.08);
    transition: transform .25s, box-shadow .25s; overflow: hidden;
}
.pet-card:hover { transform: translateY(-6px); box-shadow: 0 12px 32px rgba(0,0,0,.14); }
.pet-img-wrap {
    height: 200px; overflow: hidden;
    background: linear-gradient(135deg, #dbeafe, #bfdbfe);
    display: flex; align-items: center; justify-content: center;
}
.pet-img-wrap img { width: 100%; height: 100%; object-fit: cover; }
.pet-img-wrap .no-img-icon { font-size: 5rem; opacity: .35; }
.pet-card .card-title { font-family: 'Playfair Display', serif; font-size: 1.3rem; }
.badge-species {
    background: #1e40af; color: #fff; border-radius: 50px;
    font-size: .72rem; padding: .3em .8em; font-weight: 700; text-transform: uppercase;
}
.btn-add {
    background: #1e40af; color: #fff; border-radius: 50px;
    padding: .55rem 1.6rem; font-weight: 700; border: none;
}
.btn-add:hover { background: #1e3a8a; color: #fff; }
.btn-edit { background: #fff3cd; color: #856404; border: none; border-radius: 8px; font-size: .82rem; font-weight: 700; }
.btn-del  { background: #fee2e2; color: #b91c1c; border: none; border-radius: 8px; font-size: .82rem; font-weight: 700; }
.btn-edit:hover { background: #fde68a; }
.btn-del:hover  { background: #fca5a5; }
.search-box { border-radius: 50px; border: 2px solid #3b82f6; padding-left: 1.1rem; }
.search-box:focus { border-color: #1d4ed8; box-shadow: 0 0 0 .2rem rgba(29,78,216,.25); outline: none; }
.toast-alert {
    position: fixed; top: 80px; right: 20px; z-index: 9999;
    min-width: 260px; border-radius: 12px; box-shadow: 0 6px 24px rgba(0,0,0,.15);
    animation: slideIn .4s ease;
}
@keyframes slideIn { from { opacity:0; transform: translateX(80px); } to { opacity:1; transform: translateX(0); } }
footer { background: var(--dark); color: rgba(255,255,255,.55); font-size: .85rem; }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-custom">
    <div class="container">
        <a class="navbar-brand" href="index.php">🐾 PawRegistry</a>
        <div class="ms-auto">
            <a href="add.php" class="btn btn-add"><i class="bi bi-plus-circle-fill me-1"></i> Add Pet</a>
        </div>
    </div>
</nav>

<?php if ($msg === 'deleted'): ?>
<div class="alert alert-danger toast-alert d-flex align-items-center gap-2" id="toastMsg">
    <i class="bi bi-trash-fill"></i> Pet deleted successfully.
</div>
<?php elseif ($msg === 'added'): ?>
<div class="alert alert-success toast-alert d-flex align-items-center gap-2" id="toastMsg">
    <i class="bi bi-check-circle-fill"></i> Pet added successfully!
</div>
<?php elseif ($msg === 'updated'): ?>
<div class="alert alert-info toast-alert d-flex align-items-center gap-2" id="toastMsg">
    <i class="bi bi-pencil-fill"></i> Pet updated successfully!
</div>
<?php endif; ?>

<section class="hero-strip">
    <div class="container text-center">
        <h1>🐾 Pets Information Registry</h1>
        <p class="mt-2 mb-0 fs-5 opacity-75">Manage all your furry, feathery, and scaly friends in one place.</p>
    </div>
</section>

<div class="container pb-5">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <span class="fw-bold fs-5 text-muted">
            <i class="bi bi-grid-3x3-gap-fill me-1" style="color:var(--primary)"></i>
            <?= count($pets) ?> pet<?= count($pets) !== 1 ? 's' : '' ?> found
        </span>
        <form method="GET" class="d-flex gap-2" style="max-width:360px; width:100%;">
            <input type="text" name="search" class="form-control search-box"
                   placeholder="Search by name, species…" value="<?= htmlspecialchars($search) ?>">
            <button class="btn btn-add px-3" type="submit"><i class="bi bi-search"></i></button>
            <?php if ($search): ?>
                <a href="index.php" class="btn btn-outline-secondary rounded-pill px-3">✕</a>
            <?php endif; ?>
        </form>
    </div>

    <?php if (empty($pets)): ?>
        <div class="text-center py-5 text-muted">
            <i class="bi bi-emoji-frown" style="font-size:4rem; color:var(--secondary)"></i>
            <p class="mt-3 fs-5">No pets found. <a href="add.php">Add one now!</a></p>
        </div>
    <?php else: ?>
    <div class="row g-4">
        <?php foreach ($pets as $pet): ?>
        <div class="col-sm-6 col-lg-4 col-xl-3">
            <div class="card pet-card h-100">
                <div class="pet-img-wrap">
                    <?php if ($pet['image'] && file_exists('uploads/' . $pet['image'])): ?>
                        <img src="uploads/<?= htmlspecialchars($pet['image']) ?>" alt="<?= htmlspecialchars($pet['name']) ?>">
                    <?php else: ?>
                        <i class="bi bi-camera no-img-icon"></i>
                    <?php endif; ?>
                </div>
                <div class="card-body d-flex flex-column">
                    <div class="d-flex align-items-start justify-content-between mb-1">
                        <h5 class="card-title mb-0"><?= htmlspecialchars($pet['name']) ?></h5>
                        <span class="badge-species ms-2"><?= htmlspecialchars($pet['species']) ?></span>
                    </div>
                    <p class="text-muted small mb-1"><i class="bi bi-tag me-1"></i><?= htmlspecialchars($pet['breed']) ?></p>
                    <p class="text-muted small mb-1"><i class="bi bi-calendar3 me-1"></i><?= htmlspecialchars($pet['age']) ?> yr<?= $pet['age'] != 1 ? 's' : '' ?> old</p>
                    <p class="text-muted small mb-2"><i class="bi bi-person me-1"></i><?= htmlspecialchars($pet['owner_name']) ?></p>
                    <?php if ($pet['description']): ?>
                    <p class="small text-secondary flex-grow-1" style="font-size:.8rem;">
                        <?= nl2br(htmlspecialchars(mb_strimwidth($pet['description'], 0, 80, '…'))) ?>
                    </p>
                    <?php endif; ?>
                    <div class="d-flex gap-2 mt-3">
                        <a href="view.php?id=<?= $pet['id'] ?>" class="btn btn-sm btn-outline-secondary rounded-pill flex-fill">
                            <i class="bi bi-eye"></i> View
                        </a>
                        <a href="edit.php?id=<?= $pet['id'] ?>" class="btn btn-sm btn-edit flex-fill">
                            <i class="bi bi-pencil"></i> Edit
                        </a>
                        <a href="index.php?delete=<?= $pet['id'] ?>" class="btn btn-sm btn-del flex-fill"
                           onclick="return confirm('Delete <?= htmlspecialchars(addslashes($pet['name'])) ?>?')">
                            <i class="bi bi-trash"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<footer class="text-center py-4 mt-5">
    <p class="mb-0">🐾 PawRegistry &mdash; PHP CRUD with PDO &amp; Bootstrap 5</p>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const toast = document.getElementById('toastMsg');
    if (toast) setTimeout(() => toast.style.display = 'none', 3500);
</script>
</body>
</html>
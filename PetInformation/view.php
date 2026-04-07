<?php
require_once 'db.php';
$pdo = getConnection();

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) { header("Location: index.php"); exit; }
$id = (int) $_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM pets WHERE id = ?");
$stmt->execute([$id]);
$pet = $stmt->fetch();
if (!$pet) { header("Location: index.php"); exit; }

$speciesEmoji = ['Dog'=>'🐶','Cat'=>'🐱','Bird'=>'🐦','Rabbit'=>'🐰','Fish'=>'🐟','Reptile'=>'🦎','Hamster'=>'🐹','Other'=>'🐾'];
$emoji = $speciesEmoji[$pet['species']] ?? '🐾';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pet['name']) ?> – PawRegistry</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    <style>
        :root { --primary:#1d4ed8; --accent:#1e40af; --light-bg:#eff6ff; --radius:20px; }
body { background:var(--light-bg); font-family:'Nunito',sans-serif; }
.hero-banner {
    background:linear-gradient(135deg,#1e40af,#1e3a8a);
    color:#fff; padding:2.5rem 0 8rem;
    clip-path:polygon(0 0,100% 0,100% 78%,0 100%);
}
.pet-profile-card {
    background:#fff; border-radius:var(--radius);
    box-shadow:0 10px 40px rgba(0,0,0,.12);
    margin-top:-5rem; max-width:780px;
    margin-left:auto; margin-right:auto; overflow:hidden;
}
.pet-image-section {
    height:320px; background:linear-gradient(135deg,#dbeafe,#bfdbfe);
    display:flex; align-items:center; justify-content:center; overflow:hidden;
}
.pet-image-section img { width:100%; height:100%; object-fit:cover; }
.pet-image-section .big-emoji { font-size:6rem; }
.pet-info { padding:2rem 2.5rem; }
.pet-name { font-family:'Playfair Display',serif; font-size:2.4rem; color:#1e1b4b; margin-bottom:.3rem; }
.badge-species { background:#1e40af; color:#fff; border-radius:50px; font-size:.8rem; padding:.35em 1em; font-weight:700; text-transform:uppercase; }
.detail-grid { display:grid; grid-template-columns:1fr 1fr; gap:1rem; margin-top:1.5rem; }
.detail-item { background:#eff6ff; border-radius:12px; padding:1rem 1.2rem; border-left:4px solid #1d4ed8; }
.detail-item .label { font-size:.75rem; font-weight:800; color:#9ca3af; text-transform:uppercase; letter-spacing:.08em; }
.detail-item .value { font-size:1.05rem; font-weight:700; color:#1e1b4b; margin-top:.2rem; }
.description-box { background:#eff6ff; border-radius:12px; padding:1.2rem 1.4rem; border-left:4px solid #3b82f6; margin-top:1.2rem; }
.btn-edit { background:#fef3c7; color:#92400e; border:none; border-radius:50px; padding:.6rem 1.8rem; font-weight:800; text-decoration:none; }
.btn-del  { background:#fee2e2; color:#b91c1c; border:none; border-radius:50px; padding:.6rem 1.8rem; font-weight:800; text-decoration:none; }
.btn-back { background:#f3f4f6; color:#374151; border:none; border-radius:50px; padding:.6rem 1.8rem; font-weight:700; text-decoration:none; }
.btn-edit:hover { background:#fde68a; }
.btn-del:hover  { background:#fca5a5; }
.btn-back:hover { background:#e5e7eb; }
    </style>
</head>
<body>
<div class="hero-banner">
    <div class="container">
        <a href="index.php" class="text-white-50 text-decoration-none"><i class="bi bi-arrow-left me-1"></i> Back to all pets</a>
        <h2 class="mt-3" style="font-family:'Playfair Display',serif;"><?= $emoji ?> <?= htmlspecialchars($pet['name']) ?></h2>
        <p class="opacity-75 mb-0">Pet Detail View &mdash; ID #<?= $pet['id'] ?></p>
    </div>
</div>

<div class="container pb-5">
    <div class="pet-profile-card">
        <div class="pet-image-section">
            <?php if ($pet['image'] && file_exists('uploads/' . $pet['image'])): ?>
                <img src="uploads/<?= htmlspecialchars($pet['image']) ?>" alt="<?= htmlspecialchars($pet['name']) ?>">
            <?php else: ?>
                <span class="big-emoji"><?= $emoji ?></span>
            <?php endif; ?>
        </div>
        <div class="pet-info">
            <div class="d-flex align-items-start justify-content-between flex-wrap gap-2">
                <div>
                    <h1 class="pet-name"><?= htmlspecialchars($pet['name']) ?></h1>
                    <span class="badge-species"><?= htmlspecialchars($pet['species']) ?></span>
                </div>
                <small class="text-muted mt-2">Registered: <?= date('M d, Y', strtotime($pet['created_at'])) ?></small>
            </div>
            <div class="detail-grid">
                <div class="detail-item">
                    <div class="label"><i class="bi bi-tag me-1"></i>Breed</div>
                    <div class="value"><?= htmlspecialchars($pet['breed']) ?></div>
                </div>
                <div class="detail-item">
                    <div class="label"><i class="bi bi-calendar3 me-1"></i>Age</div>
                    <div class="value"><?= htmlspecialchars($pet['age']) ?> year<?= $pet['age'] != 1 ? 's' : '' ?></div>
                </div>
                <div class="detail-item" style="grid-column:1/-1">
                    <div class="label"><i class="bi bi-person me-1"></i>Owner</div>
                    <div class="value"><?= htmlspecialchars($pet['owner_name']) ?></div>
                </div>
            </div>
            <?php if ($pet['description']): ?>
            <div class="description-box">
                <div style="font-size:.75rem;font-weight:800;color:#9ca3af;text-transform:uppercase;letter-spacing:.08em;" class="mb-1">
                    <i class="bi bi-chat-quote me-1"></i>Description
                </div>
                <p class="mb-0" style="line-height:1.6;"><?= nl2br(htmlspecialchars($pet['description'])) ?></p>
            </div>
            <?php endif; ?>
            <div class="d-flex gap-3 mt-4 flex-wrap">
                <a href="index.php" class="btn-back"><i class="bi bi-grid me-1"></i> All Pets</a>
                <a href="edit.php?id=<?= $pet['id'] ?>" class="btn-edit"><i class="bi bi-pencil me-1"></i> Edit</a>
                <a href="index.php?delete=<?= $pet['id'] ?>" class="btn-del"
                   onclick="return confirm('Delete <?= htmlspecialchars(addslashes($pet['name'])) ?>?')">
                    <i class="bi bi-trash me-1"></i> Delete
                </a>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
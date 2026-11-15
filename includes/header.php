<?php /**
 * includes/header.php - top of every page: nav bar (when logged in) + flash/error messages.
 * Set $title before including.
 */
$flash  = $_SESSION['flash']  ?? null;
$errors = $_SESSION['errors'] ?? [];
unset($_SESSION['flash'], $_SESSION['errors']);
?><!DOCTYPE html>
<html lang="en"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($title ?? 'Data Consulting') ?></title>
<link rel="stylesheet" href="css/style.css">
</head><body>
<?php if (is_logged_in()): ?>
<nav class="topnav">
    <a class="brand" href="index.php">Data Consulting</a>
    <span class="who">Logged in as <strong><?= e($_SESSION['username']) ?></strong> (<?= e($_SESSION['full_name']) ?>)</span>
    <form action="logout.php" method="post"><?= csrf_field() ?><button class="btn" type="submit">Logout</button></form>
</nav>
<?php endif; ?>
<main>
<?php if ($flash): ?><div class="alert <?= e($flash['type']) ?>"><?= e($flash['message']) ?></div><?php endif; ?>
<?php if ($errors): ?><div class="alert error"><ul><?php foreach ($errors as $x): ?><li><?= e($x) ?></li><?php endforeach; ?></ul></div><?php endif; ?>

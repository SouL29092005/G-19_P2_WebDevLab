<?php

declare(strict_types=1);

// Shared document head and role-specific navigation; keep route links consistent with APP_BASE_URL.
$pageTitle = $pageTitle ?? APP_NAME;
$baseUrl = rtrim(APP_BASE_URL, '/');

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Laboratory Computer Reservation System">
    <link rel="stylesheet" href="<?= htmlspecialchars($baseUrl . '/assets/css/style.css?v=' . filemtime(PUBLIC_PATH . '/assets/css/style.css'), ENT_QUOTES, 'UTF-8') ?>">
    <title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></title>
</head>
<body>
    <header class="site-header">
        <h1><a href="<?= htmlspecialchars($baseUrl . '/', ENT_QUOTES, 'UTF-8') ?>" style="color:inherit;text-decoration:none;"><?= htmlspecialchars(APP_NAME, ENT_QUOTES, 'UTF-8') ?></a></h1>

        <?php if (isset($_SESSION['user']['role'])): ?>
            <nav class="header-navigation" aria-label="Account navigation">
                <?php if ($_SESSION['user']['role'] === 'admin'): ?>
                    <a href="<?= htmlspecialchars($baseUrl . '/admin/', ENT_QUOTES, 'UTF-8') ?>">Admin Dashboard</a>
                <?php else: ?>
                    <a href="<?= htmlspecialchars($baseUrl . '/dashboard.php', ENT_QUOTES, 'UTF-8') ?>">Dashboard</a>
                    <a href="<?= htmlspecialchars($baseUrl . '/my-bookings.php', ENT_QUOTES, 'UTF-8') ?>">My Bookings</a>
                <?php endif; ?>
                <a class="logout-button" href="<?= htmlspecialchars($baseUrl . '/login.php?action=logout', ENT_QUOTES, 'UTF-8') ?>">Logout</a>
            </nav>
        <?php endif; ?>
    </header>
    <main>

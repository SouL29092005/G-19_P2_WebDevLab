<?php

declare(strict_types=1);

$pageTitle = $pageTitle ?? APP_NAME;

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <meta
        name="description"
        content="Laboratory Computer Reservation System"
    >

    <link
        rel="stylesheet"
        href="<?= htmlspecialchars(
            rtrim(APP_BASE_URL, '/')
                . '/assets/css/style.css?v='
                . filemtime(PUBLIC_PATH . '/assets/css/style.css'),
            ENT_QUOTES,
            'UTF-8'
        ) ?>"
    >

    <title>
        <?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?>
    </title>
</head>

<body>
    <header class="site-header">
        <h1>
            <?= htmlspecialchars(APP_NAME, ENT_QUOTES, 'UTF-8') ?>
        </h1>

        <?php if (isset($_SESSION['user']['role'])): ?>
            <nav
                class="header-navigation"
                aria-label="Account navigation"
            >
                <?php if ($_SESSION['user']['role'] === 'admin'): ?>
                    <a
                        href="<?= htmlspecialchars(
                            rtrim(APP_BASE_URL, '/') . '/admin/',
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>"
                    >
                        Admin Dashboard
                    </a>
                <?php else: ?>
                    <a
                        href="<?= htmlspecialchars(
                            rtrim(APP_BASE_URL, '/') . '/dashboard.php',
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>"
                    >
                        Dashboard
                    </a>

                    <a
                        href="<?= htmlspecialchars(
                            rtrim(APP_BASE_URL, '/') . '/my-bookings.php',
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>"
                    >
                        My Bookings
                    </a>
                <?php endif; ?>

                <a
                    class="logout-button"
                    href="<?= htmlspecialchars(
                        rtrim(APP_BASE_URL, '/') . '/login.php?action=logout',
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>"
                >
                    Logout
                </a>
            </nav>
        <?php endif; ?>
    </header>

    <main>
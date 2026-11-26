<?php

declare(strict_types=1);

$pageTitle = $pageTitle ?? APP_NAME;
$baseUrl = rtrim(APP_BASE_URL, '/');
$currentUser = is_array($_SESSION['user'] ?? null) ? $_SESSION['user'] : null;
$userRole = is_string($currentUser['role'] ?? null) ? $currentUser['role'] : null;
$userName = is_string($currentUser['name'] ?? null) ? $currentUser['name'] : 'Account';
$userInitial = strtoupper(substr($userName, 0, 1));

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="A calmer way to reserve a laboratory computer.">
    <meta name="theme-color" content="#f5f7fb">
    <script>
        (() => {
            document.documentElement.classList.add('js');
            try {
                const savedTheme = localStorage.getItem('lab-theme');
                document.documentElement.dataset.theme = savedTheme === 'dark' ? 'dark' : 'light';
            } catch (error) {
                document.documentElement.dataset.theme = 'light';
            }
        })();
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="<?= htmlspecialchars($baseUrl . '/assets/css/style.css?v=' . filemtime(PUBLIC_PATH . '/assets/css/style.css'), ENT_QUOTES, 'UTF-8') ?>">
    <script defer src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <?php if ($pageTitle === 'Admin Dashboard'): ?>
    <script defer src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
    <?php endif; ?>
    <title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?> · Labspace</title>
</head>
<body data-base-url="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>">
    <div class="scroll-progress" id="scroll-progress" aria-hidden="true"></div>
    <header class="site-header">
        <div class="header-inner">
            <a class="brand" href="<?= htmlspecialchars($baseUrl . '/', ENT_QUOTES, 'UTF-8') ?>" aria-label="Labspace home">
                <span class="brand-mark" aria-hidden="true"><i class="fa-solid fa-cube"></i></span>
                <span class="brand-name">labspace<span class="brand-period">.</span></span>
            </a>

            <button class="nav-toggle icon-button" id="nav-toggle" type="button" aria-expanded="false" aria-controls="primary-navigation" aria-label="Open navigation">
                <i class="fa-solid fa-bars" aria-hidden="true"></i>
            </button>

            <nav class="site-nav" id="primary-navigation" aria-label="Primary navigation">
                <?php if ($userRole === 'admin'): ?>
                    <a href="<?= htmlspecialchars($baseUrl . '/admin/', ENT_QUOTES, 'UTF-8') ?>">Overview</a>
                    <a href="<?= htmlspecialchars($baseUrl . '/admin/computers', ENT_QUOTES, 'UTF-8') ?>">Computers</a>
                    <a href="<?= htmlspecialchars($baseUrl . '/admin/users', ENT_QUOTES, 'UTF-8') ?>">Users</a>
                    <a href="<?= htmlspecialchars($baseUrl . '/admin/bookings', ENT_QUOTES, 'UTF-8') ?>">Bookings</a>
                <?php elseif ($userRole === 'student'): ?>
                    <a href="<?= htmlspecialchars($baseUrl . '/dashboard.php', ENT_QUOTES, 'UTF-8') ?>">Overview</a>
                    <a href="<?= htmlspecialchars($baseUrl . '/availability', ENT_QUOTES, 'UTF-8') ?>">Availability</a>
                    <a href="<?= htmlspecialchars($baseUrl . '/new-booking.php', ENT_QUOTES, 'UTF-8') ?>">New booking</a>
                    <a href="<?= htmlspecialchars($baseUrl . '/my-bookings.php', ENT_QUOTES, 'UTF-8') ?>">My bookings</a>
                <?php else: ?>
                    <a href="<?= htmlspecialchars($baseUrl . '/availability', ENT_QUOTES, 'UTF-8') ?>">Availability</a>
                    <a href="<?= htmlspecialchars($baseUrl . '/#how-it-works', ENT_QUOTES, 'UTF-8') ?>">How it works</a>
                <?php endif; ?>
            </nav>

            <div class="header-actions">
                <button class="icon-button theme-toggle" type="button" data-theme-toggle aria-label="Switch to dark mode" title="Toggle theme">
                    <i class="fa-regular fa-moon" aria-hidden="true"></i>
                </button>
                <?php if ($currentUser !== null): ?>
                    <div class="account-menu">
                        <button class="account-trigger" id="account-menu-toggle" type="button" aria-expanded="false" aria-controls="account-menu">
                            <span class="avatar" aria-hidden="true"><?= htmlspecialchars($userInitial, ENT_QUOTES, 'UTF-8') ?></span>
                            <span class="account-name"><?= htmlspecialchars($userName, ENT_QUOTES, 'UTF-8') ?></span>
                            <i class="fa-solid fa-chevron-down" aria-hidden="true"></i>
                        </button>
                        <div class="account-dropdown" id="account-menu" hidden>
                            <span class="account-email"><?= htmlspecialchars((string) ($currentUser['email'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span>
                            <a class="dropdown-link" href="<?= htmlspecialchars($baseUrl . '/login.php?action=logout', ENT_QUOTES, 'UTF-8') ?>">
                                <i class="fa-solid fa-arrow-right-from-bracket" aria-hidden="true"></i> Sign out
                            </a>
                        </div>
                    </div>
                <?php else: ?>
                    <a class="button button-primary header-signin" href="<?= htmlspecialchars($baseUrl . '/login.php', ENT_QUOTES, 'UTF-8') ?>">Sign in <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
                <?php endif; ?>
            </div>
        </div>
    </header>
    <main id="main-content">

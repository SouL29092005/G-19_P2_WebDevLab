<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/config/app.php';

// Login, logout, and role-based redirects share one session-aware entry point.
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once CONFIG_PATH . '/database.php';
require_once APP_PATH . '/controllers/AuthController.php';

$controller = new AuthController($pdo);

// Logout is intentionally dispatched before the already-authenticated redirect below.
if (($_GET['action'] ?? '') === 'logout') {
    $controller->logout();
}

if (isset($_SESSION['user']['role'])) {
    $role = $_SESSION['user']['role'];
    $destination = $role === 'admin'
        ? rtrim(APP_BASE_URL, '/') . '/admin/'
        : rtrim(APP_BASE_URL, '/') . '/dashboard.php';

    header('Location: ' . $destination, true, 303);
    exit;
}

try {
    // The controller validates credentials and returns only safe view data on failed login attempts.
    $viewData = $controller->login();
    $pageTitle = $viewData['pageTitle'];
    extract($viewData, EXTR_SKIP);

    require APP_PATH . '/views/layouts/header.php';
    require APP_PATH . '/views/login.php';
    require APP_PATH . '/views/layouts/footer.php';
} catch (Throwable $exception) {
    error_log('Login request failed: ' . $exception->getMessage());
    http_response_code(500);

    require APP_PATH . '/views/layouts/header.php';
    ?>
    <section class="error-page">
        <h2>Login is temporarily unavailable</h2>
        <p>Please try again later.</p>
    </section>
    <?php
    require APP_PATH . '/views/layouts/footer.php';
}

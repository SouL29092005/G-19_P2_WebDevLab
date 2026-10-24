<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/config/app.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if (!isset($_SESSION['user']['role'])) {
    header(
        'Location: ' . rtrim(APP_BASE_URL, '/') . '/login.php',
        true,
        303
    );

    exit;
}

if ($_SESSION['user']['role'] !== 'student') {
    header(
        'Location: ' . rtrim(APP_BASE_URL, '/') . '/admin/',
        true,
        303
    );

    exit;
}

require_once CONFIG_PATH . '/database.php';
require_once APP_PATH . '/controllers/StudentController.php';

try {
    $controller = new StudentController(
        new BookingRepository($pdo)
    );

    $data = $controller->dashboard();

    extract($data, EXTR_SKIP);

    $pageTitle = $data['pageTitle'];

    require APP_PATH . '/views/layouts/header.php';
    require APP_PATH . '/views/student/dashboard.php';
    require APP_PATH . '/views/layouts/footer.php';

} catch (Throwable $e) {
    error_log('Student dashboard failed: ' . $e->getMessage());

    http_response_code(500);

    require APP_PATH . '/views/layouts/header.php';

    echo '<section class="error-page">';
    echo '<h2>Dashboard unavailable</h2>';
    echo '<p>Please try again later.</p>';
    echo '</section>';

    require APP_PATH . '/views/layouts/footer.php';
}

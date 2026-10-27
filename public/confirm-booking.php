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
    $controller = new StudentController(new BookingRepository($pdo));
    $data = $controller->confirmBooking();
    extract($data, EXTR_SKIP);
    $pageTitle = $data['pageTitle'];

    require APP_PATH . '/views/layouts/header.php';
    require APP_PATH . '/views/student/confirm-booking.php';
    require APP_PATH . '/views/layouts/footer.php';
} catch (Throwable $exception) {
    error_log('Booking confirmation failed: ' . $exception->getMessage());
    http_response_code(500);

    require APP_PATH . '/views/layouts/header.php';
    ?>
    <section class="error-page">
        <h2>Confirmation unavailable</h2>
        <p><?= APP_DEBUG
            ? htmlspecialchars($exception->getMessage(), ENT_QUOTES, 'UTF-8')
            : 'Please return to New Booking and try again.' ?></p>
        <a href="<?= htmlspecialchars(
            rtrim(APP_BASE_URL, '/') . '/new-booking.php',
            ENT_QUOTES,
            'UTF-8'
        ) ?>">Return to New Booking</a>
    </section>
    <?php
    require APP_PATH . '/views/layouts/footer.php';
}

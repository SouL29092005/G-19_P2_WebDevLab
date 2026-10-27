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

    if (
        ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'GET'
        && ($_GET['ajax'] ?? '') === 'availability'
    ) {
        header('Content-Type: application/json; charset=utf-8');

        try {
            echo json_encode(
                $controller->availability(),
                JSON_THROW_ON_ERROR
            );
        } catch (InvalidArgumentException $exception) {
            http_response_code(422);
            echo json_encode(
                ['error' => $exception->getMessage()],
                JSON_THROW_ON_ERROR
            );
        }

        exit;
    }

    $data = $controller->newBooking();
} catch (Throwable $exception) {
    error_log('New booking failed: ' . $exception->getMessage());
    http_response_code(500);
    $data = [
        'pageTitle' => 'New Booking',
        'selectedDate' => date('Y-m-d'),
        'minDate' => date('Y-m-d'),
        'maxDate' => date('Y-m-d', strtotime('+3 days')),
        'timeSlots' => [],
        'selectedSlotIds' => [],
        'availableComputers' => [],
        'csrfToken' => '',
        'error' => APP_DEBUG
            ? 'Booking error: ' . $exception->getMessage()
            : 'Booking is unavailable right now. Please try again later.',
        'success' => null,
    ];
}

extract($data, EXTR_SKIP);
$pageTitle = $data['pageTitle'];

require APP_PATH . '/views/layouts/header.php';
require APP_PATH . '/views/student/new-booking.php';
require APP_PATH . '/views/layouts/footer.php';

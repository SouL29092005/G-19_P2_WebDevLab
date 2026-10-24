<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/config/app.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$requestMethod = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
$requestUri = $_SERVER['REQUEST_URI'] ?? '/';

$requestPath = parse_url($requestUri, PHP_URL_PATH);

if (!is_string($requestPath) || $requestPath === '') {
    $requestPath = '/';
}

$basePath = parse_url(APP_BASE_URL, PHP_URL_PATH);

if (is_string($basePath) && $basePath !== '' && $basePath !== '/') {
    $basePath = rtrim($basePath, '/');

    if (
        $requestPath === $basePath
        || str_starts_with($requestPath, $basePath . '/')
    ) {
        $requestPath = substr($requestPath, strlen($basePath));
    }
}

$requestPath = '/' . trim($requestPath, '/');

error_log('Request path: ' . $requestPath);
error_log('Request URI: ' . $requestUri);
error_log('App base URL: ' . APP_BASE_URL);

if ($requestPath === '') {
    $requestPath = '/';
}

$routes = [
    'GET' => [
        '/' => 'home',
        '/login' => 'login',
        '/availability' => 'availability',
    ],
    'POST' => [
        '/login' => 'login',
    ],
];

error_log('DEBUG method: ' . $requestMethod);
error_log('DEBUG path: ' . $requestPath);
error_log('DEBUG URI: ' . $requestUri);

$route = $routes[$requestMethod][$requestPath] ?? null;

$viewFile = null;
$viewData = [];
$pageTitle = APP_NAME;

if ($route === null) {
    http_response_code(404);
    $pageTitle = 'Page Not Found';
} else {
    try {
        switch ($route) {
            case 'home':
                $viewFile = APP_PATH . '/views/home.php';
                break;

            case 'login':
                if (isset($_SESSION['user']['role'])) {
                    $role = $_SESSION['user']['role'];
                    $destination = $role === 'admin'
                        ? rtrim(APP_BASE_URL, '/') . '/admin/'
                        : rtrim(APP_BASE_URL, '/') . '/dashboard.php';

                    header('Location: ' . $destination, true, 303);
                    exit;
                }

                require_once CONFIG_PATH . '/database.php';
                require_once APP_PATH
                    . '/controllers/AuthController.php';

                $authController = new AuthController($pdo);
                $viewData = $authController->login();
                $pageTitle = $viewData['pageTitle'];
                $viewFile = APP_PATH . '/views/login.php';
                break;

            case 'availability':
                require_once CONFIG_PATH . '/database.php';

                require_once APP_PATH
                    . '/repositories/ComputerRepository.php';

                require_once APP_PATH
                    . '/controllers/ComputerController.php';

                $repository = new ComputerRepository($pdo);
                $controller = new ComputerController($repository);

                $viewData = $controller->index();
                $pageTitle = $viewData['pageTitle'];
                $viewFile = APP_PATH . '/views/availability.php';
                break;
        }

        if ($viewFile === null || !is_file($viewFile)) {
            http_response_code(500);
            $pageTitle = 'Internal Server Error';
            $viewFile = null;
        } else {
            http_response_code(200);
        }
    } catch (Throwable $exception) {
        error_log(
            'Application request failed: ' . $exception->getMessage()
        );

        http_response_code(500);
        $pageTitle = 'Internal Server Error';
        $viewFile = null;
    }
}

require APP_PATH . '/views/layouts/header.php';

if ($viewFile !== null) {
    extract($viewData, EXTR_SKIP);
    require $viewFile;
} elseif (http_response_code() === 404) {
    ?>
    <section class="error-page">
        <h2>404 - Page Not Found</h2>
        <p>The requested page does not exist yet.</p>
        <a href="<?= htmlspecialchars(
            rtrim(APP_BASE_URL, '/') . '/',
            ENT_QUOTES,
            'UTF-8'
        ) ?>">Return to Home</a>
    </section>
    <?php
} else {
    ?>
    <section class="error-page">
        <h2>500 - Internal Server Error</h2>
        <p>The requested page could not be loaded.</p>
    </section>
    <?php
}

require APP_PATH . '/views/layouts/footer.php';

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

if ($requestPath === '') {
    $requestPath = '/';
}

$routes = [
    'GET' => [
        '/' => 'home',
    ],
];

$route = $routes[$requestMethod][$requestPath] ?? null;

if ($route === null) {
    http_response_code(404);
    $pageTitle = 'Page Not Found';
    $viewFile = null;
} else {
    http_response_code(200);
    $pageTitle = APP_NAME;

    $viewFile = match ($route) {
        'home' => APP_PATH . '/views/home.php',
        default => null,
    };
}

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
    <title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></title>
</head>
<body>
    <header>
        <h1><?= htmlspecialchars(APP_NAME, ENT_QUOTES, 'UTF-8') ?></h1>
    </header>

    
    <main>
        <?php if ($viewFile !== null && is_file($viewFile)): ?>
            <?php require $viewFile; ?>
        <?php else: ?>
            <h2>404 - Page Not Found</h2>
            <p>The requested page does not exist yet.</p>
            <a href="<?= htmlspecialchars(
                rtrim(APP_BASE_URL, '/') . '/',
                ENT_QUOTES,
                'UTF-8'
            ) ?>">Return to Home</a>
        <?php endif; ?>
    </main>

</body>
</html>

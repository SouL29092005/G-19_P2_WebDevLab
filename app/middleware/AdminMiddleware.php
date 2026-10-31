<?php

declare(strict_types=1);

/** Blocks admin routes unless the session belongs to an authenticated administrator. */
final class AdminMiddleware
{
    /** Redirect anonymous visitors to login; return a 403 page for authenticated non-admin users. */
    public static function handle(): void
    {
        $user = $_SESSION['user'] ?? null;

        if (!is_array($user)) {
            header(
                'Location: ' . rtrim(APP_BASE_URL, '/') . '/login',
                true,
                303
            );
            exit;
        }

        if (($user['role'] ?? null) !== 'admin') {
            http_response_code(403);

            echo '<!DOCTYPE html>
            <html lang="en">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport"
                    content="width=device-width, initial-scale=1.0">
                <title>403 - Forbidden</title>
            </head>
            <body>
                <h1>403 - Forbidden</h1>
                <p>You do not have permission to access this page.</p>
                <a href="' .
                    htmlspecialchars(
                        rtrim(APP_BASE_URL, '/') . '/',
                        ENT_QUOTES,
                        'UTF-8'
                    ) .
                '">Return to Home</a>
            </body>
            </html>';

            exit;
        }
    }
}

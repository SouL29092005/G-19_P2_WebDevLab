<?php

declare(strict_types=1);

final class AuthController
{
    public function __construct(
        private readonly PDO $pdo
    ) {
    }

    public function login(): array
    {
        $email = '';
        $error = null;

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            return $this->viewData($email, $error);
        }

        if (!$this->isValidCsrfToken()) {
            return $this->viewData($email, 'Your login session expired. Please try again.');
        }

        $emailInput = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        $email = is_string($emailInput)
            ? trim($emailInput)
            : '';
        $password = is_string($password)
            ? $password
            : '';

        if ($email === '' || $password === '') {
            return $this->viewData($email, 'Enter your email and password.');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->viewData($email, 'Enter a valid email address.');
        }

        $statement = $this->pdo->prepare(
            'SELECT user_id, full_name, email, password_hash, role
             FROM users
             WHERE lower(email) = lower(:email)
             LIMIT 1'
        );
        $statement->execute(['email' => $email]);
        $user = $statement->fetch();

        if (
            !is_array($user)
            || !password_verify($password, (string) $user['password_hash'])
        ) {
            return $this->viewData($email, 'Invalid email or password.');
        }

        $role = (string) $user['role'];

        if (!in_array($role, ['student', 'admin'], true)) {
            return $this->viewData($email, 'This account has an invalid role.');
        }

        session_regenerate_id(true);
        $_SESSION['user'] = [
            'id' => (int) $user['user_id'],
            'name' => (string) $user['full_name'],
            'email' => (string) $user['email'],
            'role' => $role,
        ];

        $destination = $role === 'admin'
            ? rtrim(APP_BASE_URL, '/') . '/admin/'
            : rtrim(APP_BASE_URL, '/') . '/dashboard.php';

        header('Location: ' . $destination, true, 303);
        exit;
    }

    public function logout(): void
    {
        $_SESSION = [];

        if (ini_get('session.use_cookies') === '1') {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        session_destroy();
        header(
            'Location: ' . rtrim(APP_BASE_URL, '/') . '/login.php',
            true,
            303
        );
        exit;
    }

    private function viewData(string $email, ?string $error): array
    {
        return [
            'pageTitle' => 'Login',
            'email' => $email,
            'error' => $error,
            'csrfToken' => $this->getCsrfToken(),
        ];
    }

    private function getCsrfToken(): string
    {
        if (!isset($_SESSION['csrf_token']) || !is_string($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['csrf_token'];
    }

    private function isValidCsrfToken(): bool
    {
        $submitted = $_POST['csrf_token'] ?? null;
        $sessionToken = $_SESSION['csrf_token'] ?? null;

        return is_string($submitted)
            && is_string($sessionToken)
            && hash_equals($sessionToken, $submitted);
    }
}

<?php

declare(strict_types=1);

final class UserController
{
    public function __construct(
        private readonly UserRepository $repository
    ) {
    }

    public function index(): array
    {
        $error = null;
        $success = null;

        $formData = [
            'full_name' => '',
            'email' => '',
            'role' => 'student',
        ];

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            if (!$this->isValidCsrfToken()) {
                http_response_code(403);

                $error = 'Your session expired or the security token is invalid. Please reload the page and try again.';
            } else {
                $action = $_POST['action'] ?? '';

                if (!is_string($action)) {
                    $action = '';
                }

                if ($action === 'create') {
                    $result = $this->createUser();
                    $error = $result['error'];
                    $success = $result['success'];
                    $formData = $result['formData'];
                } elseif ($action === 'delete') {
                    $result = $this->deleteUser();
                    $error = $result['error'];
                    $success = $result['success'];
                } else {
                    http_response_code(400);
                    $error = 'Invalid user-management action.';
                }
            }
        }

        return [
            'pageTitle' => 'Manage Users',
            'users' => $this->repository->findAll(),
            'error' => $error,
            'success' => $success,
            'formData' => $formData,
            'csrfToken' => $this->getCsrfToken(),
            'currentUserId' => (int) ($_SESSION['user']['id'] ?? 0),
        ];
    }

    private function createUser(): array
    {
        $fullNameInput = $_POST['full_name'] ?? '';
        $emailInput = $_POST['email'] ?? '';
        $passwordInput = $_POST['password'] ?? '';
        $roleInput = $_POST['role'] ?? 'student';

        $fullName = is_string($fullNameInput)
            ? trim($fullNameInput)
            : '';

        $email = is_string($emailInput)
            ? trim($emailInput)
            : '';

        $password = is_string($passwordInput)
            ? $passwordInput
            : '';

        $role = is_string($roleInput)
            ? $roleInput
            : '';

        $formData = [
            'full_name' => $fullName,
            'email' => $email,
            'role' => $role,
        ];

        if ($fullName === '' || $email === '' || $password === '') {
            return [
                'error' => 'Name, email, and password are required.',
                'success' => null,
                'formData' => $formData,
            ];
        }

        if (strlen($fullName) > 100) {
            return [
                'error' => 'The name must not exceed 100 characters.',
                'success' => null,
                'formData' => $formData,
            ];
        }

        if (strlen($email) > 255 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return [
                'error' => 'Enter a valid email address of at most 255 characters.',
                'success' => null,
                'formData' => $formData,
            ];
        }

        $minPasswordLength = 6;
        $maxPasswordLength = 4096;

        if (
            strlen($password) < $minPasswordLength ||
            strlen($password) > $maxPasswordLength
        ) {
            $errorMessage = "Password must be between {$minPasswordLength} and {$maxPasswordLength} characters.";
        }

        if (!in_array($role, ['student', 'admin'], true)) {
            return [
                'error' => 'Select a valid user role.',
                'success' => null,
                'formData' => $formData,
            ];
        }

        try {
            if ($this->repository->emailExists($email)) {
                return [
                    'error' => 'An account with this email address already exists.',
                    'success' => null,
                    'formData' => $formData,
                ];
            }

            $passwordHash = password_hash(
                $password,
                PASSWORD_DEFAULT
            );

            if (!is_string($passwordHash)) {
                throw new RuntimeException('Password hashing failed.');
            }

            $this->repository->create(
                $fullName,
                $email,
                $passwordHash,
                $role
            );

            return [
                'error' => null,
                'success' => 'User created successfully.',
                'formData' => [
                    'full_name' => '',
                    'email' => '',
                    'role' => 'student',
                ],
            ];
        } catch (PDOException $exception) {
            if ($exception->getCode() === '23505') {
                return [
                    'error' => 'This email address is already registered.',
                    'success' => null,
                    'formData' => $formData,
                ];
            }

            throw $exception;
        }
    }

    private function deleteUser(): array
    {
        $userIdInput = $_POST['user_id'] ?? '';

        if (
            !is_string($userIdInput)
            || !ctype_digit($userIdInput)
            || (int) $userIdInput < 1
        ) {
            return [
                'error' => 'Invalid user ID.',
                'success' => null,
            ];
        }

        $userId = (int) $userIdInput;
        $currentUserId = (int) ($_SESSION['user']['id'] ?? 0);

        if ($userId === $currentUserId) {
            return [
                'error' => 'You cannot delete your own account while logged in.',
                'success' => null,
            ];
        }

        try {
            $user = $this->repository->findById($userId);

            if ($user === null) {
                return [
                    'error' => 'The selected user does not exist.',
                    'success' => null,
                ];
            }

            if ($user['role'] === 'admin') {
                return [
                    'error' => 'Administrator accounts cannot be deleted from this page.',
                    'success' => null,
                ];
            }

            if ($this->repository->hasBookings($userId)) {
                return [
                    'error' => 'This user has booking history and cannot be deleted. This preserves reservation records.',
                    'success' => null,
                ];
            }

            if (!$this->repository->deleteIfEligible($userId)) {
                return [
                    'error' => 'The user could not be deleted. They may have a booking or another database relationship.',
                    'success' => null,
                ];
            }

            return [
                'error' => null,
                'success' => 'User deleted successfully.',
            ];
        } catch (PDOException $exception) {
            if ($exception->getCode() === '23503') {
                return [
                    'error' => 'This user is referenced by existing records and cannot be deleted safely.',
                    'success' => null,
                ];
            }

            throw $exception;
        }
    }

    private function getCsrfToken(): string
    {
        if (
            !isset($_SESSION['csrf_token'])
            || !is_string($_SESSION['csrf_token'])
        ) {
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
<?php

declare(strict_types=1);

/** Validates administrator computer-creation requests and supplies the management view. */
final class AdminComputerController
{
    /** The repository performs inserts and list queries; this controller owns request validation. */
    public function __construct(
        private readonly ComputerRepository $computerRepository
    ) {
    }

    /**
     * Handle the add-computer form and return all active/inactive computers for display.
     * Preserve submitted text on validation errors and redirect after success to avoid duplicate inserts.
     */
    public function index(): array
    {
        $error = null;
        $computerName = '';
        $description = '';

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            $submittedToken = $_POST['csrf_token'] ?? null;
            $sessionToken = $_SESSION['csrf_token'] ?? null;

            if (
                !is_string($submittedToken)
                || !is_string($sessionToken)
                || !hash_equals($sessionToken, $submittedToken)
            ) {
                $error = 'Your session expired. Please try again.';
            } else {
                $nameInput = $_POST['computer_name'] ?? '';
                $descriptionInput = $_POST['description'] ?? '';

                $computerName = is_string($nameInput)
                    ? trim($nameInput)
                    : '';

                $description = is_string($descriptionInput)
                    ? trim($descriptionInput)
                    : '';

                if ($computerName === '') {
                    $error = 'Computer name is required.';
                } elseif (strlen($computerName) > 200) {
                    $error = 'Computer name is too long.';
                } elseif (
                    preg_match_all('/./us', $computerName) > 50
                ) {
                    $error = 'Computer name cannot exceed 50 characters.';
                } elseif (strlen($description) > 10000) {
                    $error = 'Description is too long.';
                } else {
                    try {
                        $this->computerRepository->createComputer(
                            $computerName,
                            $description === '' ? null : $description
                        );

                        header(
                            'Location: '
                            . rtrim(APP_BASE_URL, '/')
                            . '/admin/computers?success=created',
                            true,
                            303
                        );
                        exit;
                    } catch (PDOException $exception) {
                        if ($exception->getCode() === '23505') {
                            $error = 'A computer with this name already exists.';
                        } else {
                            throw $exception;
                        }
                    }
                }
            }
        }

        if (
            !isset($_SESSION['csrf_token'])
            || !is_string($_SESSION['csrf_token'])
        ) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        return [
            'pageTitle' => 'Manage Computers',
            'computers' => $this->computerRepository->getAllComputers(),
            'csrfToken' => $_SESSION['csrf_token'],
            'error' => $error,
            'computerName' => $computerName,
            'description' => $description,
            'success' => (
                ($_GET['success'] ?? null) === 'created'
            ),
        ];
    }
}

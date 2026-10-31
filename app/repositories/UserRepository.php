<?php

declare(strict_types=1);

/** Encapsulates SQL reads and writes for account records. */
final class UserRepository
{
    /** Repositories receive the configured PDO connection from the route bootstrap. */
    public function __construct(
        private readonly PDO $pdo
    ) {
    }

    /** Return account fields safe for the admin table; password hashes are intentionally excluded. */
    public function findAll(): array
    {
        $statement = $this->pdo->query(
            'SELECT user_id, full_name, email, role, created_at
             FROM users
             ORDER BY created_at DESC, user_id DESC'
        );

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Compare email addresses case-insensitively to match login and database uniqueness behavior. */
    public function emailExists(string $email): bool
    {
        $statement = $this->pdo->prepare(
            'SELECT 1
             FROM users
             WHERE lower(email) = lower(:email)
             LIMIT 1'
        );

        $statement->execute(['email' => $email]);

        return $statement->fetchColumn() !== false;
    }

    /** Insert a validated account; callers must pass an already-hashed password, never plaintext. */
    public function create(
        string $fullName,
        string $email,
        string $passwordHash,
        string $role
    ): void {
        $statement = $this->pdo->prepare(
            'INSERT INTO users (
                full_name,
                email,
                password_hash,
                role
             )
             VALUES (
                :full_name,
                :email,
                :password_hash,
                :role
             )'
        );

        $statement->execute([
            'full_name' => $fullName,
            'email' => $email,
            'password_hash' => $passwordHash,
            'role' => $role,
        ]);
    }

    /** Load only the fields needed for delete eligibility checks, or null when the account is absent. */
    public function findById(int $userId): ?array
    {
        $statement = $this->pdo->prepare(
            'SELECT user_id, full_name, email, role
             FROM users
             WHERE user_id = :user_id
             LIMIT 1'
        );

        $statement->execute(['user_id' => $userId]);

        $user = $statement->fetch(PDO::FETCH_ASSOC);

        return is_array($user) ? $user : null;
    }

    /** Preserve reservation history by preventing deletion once any booking row references this user. */
    public function hasBookings(int $userId): bool
    {
        $statement = $this->pdo->prepare(
            'SELECT 1
             FROM bookings
             WHERE user_id = :user_id
             LIMIT 1'
        );

        $statement->execute(['user_id' => $userId]);

        return $statement->fetchColumn() !== false;
    }

    /** Repeat the no-bookings predicate atomically so a concurrent booking cannot be orphaned by deletion. */
    public function deleteIfEligible(int $userId): bool
    {
        $statement = $this->pdo->prepare(
            'DELETE FROM users
             WHERE user_id = :user_id
               AND NOT EXISTS (
                   SELECT 1
                   FROM bookings
                   WHERE bookings.user_id = users.user_id
               )'
        );

        $statement->execute(['user_id' => $userId]);

        return $statement->rowCount() === 1;
    }
}

<?php

declare(strict_types=1);

final class UserRepository
{
    public function __construct(
        private readonly PDO $pdo
    ) {
    }

    public function findAll(): array
    {
        $statement = $this->pdo->query(
            'SELECT user_id, full_name, email, role, created_at
             FROM users
             ORDER BY created_at DESC, user_id DESC'
        );

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

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

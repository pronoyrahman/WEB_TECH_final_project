<?php
// TravelVista - reset_requests table (password reset requests).

class ResetRequest
{
    public static function create(int $userId): int
    {
        $sql = "INSERT INTO reset_requests (user_id, status) VALUES (:user_id, 'pending')";
        return Database::insert($sql, ['user_id' => $userId]);
    }

    public static function getPending(): array
    {
        $sql = "
            SELECT r.*, u.name, u.email
            FROM reset_requests r
            JOIN users u ON r.user_id = u.id
            WHERE r.status = 'pending'
            ORDER BY r.created_at DESC
        ";
        return Database::all($sql);
    }

    public static function markCompleted(int $id): void
    {
        $sql = "UPDATE reset_requests SET status = 'completed' WHERE id = :id";
        Database::run($sql, ['id' => $id]);
    }

    public static function hasPending(int $userId): bool
    {
        $sql = "SELECT COUNT(*) FROM reset_requests WHERE user_id = :user_id AND status = 'pending'";
        return Database::value($sql, ['user_id' => $userId]) > 0;
    }

    public static function findById(int $id): ?array
    {
        $sql = "
            SELECT r.*, u.email
            FROM reset_requests r
            JOIN users u ON r.user_id = u.id
            WHERE r.id = :id
        ";
        return Database::one($sql, ['id' => $id]);
    }
}

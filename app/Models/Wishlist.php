<?php
// TravelVista - wishlist table (a traveller's saved destinations).

class Wishlist
{
    // Everything one user has saved, newest first.
    public static function forUser(int $userId): array
    {
        return Database::all(
            'SELECT w.id AS wishlist_id, w.added_at,
                    p.id, p.title, p.country, p.genre, p.cost_level,
                    p.travel_medium_info, p.short_history, p.image,
                    c.base_cost, c.currency
             FROM wishlist w
             JOIN posts p ON p.id = w.post_id
             LEFT JOIN cost_estimates c ON c.post_id = p.id
             WHERE w.user_id = ? AND p.status = ?
             ORDER BY w.added_at DESC',
            [$userId, 'approved']
        );
    }

    // Post ids this user has saved, for pre-filling the save buttons.
    public static function postIds(int $userId): array
    {
        $rows = Database::all('SELECT post_id FROM wishlist WHERE user_id = ?', [$userId]);
        return array_map('intval', array_column($rows, 'post_id'));
    }

    public static function has(int $userId, int $postId): bool
    {
        return (bool) Database::value(
            'SELECT 1 FROM wishlist WHERE user_id = ? AND post_id = ?',
            [$userId, $postId]
        );
    }

    public static function count(int $userId): int
    {
        return (int) Database::value('SELECT COUNT(*) FROM wishlist WHERE user_id = ?', [$userId]);
    }

    // Save a post. The unique key on (user_id, post_id) blocks duplicates;
    // INSERT IGNORE makes a repeat save a no-op.
    public static function add(int $userId, int $postId): void
    {
        Database::run(
            'INSERT IGNORE INTO wishlist (user_id, post_id) VALUES (?, ?)',
            [$userId, $postId]
        );
    }

    // Remove a saved post. Returns true when a row was deleted.
    public static function remove(int $userId, int $postId): bool
    {
        return Database::run(
            'DELETE FROM wishlist WHERE user_id = ? AND post_id = ?',
            [$userId, $postId]
        )->rowCount() > 0;
    }

    // Sum of the base cost of everything saved, shown on the wishlist page.
    public static function estimatedTotal(int $userId): float
    {
        $value = Database::value(
            "SELECT COALESCE(SUM(COALESCE(c.base_cost,
                        CASE p.cost_level WHEN 'low' THEN 500 WHEN 'high' THEN 3000 ELSE 1500 END)), 0)
             FROM wishlist w
             JOIN posts p ON p.id = w.post_id
             LEFT JOIN cost_estimates c ON c.post_id = p.id
             WHERE w.user_id = ?",
            [$userId]
        );
        return (float) $value;
    }
}


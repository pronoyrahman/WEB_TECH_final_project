<?php
// TravelVista - comments table.

class Comment
{
    public const MAX_LENGTH = 800;

    // Comments on one post, oldest first, with the author's name.
    public static function forPost(int $postId): array
    {
        return Database::all(
            'SELECT c.id, c.content, c.created_at, c.user_id,
                    u.name AS author, u.role AS author_role, u.profile_picture
             FROM comments c
             JOIN users u ON u.id = c.user_id
             WHERE c.post_id = ?
             ORDER BY c.created_at ASC',
            [$postId]
        );
    }

    public static function findById(int $id): ?array
    {
        return Database::one(
            'SELECT c.*, u.name AS author, p.title AS post_title
             FROM comments c
             JOIN users u ON u.id = c.user_id
             JOIN posts p ON p.id = c.post_id
             WHERE c.id = ?',
            [$id]
        );
    }

    // The admin moderation list, optionally narrowed by a search term.
    public static function all(string $term = ''): array
    {
        $sql = 'SELECT c.id, c.content, c.created_at, c.post_id, c.user_id,
                       u.name AS author, u.email AS author_email, u.role AS author_role,
                       p.title AS post_title, p.country
                FROM comments c
                JOIN users u ON u.id = c.user_id
                JOIN posts p ON p.id = c.post_id';
        $params = [];

        if ($term !== '') {
            $sql .= ' WHERE c.content LIKE ? OR u.name LIKE ? OR p.title LIKE ?';
            $like = '%' . $term . '%';
            array_push($params, $like, $like, $like);
        }
        $sql .= ' ORDER BY c.created_at DESC';

        return Database::all($sql, $params);
    }

    public static function countAll(): int
    {
        return (int) Database::value('SELECT COUNT(*) FROM comments');
    }

    public static function countForPost(int $postId): int
    {
        return (int) Database::value('SELECT COUNT(*) FROM comments WHERE post_id = ?', [$postId]);
    }

    public static function create(int $postId, int $userId, string $content): int
    {
        return Database::insert(
            'INSERT INTO comments (post_id, user_id, content) VALUES (?, ?, ?)',
            [$postId, $userId, $content]
        );
    }

    public static function delete(int $id): void
    {
        Database::run('DELETE FROM comments WHERE id = ?', [$id]);
    }

    // Delete only if the comment belongs to this user. Returns true on success.
    public static function deleteOwn(int $id, int $userId): bool
    {
        return Database::run(
            'DELETE FROM comments WHERE id = ? AND user_id = ?',
            [$id, $userId]
        )->rowCount() > 0;
    }
}


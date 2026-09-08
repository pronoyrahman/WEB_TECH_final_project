<?php
// TravelVista - post_requests table, where scouts submit new guides and edits.

class PostRequest
{
    private const FIELDS = [
        'title', 'country', 'genre', 'cost_level', 'base_cost',
        'travel_medium_info', 'short_history', 'country_representation', 'image'
    ];

    public static function create(int $scoutId, array $data, ?int $originalPostId = null): int
    {
        $encoded = self::encode($data);
        $sql = 'INSERT INTO post_requests (scout_id, original_post_id, status, post_data)
                VALUES (?, ?, ?, ?)';
        return Database::insert($sql, [$scoutId, $originalPostId, 'pending', $encoded]);
    }

    public static function update(int $id, array $data): void
    {
        $encoded = self::encode($data);
        Database::run('UPDATE post_requests SET post_data = ? WHERE id = ?', [$encoded, $id]);
    }

    public static function countPending(): int
    {
        return (int) Database::value("SELECT COUNT(*) FROM post_requests WHERE status = 'pending'");
    }

    public static function findById(int $id): ?array
    {
        $sql = 'SELECT r.*, s.name as scout_name, p.title as original_title
                FROM post_requests r
                JOIN users s ON r.scout_id = s.id
                LEFT JOIN posts p ON r.original_post_id = p.id
                WHERE r.id = ?';
        $row = Database::one($sql, [$id]);
        return $row ? self::hydrate($row) : null;
    }

    public static function byScout(int $scoutId, string $status = ''): array
    {
        $sql = 'SELECT r.*, p.title as original_title
                FROM post_requests r
                LEFT JOIN posts p ON r.original_post_id = p.id
                WHERE r.scout_id = ?';
        $params = [$scoutId];
        if (in_array($status, ['pending', 'approved', 'rejected'], true)) {
            $sql .= ' AND r.status = ?';
            $params[] = $status;
        }
        $sql .= ' ORDER BY r.requested_at DESC';
        $rows = Database::all($sql, $params);
        $requests = [];
        foreach ($rows as $row) {
            $requests[] = self::hydrate($row);
        }
        return $requests;
    }

    public static function findByScout(int $scoutId): array
    {
        $sql = 'SELECT r.*, p.title as original_title
                FROM post_requests r
                LEFT JOIN posts p ON r.original_post_id = p.id
                WHERE r.scout_id = ?
                ORDER BY r.requested_at DESC';
        $rows = Database::all($sql, [$scoutId]);
        $requests = [];
        foreach ($rows as $row) {
            $requests[] = self::hydrate($row);
        }
        return $requests;
    }

    public static function findAllPending(): array
    {
        $sql = 'SELECT r.*, s.name as scout_name, s.email as scout_email, p.title as original_title
                FROM post_requests r
                JOIN users s ON r.scout_id = s.id
                LEFT JOIN posts p ON r.original_post_id = p.id
                WHERE r.status = ?
                ORDER BY r.requested_at ASC';
        $rows = Database::all($sql, ['pending']);
        $requests = [];
        foreach ($rows as $row) {
            $requests[] = self::hydrate($row);
        }
        return $requests;
    }

    public static function all(string $status = ''): array
    {
        return self::findAll($status);
    }

    public static function findAll(string $status = ''): array
    {
        $sql = 'SELECT r.*, s.name as scout_name, s.email as scout_email, p.title as original_title
                FROM post_requests r
                JOIN users s ON r.scout_id = s.id
                LEFT JOIN posts p ON r.original_post_id = p.id';
        
        $params = [];
        if (in_array($status, ['pending', 'approved', 'rejected'], true)) {
            $sql .= ' WHERE r.status = ?';
            $params[] = $status;
        }
        $sql .= ' ORDER BY r.requested_at DESC';

        $rows = Database::all($sql, $params);
        $requests = [];
        foreach ($rows as $row) {
            $requests[] = self::hydrate($row);
        }
        return $requests;
    }

    public static function counts(): array
    {
        $counts = ['pending' => 0, 'approved' => 0, 'rejected' => 0];
        $sql = 'SELECT status, COUNT(*) as c FROM post_requests GROUP BY status';
        $rows = Database::all($sql);
        foreach ($rows as $row) {
            if (isset($counts[$row['status']])) {
                $counts[$row['status']] = (int) $row['c'];
            }
        }
        return $counts;
    }

    public static function countsForScout(int $scoutId): array
    {
        $sql = 'SELECT status, COUNT(*) as c FROM post_requests WHERE scout_id = ? GROUP BY status';
        $rows = Database::all($sql, [$scoutId]);
        $counts = ['pending' => 0, 'approved' => 0, 'rejected' => 0];
        foreach ($rows as $row) {
            if (isset($counts[$row['status']])) {
                $counts[$row['status']] = (int) $row['c'];
            }
        }
        return $counts;
    }

    public static function setStatus(int $id, string $status, ?string $adminNote = null): void
    {
        $sql = 'UPDATE post_requests SET status = ?, admin_note = ? WHERE id = ?';
        Database::run($sql, [$status, $adminNote, $id]);
    }

    public static function delete(int $id): void
    {
        Database::run('DELETE FROM post_requests WHERE id = ?', [$id]);
    }

    public static function editableBy(array $request, int $userId): bool
    {
        return $request['scout_id'] === $userId && $request['status'] === 'pending';
    }

    public static function publish(array $request): array
    {
        $data = $request['data'];
        if ($request['original_post_id']) {
            Post::update((int) $request['original_post_id'], $data);
            if (!empty($data['image'])) {
                Post::setImage((int) $request['original_post_id'], $data['image']);
            }
            if (!empty($data['base_cost'])) {
                CostEstimate::save((int) $request['original_post_id'], (float) $data['base_cost']);
            }
            self::setStatus($request['id'], 'approved');
            return [
                'status' => 'success',
                'message' => 'Changes applied to post.',
                'post_id' => (int) $request['original_post_id'],
                'mode' => 'update'
            ];
        } else {
            $data['scout_id'] = $request['scout_id'];
            $data['status']   = 'approved';
            $postId = Post::create($data);
            if (!empty($data['base_cost'])) {
                CostEstimate::save($postId, (float) $data['base_cost']);
            }
            self::setStatus($request['id'], 'approved');
            return [
                'status' => 'success',
                'message' => 'New post published.',
                'post_id' => (int) $postId,
                'mode' => 'create'
            ];
        }
    }

    private static function encode(array $data): string
    {
        $clean = [];
        foreach (self::FIELDS as $field) {
            $clean[$field] = isset($data[$field]) ? $data[$field] : '';
        }
        return json_encode($clean, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    private static function hydrate(array $row): array
    {
        $decoded = json_decode((string) $row['post_data'], true);
        $data = is_array($decoded) ? $decoded : [];

        foreach (self::FIELDS as $field) {
            $data[$field] = isset($data[$field]) ? $data[$field] : '';
        }
        $row['data'] = $data;
        return $row;
    }
}



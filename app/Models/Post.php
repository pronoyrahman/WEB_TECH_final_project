<?php
// TravelVista - posts table (published destination guides).

class Post
{
    // Columns every listing needs, with the scout name joined in.
    private const LIST_COLUMNS = 'p.id, p.scout_id, p.title, p.short_history, p.country,
                                  p.country_representation, p.genre, p.cost_level,
                                  p.travel_medium_info, p.image, p.status,
                                  p.created_at, p.updated_at, u.name AS scout_name';

    // Reading

    public static function findById(int $id): ?array
    {
        return Database::one(
            'SELECT ' . self::LIST_COLUMNS . ', u.profile_picture AS scout_picture
             FROM posts p LEFT JOIN users u ON u.id = p.scout_id
             WHERE p.id = ?',
            [$id]
        );
    }

    // A published post, or null.
    public static function findApproved(int $id): ?array
    {
        $post = self::findById($id);
        return ($post && (isset($post['status']) ? $post['status'] : '') === 'approved') ? $post : null;
    }

    // The newest published posts, for the home page.
    public static function latestApproved(int $limit = 6): array
    {
        return Database::all(
            'SELECT ' . self::LIST_COLUMNS . '
             FROM posts p LEFT JOIN users u ON u.id = p.scout_id
             WHERE p.status = ?
             ORDER BY p.created_at DESC
             LIMIT ' . (int) $limit,
            ['approved']
        );
    }

    // Browse / search / filter. $filters accepts: q, country, genres (array),
    // cost (array), sort. Every value is bound; sort is chosen from a fixed
    // whitelist, never taken from the request as SQL.
    public static function filter(array $filters = [], int $limit = 60): array
    {
        $sql = 'SELECT ' . self::LIST_COLUMNS . ',
                       (SELECT COUNT(*) FROM comments c WHERE c.post_id = p.id) AS comment_count
                FROM posts p
                LEFT JOIN users u ON u.id = p.scout_id
                WHERE p.status = ?';
        $params = ['approved'];

        $term = trim((string) (isset($filters['q']) ? $filters['q'] : ''));
        if ($term !== '') {
            $sql .= ' AND (p.title LIKE ? OR p.country LIKE ? OR p.short_history LIKE ?)';
            $like = '%' . $term . '%';
            array_push($params, $like, $like, $like);
        }

        $country = trim((string) (isset($filters['country']) ? $filters['country'] : ''));
        if ($country !== '') {
            $sql .= ' AND p.country = ?';
            $params[] = $country;
        }

        $genres = self::whitelist((isset($filters['genres']) ? $filters['genres'] : []), GENRES);
        if ($genres) {
            $sql .= ' AND p.genre IN (' . self::placeholders(count($genres)) . ')';
            $params = array_merge($params, $genres);
        }

        $costs = self::whitelist((isset($filters['cost']) ? $filters['cost'] : []), COST_LEVELS);
        if ($costs) {
            $sql .= ' AND p.cost_level IN (' . self::placeholders(count($costs)) . ')';
            $params = array_merge($params, $costs);
        }

        $sort = self::sortClause((string) (isset($filters['sort']) ? $filters['sort'] : ''));
        $sql .= " ORDER BY $sort LIMIT " . (int) $limit;

        return Database::all($sql, $params);
    }

    public static function search(string $term = '', string $status = 'all'): array
    {
        $sql = 'SELECT p.*, u.name AS scout_name
                FROM posts p
                JOIN users u ON p.scout_id = u.id
                WHERE 1 = 1';
        $params = [];

        if ($term !== '') {
            $sql .= ' AND p.title LIKE ?';
            $params[] = '%' . $term . '%';
        }

        if (in_array($status, ['published', 'archived'], true)) {
            $sql .= ' AND p.status = ?';
            $params[] = $status;
        }

        $sql .= ' ORDER BY p.updated_at DESC';
        return Database::all($sql, $params);
    }

    public static function countAll(): int
    {
        return (int) Database::value('SELECT COUNT(*) FROM posts');
    }

    public static function countryCounts(): array
    {
        return Database::all("SELECT country, COUNT(*) as total FROM posts WHERE status = 'approved' GROUP BY country ORDER BY country ASC");
    }

    public static function allForAdmin(string $status = ''): array
    {
        $sql = 'SELECT ' . self::LIST_COLUMNS . ',
                       (SELECT COUNT(*) FROM comments c WHERE c.post_id = p.id) AS comment_count
                FROM posts p LEFT JOIN users u ON u.id = p.scout_id';
        $params = [];
        if ($status !== '') {
            $sql .= ' WHERE p.status = ?';
            $params[] = $status;
        }
        $sql .= ' ORDER BY p.created_at DESC';
        return Database::all($sql, $params);
    }

    public static function byScout(int $scoutId): array
    {
        return Database::all(
            'SELECT ' . self::LIST_COLUMNS . ',
                    (SELECT COUNT(*) FROM comments c WHERE c.post_id = p.id) AS comment_count,
                    (SELECT COUNT(*) FROM wishlist w WHERE w.post_id = p.id) AS saved_count
             FROM posts p LEFT JOIN users u ON u.id = p.scout_id
             WHERE p.scout_id = ?
             ORDER BY p.created_at DESC',
            [$scoutId]
        );
    }

    public static function countries(): array
    {
        $rows = Database::all('SELECT DISTINCT country FROM posts WHERE status = ? ORDER BY country ASC', ['approved']);
        return array_column($rows, 'country');
    }

    public static function genreCounts(): array
    {
        $rows = Database::all(
            'SELECT genre, COUNT(*) as count FROM posts WHERE status = ? GROUP BY genre ORDER BY genre ASC',
            ['approved']
        );
        $result = [];
        foreach ($rows as $r) {
            $result[$r['genre']] = (int) $r['count'];
        }
        return $result;
    }

    public static function stats(): array
    {
        return Database::one(
            'SELECT COUNT(*) as total_posts, 
                    CAST(SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) AS SIGNED) as approved,
                    (SELECT COUNT(DISTINCT country) FROM posts WHERE status = ?) as countries,
                    (SELECT COUNT(DISTINCT scout_id) FROM posts) as scouts
             FROM posts',
            ['approved', 'approved']
        ) ?: ['total_posts' => 0, 'approved' => 0, 'countries' => 0, 'scouts' => 0];
    }

    // Writing

    public static function create(array $data): int
    {
        return Database::insert(
            'INSERT INTO posts
                (scout_id, title, short_history, country, country_representation,
                 genre, cost_level, travel_medium_info, image, status)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
            [
                isset($data['scout_id']) ? $data['scout_id'] : null,
                $data['title'],
                $data['short_history'],
                $data['country'],
                $data['country_representation'],
                $data['genre'],
                $data['cost_level'],
                $data['travel_medium_info'],
                isset($data['image']) ? $data['image'] : '',
                isset($data['status']) ? $data['status'] : 'pending'
            ]
        );
    }

    public static function update(int $id, array $data): void
    {
        Database::run(
            'UPDATE posts SET
                title = ?, short_history = ?, country = ?, country_representation = ?,
                genre = ?, cost_level = ?, travel_medium_info = ?
             WHERE id = ?',
            [
                $data['title'],
                $data['short_history'],
                $data['country'],
                $data['country_representation'],
                $data['genre'],
                $data['cost_level'],
                $data['travel_medium_info'],
                $id
            ]
        );
    }

    public static function setImage(int $id, string $filename): void
    {
        Database::run('UPDATE posts SET image = ? WHERE id = ?', [$filename, $id]);
    }

    public static function setStatus(int $id, string $status): void
    {
        Database::run('UPDATE posts SET status = ? WHERE id = ?', [$status, $id]);
    }

    public static function delete(int $id): void
    {
        Database::run('DELETE FROM posts WHERE id = ?', [$id]);
    }

    public static function validate(array $input): array
    {
        $errors = [];
        if (trim($input['title']) === '') $errors['title'] = 'Title is required.';
        if (trim($input['country']) === '') $errors['country'] = 'Country is required.';
        if (trim($input['short_history']) === '') $errors['short_history'] = 'History is required.';
        if (!in_array($input['genre'], GENRES, true)) $errors['genre'] = 'Invalid genre.';
        if (!in_array($input['cost_level'], COST_LEVELS, true)) $errors['cost_level'] = 'Invalid cost level.';
        if (trim($input['travel_medium_info']) === '') $errors['travel_medium_info'] = 'Travel info is required.';
        
        return $errors;
    }

    // Helpers

    private static function whitelist($values, array $allowed): array
    {
        if (is_string($values)) {
            $values = [$values];
        }
        if (!is_array($values)) {
            return [];
        }
        return array_values(array_intersect(array_map('strval', $values), $allowed));
    }

    private static function placeholders(int $count): string
    {
        return implode(', ', array_fill(0, $count, '?'));
    }

    private static function sortClause(string $sort): string
    {
        $map = [
            'newest'    => 'p.created_at DESC',
            'oldest'    => 'p.created_at ASC',
            'title'     => 'p.title ASC',
            'country'   => 'p.country ASC, p.title ASC',
            'cost-low'  => "FIELD(p.cost_level, 'low', 'medium', 'high') ASC, p.title ASC",
            'cost-high' => "FIELD(p.cost_level, 'high', 'medium', 'low') ASC, p.title ASC",
        ];
        return isset($map[$sort]) ? $map[$sort] : 'p.created_at DESC';
    }
}


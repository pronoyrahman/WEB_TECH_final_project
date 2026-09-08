<?php
// TravelVista - cost_estimates table and the trip calculator. Each post has one
// base cost; when a scout supplies none, the cost level maps to it (config.php:
// low = 500, medium = 1500, high = 3000 USD).

class CostEstimate
{
    public static function forPost(int $postId): ?array
    {
        return Database::one('SELECT * FROM cost_estimates WHERE post_id = ?', [$postId]);
    }

    // The stored base cost, or the level mapping when nothing is stored.
    public static function baseCost(int $postId, string $costLevel): float
    {
        $stored = Database::value('SELECT base_cost FROM cost_estimates WHERE post_id = ?', [$postId]);
        if ($stored !== null && (float) $stored > 0) {
            return (float) $stored;
        }
        return (float) cost_base($costLevel);
    }

    public static function currency(int $postId): string
    {
        $currency = Database::value('SELECT currency FROM cost_estimates WHERE post_id = ?', [$postId]);
        return is_string($currency) && $currency !== '' ? $currency : 'USD';
    }

    // Create or replace the base cost for a post.
    public static function save(int $postId, float $baseCost, string $currency = 'USD'): void
    {
        Database::run(
            'INSERT INTO cost_estimates (post_id, base_cost, currency)
             VALUES (?, ?, ?)
             ON DUPLICATE KEY UPDATE base_cost = VALUES(base_cost), currency = VALUES(currency)',
            [$postId, $baseCost, $currency]
        );
    }

    public static function delete(int $postId): void
    {
        Database::run('DELETE FROM cost_estimates WHERE post_id = ?', [$postId]);
    }

    // Trip estimate. Mirrors the same formula in cost.js so the on-screen number
    // matches the server's:
    //   weeks = days / 7
    //   party = first traveller full price, each extra one at 85%
    //   total = base * weeks * party
    public static function calculate(float $baseCost, int $travellers, int $days): array
    {
        $travellers = max(1, min(10, $travellers));
        $days       = max(1, min(60, $days));

        $weeks      = $days / 7;
        $partyUnits = 1 + ($travellers - 1) * 0.85;

        $total      = $baseCost * $weeks * $partyUnits;
        $perPerson  = $total / $travellers;

        return [
            'base_cost'   => round($baseCost, 2),
            'travellers'  => $travellers,
            'days'        => $days,
            'weeks'       => round($weeks, 2),
            'total'       => round($total, 2),
            'per_person'  => round($perPerson, 2),
            'per_day'     => round($total / $days, 2),
        ];
    }
}


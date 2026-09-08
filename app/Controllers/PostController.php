<?php

class PostController extends Controller
{
    public function browse()
    {
        require_verified();

        $filters = [
            'q'       => isset($_GET['q']) ? trim((string) $_GET['q']) : '',
            'country' => isset($_GET['country']) ? trim((string) $_GET['country']) : '',
            'sort'    => isset($_GET['sort']) ? (string) $_GET['sort'] : 'newest',
        ];
        
        $activeGenres = isset($_GET['genre']) ? (array) $_GET['genre'] : [];
        $activeCosts  = isset($_GET['cost']) ? (array) $_GET['cost'] : [];
        $countries    = Post::countryCounts();
        $genreCounts  = Post::genreCounts();
        $genres       = GENRES;
        
        $posts = Post::filter(array_merge($filters, ['genres' => $activeGenres, 'cost' => $activeCosts]), 60);
        $savedIds = [];
        if (is_traveller()) {
            $user = current_user();
            if ($user) {
                $savedIds = Wishlist::postIds((int) $user['id']);
            }
        }

        $this->render('posts/browse', [
            'pageTitle'    => 'Browse Destinations',
            'pageScripts'  => ['browse.js'],
            'filters'      => $filters,
            'activeGenres' => $activeGenres,
            'activeCosts'  => $activeCosts,
            'countries'    => $countries,
            'genreCounts'  => $genreCounts,
            'genres'       => $genres,
            'posts'        => $posts,
            'savedIds'     => $savedIds
        ]);
    }

    public function show()
    {
        require_verified();

        $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
        $post = Post::findApproved($id);

        if ($post === null) {
            http_response_code(404);
            $this->render('posts/not_found', [
                'pageTitle' => 'Not Found'
            ]);
            return;
        }

        $this->render('posts/show', [
            'pageTitle'       => $post['title'],
            'pageDescription' => excerpt($post['short_history'], 150),
            'pageScripts'     => ['cost.js', 'comments.js', 'wishlist.js'],
            'post'            => $post,
            'postId'          => $id,
            'comments'        => Comment::forPost($id),
            'baseCost'        => CostEstimate::baseCost($id, $post['cost_level']),
            'currency'        => CostEstimate::currency($id),
            'isSaved'         => is_traveller() && Wishlist::has((int) current_user()['id'], $id),
            'canSave'         => is_traveller()
        ]);
    }

    public function apiFilter()
    {
        api_require_role();
        api_require_method('GET');

        $filters = [
            'q'       => isset($_GET['q']) ? trim((string) $_GET['q']) : '',
            'country' => isset($_GET['country']) ? trim((string) $_GET['country']) : '',
            'genres'  => isset($_GET['genre']) ? (array) $_GET['genre'] : [],
            'cost'    => isset($_GET['cost']) ? (array) $_GET['cost'] : [],
            'sort'    => isset($_GET['sort']) ? (string) $_GET['sort'] : 'newest',
        ];

        $posts = Post::filter($filters, 60);

        $savedIds = [];
        if (is_traveller()) {
            $me = current_user();
            $savedIds = Wishlist::postIds((int) $me['id']);
        }

        $results = [];
        foreach ($posts as $post) {
            $results[] = post_to_json($post, $savedIds);
        }

        json_out([
            'ok'      => true,
            'count'   => count($posts),
            'filters' => [
                'q'       => $filters['q'],
                'country' => $filters['country'],
                'genre'   => array_values(array_intersect((array) $filters['genres'], GENRES)),
                'cost'    => array_values(array_intersect((array) $filters['cost'], ['low', 'medium', 'high'])),
                'sort'    => $filters['sort'],
            ],
            'results' => $results,
        ]);
    }

    public function apiSearch()
    {
        api_require_role();
        api_require_method('GET');

        $q = isset($_GET['q']) ? trim((string) $_GET['q']) : '';

        if ($q === '') {
            json_out(['ok' => true, 'count' => 0, 'results' => []]);
        }

        $posts = Post::filter(['q' => $q], 60);

        $savedIds = [];
        if (is_traveller()) {
            $me = current_user();
            $savedIds = Wishlist::postIds((int) $me['id']);
        }

        $results = [];
        foreach ($posts as $post) {
            $results[] = post_to_json($post, $savedIds);
        }

        json_out([
            'ok'      => true,
            'count'   => count($posts),
            'results' => $results,
        ]);
    }

    public function apiCostEstimate()
    {
        api_require_role();
        api_require_method('GET');

        $postId = isset($_GET['post_id']) ? (int) $_GET['post_id'] : 0;
        $post   = $postId > 0 ? Post::findApproved($postId) : null;

        if ($post === null) {
            json_error('That destination is not published.', 404);
        }

        $rawTravellers = isset($_GET['travellers']) ? $_GET['travellers'] : 1;
        $rawDays       = isset($_GET['days']) ? $_GET['days'] : 7;

        if (filter_var($rawTravellers, FILTER_VALIDATE_INT) === false
            || filter_var($rawDays, FILTER_VALIDATE_INT) === false) {
            json_error('Travellers and days must both be whole numbers.', 422);
        }

        $travellers = max(1, min(10, (int) $rawTravellers));
        $days       = max(1, min(60, (int) $rawDays));

        $baseCost = CostEstimate::baseCost($postId, $post['cost_level']);
        $currency = CostEstimate::currency($postId);

        $estimate = CostEstimate::calculate($baseCost, $travellers, $days);

        json_out([
            'ok'       => true,
            'post_id'  => $postId,
            'currency' => $currency,
            'estimate' => $estimate,
            'display'  => [
                'base'       => money($estimate['base_cost'], $currency),
                'total'      => money($estimate['total'], $currency),
                'per_person' => money($estimate['per_person'], $currency),
                'per_day'    => money($estimate['per_day'], $currency),
            ],
        ]);
    }
}


<?php

class WishlistController extends Controller
{
    public function index()
    {
        $user = require_role('user');
        $userId = (int) $user['id'];

        $saved = Wishlist::forUser($userId);
        $total = Wishlist::estimatedTotal($userId);

        $this->render('wishlist/index', [
            'pageTitle'   => 'Your Wishlist',
            'pageScripts' => ['wishlist.js'],
            'saved'       => $saved,
            'total'       => $total,
        ]);
    }

    public function apiAdd()
    {
        $user   = api_require_role('user');
        api_require_method('POST');

        $input = json_input();
        api_csrf_guard($input);

        $postId = isset($input['post_id']) ? (int) $input['post_id'] : 0;

        if ($postId <= 0) {
            json_error('Missing post ID.', 400);
        }

        // Only a published destination can be saved.
        if (Post::findApproved($postId) === null) {
            json_error('That destination is not published.', 404);
        }

        Wishlist::add((int) $user['id'], $postId);

        json_out($this->state((int) $user['id'], $postId, true, 'Saved to wishlist'));
    }

    public function apiRemove()
    {
        $user   = api_require_role('user');
        api_require_method('POST');

        $input = json_input();
        api_csrf_guard($input);

        $postId = isset($input['post_id']) ? (int) $input['post_id'] : 0;

        if ($postId <= 0) {
            json_error('Missing post ID.', 400);
        }

        Wishlist::remove((int) $user['id'], $postId);

        json_out($this->state((int) $user['id'], $postId, false, 'Removed from wishlist'));
    }

    // Shared JSON reply for both wishlist endpoints. wishlist.js uses post_id
    // to locate the row and total to redraw the running figure.
    private function state(int $userId, int $postId, bool $saved, string $message): array
    {
        return [
            'ok'      => true,
            'post_id' => $postId,
            'saved'   => $saved,
            'count'   => Wishlist::count($userId),
            'total'   => money(Wishlist::estimatedTotal($userId)),
            'message' => $message
        ];
    }
}

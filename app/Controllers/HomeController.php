<?php

class HomeController extends Controller
{
    public function index()
    {
        $stats = Post::stats();
        
        $savedIds = [];
        if (is_traveller()) {
            $user = current_user();
            if ($user) {
                $savedIds = Wishlist::postIds((int) $user['id']);
            }
        }

        $this->render('home/index', [
            'pageTitle' => 'Explore the world',
            'pageScripts' => [],
            'stats' => $stats,
            'savedIds' => $savedIds
        ]);
    }
}

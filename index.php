<?php
// TravelVista - entry point and route table.

require_once __DIR__ . '/config/init.php';
require_once APP_ROOT . '/core/Router.php';
require_once APP_ROOT . '/core/Controller.php';

$router = new Router();

// Auth
$router->add('login', 'AuthController', 'login');
$router->add('register', 'AuthController', 'register');
$router->add('logout', 'AuthController', 'logout');
$router->add('pending', 'AuthController', 'pending');
$router->add('forgot-password', 'AuthController', 'forgotPassword');
$router->add('forgot-password-submit', 'AuthController', 'submitResetRequest');

// Profile
$router->add('profile', 'ProfileController', 'index');
$router->add('profile/update', 'ProfileController', 'updateDetails');
$router->add('profile/password', 'ProfileController', 'updatePassword');

// Posts (Browse & Show)
$router->add('browse', 'PostController', 'browse');
$router->add('post', 'PostController', 'show');

// Wishlist
$router->add('wishlist', 'WishlistController', 'index');

// Scout Dashboard
$router->add('scout/dashboard', 'ScoutController', 'dashboard');
$router->add('scout/requests', 'ScoutController', 'requests');
$router->add('scout/published', 'ScoutController', 'published');
$router->add('scout/request_form', 'ScoutController', 'requestForm');
$router->add('scout/submit_request', 'ScoutController', 'submitRequest');

// Admin Dashboard
$router->add('admin/dashboard', 'AdminController', 'dashboard');
$router->add('admin/users', 'AdminController', 'users');
$router->add('admin/user_action', 'AdminController', 'userAction');
$router->add('admin/requests', 'AdminController', 'requests');
$router->add('admin/review', 'AdminController', 'review');
$router->add('admin/review_action', 'AdminController', 'reviewAction');
$router->add('admin/posts', 'AdminController', 'posts');
$router->add('admin/post_edit', 'AdminController', 'postEdit');
$router->add('admin/post_update', 'AdminController', 'postUpdate');
$router->add('admin/comments', 'AdminController', 'comments');
$router->add('admin/resets', 'AdminController', 'resets');

// API - JSON endpoints
$router->add('api/wishlist_add', 'WishlistController', 'apiAdd');
$router->add('api/wishlist_remove', 'WishlistController', 'apiRemove');
$router->add('api/comments_add', 'CommentController', 'apiAdd');
$router->add('api/comments_delete', 'CommentController', 'apiDelete');
$router->add('api/posts_filter', 'PostController', 'apiFilter');
$router->add('api/posts_search', 'PostController', 'apiSearch');
$router->add('api/cost_estimate', 'PostController', 'apiCostEstimate');
$router->add('api/check_email', 'AdminController', 'apiCheckEmail');
$router->add('api/admin_approve_request', 'AdminController', 'apiApprove');
$router->add('api/admin_verify_user', 'AdminController', 'apiVerify');
$router->add('api/scout_request_delete', 'ScoutController', 'apiDelete');
$router->add('api/admin_approve_reset', 'AdminController', 'apiApproveReset');

// Home
$router->add('home', 'HomeController', 'index');

$page = isset($_GET['page']) ? (string)$_GET['page'] : 'home';
$router->dispatch($page);

<?php

class AdminController extends Controller
{
    public function dashboard()
    {
        $admin = require_role('admin');

        $userCounts    = User::counts();
        $requestCounts = ['pending' => PostRequest::countPending()];
        $postStats     = Post::stats();
        $commentTotal  = Comment::countAll();
        
        $queue         = PostRequest::findAllPending();
        $unverified    = User::search('', '', '0');

        $this->render('admin/dashboard', [
            'pageTitle'     => 'Admin Dashboard',
            'pageScripts'   => ['admin.js'],
            'userCounts'    => $userCounts,
            'requestCounts' => $requestCounts,
            'postStats'     => $postStats,
            'commentTotal'  => $commentTotal,
            'queue'         => $queue,
            'unverified'    => $unverified
        ]);
    }

    public function users()
    {
        $admin = require_role('admin');

        $term = isset($_GET['term']) ? trim((string)$_GET['term']) : '';
        $roleF = isset($_GET['role']) ? trim((string)$_GET['role']) : '';
        $verified = isset($_GET['verified']) ? trim((string)$_GET['verified']) : '';

        // Legacy filter handling for links coming from dashboard
        $filter = isset($_GET['filter']) ? $_GET['filter'] : '';
        if ($filter === 'unverified') {
            $verified = '0';
        }

        $people  = User::search($term, $roleF, $verified);
        $counts  = User::counts();

        $errors = take_errors();
        $old    = take_old();
        $formOpen = isset($_SESSION['user_form_open']) && $_SESSION['user_form_open'];
        unset($_SESSION['user_form_open']);

        $this->render('admin/users', [
            'pageTitle' => 'User Management',
            'pageScripts' => ['admin.js', 'validation.js'],
            'people' => $people,
            'counts' => $counts,
            'term' => $term,
            'roleF' => $roleF,
            'verified' => $verified,
            'errors' => $errors,
            'old' => $old,
            'formOpen' => $formOpen,
            'adminId' => (int) $admin['id']
        ]);
    }

    public function userAction()
    {
        $admin = require_role('admin');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('?page=admin/users');
        }

        csrf_guard();

        $action    = field($_POST, 'action');
        $targetId  = isset($_POST['user_id']) ? (int) $_POST['user_id'] : 0;
        $adminId   = (int) $admin['id'];

        if ($action === 'create') {
            $name     = field($_POST, 'name');
            $email    = strtolower(field($_POST, 'email'));
            $password = isset($_POST['password']) ? (string) $_POST['password'] : '';
            $role     = field($_POST, 'role');
            $verified = isset($_POST['is_verified']) ? 1 : 0;

            $input  = ['name' => $name, 'email' => $email, 'role' => $role, 'is_verified' => $verified];
            $errors = [];

            if ($name === '') {
                $errors['name'] = 'Enter a name.';
            } elseif (mb_strlen($name) > 100) {
                $errors['name'] = 'That name is too long.';
            }

            if ($email === '') {
                $errors['email'] = 'Enter an email address.';
            } elseif (!valid_email($email)) {
                $errors['email'] = 'That does not look like an email address.';
            } elseif (User::emailTaken($email)) {
                $errors['email'] = 'An account already uses this address.';
            }

            if (strlen($password) < 8) {
                $errors['password'] = 'Set a password of at least 8 characters.';
            }

            if (!in_array($role, ['admin', 'scout', 'user'], true)) {
                $errors['role'] = 'Choose a role.';
            }

            if ($errors) {
                $_SESSION['user_form_open'] = true;
                back_with_errors($errors, $input, '?page=admin/users');
            }

            User::create($name, $email, $password, $role, $verified);
            flash('success', $name . ' added as ' . $role . ($verified ? ', already verified.' : ', pending verification.'));
            redirect('?page=admin/users');
        }

        $target = $targetId > 0 ? User::findById($targetId) : null;
        if ($target === null) {
            flash('error', 'That account no longer exists.');
            redirect('?page=admin/users');
        }

        if ($action === 'role') {
            $role = field($_POST, 'role');
            if (!in_array($role, ['admin', 'scout', 'user'], true)) {
                flash('error', 'That is not a role.');
                redirect('?page=admin/users');
            }

            if ($targetId === $adminId && $role !== 'admin' && User::adminCount() <= 1) {
                flash('error', 'You are the only admin. Promote someone else first.');
                redirect('?page=admin/users');
            }

            User::setRole($targetId, $role);
            flash('success', $target['name'] . ' is now a ' . $role . '.');
            redirect('?page=admin/users');
        }

        if ($action === 'delete') {
            if ($targetId === $adminId) {
                flash('error', 'You cannot delete your own admin account.');
                redirect('?page=admin/users');
            }

            if ($target['role'] === 'admin' && User::adminCount() <= 1) {
                flash('error', 'That is the last admin account. It has to stay.');
                redirect('?page=admin/users');
            }

            if (!empty($target['profile_picture'])) {
                delete_image($target['profile_picture'], 'avatars');
            }
            User::delete($targetId);

            flash('success', $target['name'] . ' was removed, along with everything they filed.');
            redirect('?page=admin/users');
        }

        flash('error', 'That form was not recognised.');
        redirect('?page=admin/users');
    }

    public function requests()
    {
        $admin = require_role('admin');
        
        $status = isset($_GET['status']) ? $_GET['status'] : 'pending';
        $tabs = [
            '' => 'All',
            'pending' => 'Pending',
            'approved' => 'Approved',
            'rejected' => 'Rejected'
        ];

        $requests = PostRequest::findAll($status);

        $this->render('admin/requests', [
            'pageTitle' => 'Requests',
            'pageScripts' => ['admin.js'],
            'requests' => $requests,
            'status' => $status,
            'tabs' => $tabs
        ]);
    }

    public function review()
    {
        $admin = require_role('admin');

        $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
        $request = PostRequest::findById($id);

        if (!$request) {
            flash('error', 'That request does not exist.');
            redirect('?page=admin/requests');
        }

        $requestId = (int) $request['id'];
        $data      = $request['data'];

        // Is this a change request on an existing post?
        $isChange = !empty($request['original_post_id']);
        $original = null;
        $compared = [];

        if ($isChange) {
            $original = Post::findById((int) $request['original_post_id']);
            $compared = [
                'title'                  => 'Title',
                'country'                => 'Country',
                'genre'                  => 'Genre',
                'cost_level'             => 'Cost level',
                'short_history'          => 'Short history',
                'country_representation' => 'What it represents',
                'travel_medium_info'     => 'Getting there',
            ];
        }

        $this->render('admin/review', [
            'pageTitle'  => 'Review Request',
            'pageScripts' => [],
            'request'    => $request,
            'requestId'  => $requestId,
            'data'       => $data,
            'isChange'   => $isChange,
            'original'   => $original,
            'compared'   => $compared,
        ]);
    }

    public function reviewAction()
    {
        $admin = require_role('admin');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('?page=admin/requests');
        }

        csrf_guard();
        $action = isset($_POST['action']) ? $_POST['action'] : '';

        if ($action === 'approve_request') {
            $requestId = isset($_POST['request_id']) ? (int) $_POST['request_id'] : 0;
            $request   = PostRequest::findById($requestId);

            if ($request === null) {
                flash('error', 'That request no longer exists.');
                redirect('?page=admin/requests');
            }

            if ($request['status'] !== 'pending') {
                flash('error', 'That request has already been dealt with.');
                redirect('?page=admin/requests');
            }

            $result = PostRequest::publish($request);
            flash('success', $result['message']);
            redirect('?page=admin/requests');
        }

        if ($action === 'reject_request') {
            $requestId = isset($_POST['request_id']) ? (int) $_POST['request_id'] : 0;
            $note      = isset($_POST['admin_note']) ? trim($_POST['admin_note']) : '';
            $request   = PostRequest::findById($requestId);

            if ($request === null) {
                flash('error', 'That request no longer exists.');
                redirect('?page=admin/requests');
            }

            if (mb_strlen($note) > 255) {
                flash('error', 'Keep the reason under 255 characters.');
                redirect('?page=admin/review&id=' . $requestId);
            }

            PostRequest::setStatus($requestId, 'rejected', $note !== '' ? $note : null);
            flash('success', 'Request rejected. The scout can see your note.');
            redirect('?page=admin/requests');
        }

        if ($action === 'delete_request') {
            $requestId = isset($_POST['request_id']) ? (int) $_POST['request_id'] : 0;
            $request   = PostRequest::findById($requestId);

            if ($request !== null) {
                $image = isset($request['data']['image']) ? $request['data']['image'] : '';
                if ($image) {
                    delete_image($image, 'posts');
                }
                PostRequest::delete($requestId);
                flash('success', 'Request deleted.');
            }
            redirect('?page=admin/requests');
        }

        redirect('?page=admin/dashboard');
    }

    public function posts()
    {
        $admin = require_role('admin');
        
        $status = isset($_GET['status']) ? $_GET['status'] : 'approved';
        $tabs = [
            '' => 'All',
            'approved' => 'Published',
            'hidden' => 'Hidden'
        ];

        $posts = Post::allForAdmin($status);

        $this->render('admin/posts', [
            'pageTitle' => 'Published Posts',
            'pageScripts' => [],
            'posts' => $posts,
            'status' => $status,
            'tabs' => $tabs
        ]);
    }

    public function postEdit()
    {
        $admin = require_role('admin');
        
        $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
        $post = Post::findById($id);

        if (!$post) {
            flash('error', 'That post does not exist.');
            redirect('?page=admin/posts');
        }

        $errors = take_errors();
        $old    = take_old();
        $baseCost = CostEstimate::baseCost($id, $post['cost_level']);

        $this->render('admin/post_edit', [
            'pageTitle' => 'Edit Post',
            'pageScripts' => ['validation.js'],
            'post' => $post,
            'baseCost' => $baseCost,
            'errors' => $errors,
            'old' => $old
        ]);
    }

    public function postUpdate()
    {
        $admin = require_role('admin');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('?page=admin/posts');
        }

        csrf_guard();
        $action = isset($_POST['action']) ? $_POST['action'] : '';

        if ($action === 'update_post') {
            $postId = isset($_POST['post_id']) ? (int) $_POST['post_id'] : 0;
            $post   = Post::findById($postId);

            if ($post === null) {
                flash('error', 'Post not found.');
                redirect('?page=admin/posts');
            }

            $data = [
                'title'                  => isset($_POST['title']) ? trim($_POST['title']) : '',
                'country'                => isset($_POST['country']) ? trim($_POST['country']) : '',
                'genre'                  => isset($_POST['genre']) ? trim($_POST['genre']) : '',
                'cost_level'             => isset($_POST['cost_level']) ? trim($_POST['cost_level']) : '',
                'travel_medium_info'     => isset($_POST['travel_medium_info']) ? trim($_POST['travel_medium_info']) : '',
                'short_history'          => isset($_POST['short_history']) ? trim($_POST['short_history']) : '',
                'country_representation' => isset($_POST['country_representation']) ? trim($_POST['country_representation']) : '',
            ];

            $errors = Post::validate($data);

            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $upload = save_image($_FILES['image'], 'posts');
                if (!$upload['ok']) {
                    $errors['image'] = $upload['error'];
                } else {
                    $oldImage = isset($post['image']) ? $post['image'] : '';
                    if ($oldImage) {
                        delete_image($oldImage, 'posts');
                    }
                    Post::setImage($postId, $upload['filename']);
                }
            }

            if (!empty($errors)) {
                $_SESSION['errors'] = $errors;
                $_SESSION['old'] = $_POST;
                redirect('?page=admin/post_edit&id=' . $postId);
            }

            Post::update($postId, $data);
            
            $baseCost = isset($_POST['base_cost']) ? trim($_POST['base_cost']) : '';
            if ($baseCost !== '' && is_numeric($baseCost)) {
                CostEstimate::save($postId, (float) $baseCost);
            } else {
                CostEstimate::save($postId, (float) cost_base($data['cost_level']));
            }

            flash('success', 'Post updated successfully.');
            redirect('?page=admin/posts');
        }

        if ($action === 'delete_post') {
            $postId = isset($_POST['post_id']) ? (int) $_POST['post_id'] : 0;
            $post   = Post::findById($postId);

            if ($post !== null) {
                $image = isset($post['image']) ? $post['image'] : '';
                if ($image) {
                    delete_image($image, 'posts');
                }
                Post::delete($postId);
                flash('success', 'Post deleted.');
            }
            redirect('?page=admin/posts');
        }

        if ($action === 'hide_post') {
            $postId = isset($_POST['post_id']) ? (int) $_POST['post_id'] : 0;
            $post   = Post::findById($postId);
            if ($post !== null) {
                Post::setStatus($postId, 'hidden');
                flash('success', '"' . $post['title'] . '" is now hidden from the public archive.');
            }
            redirect('?page=admin/posts');
        }

        if ($action === 'unhide_post') {
            $postId = isset($_POST['post_id']) ? (int) $_POST['post_id'] : 0;
            $post   = Post::findById($postId);
            if ($post !== null) {
                Post::setStatus($postId, 'approved');
                flash('success', '"' . $post['title'] . '" is back in the public archive.');
            }
            redirect('?page=admin/posts&status=hidden');
        }

        redirect('?page=admin/dashboard');
    }

    public function comments()
    {
        $admin = require_role('admin');

        $limit = 50;
        $offset = isset($_GET['offset']) ? (int) $_GET['offset'] : 0;
        $offset = max(0, $offset);

        // The search box on the page posts back as ?q=
        $term = isset($_GET['q']) ? trim((string) $_GET['q']) : '';

        $matches  = Comment::all($term);
        $matched  = count($matches);
        $comments = array_slice($matches, $offset, $limit);

        $this->render('admin/comments', [
            'pageTitle' => 'Moderation - Comments',
            'pageScripts' => ['admin.js'],
            'comments' => $comments,
            'term' => $term,
            'total' => $term === '' ? Comment::countAll() : $matched,
            'offset' => $offset,
            'limit' => $limit,
            'hasMore' => ($offset + $limit) < $matched
        ]);
    }

    public function apiApprove()
    {
        $admin = api_require_role('admin');
        api_require_method('POST');

        $input = json_input();
        api_csrf_guard($input);

        $requestId = isset($input['request_id']) ? (int) $input['request_id'] : 0;

        $request = PostRequest::findById($requestId);
        if ($request === null) {
            json_error('Request not found.', 404);
        }

        if ($request['status'] !== 'pending') {
            json_error('Request has already been processed.', 400);
        }

        $result = PostRequest::publish($request);
        $postId  = (int) $result['post_id'];
        $counts  = PostRequest::counts();

        json_out([
            'ok'       => true,
            'message'  => $result['message'],
            'post_url' => url('?page=post&id=' . $postId),
            'counts'   => [
                'pending'  => (int) $counts['pending'],
                'approved' => (int) $counts['approved'],
                'rejected' => (int) $counts['rejected'],
            ],
        ]);
    }

    public function apiVerify()
    {
        $admin = api_require_role('admin');
        api_require_method('POST');

        $input = json_input();
        api_csrf_guard($input);

        $userId = isset($input['user_id']) ? (int) $input['user_id'] : 0;

        // admin.js sends "verified"; accept "is_verified" too so no caller breaks.
        if (isset($input['verified'])) {
            $raw = $input['verified'];
        } elseif (isset($input['is_verified'])) {
            $raw = $input['is_verified'];
        } else {
            json_error('Say whether the account should be verified.', 400);
        }

        $verify = (int) ((bool) $raw);

        $target = User::findById($userId);
        if ($target === null) {
            json_error('User not found.', 404);
        }

        // An admin must not strip their own verification and lock themselves out.
        if ($userId === (int) $admin['id'] && $verify === 0) {
            json_error('You cannot unverify your own account.', 403);
        }

        User::setVerified($userId, $verify);
        $counts = User::counts();

        json_out([
            'ok'         => true,
            'user_id'    => $userId,
            'verified'   => $verify === 1,
            'unverified' => (int) $counts['unverified'],
            'message'    => $target['name'] . ($verify === 1 ? ' is now verified.' : ' is no longer verified.')
        ]);
    }

    public function apiCheckEmail()
    {
        $admin = api_require_role('admin');
        api_require_method('POST');

        $input = json_input();
        $email = isset($input['email']) ? strtolower(trim((string) $input['email'])) : '';

        if ($email === '' || !valid_email($email)) {
            json_out(['ok' => true, 'taken' => false]);
        }

        json_out(['ok' => true, 'taken' => User::emailTaken($email)]);
    }

    public function resets()
    {
        $admin = require_role('admin');

        $this->render('admin/resets', [
            'pageTitle' => 'Password Resets',
            'pageScripts' => ['admin.js'],
            'admin'     => $admin,
            'requests'  => ResetRequest::getPending()
        ]);
    }

    public function apiApproveReset()
    {
        $admin = api_require_role('admin');
        api_require_method('POST');

        $input = json_input();
        api_csrf_guard($input);

        $requestId = isset($input['request_id']) ? (int) $input['request_id'] : 0;
        $request = ResetRequest::findById($requestId);

        if ($request === null || $request['status'] !== 'pending') {
            json_error('Reset request not found or already completed.', 404);
        }

        // random temporary password
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%';
        $randomPassword = '';
        for ($i = 0; $i < 10; $i++) {
            $randomPassword .= $chars[random_int(0, strlen($chars) - 1)];
        }

        User::updatePassword((int) $request['user_id'], $randomPassword);
        ResetRequest::markCompleted($requestId);

        json_out([
            'ok' => true,
            'email' => $request['email'],
            'password' => $randomPassword
        ]);
    }
}

<?php

class ScoutController extends Controller
{
    public function dashboard()
    {
        $user = require_role('scout');
        $scoutId = (int) $user['id'];

        $pending = PostRequest::byScout($scoutId, 'pending');
        $approved = PostRequest::byScout($scoutId, 'approved');
        $rejected = PostRequest::byScout($scoutId, 'rejected');

        $publishedPosts = Post::byScout($scoutId);
        $commentsCount = array_sum(array_column($publishedPosts, 'comment_count'));
        $savesCount = (int) Database::value('SELECT COUNT(*) FROM wishlist w JOIN posts p ON p.id = w.post_id WHERE p.scout_id = ?', [$scoutId]);

        // The panel on the dashboard shows the five most recent of any status.
        $recent = array_slice(PostRequest::findByScout($scoutId), 0, 5);

        $this->render('scout/dashboard', [
            'pageTitle' => 'Scout Desk',
            'pageScripts' => [],
            'pending' => $pending,
            'approved' => $approved,
            'rejected' => $rejected,
            'recent' => $recent,
            'publishedPosts' => $publishedPosts,
            'commentsCount' => $commentsCount,
            'savesCount' => $savesCount
        ]);
    }

    public function requests()
    {
        $user = require_role('scout');
        $scoutId = (int) $user['id'];

        $tabs = [
            ''         => 'All',
            'pending'  => 'Pending',
            'approved' => 'Approved',
            'rejected' => 'Rejected'
        ];

        $status = isset($_GET['status']) ? (string) $_GET['status'] : '';
        if (!array_key_exists($status, $tabs)) {
            $status = '';
        }

        // An empty status means every one of this scout's requests.
        $requests = PostRequest::byScout($scoutId, $status);

        $this->render('scout/requests', [
            'pageTitle' => 'Your Requests',
            'pageScripts' => ['scout.js'],
            'requests' => $requests,
            'status' => $status,
            'tabs' => $tabs
        ]);
    }

    public function published()
    {
        $user = require_role('scout');
        $scoutId = (int) $user['id'];

        $posts = Post::byScout($scoutId);

        $this->render('scout/published', [
            'pageTitle' => 'Your Published Dispatches',
            'pageScripts' => [],
            'posts' => $posts
        ]);
    }

    public function requestForm()
    {
        $user = require_role('scout');
        $scoutId = (int) $user['id'];

        $errors = take_errors();
        $old    = take_old();

        $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
        // Support both ?change=X (from Published page) and ?original_post_id=X
        $originalPostId = isset($_GET['change']) ? (int) $_GET['change']
                        : (isset($_GET['original_post_id']) ? (int) $_GET['original_post_id'] : null);

        $request = null;
        $originalPost = null;

        if ($id > 0) {
            $request = PostRequest::findById($id);
            if (!$request || !PostRequest::editableBy($request, $scoutId)) {
                http_response_code(404);
                exit('Request not found or not editable.');
            }
        } elseif ($originalPostId) {
            $originalPost = Post::findById($originalPostId);
            if (!$originalPost || (int)$originalPost['scout_id'] !== $scoutId) {
                http_response_code(403);
                exit('Original post not found or not yours.');
            }
        }

        $pageTitle = $id > 0 ? 'Edit Request'
                   : ($originalPost ? 'Request a change: ' . $originalPost['title'] : 'File a Dispatch');

        $this->render('scout/request_form', [
            'pageTitle'     => $pageTitle,
            'pageScripts'   => ['validation.js'],
            'errors'        => $errors,
            'old'           => $old,
            'id'            => $id,
            'originalPostId'=> $originalPostId,
            'request'       => $request,
            'originalPost'  => $originalPost
        ]);
    }

    public function submitRequest()
    {
        $user = require_role('scout');
        $scoutId = (int) $user['id'];

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('?page=scout/dashboard');
        }

        csrf_guard();

        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        $original_post_id = !empty($_POST['original_post_id']) ? (int)$_POST['original_post_id'] : null;

        $data = [
            'title'                  => isset($_POST['title']) ? trim($_POST['title']) : '',
            'country'                => isset($_POST['country']) ? trim($_POST['country']) : '',
            'genre'                  => isset($_POST['genre']) ? trim($_POST['genre']) : '',
            'cost_level'             => isset($_POST['cost_level']) ? trim($_POST['cost_level']) : '',
            'base_cost'              => isset($_POST['base_cost']) ? trim($_POST['base_cost']) : '',
            'travel_medium_info'     => isset($_POST['travel_medium_info']) ? trim($_POST['travel_medium_info']) : '',
            'short_history'          => isset($_POST['short_history']) ? trim($_POST['short_history']) : '',
            'country_representation' => isset($_POST['country_representation']) ? trim($_POST['country_representation']) : '',
            'image'                  => ''
        ];

        $errors = Post::validate($data);

        if ($id > 0) {
            $existing = PostRequest::findById($id);
            if (!$existing || !PostRequest::editableBy($existing, $scoutId)) {
                http_response_code(403);
                exit('Forbidden');
            }
            $data['image'] = isset($existing['data']['image']) ? $existing['data']['image'] : '';
        }

        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $upload = save_image($_FILES['image'], 'posts');
            if (!$upload['ok']) {
                $errors['image'] = $upload['error'];
            } else {
                if ($id > 0 && !empty($data['image'])) {
                    delete_image($data['image'], 'posts');
                }
                $data['image'] = $upload['filename'];
            }
        }

        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old'] = $_POST;
            if ($id > 0) {
                redirect('?page=scout/request_form&id=' . $id);
            } elseif ($original_post_id) {
                redirect('?page=scout/request_form&original_post_id=' . $original_post_id);
            } else {
                redirect('?page=scout/request_form');
            }
        }

        if ($id > 0) {
            PostRequest::update($id, $data);
            flash('success', 'Request updated. The editors will see the new version.');
        } else {
            PostRequest::create($scoutId, $data, $original_post_id);
            flash('success', 'Request submitted. The editors have it now.');
        }

        redirect('?page=scout/dashboard');
    }

    public function apiDelete()
    {
        $user    = api_require_role('scout');
        $scoutId = (int) $user['id'];

        api_require_method('POST');

        $input = json_input();
        api_csrf_guard($input);

        $requestId = isset($input['request_id']) ? (int) $input['request_id'] : 0;

        $request = PostRequest::findById($requestId);
        if (!$request || !PostRequest::editableBy($request, $scoutId)) {
            json_error('You can only withdraw your own request while it is still pending.', 403);
        }

        if (!empty($request['data']['image'])) {
            delete_image($request['data']['image'], 'posts');
        }

        PostRequest::delete($requestId);

        json_out([
            'ok'      => true,
            'message' => 'Request withdrawn.',
            'counts'  => PostRequest::countsForScout($scoutId)
        ]);
    }
}

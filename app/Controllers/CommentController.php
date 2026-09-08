<?php

class CommentController extends Controller
{
    public function apiAdd()
    {
        $user = api_require_role('user');
        api_require_method('POST');

        $input = json_input();
        api_csrf_guard($input);

        $postId = isset($input['post_id']) ? (int) $input['post_id'] : 0;
        $body = isset($input['body']) ? trim((string) $input['body']) : '';

        if ($postId <= 0 || $body === '') {
            json_error('Missing post ID or comment body.', 400);
        }
        if (mb_strlen($body) < 3) {
            json_error('That is a little short - add a few more words.', 400);
        }
        if (mb_strlen($body) > Comment::MAX_LENGTH) {
            json_error('Keep it under ' . Comment::MAX_LENGTH . ' characters.', 400);
        }

        // Notes belong on a published destination, nowhere else.
        if (Post::findApproved($postId) === null) {
            json_error('That destination is not published.', 404);
        }

        $commentId = Comment::create($postId, (int) $user['id'], $body);
        $count = Comment::countForPost($postId);

        json_out([
            'ok' => true,
            'message' => 'Note posted.',
            'count' => $count,
            'comment' => [
                'id' => $commentId,
                'avatar' => avatar_url(isset($user['profile_picture']) ? $user['profile_picture'] : null),
                'initial' => initial($user['name']),
                'author' => $user['name'],
                'when' => 'Just now',
                'content' => $body,
                'own' => true
            ]
        ]);
    }

    public function apiDelete()
    {
        $user = api_require_role();
        api_require_method('POST');

        $input = json_input();
        api_csrf_guard($input);

        $commentId = isset($input['comment_id']) ? (int) $input['comment_id'] : 0;

        if ($commentId <= 0) {
            json_error('Missing comment ID.', 400);
        }

        $comment = Comment::findById($commentId);
        if (!$comment) {
            json_error('Comment not found.', 404);
        }

        // A writer may remove their own note; an admin may remove any note.
        if ($user['role'] !== 'admin' && (int) $comment['user_id'] !== (int) $user['id']) {
            json_error('You cannot delete this comment.', 403);
        }

        Comment::delete($commentId);
        $count = Comment::countForPost((int) $comment['post_id']);
        $total = Comment::countAll();

        json_out([
            'ok'      => true,
            'message' => 'Comment deleted.',
            'count'   => $count,
            'total'   => $total
        ]);
    }
}

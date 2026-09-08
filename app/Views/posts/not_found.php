<?php
$postId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
?>
<section class="section">
    <div class="wrap wrap--narrow">
        <div class="empty">
            <h1>No such dispatch</h1>
            <p>
                <?php if ($postId > 0): ?>
                    Record <?= e(file_number($postId)) ?> is not in the archive. It may have been
                    withdrawn, or the link may be wrong.
                <?php else: ?>
                    The destination you are looking for is not in the archive. It may have been
                    withdrawn, or the link may be wrong.
                <?php endif; ?>
            </p>
            <a class="btn btn--primary" href="<?= e(url('?page=browse')) ?>">Browse the archive</a>
        </div>
    </div>
</section>

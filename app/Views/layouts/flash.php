<?php
// TravelVista - flash message stack. Include once near the top of the content area.
$flashes = take_flashes();
if (!$flashes) {
    return;
}
$marks = ['success' => 'Done', 'error' => 'Stop', 'info' => 'Note'];
?>
<div class="flash-stack" role="status" aria-live="polite">
    <?php foreach ($flashes as $item): ?>
        <?php $type = in_array($item['type'], ['success', 'error', 'info'], true) ? $item['type'] : 'info'; ?>
        <div class="flash flash--<?= e($type) ?>">
            <span class="flash__mark"><?= e($marks[$type]) ?></span>
            <span><?= e($item['message']) ?></span>
            <button class="flash__close" type="button" aria-label="Dismiss">&times;</button>
        </div>
    <?php endforeach; ?>
</div>

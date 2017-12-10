<div class="g12">
    <h1><?= $title; ?></h1>
    <?php if (is_string($message) && $message !== ''): ?><div class="alert warning"><?php echo $message; ?></div><?php endif; ?>
</div>

<div class="g12 calendar" rel="/user/presence"></div>

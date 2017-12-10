<div class="g12">
    <?php if(is_string($message) && $message !== ''): ?><div class="alert warning"><?php echo $message;?></div><?php endif; ?>
    
    <div class="calendar" rel="/admin/presence"></div>
</div>
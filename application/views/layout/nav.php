<nav>
    <ul id="nav">
        <?php foreach($menu as $m): ?>
            <li<?= (isset($m['icon']) ? ' class="' . $m['icon'] . '"' : ''); ?>><a<?= (is_string($m['content']) ? ' href="' . $m['content'] .'"' : ''); ?>><span><?= $m['title']; ?></span></a>
            <?php if(is_array($m['content'])): ?>
                <ul>
                <?php foreach($m['content'] as $sm): ?>
                    <li><a href="<?= $sm['content']; ?>"><span><?= $sm['title']; ?></span></a></li>
                <?php endforeach; ?>
                </ul>
            <?php endif; ?>
            </li>
        <?php endforeach; ?>
    </ul>
</nav>

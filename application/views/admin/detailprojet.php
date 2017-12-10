<div class="g12">
    <h1>Travaux de <?= $identity; ?> le <?= substr($day, 8, 2) . '/' . substr($day, 5, 2) . '/' . substr($day, 0, 4); ?></h1>
</div>

<div class="g12 bgsample bg05">
    <?php foreach ($projet as $p): ?>
        <blockquote>
            <h2><?= $p['nom']; ?></h2>
            <p>Temps passé au projet : <?= $p['duree']; ?></p>
        </blockquote>
    <?php endforeach; ?>
</div>

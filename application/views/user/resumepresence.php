<div class="g12">
    <h1><?= $title; ?></h1>
    <?php if (is_string($message) && $message !== ''): ?><div class="alert warning"><?php echo $message; ?></div><?php endif; ?>
</div>

<div class="g12 bgsample bg05">
    <?php foreach ($semainier as $nsemaine => $resume): ?>
        <blockquote>
            <h2>Semaines <?= $nsemaine; ?></h2>
            <p>Temps de présence : <?= $resume['hmnote']; ?></p>

            <h4>Répartition du temps :</h4>
            <ul>
                <?php foreach ($resume['projet'] as $projet): ?>
                    <li><?php
                        $h = floor($projet['secondes'] / 3600);
                        $m = floor($projet['secondes'] / 60 - floor($projet['secondes'] / 3600) * 60);
                        $hm = $h . 'H ' . ($m < 10 ? '0' : '') . $m . 'mn';

                        echo $hm;
                    ?>
                        sur le projet :
                    <?= $projet['nom']; ?></li>
                <?php endforeach; ?>
            </ul>

            <p>Temps du timer :
                <?php
                    $h = floor($resume['total'] / 3600);
                    $m = floor($resume['total'] / 60 - floor($resume['total'] / 3600) * 60);
                    $hm = $h . 'H ' . ($m < 10 ? '0' : '') . $m . 'mn';

                    echo $hm;
                ?>
            </p>
        </blockquote>
    <?php endforeach; ?>
</div>

<header>
    <div id="logo">
        <a href="dashboard.html">Logo Here</a>
    </div>
    <div id="header">
        <ul id="headernav">
            <li>
                <ul>
                    <li><a href="#">Aujourd'hui</a><span><?= $timers; ?></span>
                        <ul>
                            <?php if ($timers > 0): ?>
                                <?php foreach ($times as $t): ?>
                                    <li><a href="/user/modifiertimer"><?php
                                            $date = date('H:i:s', strtotime($t['demarrage']));
                                            $dure = (strtotime($t['arret']) - strtotime($t['demarrage']));

                                            $h = floor($dure / 3600);
                                            $m = floor($dure / 60) - $h * 60;

                                            echo $date . ' - ' . $h . 'H' . ($m < 10 ? '0' : '') . $m . 'mn.';
                                            ?></a></li>
                                <?php endforeach; ?>
                            <?php endif; ?>
                            <li><a href="javascript: loadTimer();">Afficher le Timer</a></li>
                        </ul>
                    </li>

                    <li><a href="#">Ma semaine</a><span><?= count($days); ?></span>
                        <?php if (count($days) > 0): ?>
                            <ul>
                                <?php foreach ($days as $d): ?>
                                    <li><a href="#"><?= $d['presence'] . ' le ' . $d['jour'] ?></a></li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </li>

                    <?php if (isset($closed) && count($closed) > 0): ?>
                        <li><a href="#">Projets fermés</a><span><?= count($closed); ?></span>
                            <ul>
                                <?php foreach ($closed as $c): ?>
                                    <li>
                                        <a href="/gestionprojets/modifierprojet/<?= $c['id_projet']; ?>"><?= $c['nom_client'] . ' - ' . $c['nom_projet'] ?></a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </li>
                    <?php endif; ?>
                </ul>
            </li>
        </ul>
    </div>
</header>
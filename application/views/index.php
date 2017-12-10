<div class="g12">
  <h1 id="numbers">Bonjour <?= $username; ?> <span><?= $title; ?></span></h1>

  <?php if (is_string($message) && $message !== ''): ?><div class="alert warning"><?php echo $message; ?></div><?php endif; ?>

  <blockquote>
    <ul class="breadcrumb" data-connect="breadcrumbcontent" data-start="<?= ($am_fin != NULL ? 4 : ($am_debut != NULL ? 3 : ($matin_fin != NULL ? 2 : ($matin_debut != NULL ? 1 : 0)))); ?>">
      <li><a href="#">arrivé au bureau</a></li>
      <li><a href="#">pause déjeuné</a></li>
      <li><a href="#">retour de pause déjeuné</a></li>
      <li><a href="#">départ du bureau</a></li>
      <li><a href="#">journée terminée</a></li>
    </ul>

    <input type="hidden" id="time" value="<?= time(); ?>" />

    <div id="breadcrumbcontent">
      <div>
        <h3>
          <?php if ($matin_debut == NULL): ?>
            Définir l'heure d'arrivé au bureau à : <a href="/index/presence/matin_debut/" class="timer" onClick="$(this).attr('href', encodeURI($(this).attr('href') + $(this).html()));"><?= date('H:i'); ?></a>
          <?php else: ?>
            Heure d'arrivé au bureau définie à : <?= date('H:i', strtotime(date('Y-m-d') . ' ' . $matin_debut)); ?>
          <?php endif; ?>
        </h3>
      </div>
      <div>
        <h3>
          <?php if ($matin_fin == NULL): ?>
            Définir l'heure de la pause déjeuné à : <a href="/index/presence/matin_fin/" class="timer" onClick="$(this).attr('href', encodeURI($(this).attr('href') + $(this).html()));" class="timer">Définir à <?= date('H:i'); ?></a></h3>
        <?php else: ?>
          Heure d'arrivé au bureau définie à : <?= date('H:i', strtotime(date('Y-m-d') . ' ' . $matin_fin)); ?>
        <?php endif; ?>
        </h3>
      </div>
      <div>
        <h3>
          <?php if ($am_debut == NULL): ?>
            Définir l'heure du retour de pause à : <a href="/index/presence/am_debut/" class="timer" onClick="$(this).attr('href', encodeURI($(this).attr('href') + $(this).html()));" class="timer">Définir à <?= date('H:i'); ?></a></h3>
        <?php else: ?>
          Heure d'arrivé au bureau définie à : <?= date('H:i', strtotime(date('Y-m-d') . ' ' . $am_debut)); ?>
        <?php endif; ?>
        </h3>
      </div>
      <div>
        <h3>
          <?php if ($am_fin == NULL): ?>
            Définir l'heure départ du bureau à : <a href="/index/presence/am_fin/" class="timer" onClick="$(this).attr('href', encodeURI($(this).attr('href') + $(this).html()));" class="timer">Définir à <?= date('H:i'); ?></a></h3>
          <?php else: ?>
          Heure d'arrivé au bureau définie à : <?= date('H:i', strtotime(date('Y-m-d') . ' ' . $am_fin)); ?>
        <?php endif; ?>
      </div>
      <div>
        <h3>
          Bonne fin de journée !
        </h3>  
      </div>
    </div>

    <p>
      <a href="/user/modifierpresence/<?= date('Y-m-d'); ?>">Vous pouvez à tout moment modifier vos horaires en cliquant ici</a>
    </p>
  </blockquote>
</div>

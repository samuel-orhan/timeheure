<div class="g12">
  <h1><?= $title; ?></h1>
  <?php if (is_string($message) && $message !== ''): ?><div class="alert warning"><?php echo $message; ?></div><?php endif; ?>
</div>

<div class="g12">
  <h2>Période</h2>

  <?php echo form_open("", array('id' => 'form', 'autocomplete' => 'off', 'data-confirm-send' => 'false')); ?>
    <fieldset>
      <section>
        <label>Date de début de la période<br><span>Format YYYY-MM-AA</span></label>
        <div><?php echo form_input($datedebut); ?></div>
      </section>

      <section>
        <label>Date de fin de la période<br><span>Format YYYY-MM-AA</span></label>
        <div><?php echo form_input($datefin); ?></div>
      </section>
    </fieldset>

    <fieldset>
        <label></label>

        <section>
            <div><button class="submit" name="submitbuttonname" value="submitbuttonvalue">Générer un rapport</button></div>
        </section>
    </fieldset>
  <?php echo form_close(); ?>
</div>

<div class="g12">
  <h2>Statistiques :</h2>

  <blockquote>
    <p>Nombre de jours comptabilisés dans la période : <?= count($semainier); ?></p>
    <p>Total du temps passé sur la période : <?= $total; ?></p>
    <p>Moyenne journalière sur la période : <?= $moyenne; ?></p>
  </blockquote>

  <h2>Comptes :</h2>

  <blockquote>
    <dd>
      <?php foreach ($semainier as $resume): ?>
        <dl>Le <?= $resume['jour']; ?> : <?= $resume['tempspresence']; ?></li>
        <?php endforeach; ?>
    </dd>
  </blockquote>
</div>
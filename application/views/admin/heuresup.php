<div class="g12">
  <h1><?= $title; ?></h1>
  
  <p>
    Rechercher sur d'autres années : <?php for($i = 2013; $i <= (int) date('Y'); $i++) : ?>
    <a href="/admin/heuresup/<?= $i; ?>"><?= $i; ?></a>
    <?php endfor; ?>
  </p>
  
  <?php if (is_string($message) && $message !== ''): ?><div class="alert warning"><?php echo $message; ?></div><?php endif; ?>
</div>

<table class="g12">
  <thead>
    <tr>
      <th>Mois</th>
      <?php
      $datamax = 0;

      foreach ($user as $u => $data) :
        $datamax = max($datamax, count($data));
        ?>
        <th>
          <?= $u; ?>
        </th>
      <?php endforeach; ?>
    </tr>
  </thead>

  <tbody>
    <?php for ($i = 0; $i < $datamax; $i++) : ?>
      <tr>
        <?php $mois = true; foreach ($user as $u => $data) : ?>
          <?php if ($mois) : $mois = false; ?>
            <td align="left"><?= $data[$i]['month']; ?></td>
          <?php endif; ?>

          <td align="left">
            <?= $data[$i]['temps']; ?><br>
            <strong><?= $data[$i]['diff']; ?></strong><br>
            <?= $data[$i]['workdays']; ?> jour(s)<br>
          </td>
        <?php endforeach; ?>
      </tr>
    <?php endfor; ?>
  </tbody>
</table>
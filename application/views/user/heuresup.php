<div class="g12">
  <h1><?= $title; ?></h1>
  
  <p>
    Rechercher sur d'autres années : <?php for($i = 2013; $i <= (int) date('Y'); $i++) : ?>
    <a href="/user/heuresup/<?= $i; ?>"><?= $i; ?></a>
    <?php endfor; ?>
  </p>
  
  <?php if (is_string($message) && $message !== ''): ?><div class="alert warning"><?php echo $message; ?></div><?php endif; ?>
</div>

<table class="g12">
  <thead>
    <tr>
      <th>Mois</th>
      <th>Présence totale</th>
      <th>Jours comptés</th>
      <th>Heures supplémentaires</th>
    </tr>
  </thead>

  <tbody>
    <?php foreach($user as $u) : ?>
      <tr>
        <td align="left"><?= $u['month']; ?></td>
        <td align="left"><?= $u['temps']; ?></td>
        <td align="left"><?= $u['workdays']; ?></td>
        <td align="left"><?= $u['diff']; ?></td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>
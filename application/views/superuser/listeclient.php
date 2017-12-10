<div class="g12">
    <h1 id="numbers"><?= $title; ?></h1>
    <p>Le tableau ci-dessous résume l'ensemble des clients que vous pouvez associer à vos projets</p>

    <?php if (is_string($message) && $message !== ''): ?>
        <div class="alert warning"><?php echo $message; ?></div><?php endif; ?>

    <div class="g12">
        <?php if (count($liste_client) > 0): ?>
            <table class="datatable client">
                <thead>
                <tr>
                    <th>identifiant</th>
                    <th>nom du client</th>
                    <th>ordre d'apparition</th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($liste_client as $c): ?>
                    <tr>
                        <td><?= $c['id']; ?></td>
                        <td><?= $c['nom']; ?></td>
                        <td><?= $c['index']; ?></td>
                        <td>
                            <a href="/superuser/modifier/<?= $c['id']; ?>">modifier ce client</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>Vous n'avez encore aucuns clients dans votre carnet</p>
        <?php endif; ?>
    </div>

    <div class="g12">
        <p><a class="btn i_admin_user icon" href="/superuser/modifier">Ajouter un client</a></p>
    </div>
</div>

<div class="g12">
    <h1>Tableau des utilisateurs</h1>
    <p>Liste de l'ensemble de vos utilisateurs</p>

    <?php if (is_string($message) && $message !== ''): ?><div class="alert warning"><?php echo $message; ?></div><?php endif; ?>

    <div class="g12">
        <table class="datatable">
            <thead>
                <tr>
                    <th>identifiant</th>
                    <th>nom d'utilisateur</th>
                    <th>appartiens au(x) groupe(s)</th>
                    <th> </th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?php echo $user['id']; ?></td>
                        <td><?php echo $user['username']; ?></td>
                        <td><?php echo $user['groups']; ?></td>
                        <td>
                            <a class="btn i_create_write icon small" href="/superadmin/update_user/<?php echo $user['id']; ?>"></a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="g12">
        <p><a class="btn i_user icon" href="/superadmin/register_user">Ajouter un utilisateur</a></p>
    </div>
</div>
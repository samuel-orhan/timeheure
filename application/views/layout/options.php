<div id="pageoptions">
    <ul>
        <li><a href="/logout">Logout</a></li>
        <li><a href="#" id="wl_config">Vos status</a></li>
    </ul>
    <div>
        <h3>Bonjour <?= ($user != NULL ? $user->username : 'Utilisateur inconnu');?></h3>
        <p>Vous faite partie du(des) groupe(s) <?= $group; ?></p>
    </div>
</div>

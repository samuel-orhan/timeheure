<div class="g12">
    <h1 id="numbers"><?= $title; ?> <span><?= $nom; ?></span></h1>
    <p>Vos clients sont affichés par ordre d'apparition.</p>

    <?php if (is_string($message) && $message !== ''): ?>
        <div class="alert warning"><?php echo $message; ?></div><?php endif; ?>

    <?php echo form_open_multipart("", array('id' => 'form', 'autocomplete' => 'off', 'data-confirm-send' => 'false')); ?>
    <?php echo form_input($id_projet); ?>

    <fieldset>
        <legend>Identification du projet</legend>

        <section><label for="input">Client associé au projet</label>
            <div><?php echo form_dropdown('id_client', $clients, $client_selected); ?></div>
        </section>

        <section><label for="input">Nom du projet</label>
            <div><?php echo form_input($nom_projet); ?></div>
        </section>

        <section><label for="textarea">Description de votre projet</label>
            <div><?php echo form_textarea($description_projet); ?></div>
        </section>


        <section><label for="input">Date de fin de projet</label>
            <div><?php echo form_input($dead_line); ?></div>
        </section>
    </fieldset>

    <fieldset>
        <legend>Status du projet</legend>

        <section><label for="input">Etat du projet</label>
            <div><?php echo form_dropdown('etat_projet', $etat_projet, $etat_selected); ?></div>
        </section>
    </fieldset>

    <fieldset>
        <legend>Participants au projet</legend>
        <?php if (!isset($username)): ?>
            <section><label for="input">Nom d'utilisateur</label>
                <div><?php echo form_multiselect('users', $users, $selected_users); ?></div>
            </section>
        <?php else: ?>
            <section><label for="input">Nom d'utilisateur</label>
                <?php foreach ($selected_users as $id_selected): ?>
                    <input type="hidden" name="users[]" value="<?= $id_selected; ?>"/>
                <?php endforeach; ?>

                <?php if (count($selected_users) < 1): ?>
                    <input type="hidden" name="users[]" value="<?= $id_users; ?>"/>
                <?php endif; ?>
                <div><?= $username; ?></div>
            </section>
        <?php endif; ?>
        <section><label for="input">Estimation de temps</label>
            <div>
                <?php echo form_input($duree_heures); ?>H.
                <?php echo form_input($duree_minutes); ?>MN.
            </div>
        </section>
    </fieldset>

    <fieldset>
        <legend>Références du projets</legend>

        <section><label for="textarea">Numéro du bon de commande</label>
            <div><?php echo form_input($bdc_projet); ?></div>
        </section>
        <section><label for="textarea">Numéro du devis</label>
            <div><?php echo form_input($devis_projet); ?></div>
        </section>
        <section><label for="textarea">Numéro de facture</label>
            <div><?php echo form_input($facture_projet); ?></div>
        </section>
    </fieldset>

    <fieldset>
        <label>Bon de commande</label>
        <section><label for="file_upload">Fichier<br><span>sélectionnez le fichier à ajouter</span></label>
            <div><input type="file" name="bdc_file" data-auto-upload="false"></div>
        </section>
    </fieldset>

    <fieldset>
        <label>Enregistrement</label>

        <section>
            <div>
                <button class="submit" name="submitbuttonname" value="submitbuttonvalue">Enregistrer votre projet
                </button>
            </div>
        </section>
    </fieldset>
    <?php echo form_close(); ?>
</div>

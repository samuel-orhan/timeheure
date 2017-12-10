<div class="g12">
    <h1><?= $title; ?></h1>

    <?php if(is_string($message) && $message !== ''): ?><div class="alert warning"><?php echo $message;?></div><?php endif; ?>

    <?php echo form_open("", array('id' => 'form', 'autocomplete' => 'off'));?>
        <fieldset>
            <label>Identification</label>

            <section><label for="text_field">Nom d'utilisateur</label>
                <div><?php echo form_input($username);?></div>
            </section>

            <section><label for="password">Mot de passe choisie<br><span>(8 caractères minimum)</span></label>
                <div><?php echo form_input($password);?></div>
            </section>
        </fieldset>

        <fieldset>
            <label>Autres renseignements</label>

            <section><label for="text_field">Nom</label>
                <div><?php echo form_input($first_name);?></div>
            </section>
            <section><label for="text_field">Prénom</label>
                <div><?php echo form_input($last_name);?></div>
            </section>

            <section><label for="email">E-mail</label>
                <div><?php echo form_input($email);?></div>
            </section>
        </fieldset>

        <fieldset>
            <label>Enregistrement</label>

            <section>
                <div><button class="submit" name="submitbuttonname" value="submitbuttonvalue">Enregistrer les changements</button></div>
            </section>
        </fieldset>
   <?php echo form_close();?>
</div>
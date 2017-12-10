<div class="g12">
    <h1 id="numbers"><?= $title; ?> <span><?= $dateTexte; ?></span></h1>

    <?php if(is_string($message) && $message !== ''): ?><div class="alert warning"><?php echo $message;?></div><?php endif; ?>

    <?php echo form_open('user/modifierpresence/'.$date['value'], array('id' => 'form', 'autocomplete' => 'off', 'data-confirm-send' => 'false'));?>
        <?php echo form_input($date);?>

        <fieldset>
            <legend>Matinée</legend>

            <section><label for="input">Arrivée</label>
                <div><?php echo form_input($matin_debut);?></div>
            </section>
            <section><label for="input">Départ</label>
                <div><?php echo form_input($matin_fin);?></div>
            </section>
        </fieldset>

        <fieldset>
            <legend>Après midi</legend>

            <section><label for="input">Arrivée</label>
                <div><?php echo form_input($am_debut);?></div>
            </section>
            <section><label for="input">Départ</label>
                <div><?php echo form_input($am_fin);?></div>
            </section>
        </fieldset>

        <fieldset>
            <label>Enregistrement</label>

            <section>
                <div><button class="submit" name="submitbuttonname" value="submitbuttonvalue">Enregistrer vos changements</button></div>
            </section>
        </fieldset>
    <?php echo form_close();?>
</div>
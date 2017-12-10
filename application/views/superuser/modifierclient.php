<div class="g12">
    <h1 id="numbers"><?= $title; ?> <span><?= $nom; ?></span></h1>
    <p>Vos clients sont affichés par ordre d'apparition.</p>

    <?php if (is_string($message) && $message !== ''): ?>
        <div class="alert warning"><?php echo $message; ?></div><?php endif; ?>

    <?php echo form_open("", array('id' => 'form', 'autocomplete' => 'off', 'data-confirm-send' => 'false')); ?>
    <?php echo form_input($id_client); ?>

    <fieldset>
        <legend>Identification du client</legend>

        <section><label for="input">Nom du client</label>
            <div><?php echo form_input($nom_client); ?></div>
        </section>
        <section><label for="input">Ordre d'apparition</label>
            <div><?php echo form_input($index_client); ?></div>
        </section>
    </fieldset>
    <fieldset>
        <label>Enregistrement</label>

        <section>
            <div>
                <button class="submit" name="submitbuttonname" value="submitbuttonvalue">Enregistrer vos changements
                </button>
            </div>
        </section>
    </fieldset>
    <?php echo form_close(); ?>
</div>

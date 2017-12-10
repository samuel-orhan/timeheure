<div class="g12">
    <h1 id="numbers"><?= $title; ?></h1>

    <?php if (is_string($message) && $message !== ''): ?><div class="alert warning"><?php echo $message; ?></div><?php endif; ?>

    <?php echo form_open('', array('autocomplete' => 'off', 'data-confirm-send' => 'false')); ?>
        <fieldset>
            <legend>Modifier d'anciens timers</legend>

            <section><label>Choisir la date<br></label>
                <div>
                    <div name="date" class="date" id="inline_date" data-value="now"></div>
                    <button class="submit" name="submitbuttonname" value="submitbuttonvalue">Aller à la date</button>
                </div>
            </section>
        </fieldset>
    </form>

    <h2>Tous vos timer du <?= $datetitle;?></h2>

    <?php foreach ($timers as $timer):
        echo form_open('', array('autocomplete' => 'off', 'data-confirm-send' => 'false')); ?>
        <input type="hidden" name="id_timer" value="<?= $timer['id_timer']; ?>">
        <input type="hidden" name="id_projet" value="<?= $timer['id_projet']; ?>">

        <input type="hidden" name ="date_demarrage" value="<?= date('Y-m-d', strtotime($timer['demarrage'])); ?>">
        <input type="hidden" name ="date_arret" value="<?= date('Y-m-d', strtotime($timer['arret'])); ?>">

        <input type="hidden" name="date" value="<?= $date;?>">

        <fieldset>
            <label>Pour le timer du <?= date('d/m/Y', strtotime($timer['demarrage'])); ?> concernant : <?= $timer['identite']; ?></label>

            <section>
                <label>Horaires de début / fin</label>
                <div>
                    <input type="text" class="time" name="demarrage" value="<?= date('H:i:s', strtotime($timer['demarrage'])); ?>">
                    /
                    <input type="text" class="time" name="arret" value="<?= date('H:i:s', strtotime($timer['arret'])); ?>">
                </div>
            </section>

            <section><label for="textarea">Note associé</label>
                <div><textarea name="notes" rows="2"><?= $timer['notes']; ?></textarea></div>
            </section>

            <section><label>Enregistrement</label>
                <div><button class="submit" name="submitbuttonname" value="submitbuttonvalue">Modifier ce timer</button></div>
            </section>
        </fieldset>
    </form>
    <?php endforeach; ?>
</div>
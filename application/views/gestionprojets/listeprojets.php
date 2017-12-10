<div class="g12">
    <h1 id="numbers"><?= $title; ?></h1>
    <p>Le tableau ci-dessous résume l'ensemble des clients que vous pouvez associer à vos projets</p>

    <?php if (is_string($message) && $message !== ''): ?><div class="alert warning"><?php echo $message; ?></div><?php endif; ?>
    <input type="hidden" id="projetlist" value="<?= $projettype; ?>">

    <div class="g12">
        <table class="datatable projet">
            <thead>
                <tr>
                    <th>date de création</th>
                    <th>Devis<br>BDC<br>Facture</th>

                    <th>nom du client</th>
                    <th>
                        nom du projet<br>
                        dossier concerné
                    </th>
                    <th>Actions</th>
                </tr>
            </thead>

            <tbody></tbody>
        </table>
    </div>

    <div class="g12">
        <p><a class="btn i_folder icon" href="/gestionprojets/modifierprojet">Ajouter un projet</a></p>
    </div>
</div>

<!doctype html>
<html lang="fr-fr">
    <head>
        <?= $HEAD; ?>
    </head>

    <body>
        <?= isset($OPTIONS) ? $OPTIONS : ''; ?>
        <?= isset($HEADER) ? $HEADER : ''; ?>
        <?= isset($NAV) ?  $NAV : ''; ?>

        <section id="content">
            <?= $CONTENT; ?>
        </section>
        <footer>Copyright by revaxarts.com 2012</footer>
    </body>
</html>
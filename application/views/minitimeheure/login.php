<!doctype html>
<html lang="fr">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
        <title>Time Heure</title>
        <link rel="stylesheet" href="/css/timerStyles.css">
    </head>
    <body>
        <div id="Timer">
            <a id="ihm_gestion" href="/index" target="_blank">Interface de gestion</a>

            <?php if(is_string($message) && $message !== ''): ?><p><?php echo $message;?></p><?php endif; ?>

            <?php echo form_open("/minitimeheure/login", array('id' => 'loginform', 'accept-charset' => 'utf-8'));?>
                <?php echo form_input($identity);?>
                <?php echo form_input($password);?>

                <button class="fr submit">Se connecter</button>
            <?php echo form_close();?>
        </div>

        <script src="//ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js"></script>
        <script>window.jQuery || document.write('<script src="/js/jquery_libs/jquery-1.7.1.min.js"><\/script>')</script>

        <script src="/js/minitimeheure/pluginTimer.js"></script>
    </body>
</html>
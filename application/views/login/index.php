<!doctype html>
<html lang="fr-fr">
    <head>
        <meta charset="utf-8">

        <title>Quinze Mille</title>

        <meta name="description" content="">
        <meta name="author" content="revaxarts.com">


        <!-- Apple iOS and Android stuff -->
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="black">
        <link rel="apple-touch-icon-precomposed" href="img/icon.png">
        <link rel="apple-touch-startup-image" href="img/startup.png">
        <meta name="viewport" content="width=device-width,initial-scale=1,user-scalable=no,maximum-scale=1">

        <!-- Google Font and style definitions -->
        <link rel="stylesheet" href="http://fonts.googleapis.com/css?family=PT+Sans:regular,bold">
        <link rel="stylesheet" href="/css/style.css">

        <!-- include the skins (change to dark if you like) -->
        <link rel="stylesheet" href="/css/light/theme.css" id="themestyle">
        <!-- <link rel="stylesheet" href="css/dark/theme.css" id="themestyle"> -->

        <!--[if lt IE 9]>
        <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
        <link rel="stylesheet" href="css/ie.css">
        <![endif]-->

        <!-- Use Google CDN for jQuery and jQuery UI -->
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js"></script>
        <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.12/jquery-ui.min.js"></script>

        <!-- Loading JS Files this way is not recommended! Merge them but keep their order -->

        <!-- some basic functions -->
        <script src="/js/gestionnaire/functions.js"></script>

        <!-- all Third Party Plugins -->
        <script src="/js/gestionnaire/plugins.js"></script>

        <!-- Whitelabel Plugins -->
        <script src="/js/wl_libs/wl_Alert.js"></script>
        <script src="/js/wl_libs/wl_Dialog.js"></script>
        <script src="/js/wl_libs/wl_Form.js"></script>

        <!-- configuration to overwrite settings -->
        <script src="/js/gestionnaire/config.js"></script>

        <!-- the script which handles all the access to plugins etc... -->
        <script src="/js/login.js"></script>
    </head>
    <body id="login">
        <header>
            <div id="logo">
                <a href="login.html">Quinze-Mille</a>
            </div>
        </header>
        <section id="content">
            <?php if(is_string($message) && $message !== ''): ?><div class="alert warning"><?php echo $message;?></div><?php endif; ?>

            <?php echo form_open("/login", array('id' => 'loginform'));?>
                <fieldset>
                    <section><label for="identity">Nom d'utilisateur</label>
                        <div><?php echo form_input($identity);?></div>
                    </section>
                    <section><label for="password">Mot de passe <a href="#">mot de passe oublié ?</a></label>
                        <div><?php echo form_input($password);?></div>
                        <div><?php echo form_checkbox($remember);?><label for="remember" class="checkbox">remember me</label></div>
                    </section>
                    <section>
                        <div><button class="fr submit">Se connecter</button></div>
                    </section>
                </fieldset>
            <?php echo form_close();?>
        </section>
        <footer>Copyright by revaxarts.com 2011</footer>

    </body>
</html>
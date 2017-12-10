<!doctype html>
<html lang="fr">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
        <title>Time Heure</title>
        <link rel="stylesheet" href="/css/timerStyles.css">
        <link rel="stylesheet" href="/css/timerSam.css">
    </head>
    <body>
        <div id="Timer">
            <!--
            <a id="ihm_gestion" href="/index" target="_blank">Interface de gestion</a>
            -->

            <!-- ONGLETS DE NAVIGATION -->

            <ul id="Onglets">
                <li><a href="#" class="IcoCompteur active" rel="Projet"><span class="visuallyhidden">Compteur</span></a></li>
                <li><a href="#" class="IcoDue" rel="Due"><span class="visuallyhidden">Due</span></a></li>
                <li><a href="#" class="IcoFavoris" rel="Favoris"><span class="visuallyhidden">Favoris</span></a></li>
                <li><a href="#" class="IcoTodo"><span class="visuallyhidden">ToDo</span></a></li>
                <li><a href="/index" class="IcoReglages" target="_blank"><span class="visuallyhidden">Reglages</span></a></li>
            </ul>

            <!-- LISTES DEROULANTES -->

            <div id="mainContent"></div>
        </div>

        <script src="//ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js"></script>
        <script>window.jQuery || document.write('<script src="/js/jquery_libs/jquery-1.7.1.min.js"><\/script>')</script>

        <script src="/js/minitimeheure/pluginTimer.js"></script>

        <!-- Scripts par contenu -->

        <script src="/js/minitimeheure/jquery.cookie.js"></script>
        <script src="/js/minitimeheure/projetsTimer.js"></script>

        <!-- Script de gestion des contenus -->

        <script src="/js/minitimeheure/scripts.js"></script>
    </body>
</html>
var controllerBaseUrl = '/minitimeheure/';     // Adresse de base de la page

////////////////////////////////////////////////////////////////////////////////
// Chargement de la liste des projets

var client_projet;

var selected_id_client;
var selected_id_projet;

function reloadProjets() {
    // Les évènements sur les favoris

    if($('#favoris').length > 0)
       $('#favoris').unbind('click');

    if($('#fermer').length > 0)
       $('#fermer').unbind('click');

    // Le reste

    if($('#mainContent > div:first').attr('rel') == "Projet") {
        $.get(controllerBaseUrl + 'get_projets', function(data) {
            client_projet = new Object();

            for(var i = 0; i < data.length; i++) {
                if(! client_projet[data[i].id_client])
                    client_projet[data[i].id_client] = {'index_client': data[i].index_client, 'client': data[i].nom_client, 'projet': new Array()};

                client_projet[data[i].id_client].projet[data[i].id_projet] = data[i].nom_projet + (data[i].dossier_projet != null ? ' ' + data[i].dossier_projet : '');
            }

            // Mise en place de la liste

            setListeClient();
            loadFavoris();

            $('#fermer').bind('click', unsetParticipation);
        });
    }
    else if($('#mainContent > div:first').attr('rel') == "Due") {
        $.get(controllerBaseUrl + 'get_dues', function(data) {
            var dataValue = new Array();

            for(var i = 0; i < data.length; i++)
                if(! dataValue[data[i].id_projet]) {
                    var dayBefore = 16;

                    if(data[i].dead_line != null) {
                        var dueDate = new Date();
                        dueDate.setFullYear(parseInt(data[i].dead_line.substr(0, 4)), parseInt(data[i].dead_line.substr(5, 2)) - 1, parseInt(data[i].dead_line.substr(8, 2)));

                        dayBefore = (dueDate.getTime() - new Date().getTime()) / (1000 * 3600 * 24);
                    }

                    if(dayBefore < 1)
                        dl = '<span class="dueDate dueDay">';
                    else if(dayBefore > 0 && dayBefore < 8)
                        dl = '<span class="dueDate dueWeek">';
                    else
                        dl = '<span class="dueDate">';

                    dl += ' </span> ';

                    if(data[i].dead_line != null)
                        dataValue.unshift({value: data[i].id_projet, html: dl + data[i].nom_projet});
                    else
                        dataValue.push({value: data[i].id_projet, html: dl + data[i].nom_projet});
                }

            var dataDefault = (selected_id_projet ? selected_id_projet : 0);
            var jqRefList = $('#ihm_due');
            var clickFunc = projetChangeEvent;

            setListe(dataValue, dataDefault, jqRefList, clickFunc);
            loadFavoris();
        });
    }
    else if($('#mainContent > div:first').attr('rel') == "Favoris") {
        $.get(controllerBaseUrl + 'get_favoris', function(data) {
            var dataValue = new Array();

            for(var i = 0; i < data.length; i++)
                dataValue.push({value: data[i].id_projet, html: '<span class="fav"> </span>' + data[i].nom_projet});

            var dataDefault = (selected_id_projet ? selected_id_projet : 0);
            var jqRefList = $('#ihm_favoris');
            var clickFunc = projetFavChangeEvent;

            setFav = true;

            setListe(dataValue, dataDefault, jqRefList, clickFunc);
            loadFavoris();
        });
    }

    setTimeout('reloadProjets()', 600000); // Reload toutes les 10 min.
}

////////////////////////////////////////////////////////////////////////////////
// Définition du fonctionnement des listes

function setListe(dataValue, dataDefault, jqRefList, clickFunc) {
    // Mise en place d'une liste avec :
    //   dataValue, tableau associatif dataValue[idListe] = titreListe;
    //   dataDefault, l'id selectionné par défaut
    //   jqRefListe, object DOM de la liste
    //   clickFunc, écouteur pour les click

    // Préparation (retraits des anciens écouteurs, vider la liste)

    jqRefList.find('a.selector').unbind('click');
    jqRefList.find('ul').find('li').each(function () {
        $(this).unbind('click');
    });

     jqRefList.find('ul').css('height', 'auto');

    jqRefList.find('ul').empty();


    for(var v in dataValue)
        jqRefList.find('ul').append('<li rel="' + dataValue[v].value + '">' + dataValue[v].html + '</li>');

    // Validation de la valeur par défaut (sinon, premier item de la liste)

    var defaultValue = parseInt(jqRefList.find('ul > li:first').attr('rel'));
    var defaultHtml = jqRefList.find('ul > li:first').html();

    for(var v in dataValue)
        if(dataDefault == dataValue[v].value) {
            defaultValue = dataValue[v].value;
            defaultHtml = dataValue[v].html;
        }

    // Mise en place de la valeur par défaut

    jqRefList.find('a.current').html(defaultHtml);
    jqRefList.find('a.current').attr('rel', defaultValue);

    // Ouverture et fermeture liste

    jqRefList.find('a.selector, a.current').bind('click', function() {
        // Referme toute liste précédemment ouverte

        if(! $(this).parent().find('ul').is(':visible')) {
            $('a.selector').parent().each(function() {
                $(this).find('ul').hide();
            });

            // ouvre celle demandée

            $(this).parent().find('ul').show();
        }
        else {
            // Ferme celle demandée

            $(this).parent().find('ul').hide();
        }

        return false;
    });

    // Ecouteur de modification de liste

    jqRefList.find('ul > li').bind('click', function () {
        $(this).parent().find('li').removeClass('selected');
        $(this).addClass('selected');

        var html = $(this).html();
        var id = parseInt($(this).attr('rel'));

        var selector = $(this).parent().parent().find('a.current');
        selector.html(html);
        selector.attr('rel', id);

        clickFunc(id);
        isFavoris();

        // Fermeture de la liste

        $(this).parent().hide();
        return false;
    });

    jqRefList.find('ul > li[rel=' + defaultValue + ']').click();

    // Ajout d'un ascensseur si nécessaire

    var h = jqRefList.offset().top + jqRefList.find('a.current').height();
    var w = jqRefList.offset().left;


    if(jqRefList.find('ul').height() > ($(window).height() - h - 5))
        jqRefList.find('ul').css({
            'width' : ($(window).width() - w - 20),
            'height' : ($(window).height() - h - 5),
            'overflow' : 'auto'
        });
    else
        jqRefList.find('ul').css({
            'width' : ($(window).width() - w - 20),
            'height' : 'auto',
            'overflow' : 'auto'
        });
}

////////////////////////////////////////////////////////////////////////////////
// Création des listes

function setListeClient() {
    // Mise en place de la liste

    var noOrder = new Array();
    for(var cp in client_projet) {
        if(! noOrder[client_projet[cp].index_client])
            noOrder[client_projet[cp].index_client] = new Array();

        noOrder[client_projet[cp].index_client].push({value: cp, html: client_projet[cp].client})
    }

    var dataValue = new Array();
    for(var i in noOrder) {
        while(noOrder[i].length > 0)
            dataValue.push(noOrder[i].shift());
    }

    var dataDefault = (selected_id_client ? selected_id_client : 0);
    var jqRefList = $('#ihm_client');
    var clickFunc = clientChangeEvent;

    setListe(dataValue, dataDefault, jqRefList, clickFunc);
}
function clientChangeEvent(id_client) {
    // Stoppe les projets en cours

    if(selected_id_client != id_client)
        stopCompteur();

    // Redéfinis le projet en cours

    selected_id_client = id_client;

    // Mise en place de la liste

    var dataValue = new Array();
    for(var cp in client_projet[id_client].projet)
        dataValue[cp] = {value: cp, html: '<span class="projet"> </span>' + client_projet[id_client].projet[cp]};

    var dataDefault = (selected_id_projet ? selected_id_projet : undefined);
    var jqRefList = $('#ihm_projet');
    var clickFunc = projetChangeEvent;

    setListe(dataValue, dataDefault, jqRefList, clickFunc);
}

function projetChangeEvent(id_projet) {
    // Stoppe les projets en cours

    if(id_projet != selected_id_projet)
        stopCompteur();

    // Définis le projet en cours

    selected_id_projet = id_projet;
    setTime(id_projet);
}

function projetFavChangeEvent(id_projet) {
    // Définis le projet en cours

    if(selected_id_projet != id_projet) {
        selected_id_projet = id_projet;
        setTime(id_projet);

        restartCompteur();
    }
}

////////////////////////////////////////////////////////////////////////////////
// Affichage des compteurs chronologiques

var temps_total;
var temps_prevu;
var idTimer;

function compte() {
    var passe = temps_total + (new Date().getTime() - startDate.getTime()) / 1000;
    $('#ihm_total').html(Math.floor(passe / 3600) + (new Date().getTime() % 1000 < 600 ? '<span style="opacity: 1;">:</span>' : '<span style="opacity: 0;">:</span>') + (Math.floor(passe / 60 % 60) < 10 ? '0' : '') + Math.floor(passe / 60 % 60));
}

function setTime(id) {
    $.get(controllerBaseUrl + 'get_infoprojet/' + id, function(data) {
        selected_id_projet = data.id_projet;

        temps_total = data.temps_total;
        temps_prevu = data.temps_prevu;

        $('#ihm_total').html(Math.floor(temps_total / 3600) + ':' + (Math.floor(temps_total / 60 % 60) < 10 ? '0' : '') + Math.floor(temps_total / 60 % 60));
        $('#ihm_prevu').html(Math.floor(temps_prevu / 3600) + ':' + (Math.floor(temps_prevu / 60 % 60) < 10 ? '0' : '') + Math.floor(temps_prevu / 60 % 60));
    });
}

////////////////////////////////////////////////////////////////////////////////
// Démarrage et arrêt des projets

var intervalIdTime;
var startDate;

function initStartStop() {
    // Initialisation du bouton projet

    $('#ihm_playpause').css('cursor', 'pointer');
    $('#ihm_playpause').attr('rel', 'stopped');

    // Evènement sur le compteur

    $('#ihm_playpause').bind('click', function() {
        switch($(this).attr('rel')) {
            case 'starting' :
            case 'started' :
                lotOfStopEvents = 5;
                stopCompteur();
            break;

            case 'stopping' :
                ; // Pour l'instant, ne rien faire
            break;

            case 'stopped' :
            default :
                startCompteur();
            break;
        }

        return false;
    });

    $('textarea.medium').attr('disabled', 'disabled');
}

function startCompteur() {
    // Signal, l'info est prise en compte

    $('#ihm_playpause').attr('rel', 'starting');
    $(this).html('Démarrage...');

    // Création d'un timer (server call)

    $.get(controllerBaseUrl + 'get_timer/' + selected_id_projet, function (data) {
        // Retour du serveur

        if($('#ihm_playpause').attr('rel') == 'starting') {
            $('textarea.medium').removeAttr('disabled');

            // Si l'action STOP n'à pas été lancé, on démarre le timer

            idTimer = data.id_timer;

            startDate = new Date();
            intervalIdTime = setInterval('compte();', 100);

            $('#ihm_playpause').attr('rel', 'started');
            $('#ihm_playpause').html('Stopper');
        }
    });
}
function stopCompteur() {
    if(! startDate) {
        $('#ihm_playpause').attr('rel', 'stopped');
        $('#ihm_playpause').html('Démarrer');

        return;
    }

    // Signal, l'info est prise en compte

    $('#ihm_playpause').attr('rel', 'stopping');
    $('#ihm_playpause').html('Arrêt en cours...');

    // Fermeture du dernier timer (server call)

    var dureeDecompte = Math.floor((new Date().getTime() - startDate.getTime()) / 1000);
    var noteComteur = $('textarea').val();

    $.post(controllerBaseUrl + 'set_timer/' + idTimer, {'duree' : dureeDecompte, 'note' : noteComteur}, stopCompteurEvent);
}

var lotOfStopEvents;
function stopCompteurEvent(data) {
    if(data.recept) {
       $('textarea.medium').attr('disabled', 'disabled');

        var passe = temps_total + (new Date().getTime() - startDate.getTime()) / 1000;
        $('#ihm_total').html(Math.floor(passe / 3600) + ':' + (Math.floor(passe / 60 % 60) < 10 ? '0' : '') + Math.floor(passe / 60 % 60));

        startDate = undefined;

        $('#ihm_playpause').attr('rel', 'stopped');
        $('#ihm_playpause').html('Démarrer');

        $('textarea').val('');
        clearInterval(intervalIdTime);
    }
    else if(lotOfStopEvents > 0) {
        // Seconde tentative d'arrêt

        stopCompteur();
        lotOfStopEvents--;
    }
    else {
        // Trop de tentatives ont échoués

        $('#ihm_playpause').attr('rel', 'stopped');
        $('#ihm_playpause').html('Redémarrer');
    }
}

var setFav;

function restartCompteur() {
    if(setFav) {
        setFav = false;

        return;
    }

    if(! startDate) {
        $('#ihm_playpause').attr('rel', 'stopped');
        $('#ihm_playpause').html('Démarrer');

        startCompteur();

        return;
    }

    // Signal, l'info est prise en compte

    $('#ihm_playpause').attr('rel', 'stopping');
    $('#ihm_playpause').html('Arrêt en cours...');

    // Fermeture du dernier timer (server call)

    var dureeDecompte = Math.floor((new Date().getTime() - startDate.getTime()) / 1000);
    var noteComteur = $('textarea').val();

    $.post(controllerBaseUrl + 'set_timer/' + idTimer, {'duree' : dureeDecompte, 'note' : noteComteur}, restartCompteurEvent);
}

function restartCompteurEvent(data) {
    if(data.recept) {
        var passe = temps_total + (new Date().getTime() - startDate.getTime()) / 1000;
        $('#ihm_total').html(Math.floor(passe / 3600) + ':' + (Math.floor(passe / 60 % 60) < 10 ? '0' : '') + Math.floor(passe / 60 % 60));

        startDate = undefined;

        $('#ihm_playpause').attr('rel', 'stopped');
        $('#ihm_playpause').html('Démarrer');

        $('textarea').val('');
        clearInterval(intervalIdTime);

        startCompteur();
    }
    else if(lotOfStopEvents > 0) {
        // Seconde tentative d'arrêt

        restartCompteur();
        lotOfStopEvents--;
    }
    else {
        // Trop de tentatives ont échoués

        $('#ihm_playpause').attr('rel', 'stopped');
        $('#ihm_playpause').html('Redémarrer');
    }
}

////////////////////////////////////////////////////////////////////////////////
// Gestion des favoris

var favorisData;

function loadFavoris() {
    $.get(controllerBaseUrl + 'get_favoris', function(data) {
        favorisData = data;

        setFavoris();
        isFavoris();
    });
}

function setFavoris() {
    if($('#favoris').length > 0) {
        $('#favoris').bind('click', function() {
            if(! $(this).hasClass('selected')) {
                $.get(controllerBaseUrl + 'set_favoris/' + selected_id_projet, function(data) {
                    favorisData = data;

                    if($('#mainContent > div:first').attr('rel') == "Favoris")
                       reloadProjets();
                    else
                        isFavoris();
                });
            }
            else {
                $.get(controllerBaseUrl + 'unset_favoris/' + selected_id_projet, function(data) {
                    favorisData = data;

                    if($('#mainContent > div:first').attr('rel') == "Favoris")
                       reloadProjets();
                    else
                        isFavoris();
                });
            }
        })
    }
}

function isFavoris() {
    if($('#favoris').length > 0 && favorisData) {
        $('#favoris').removeClass('selected');

        for(var i in favorisData)
            if(selected_id_projet == parseInt(favorisData[i].id_projet)) {
                $('#favoris').addClass('selected');
            }
    }
}

function unsetParticipation() {
    if(selected_id_projet) {
        $.get(controllerBaseUrl + 'unset_participation/' + selected_id_projet, function() {
            reloadProjets();
        });
    }
}
////////////////////////////////////////////////////////////////////////////////
// Déchargement des évènements relatifs à chaques pages

function unsetEvents() {
    // Les évènements sur les favoris

    if($('#favoris').length > 0)
       $('#favoris').unbind('click');

     if($('#fermer').length > 0)
       $('#fermer').unbind('click');

    // La liste des clients

    if($('ihm_client').length > 0) {
        var jqRefList = $('ihm_client');

        jqRefList.find('a.selector').unbind('click');
        jqRefList.find('ul').find('li').each(function () {
            $(this).unbind('click');
        });
    }

    // La liste des projets

    if($('ihm_projet').length > 0) {
        var jqRefList = $('ihm_projet');

        jqRefList.find('a.selector').unbind('click');
        jqRefList.find('ul').find('li').each(function () {
            $(this).unbind('click');
        });
    }

    // La liste des due

    if($('ihm_due').length > 0) {
        var jqRefList = $('ihm_due');

        jqRefList.find('a.selector').unbind('click');
        jqRefList.find('ul').find('li').each(function () {
            $(this).unbind('click');
        });
    }
}

////////////////////////////////////////////////////////////////////////////////
// Entrée dans la page

$(document).ready(function () {
    $('#mainContent').css('position', 'relative');
    $('#mainContent').css('overflow', 'hidden');

    $('#mainContent').css('width', 255);
    $('#mainContent').css('height', 300);

    $('#Onglets > li > a').each(function() {
        $(this).bind('click', function() {
            if($(this).attr('href') == '#') {
                $('#Onglets > li > a').removeClass('active');
                $(this).addClass('active');

                if($(this).attr('rel'))
                    setContent($(this).attr('rel'));

                return false;
            }
        });
    });

    $('#Onglets > li > a:first').click();
});

////////////////////////////////////////////////////////////////////////////////
// Changement de page

function setContent(content) {
    stopCompteur();
    unsetEvents();

    if($('#mainContent > div:first').attr('rel') != content) {
        $('#mainContent').css('overflow', 'hidden');
        $('#mainContent').prepend('<div rel="' + content + '">');

        $('#mainContent > div').each(function(i) {
            $(this).css('position', 'absolute');
            $('#mainContent > div:first').css('left', 0);

            $(this).css('top', (i < 1 ? $('#mainContent').height() : 0));
        });

        $('#mainContent > div:first').load('/minitimeheure/get_content/' + content, function() {
            rollContent();
        });
    }
}
function rollContent() {
    $('#mainContent > div').each(function(i) {
        if(i <= 0)
            $(this).clearQueue().animate({top: 0}, resetContent);
        else
            $(this).clearQueue().animate({top: -$('#mainContent').height()}, function() { $(this).remove(); });
    });
}

function resetContent() {
    selected_id_client = undefined;
    selected_id_projet = undefined;

    $('#mainContent > div:first').css('position', 'relative');
    $('#mainContent').css('overflow', 'visible');

    reloadProjets();
    initStartStop();
}
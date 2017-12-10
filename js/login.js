$(document).ready(function() {
    var $body = $('body'),
    $content = $('#content'),
    $form = $content.find('#loginform');


    //IE doen't like that fadein
    if(!$.browser.msie) $body.fadeTo(0,0.0).delay(500).fadeTo(1000, 1);

    $("input").uniform();
});
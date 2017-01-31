window.$ = window.jQuery = require('jquery')
require('bootstrap-sass');

$( document ).ready(function() {
    $( "#load" ).on( "click", function() {
        var current = $( ".inner" ).data('current');
        $.ajax({
            url : 'poem/next/' + current,
            type : 'GET',
            dataType : 'html',
            success : function(html, statut){
                $( ".inner" ).html(html);
                $( ".inner" ).data('current', current + 1);
            }
        });
    });
});
window.$ = window.jQuery = require('jquery')
require('bootstrap-sass');

$( document ).ready(function() {
    $( "#load" ).on( "click", function() {
        var current = $( ".inner" ).data('current');
        $.ajax({
            url : 'poem/next/' + current,
            type : 'GET',
            dataType : 'html',
            beforeSend : function(){
                $( ".inner" ).html('<div class="form-group"><div class="col-md-12 text-center"><span class="glyphicon glyphicon-refresh glyphicon-refresh-animate"></span></div></div>');
            },
            success : function(html, statut){
                $( ".inner" ).html(html);
                $( ".inner" ).data('current', current + 1);
            }
        });
    });
});
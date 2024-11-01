;(function($){
    //get cookie value
    var cookieValue = $.cookie("visitors-tracker-notice");
    //check cookie
    if( cookieValue == 'true' ) {
        $( "#visitor-tracker__container" ).hide();
    }
    else {
        $( "#visitor-tracker__btn" ).on( 'click', function() {
            //set cookie
            $.cookie( "visitors-tracker-notice", "true", { expires: 30 / 1440, path: '/' } );
            $( "#visitor-tracker__container" ).hide();
        } );
    }    
   
})( jQuery );
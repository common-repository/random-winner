jQuery('document').ready( function() {
	
jQuery('#winner').click(function(){
 field_name=jQuery('#field_name').val();	
 win_table=jQuery('#win_table').val();	
 form_name=jQuery('#form_name').val();	
if(jQuery('#recent_win').is(':checked')){
   recent='true';	
}
else{
recent='false';		
}
var request= jQuery.ajax({
   url:ajaxurl,
   method: "POST",
   data: { 
   'action':'aj_ajax',
  	'field_name' : field_name,
   	'win_table':win_table,
   	 'recent':recent,
   	'form_name':form_name },
    
    });
request.done(function(msg) {
  jQuery( "#winners" ).html(msg);
});
 
request.fail(function( jqXHR, textStatus ) {
  alert( "Request failed: " + textStatus );
});

});
});
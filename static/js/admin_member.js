
jQuery( document ).ready(function() {
     
  var divs = jQuery('.row.memberships.content .field');
  for(var i = 0; i < divs.length; i+=5) {
     j = i + 1; 
    divs.slice(i, i+5).wrapAll("<div id='rowgroup"+j+"' class='row-group'></div>");
  }
  
  var html = '<div class="dashicons dashicons-dismiss"></div>';
  if( jQuery('.row.memberships.content') )
    jQuery(html).insertAfter( jQuery('.row.memberships.content .field:nth-child(5n)') );

  // delete function 
  jQuery('.row.memberships.content .dashicons').click(function(e){
    jQuery(this).parent().remove();
    jQuery('input[type="submit"]').trigger('click');    
  });
  
});
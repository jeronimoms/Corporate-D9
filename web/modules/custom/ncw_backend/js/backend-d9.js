jQuery(document).ready(function($) {
  $('tr').each(function(){
    if($('td:contains("Red")', this).length){
      $(this).addClass('red');
    }
    if($('td:contains("Black")', this).length){
      $(this).addClass('black');
    }
    if($('td:contains("Grey")', this).length){
      $(this).addClass('grey');
    }
  });
});

jQuery(document).ready(function($) {
  $('tr').each(function(){
    if($('td:nth-child(1):contains("Red")', this).length){
      $(this).addClass('red');
    }
    if($('td:nth-child(1):contains("Black")', this).length){
      $(this).addClass('black');
    }
    if($('td:nth-child(1):contains("Grey")', this).length){
      $(this).addClass('grey');
    }
  });
});

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

  // make paste from word button work
  CKEDITOR.on("instanceReady", function(event) {
    event.editor.on("beforeCommandExec", function(event) {
      // Show the paste dialog for the paste buttons and right-click paste
      if (event.data.name == "paste") {
        event.editor._.forcePasteDialog = true;
      }
      // Don't show the paste dialog for Ctrl+Shift+V
      if (event.data.name == "pastetext" && event.data.commandData.from == "keystrokeHandler") {
        event.cancel();
      }
    })});
});

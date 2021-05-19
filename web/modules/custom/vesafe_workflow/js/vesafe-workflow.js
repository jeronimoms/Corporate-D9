
(function ($, Drupal) {

  function isEmpty(string) {
    if (string === undefined || string === null || string === '') {
      return true;
    }
    return false;
  }

  $.extend(Drupal.theme, {
    tableDragChangedWarning: function tableDragChangedWarning() {
      var $table = $('table[class^="vw-table-"]');
      if (!isEmpty($table)) {
        var currentRow = $($table).find("tbody tr");
        $.each(currentRow, function (i) {
          var $table = $(currentRow[i]).attr('table');
          var $user = $(currentRow[i]).attr('user');
          var $node = $(currentRow[i]).attr('node');
          var $weight = $(currentRow[i]).find("td select").val();
          if (!isEmpty($user) && !isEmpty($weight) && !isEmpty($node) && !isEmpty($table)) {

            // Set the new weights.
            let data = $.ajax({
              url: '/vesafe_workflow/user_weight/' + $table + '/' + $node + '/' + $user + '/' + $weight,
              method: 'GET',
              async: false
            }).responseText;

            if (Drupal.isEmpty(data)) {
              throw new Error('Data error');
            }

            $.parseJSON(data);
          }
        });
        return "<div class=\"tabledrag-changed-warning messages messages--warning\" role=\"alert\">".concat(Drupal.theme('tableDragChangedMarker'), " ").concat(Drupal.t('The order of users have been saved.'), "</div>");
      } else {
        return "<div class=\"tabledrag-changed-warning messages messages--warning\" role=\"alert\">".concat(Drupal.theme('tableDragChangedMarker'), " ").concat(Drupal.t('You have unsaved changes.'), "</div>");
      }
    },
  });

})(jQuery, Drupal);

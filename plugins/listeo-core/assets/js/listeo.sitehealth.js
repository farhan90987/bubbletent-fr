/* ----------------- Start Document ----------------- */
(function ($) {
  "use strict";

  $(document).ready(function () {
    $(document).on("click", ".listeo-health-check-table-pages .button", function (e) {
      e.preventDefault();
      if (window.confirm("Are you sure?")) {
        var $this = $(this);

        // preparing data for ajax
        var ajax_data = {
          action: "listeo_recreate_page",
          page: $this.data("page"),
          //'nonce': nonce
        };
        $.ajax({
          type: "POST",
          dataType: "json",
          url: ajaxurl,
          data: ajax_data,

          success: function (data) {
            // display loader class
            location.reload();
          },
        });
      }
    });
  });
})(this.jQuery);

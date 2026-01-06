/**
 * Calendar view on single listin
 *
 * @since 1.8.24
 */

(function (window, undefined) {
  window.wp = window.wp || {};
  var document = window.document;
  var $ = window.jQuery;
  var wp = window.wp;
  var $document = $(document);
 var today = new Date().toISOString().slice(0, 10);
  document.addEventListener("DOMContentLoaded", function () {
    var calendarEl = document.getElementById("calendar");
    if(calendarEl){
      var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: "dayGridMonth",
        locale: listeoCal.language,
        
        headerToolbar: {
          left: "prev,next today",
          center: "title",
          right: "",
        },
        validRange: {
          start: today,
        },
        eventTimeFormat: {
          // like '14:30:00'
          hour: "2-digit",
          minute: "2-digit",
          meridiem: false,
        },
        events: function (fetchInfo, successCallback, failureCallback) {
          $.ajax({
            type: "POST",
            dataType: "json",
            url: listeo.ajaxurl,
            data: {
              action: "listeo_get_calendar_view_single_events",
              dates: fetchInfo,
              listing_id: $("#calendar").data("listing-id"),
            },
            success: function (response) {
              var events = [];
              $.each(response, function (i, item) {
                events.push(item);
              });

              successCallback(events);
            },
          });
        },
        loading: function (isLoading) {
          if (isLoading) {
            $(".dashboard-list-box").addClass("loading");
          } else {
            $(".dashboard-list-box").removeClass("loading");
          }
        },
      });
      calendar.render();
    }
  });


})(window);
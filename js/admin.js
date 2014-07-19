jQuery(document).ready(function() {
  addOnClick($);
});

function addOnClick() {
  jQuery('#wppaw-next-page').click(function(evt){
    evt.preventDefault();
    showLoader();
    var data = {
      'action': 'get_recent_pages',
      'page': parseInt(jQuery('#wppaw-current-page').text()) + 1
    };

    // since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
    jQuery.post(ajaxurl, data, function(response) {
      jQuery('#wppaw_recent_dashboard_widget .inside').html(response);
      addOnClick();
    });
  });

  jQuery('#wppaw-prev-page').click(function(evt){
    evt.preventDefault();
    showLoader();
    var data = {
      'action': 'get_recent_pages',
      'page': parseInt(jQuery('#wppaw-current-page').text()) - 1
    };

    // since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
    jQuery.post(ajaxurl, data, function(response) {
      jQuery('#wppaw_recent_dashboard_widget .inside').html(response);
      addOnClick();
    });
  });
}

function showLoader() {
    jQuery('.wppaw-pagination span.loading').show();
}

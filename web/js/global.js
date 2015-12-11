$(document).ready(function() {
  // initialize modals
  $('.modal-immediate').modal('show');

  // initialize tooltips
  var showing = false;
  $('a[href!="#"]:not(.hide-tooltip)').each(function () {
    $(this).tooltip({
      // determine what title of tooltip will be
      'title': function() {

        // if this has a title attribute use that
        if ($(this).attr('title') != '') {
          return $(this).attr('title');
        } else {
          // use href attribute (trim query for space and clarity)
          var title = $(this).attr('href');
          if(title.indexOf('?') === -1) {
            return title;
          } else {
            return title.substring(0, title.indexOf('?'));
          }
        }
      }
    });
  });

  // toggle all tooltips immediately
  $(document).keypress(function() {
    if ( event.which == 113 ) {
      if (showing == true) {
        $('a[href!="#"]:not(.hide-tooltip)').tooltip('hide');
        showing = false;
      } else {
        $('a[href!="#"]:not(.hide-tooltip)').tooltip('show');
        showing = true;
      }
    }
  });

  // initialize collapsible divs
  $('.collapsible.collapsed').siblings('.collapsible-content').hide();
  $('.collapsible').prepend('<span class="caret pull-right" style="margin: .5em 0 .5em .5em"></span> ');
  $('.collapsible').click(function() {
    console.log('clicked');
    if ($(this).hasClass('collapsed')) {
      $(this).siblings('.collapsible-content').slideDown();
      $(this).removeClass('collapsed');
    } else {
      $(this).siblings('.collapsible-content').slideUp();
      $(this).addClass('collapsed');
    }
  });
});

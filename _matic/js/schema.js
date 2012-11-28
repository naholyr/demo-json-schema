$(document).ready(function() {
  // Hide properties blocks.
  $('.properties').hide();

  $('.new-block', '.new-block').hide();

  $('h2').bind('click', function() {
    $(this).siblings('.properties').toggle(300);
  });

  $('.property').bind('click', function() {
    $(this).next('.new-block').toggle(300);
  });
});
$('.katest-skill-button').click(function() {
  $(this).addClass('katest-disabled');
});

$('.katest-submit-button').click(function() {
  var time = new Date();
  var timeString = time.toLocaleTimeString();
  alert('Thanks for submitting your test. Your data is being collected from Khan Academy and a grade shall be issued shortly.\n'+timeString)
});

$(".katest-chosen-select").chosen({width:'250px'});

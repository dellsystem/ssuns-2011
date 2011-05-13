$(document).ready(function() {
	// Hide the "how did you hear about ssuns" thing automatically
    $('#how_hear').hide();
    $('#first_time_yes').click(function() {
    	$('#how_hear').show();
    });
    $('#first_time_no').click(function() {
    	$('#how_hear').hide();
    });
});

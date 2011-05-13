$(document).ready(function() {
	// Hide the "how did you hear about ssuns" thing automatically
    $('#how_hear').hide();
    $('#first_time_yes').click(function() {
    	$('#how_hear').show();
    });
    $('#first_time_no').click(function() {
    	$('#how_hear').hide();
    });
    
    $('#country_dl').hide();
    // Toggling between the country-specific forms (for state/province etc)
    $("#where_school").change(function() {
	    var current_country = $('#where_school option:selected').val();
		if (current_country == 'canada') {
			$('#province_or_state_label').text('Province');
			$('#postal_or_zip_code_label').text('Postal code');
			$('#country_dl').hide();
		} else if (current_country == 'usa') {
		 	$('#province_or_state_label').text('State');
			$('#postal_or_zip_code_label').text('Zip code');
			$('#country_dl').hide();
		} else {
			$('#province_or_state_label').text('Province or state');
			$('#postal_or_zip_code_label').text('Postal code');
			$('#country_dl').show();
		}
	});
	
	// Delete countries from other dropdowns when selected, replace when unselected
	$('[id^=del_choice_]').change(function() {
		// First make sure the selected one isn't 0
		console.log('just selected something in ' + $(this).attr('id'));
		// Do this later ... sure, it would be cool but, food
	});
	// Fuck yeah jQuery is awesome
	
	// Same thing for the specialised agencies etc committee list i guess
	$('[id^=cpm_choice_]').change(function() {
		// First make sure the selected one isn't 0
		console.log('just selected something in ' + $(this).attr('id'));
		// Do this later ... sure, it would be cool but, food
	});
});

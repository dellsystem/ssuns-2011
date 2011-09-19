
// All the jQuery stuff for the registration form
$(document).ready(function() {

	// In case it's preview, do some things initially
	handleCountryChanges();
	
	function handleCountryChanges() {
		var currentCountry = $('#where_school option:selected').val();
		if (currentCountry == 'canada') {
			setCountryCanada();
		} else if (currentCountry == 'usa') {
		 	setCountryUsa();
		} else {
			setCountryInt();
		}
	}

	function setCountryCanada() {
		$('#province_or_state_label').text('Province');
		$('#postal_or_zip_code_label').text('Postal code');
		$('#country_dl').hide();
	}
	
	function setCountryUsa() {
		$('#province_or_state_label').text('State');
		$('#postal_or_zip_code_label').text('Zip code');
		$('#country_dl').hide();
	}
	
	function setCountryInt() {
		$('#province_or_state_label').text('Province or state');
		$('#postal_or_zip_code_label').text('Postal code');
		$('#country_dl').show();
	}

	// Hide the "how did you hear about ssuns" thing automatically ... unless "Yes" is selected
	if (!$('#first_time_yes').attr('checked')) {
	    $('#how_hear_dl').hide();
	}
    $('#first_time_yes').click(function() {
    	$('#how_hear_dl').show();
    });
    $('#first_time_no').click(function() {
    	$('#how_hear_dl').hide();
    });
    
    // Hide it only if it's not set to other ...
    if ($('#how_hear option:selected').val() != 'other') {
		$('#how_hear_other').hide();
	}
    // If "other" is selected for "how did you hear about ssuns" make the input box show up
    $('#how_hear').change(function() {
    	if ($('#how_hear option:selected').val() == 'other') {
    		$('#how_hear_other').show();
    	} else {
    		$('#how_hear_other').hide();
    	}
    });
    
    $('#country_dl').hide();
    // Toggling between the country-specific forms (for state/province etc)
    $("#where_school").change(function() {
		handleCountryChanges();
	});
	
	// An index of 0 corresponds to choice 10 etc
	var delSelections = [];
	
	// Delete countries from other dropdowns when selected, replace when unselected
	$('[id^=del_choice_]').change(function() {
		// First make sure the selected one isn't 0 (it shouldn't be removed if it is)
		var thisID = $(this).attr('id');
		//console.log($(this).val());
		var selection = parseInt($(this).val(), 10);
		//console.log('this ID: ' + thisID);
		//console.log("selection: " + selection);
		
		// Now get the number from the ID
		var selectNumber = parseInt(thisID.charAt(thisID.length-1), 10);
		
		// Now delete it from the other dropdown menus
		if (selection > 0) {
			$('[id^=del_choice_]').not('[id=del_choice_' + selectNumber + ']').each(function() {
				$('#' + $(this).attr('id') + ' > option[value=' + selection + ']').hide();
				//console.log('to hide: ' + $(this).attr('id') + ' > option[value=' + selection + ']');
			});
		}
		
		// Make the previous one show up lol
		var previousSelection = parseInt(delSelections[selectNumber], 10);
		if (previousSelection > 0) {
			// Make that one show up for all the other selects
			$('[id^=del_choice_]').not('[id$=' + selectNumber + ']').each(function() {
				$('#' + $(this).attr('id') + ' > option[value=' + previousSelection + ']').show();
			});
		}
		
		// Add it to the array of previous ones
		delSelections[selectNumber] = selection;
	});
	// Fuck yeah jQuery is awesome
	
	var comSelections = [];
	// Same thing for the specialised agencies etc committee list i guess
	$('[id^=com_choice_]').change(function() {
		// First make sure the selected one isn't 0
		// lolcopyingandpastingcode	
		// First make sure the selected one isn't 0 (it shouldn't be removed if it is)
		var thisID = $(this).attr('id');
		var selection = $(this).val();
		
		// Now get the number from the ID
		var selectNumber = parseInt(thisID.charAt(thisID.length-1), 10);
		
		// Now delete it from the other dropdown menus
		if (selection > 0) {
			$('[id^=com_choice_]').not('[id$=' + selectNumber + ']').each(function() {
				$('#' + $(this).attr('id') + ' > option[value=' + selection + ']').hide();
			});
		}
		
		// Make the previous one show up lol
		var previousSelection = parseInt(delSelections[selectNumber], 10);
		if (previousSelection > 0) {
			// Make that one show up for all the other selects
			$('[id^=com_choice_]').not('[id$=' + selectNumber + ']').each(function() {
				$('#' + $(this).attr('id') + ' > option[value=' + previousSelection + ']').show();
			});
		}
		
		// Add it to the array of previous ones
		comSelections[selectNumber] = selection;
	});
	
	// If the ad-hoc application is desired, have it show up etc, otherwise, hidden
	if (!$('#apply_ad_hoc_yes').attr('checked')) {
		$('#ad_hoc_application').hide();
	}
	$('#apply_ad_hoc_yes').click(function() {
		$('#ad_hoc_application').show();
	});
	$('#apply_ad_hoc_no').click(function() {
		$('#ad_hoc_application').hide();
	});
});

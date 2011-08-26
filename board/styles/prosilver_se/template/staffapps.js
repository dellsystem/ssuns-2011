
// All the jQuery stuff for the staff apps etc
$(document).ready(function() {
	console.log("sigh");
	$('#crisis-image').click(function() {
		// Show the fieldset but hide all the director-related select boxes
		// Has to be done this way so that things at least work without js enabled
		$('#committees-fieldset').show();
		$('select[name^=director_committee]').hide();
		$('select[name^=crisis_committee]').show();
		$('#crisis-radio').attr("checked", true);
		return false;
	});

	$('#director-image').click(function() {
		$('#committees-fieldset').show();
		$('select[name^=crisis_committee]').hide();
		$('select[name^=director_committee]').show();
		$('#director-radio').attr('checked', true);
		return false;
	});

	$('#committees-fieldset').hide();
	$('#hide-this-shit').hide(); // shows up if js is disabled, as a fallback
});

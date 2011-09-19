<?php
define('IN_PHPBB', true);
$phpbb_root_path = './board/';
$phpEx = substr(strrchr(__FILE__, '.'), 1);

/* Handling the registration - displays a receipt confirmation only if there was a submit posted etc */

// To get rid of the "board disabled" message for custom pages
define('NOT_IN_PHPBB', true);

include($phpbb_root_path . 'common.' . $phpEx);

// Start session management
$user->session_begin();
$auth->acl($user->data);
$user->setup();

$submit = (isset($_POST['submit'])) ? true : false;
$errors = 'something';

if ($submit)
{
	// Validate stuff
	$errors = '';
	// input name => mandatory
	$inputs = array(
		'name'					=> '',
		'program'				=> '',
		'telephone'				=> '',
		'email'					=> '',
		'crisis_committee_1' 	=> '',
		'crisis_committee_2'	=> '',
		'crisis_committee_3'	=> '',
		'director_committee_1'	=> '',
		'director_committee_2'	=> '',
		'director_committee_3'	=> '',
		'position_1'			=> '', // logistical
		'position_2'			=> '',
		'position_3'			=> '',
		'attend_training'		=> 0,
		'attend_ssuns'			=> 0,
		'mun_experience'		=> '',
		'anything_else'			=> '',
		'leadership_experience'	=> '',
		'good_candidate'		=> '',
	);
	$choice_1 = ''; // scoping issues?
	$choice_2 = '';
	$choice_3 = '';

	foreach ($inputs as $key => $value)
	{
		// This is a built-in phpBB method, see the online documentation if you need to modify this
		// 0 casts it to an int, '' to a string, etc
		if ($value == 0)
		{
			$$key = request_var($key, $value);
		}
		else
		{
			// If it's a string just support other characters etc
			$$key = utf8_normalize_nfc(request_var($key, $value, true));
		}
	}

	// The first four, the attends, and mun_experience must always be filled out
	// But we can't really check for the attends so whatever
	if ($name == '' || $program == '' || $telephone == '' || $email == '')
	{
		$errors .= '<br />You must fill in the personal information section.';
	}

	// Check if it's logistical or committees
	// Assume that the first position preference always has to be filled
	if ($position_1 != '')
	{
		// Logistical.
		$is_logistical = true;
		$choice_1 = $position_1;
		$choice_2 = $position_2;
		$choice_3 = $position_3;
		if ($leadership_experience == '' || $good_candidate == '' || $mun_experience == '')
		{
			$errors .= '<br />You must fill in the experience text fields.';
		}
	}
	else if ($crisis_committee_1 != '' || $director_committee_1 != '')
	{
		// Committees
		$is_logistical = false;

		// Assume it's crisis committees if that one is filled out
		if ($crisis_committee_1 != '')
		{
			$choice_1 = $crisis_committee_1;
			$choice_2 = $crisis_committee_2;
			$choice_3 = $crisis_committee_3;
		}
		else
		{
			$choice_1 = $director_committee_1;
			$choice_2 = $director_committee_2;
			$choice_3 = $director_committee_3;
		}

		if ($mun_experience == '')
		{
			$errors .= '<br />You must fill in the MUN experience text field.';
		}
	}
	else
	{
		// Someone has not filled in the preferences thing
		$errors .= '<br />You have not selected your top preferred position.';
	}
}

// Now if there are no errors, proceed with updating the database
if ($submit && !$errors)
{

	// Build the query using phpBB's awesome helper functions
	$sql_array = array(
		'name'					=> $name,
		'program'				=> $program,
		'telephone'				=> $telephone,
		'email'					=> $email,
		'choice_1'				=> $choice_1,
		'choice_2'				=> $choice_2,
		'choice_3'				=> $choice_3,
		'attend_training'		=> $attend_training,
		'attend_ssuns'			=> $attend_ssuns,
		'leadership_experience'	=> $leadership_experience,
		'good_candidate'		=> $good_candidate,
		'mun_experience'		=> $mun_experience,
		'anything_else'			=> $anything_else,
		'is_logistical'			=> $is_logistical,
	);

	$sql = 'INSERT INTO ' . STAFF_APPS_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_array);
	$db->sql_query($sql);
	
	include($phpbb_root_path . 'includes/functions_messenger.php');
	$messenger = new messenger(false);
	// Now send off an email to the faculty advisor informing him/her of the registration
	$messenger->template('staff_app');
	$messenger->to($email, $name);
	$messenger->subject('Confirmation of SSUNS staff application');
	$messenger->from("it@ssuns.org");

	$messenger->assign_vars(array(
		'NAME'			=> $name)
	);

	$messenger->send();
}

page_header('');

$template->set_filenames(array(
	'body' => 'staffapp_receipt.html',
));

$template->assign_vars(array(
	'PAGE_TITLE' 			=> 'Receipt of confirmation',
	'SUBMIT'				=> $submit,
	'ERRORS'				=> $errors,
	'OUTSIDE_OF_FORUM' 		=> true)
);

page_footer();
?>

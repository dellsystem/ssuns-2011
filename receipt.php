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

// Now store all the posted information
$submit = (isset($_POST['submit'])) ? true : false;
// Make an array of all the names and defaults
$inputs = array(
	'name_of_school'		=> '',
	'first_time'			=> 0,
	'how_hear'				=> '',
	'how_hear_other'		=> '',
	'fac_ad_name'			=> '',
	'fac_ad_email'			=> '',
	'where_school'			=> '',
	'mailing_address'		=> '',
	'city'					=> '',
	'province_or_state' 	=> '',
	'country'				=> '',
	'postal_or_zip_code' 	=> '',
	'phone_number'			=> '',
	'fax_number'			=> '',
	'number_of_delegates'	=> 0,
	'del_choice_1' 			=> 0,
	'del_choice_2' 			=> 0,
	'del_choice_3' 			=> 0,
	'del_choice_4' 			=> 0,
	'del_choice_5' 			=> 0,
	'del_choice_6' 			=> 0,
	'del_choice_7' 			=> 0,
	'del_choice_8' 			=> 0,
	'del_choice_9' 			=> 0,
	'del_choice_10' 		=> 0,
	'com_choice_1' 			=> 0,
	'com_choice_2' 			=> 0,
	'com_choice_3' 			=> 0,
	'apply_ad_hoc'			=> 0, 
	'ad_hoc_application_form' => '',
	'previous_experience'	=> '',
);

// Nice readable names for the ones we'll be printing out during errors
$nice_names = array(
	'name_of_school'		=> 'name of your school',
	'how_hear'				=> 'how did you hear about SSUNS',
	'how_hear_other'		=> 'how did you hear about SSUNS (other)',
	'fac_ad_name'			=> 'faculty advisor name',
	'fac_ad_email'			=> 'faculty advisor email',
	'where_school'			=> 'location of your school',
	'mailing_address'		=> 'mailing address',
	'city'					=> 'city',
	'province_or_state' 	=> 'province or state',
	'country'				=> 'country',
	'postal_or_zip_code' 	=> 'postal or zip code',
	'phone_number'			=> 'phone number',
	'fax_number'			=> 'fax number',
	'ad_hoc_application_form' => 'ad-hoc application form',
	'previous_experience'	=> 'previous experience',
);

foreach ($inputs as $key => $value)
{
	// This is a built-in phpBB method, see the online documentation if you need to modify this
	// 0 casts it to an int, '' to a string, etc
	$$key = request_var($key, $value);
}

// Now do the validation
$errors = '';

// Of the inputs, only a few can be empty
foreach ($inputs as $key => $value)
{
	// Check the integers first
	if (is_int($$key))
	{
		// Only two can be 0 - the rest cannot be. Actually the committees can be 0.
		if ($$key == 0 && $key != 'apply_ad_hoc' && $key != 'first_time' && $key != 'number_of_delegates' && $key[0] != 'c' )
		{
			// check if it's the delegation or committees that was omitted here
			$errors .= 'You must choose all 10 delegations<br />';
			// Might as well break it here
			break;
		}
		else if ($key == 'number_of_delegates' && ($$key > 35 || $$key < 1))
		{
			// Validate the number of delegates, too
			$errors .= 'Invalid number of delegates<br />';
		} 
	}
	else
	{
		// Should be a string
		// The only ones that are allowed to be empty are: how_hear_other (if how_hear != other), country (if where_school != other), and ad_hoc_application_form (if apply_ad_hoc == 0)
		if (empty($$key))
		{
			// Situations where there is no error:
			if (($key == 'how_hear_other' && ($how_hear != 'other' || $first_time == 0)) || ($key == 'country' && $where_schol != 'other') || ($key == 'ad_hoc_application_form' && $apply_ad_hoc == 0))
			{
				// Do nothing lol
			}
			else
			{
				// Do an associative array lookup to find the nice names
				$errors .= "You have left the <strong>" . $nice_names[$key] . "</strong> field empty.<br />";
			}
		}
	}
}

// Now calculate the total cost lol
// Okay just assume that it's before July 22 for now I can hard-update it later whatever
$delegation_fee = 85;
$delegate_fee = 85;
$total_cost = $delegation_fee + ($delegate_fee * $number_of_delegates);

// Now get the delegation choices ... select from a table I guess?
for ($i = 1; $i <= 10; $i++)
{
	// I am NOT doing 10 sql queries. 10 array lookups it is.
	include_once("delegations_array.php");
	$label = ($i == 1) ? "Choice 1 (top choice)" : "Choice $i";
	$field_name = 'del_choice_' . $i;
	$choice = $delegations[$$field_name];
	$template->assign_block_vars('del', array(
		'LABEL'		=> $label,
		'CHOICE'	=> $choice)
	);
}

// Now for the committees, if they exist
for ($i = 1; $i <= 3; $i++)
{
	$label = ($i == 1) ? "Choice 1 (top choice)" : "Choice $i";
	$field_name = 'com_choice_' . $i;
	include_once("committees_array.php");
	$choice = ($$field_name > 0) ? $committees[$$field_name] : 'None';
	$template->assign_block_vars('com', array(
		'LABEL'		=> $label,
		'CHOICE'	=> $choice)
	);
}

page_header('');

$template->set_filenames(array(
	'body' => 'receipt_body.html',
));

$template->assign_vars(array(
	'PAGE_TITLE' 		=> 'Receipt of confirmation',
	'SUBMIT'			=> $submit,
	'OUTSIDE_OF_FORUM' 	=> true,
	'NAME_OF_SCHOOL'		=> $name_of_school,
	'FIRST_TIME'			=> ($first_time) ? 'Yes' : 'No',
	'HOW_HEAR'				=> ucfirst($how_hear),
	'HOW_HEAR_OTHER'		=> $how_hear_other,
	'FAC_AD_NAME'			=> $fac_ad_name,
	'FAC_AD_EMAIL'			=> $fac_ad_email,
	'WHERE_SCHOOL'			=> ucfirst($where_school),
	'MAILING_ADDRESS'		=> $mailing_address,
	'CITY'					=> $city,
	'POSTAL_OR_ZIP_CODE'	=> $postal_or_zip_code,
	'PROVINCE_OR_STATE'		=> $province_or_state,
	'COUNTRY'				=> $country,
	'PHONE_NUMBER'			=> $phone_number,
	'FAX_NUMBER'			=> $fax_number,
	'APPLY_AD_HOC'			=> ($apply_ad_hoc) ? 'Yes' : 'No',
	'AD_HOC_APPLICATION'	=> $ad_hoc_application_form,
	'PREVIOUS_EXPERIENCE'	=> $previous_experience,
	'TOTAL_COST'			=> $total_cost,
	'DELEGATION_FEE'		=> $delegation_fee,
	'DELEGATE_FEE'			=> $delegate_fee,
	'NUMBER_OF_DELEGATES'	=> $number_of_delegates,
	'ERRORS'			=> $errors,
	'ENABLE_SLIDESHOW' 	=> false)
);

page_footer();
?>

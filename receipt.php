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

include("validate_registration.php");

// Now calculate the total cost lol
// Okay just assume that it's before July 22 for now I can hard-update it later whatever
$delegation_fee = 85;
$delegate_fee = 85;
$total_cost = $delegation_fee + ($delegate_fee * $number_of_delegates);

// Now if there are no errors, proceed with updating the database
if (!$errors && !$preview)
{
	// First get the region ... either INTL, CAN, or US
	switch ($where_school)
	{
		// sigh
		case 'canada':
			$country = 'Canada';
			$region = 'CAN';
			break;
		case 'usa':
			$region = 'US';
			$country = 'USA';
			break;
		default:
			$region = 'INTL';
			break;
	}
	// Build the query using phpBB's awesome helper functions
	$sql_array = array(
		'school_name'			=> $name_of_school,
		'fac_ad_name'			=> $fac_ad_name,
		'fac_ad_email'			=> $fac_ad_email,
		'address'				=> $mailing_address,
		'city'					=> $city,
		'province'				=> $province_or_state,
		'postal_code'			=> $postal_or_zip_code,
		'first_time'			=> $first_time,
		'how_hear'				=> ($how_hear == 'other') ? $how_hear_other : $how_hear,
		'country'				=> $country,
		'phone_number'			=> $phone_number,
		'fax_number'			=> $fax_number,
		'region'				=> $region,
		'registration_time'		=> time(),
		'number_of_delegates'	=> $number_of_delegates,
		'country_choice_1'		=> $del_choice_1,
		'country_choice_2'		=> $del_choice_2,
		'country_choice_3'		=> $del_choice_3,
		'country_choice_4'		=> $del_choice_4,
		'country_choice_5'		=> $del_choice_5,
		'country_choice_6'		=> $del_choice_6,
		'country_choice_7'		=> $del_choice_7,
		'country_choice_8'		=> $del_choice_8,
		'country_choice_9'		=> $del_choice_9,
		'country_choice_10'		=> $del_choice_10,
		'committee_choice_1'	=> $com_choice_1,
		'committee_choice_2'	=> $com_choice_2,
		'committee_choice_3'	=> $com_choice_3,
		'apply_ad_hoc'			=> $apply_ad_hoc,
		'ad_hoc_application'	=> $ad_hoc_application_form,
		'previous_experience'	=> $previous_experience,
	);

	$sql = 'INSERT INTO ' . SCHOOLS_CONTACT_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_array);
	$db->sql_query($sql);
}

page_header('');

// Show the receipt body if submitted, but show the cp_registration page if in preview mode
if ($preview)
{
	$template->set_filenames(array(
		'body'	=> 'cp_registration.html',
	));
}
else
{
	$template->set_filenames(array(
		'body' => 'receipt_body.html',
	));
}

$template->assign_vars(array(
	'PAGE_TITLE' 			=> 'Receipt of confirmation',
	'SUBMIT'				=> $submit,
	'IN_PREVIEW'			=> $preview,
	'OUTSIDE_OF_FORUM' 		=> true,
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
	'ERRORS'				=> $errors,
	'DEL_CHOICE_1'			=> $del_choice_1,
	'DEL_CHOICE_2'			=> $del_choice_2,
	'DEL_CHOICE_3'			=> $del_choice_3,
	'DEL_CHOICE_4'			=> $del_choice_4,
	'DEL_CHOICE_5'			=> $del_choice_5,
	'DEL_CHOICE_6'			=> $del_choice_6,
	'DEL_CHOICE_7'			=> $del_choice_7,
	'DEL_CHOICE_8'			=> $del_choice_8,
	'DEL_CHOICE_9'			=> $del_choice_9,
	'DEL_CHOICE_10'			=> $del_choice_10,
	'COM_CHOICE_1'			=> $com_choice_1,
	'COM_CHOICE_2'			=> $com_choice_2,
	'COM_CHOICE_3'			=> $com_choice_3,
	'ENABLE_SLIDESHOW' 		=> false)
);

page_footer();
?>

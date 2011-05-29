<?php
/**
*
* @package Custom Pages MOD
* @version $Id: acp_custom_pages.php ilostwaldo@gmail.com$
* @copyright (c) 2011 dellsystem (www.dellsystem.me)
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

class acp_registration {
   var $u_action;
   var $new_config;
   
   function main($id, $mode)
   {
		global $phpbb_root_path, $db, $phpEx, $auth, $user, $template, $config;
		
     	$submit = (isset($_POST['submit'])) ? true : false;
		switch($mode)
		{
			case 'overview':
				$edit_id = request_var('edit', 0);
				$delete_id = request_var('delete', 0);
				$approve_id = request_var('approve', 0);
				
				if ($edit_id > 0) {
				
					// Editing something
					$sql = "SELECT *
							FROM " . SCHOOLS_CONTACT_TABLE . "
							WHERE school_id = $edit_id";
					$result = $db->sql_query($sql);
					$row = $db->sql_fetchrow($result);
					
					// Assuming the school name hasn't been changed ... could be problematic one day
					$school_name = $row['school_name'];
					
					// If we're submitting
					if ($submit)
					{
						$is_approved = request_var('is_approved', '');
						// Check if we're newly approving the user
						$approved_text = '';
						if ($is_approved == 'on' && $row['is_approved'] == 0)
						{
							$approved_text = 'and approved ';
							// Add it to the log
							add_log('admin', 'LOG_APPROVE_SCHOOL', $row['school_name']);
							// meh language constants
							// First create a new user for that school
							// Then send out the approval email
							include($phpbb_root_path . 'includes/functions_messenger.php');
							$messenger = new messenger(false);
							
							// Now send off an email to the faculty advisor informing him/her of the registration
							$messenger->template('registration_approved');
							$messenger->to($row['fac_ad_email'], $row['fac_ad_name']);
							$messenger->subject('SSUNS registration approved');
							$messenger->from("it@ssuns.org");

							$messenger->assign_vars(array(
								'FAC_AD_NAME'			=> $fac_ad_name,
								'SCHOOL_NAME'			=> $row['school_name'],
								'SCHOOL_USERNAME'		=> 'not yet lol',
								'SCHOOL_PASSWORD'		=> 'me neither lol')
							);

							$messenger->send();
						}
						
						$sql_array = array(
							'school_name'			=> utf8_normalize_nfc(request_var('school_name', '', true)),
							'fac_ad_name'			=> utf8_normalize_nfc(request_var('school_name', '', true)),
							'fac_ad_email'			=> utf8_normalize_nfc(request_var('fac_ad_email', '', true)),
							'address'				=> utf8_normalize_nfc(request_var('address', '', true)),
							'city'					=> utf8_normalize_nfc(request_var('city', '', true)),
							'province'				=> request_var('province', ''),
							'postal_code'			=> request_var('postal_code', ''),
							'country'				=> utf8_normalize_nfc(request_var('country', '', true)),
							'number_of_delegates'	=> request_var('number_of_delegates', 0),
							'country_choice_1'		=> request_var('country_choice_1', 0),
							'country_choice_2'		=> request_var('country_choice_2', 0),
							'country_choice_3'		=> request_var('country_choice_3', 0),
							'country_choice_4'		=> request_var('country_choice_4', 0),
							'country_choice_5'		=> request_var('country_choice_5', 0),
							'country_choice_6'		=> request_var('country_choice_6', 0),
							'country_choice_7'		=> request_var('country_choice_7', 0),
							'country_choice_8'		=> request_var('country_choice_8', 0),
							'country_choice_9'		=> request_var('country_choice_9', 0),
							'country_choice_10'		=> request_var('country_choice_10', 0),
							'committee_choice_1'	=> request_var('committee_choice_1', 0),
							'committee_choice_2'	=> request_var('committee_choice_2', 0),
							'committee_choice_3'	=> request_var('committee_choice_3', 0),
							'apply_ad_hoc'			=> request_var('apply_ad_hoc', 0),
							'previous_experience'	=> utf8_normalize_nfc(request_var('previous_experience', '')),
							'is_approved'			=> request_var('is_approved', 0),
						);
						
						// Now do the SQL update for the table (ONLY DO IT NOW, OTHERWISE $row['is_approved'] IS FUCKED
						$sql = "UPDATE " . SCHOOLS_CONTACT_TABLE . "
								SET " . $db->sql_build_array('UPDATE', $sql_array) . "
								WHERE school_id = $edit_id";
         				$db->sql_query($sql);
         				
						add_log('admin', 'LOG_EDIT_SCHOOL', $row['school_name']);
						trigger_error("Successfully edited " . $approved_text . "school." . adm_back_link($this->u_action)); 
					}
					
					$this->page_title = 'Editing ' . $school_name;
					$this->tpl_name = 'acp_registration_edit';
					
					$template->assign_vars(array(
						'SCHOOL_NAME'		=> $school_name,
						'FAC_AD_NAME'		=> $row['fac_ad_name'],
						'FAC_AD_EMAIL'		=> $row['fac_ad_email'],
						'ADDRESS'			=> $row['address'],
						'CITY'				=> $row['city'],
						'PROVINCE'			=> $row['province'],
						'POSTAL_CODE'		=> $row['postal_code'],
						'FIRST_TIME'		=> $row['first_time'],
						'HOW_HEAR'			=> $row['how_hear'],
						'COUNTRY'			=> $row['country'],
						'PHONE_NUMBER'		=> $row['phone_number'],
						'FAX_NUMBER'		=> $row['fax_number'],
						'REGION'			=> $row['region'],
						'REGISTRATION_DATE'	=> $user->format_date($row['registration_time']),
						'NUMBER_OF_DELEGATES'	=> $row['number_of_delegates'],
						'APPLY_AD_HOC'		=> $row['apply_ad_hoc'],
						'PREVIOUS_EXPERIENCE'	=> $row['previous_experience'],
						'IS_APPROVED'		=> $row['is_approved'])
					);
					
					include_once($phpbb_root_path . '../delegations_array.php');
					include_once($phpbb_root_path . '../committees_array.php');
					
					// Now figure out the country choices etc
					for ($i = 1; $i <= 10; $i++)
					{
						$dropdown = '<select name="country_choice_' . $i . '">';
						$j = 0;
						foreach ($delegations as $delegation)
						{
							// skip over the first one because it's just ''
							if ($j > 0)
							{
								$this_country_choice = 'country_choice_' . $i;
								
								$selected = ($j == $row[$this_country_choice]) ? 'selected="selected"' : '';
								$dropdown .= '<option value="' . $j . '" ' . $selected . '>' . $delegation . '</option>';
							}
							$j++;
						}
						$dropdown .= '</select>';
						$template->assign_block_vars('country', array(
							'NUMBER'		=> $i,
							'DROPDOWN'		=> $dropdown,
						));
					}
					
					// Same for the committee choices
					for ($i = 1; $i <= 3; $i++)
					{
						$dropdown = '<select name="committee_choice_' . $i . '">';
						$j = 0;
						foreach ($committees as $committee)
						{
							// skip over the first one because it's just ''
							if ($j > 0)
							{
								$this_committee_choice = 'committee_choice_' . $i;
								
								$selected = ($j == $row[$this_committee_choice]) ? 'selected="selected"' : '';
								$dropdown .= '<option value="' . $j . '" ' . $selected . '>' . $committee . '</option>';
							}
							$j++;
						}
						$dropdown .= '</select>';
						$template->assign_block_vars('committee', array(
							'NUMBER'		=> $i,
							'DROPDOWN'		=> $dropdown,
						));
					}
				} else if ($delete_id > 0) {
				} else if ($approve_id > 0) {
				} else {
					$this->page_title = 'SSUNS registration management';
					$this->tpl_name = 'acp_registration';
				
					// Shows you all the schools
					$sql = "SELECT school_id, school_name, country, registration_time, number_of_delegates
							FROM " . SCHOOLS_CONTACT_TABLE . "
							ORDER BY registration_time DESC";
					$result = $db->sql_query($sql);
				
					while ($row = $db->sql_fetchrow($result)) {
						$template->assign_block_vars('schools', array(
							'ID'					=> $row['school_id'],
							'NAME'					=> $row['school_name'],
							'COUNTRY'				=> $row['country'],
							'REGISTRATION_TIME'		=> $user->format_date($row['registration_time']),
							'NUMBER_OF_DELEGATES'	=> $row['number_of_delegates'],
							'IS_APPROVED'			=> $row['is_approved'],
							'U_EDIT'				=> $this->u_action . '&amp;edit=' . $row['school_id'],
							'U_DELETE'				=> $this->u_action . '&amp;delete=' . $row['school_id'],
							'U_APPROVE'				=> $this->u_action . '&amp;approve=' . $row['school_id'])
						);
					}
				}
            break;
      	}
	}
}

?>

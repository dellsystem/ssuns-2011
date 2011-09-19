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
			case 'matrix':
				$this->page_title = 'Country-committee matrix';
				$this->tpl_name = 'acp_registration_matrix';

				include($phpbb_root_path . '../committees_array.php');
				include($phpbb_root_path . '../delegations_array.php');

				$sql = "SELECT *
						FROM " . CCM_TABLE; // don't need ordering lol (slightly more efficient this way I think)
				$result = $db->sql_query($sql);

				// Yeah okay two while loops but it's better than 1000 db queries so whatever
				// Builds up the CCM data structure (2D array)
				$country_assignments = array();
				while ($row = $db->sql_fetchrow($result))
				{
					$country_id = $row['country_id'];
					$committee_id = $row['committee_id'];
					$num_delegates = $row['num_delegates'];

					// array_key_exists I don't even, PHP why do you hate OOP
					if (!array_key_exists($country_id, $country_assignments))
					{
						$country_assignments[$country_id] = array();
					}

					$country_assignments[$country_id][$committee_id] = $num_delegates;
				}

				foreach ($country_assignments as $country => $committees)
				{
					// Loop through all the possible committee assignments in this matrix
					$template_vars_array = array(
						'COUNTRY' 	=> $delegations[$country],
						'ID'		=> $country,
						//'TOTAL' 	=> "Doesn't work", // it really doesn't ... think of a workaround sometime
						// Never mind found a workaround that is actually kind of clever
						'TOTAL'		=> array_sum($committees),
					);
					$template->assign_block_vars('matrix', $template_vars_array);
					for ($i = 0; $i < 10; $i++)
					{
						if (isset($committees[$i]))
						{
							$num_delegates = $committees[$i];
						}
						else
						{
							$num_delegates = 0;
						}
						$template->assign_block_vars('matrix.committees', array(
							'NAME'			=> $ccm_committees[$i],
							'NUM_DELEGATES'	=> $num_delegates,
						));
					}
				}

				// Stupid stuff for the table headers
				for ($i = 0; $i < 10; $i++)
				{
					$template->assign_block_vars('header', array(
						'NAME'		=> $ccm_committees[$i],
						'ID'		=> $i,
					));
				}
			break;
			case 'assign':
				$school_id = request_var('id', 0);
				$sql = "SELECT *
						FROM " . SCHOOLS_CONTACT_TABLE . "
						WHERE school_id = $school_id";
				$result = $db->sql_query($sql);
				$row = $db->sql_fetchrow($result);

				$this->page_title = 'Assigning stuff for ' . $row['school_name'];
				$this->tpl_name = 'acp_registration_assign';

				include($phpbb_root_path . '../committees_array.php');
				include($phpbb_root_path . '../delegations_array.php');

				$template->assign_vars(array(
					'NUMBER_OF_DELEGATES'		=> $row['number_of_delegates'],
				));

				for ($i = 1; $i <= 10; $i++)
				{
					$country_id = $row['country_choice_' . $i];
					$template->assign_block_vars('countries', array(
						'ID' => $country_id,
						'CHOICE' => $delegations[$country_id],
					));
				}

				for ($i = 1; $i <= 3; $i++)
				{
					$committee_id = $row['committee_choice_' . $i];
					$template->assign_block_vars('committees', array(
						'ID' => $committee_id,
						'CHOICE'	=> $committees[$committee_id],
					));
				}

				// Now get all the POSSIBLE COUNTRIES and the total number of delegates for each
				$sql = "SELECT SUM(num_delegates) as sum, country_id
						FROM " . CCM_TABLE . "
						GROUP BY country_id";
				$result = $db->sql_query($sql);

				while ($row = $db->sql_fetchrow($result))
				{
					$country_id = $row['country_id'];
					$template->assign_block_vars('ca', array(
						'ID' => $country_id,
						'TEXT' => $delegations[$country_id],
						'NUM_DELEGATES' => $row['sum'], // use js to do total calculations etc
					));
				}
						
			break;
			case 'finances':
				$school_id = request_var('id', 0);
				$sql = "SELECT *
						FROM " . SCHOOLS_CONTACT_TABLE . "
						WHERE school_id = $school_id";
				$result = $db->sql_query($sql);
				$row = $db->sql_fetchrow($result);

				if ($submit)
				{
					$new_amount_owed = request_var('amount_owed', 0);
					$new_amount_paid = request_var('amount_paid', 0);

					$sql = "UPDATE " . SCHOOLS_CONTACT_TABLE . "
							SET amount_owed = $new_amount_owed, amount_paid = $new_amount_paid
							WHERE school_id = $school_id";
					$db->sql_query($sql);

					add_log('admin', 'LOG_FINANCES_SCHOOL', $row['school_name']);
					trigger_error("Successfully edited financial information for " . $row['school_name'] . "." . adm_back_link($this->u_action . '&amp;mode=overview')); // stupidest thing ever 
				}

				$this->page_title = 'Viewing financial information for ' . $row['school_name'];
				$this->tpl_name = 'acp_registration_finances';

				$template->assign_vars(array(
					'NUMBER_OF_DELEGATES'		=> $row['number_of_delegates'],
					'AMOUNT_OWED'				=> $row['amount_owed'],
					'AMOUNT_PAID'				=> $row['amount_paid'],
				));
			break;
			case 'overview':
				$edit_id = request_var('edit', 0);
				$delete_id = request_var('delete', 0);
				$approve_id = request_var('approve', 0);
				
				if ($edit_id > 0)
				{
				
					// Editing something
					$sql = "SELECT *
							FROM " . SCHOOLS_CONTACT_TABLE . "
							WHERE school_id = $edit_id";
					$result = $db->sql_query($sql);
					$row = $db->sql_fetchrow($result);
					
					// If we're submitting
					if ($submit)
					{
						$is_approved = request_var('is_approved', ''); // will be "on" if selected
						$school_name = utf8_normalize_nfc(request_var('school_name', '', true));
						$fac_ad_name = utf8_normalize_nfc(request_var('fac_ad_name', '', true));
						$fac_ad_email = utf8_normalize_nfc(request_var('fac_ad_email', '', true));
						
						$sql_array = array(
							'school_name'			=> $school_name,
							'fac_ad_name'			=> $fac_ad_name,
							'fac_ad_email'			=> $fac_ad_email,
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
							'is_approved'			=> ($is_approved == 'on' || $row['is_approved'] > 0) ? 1 : 0,
						);
						
						// Check if we're newly approving the user
						$approved_text = '';
						if ($is_approved == 'on' && $row['is_approved'] == 0)
						{
							$approved_text = 'and approved ';
							$password = $this->generate_random_password();
							// Add it to the log
							add_log('admin', 'LOG_APPROVE_SCHOOL', $school_name);
							// meh language constants
							// First create a new user for that school
							$user_row = array(
								'username'              => $school_name,
								'user_password'         => phpbb_hash($password),
								'user_email'            => $fac_ad_email,
								'group_id'              => 9, // Hardcoded for now because the Schools group is 9
								'user_timezone'         => '-5', // Whatever
								'user_dst'              => 0,
								'user_lang'             => 'en',
								'user_type'             => USER_NORMAL,
								'user_actkey'           => '',
								'user_ip'               => '', // Don't have anything for this
								'user_regdate'          => time(),
								'user_inactive_reason'  => '',
								'user_inactive_time'    => time(), // not sure if need
							);

							// all the information has been compiled, add the user
							// tables affected: users table, profile_fields_data table, groups table, and config table.
							include($phpbb_root_path . 'includes/functions_user.php');
							$user_id = user_add($user_row);
							
							// Then send out the approval email
							include($phpbb_root_path . 'includes/functions_messenger.php');
							$messenger = new messenger(false);

							// Now send off an email to the faculty advisor informing him/her of the registration
							$messenger->template('registration_approved');
							$messenger->to($fac_ad_email, $fac_ad_name);
							$messenger->subject('SSUNS registration approved');
							$messenger->from("it@ssuns.org");
						
							$messenger->assign_vars(array(
								'FAC_AD_NAME'			=> $fac_ad_name,
								'SCHOOL_NAME'			=> $school_name,
								'SCHOOL_USERNAME'		=> $school_name,
								'SCHOOL_PASSWORD'		=> $password)
							);

							// Should send a copy to it@ssuns.org as well
							$messenger->bcc('it@ssuns.org');
							$messenger->send();
						}
						
						
						// Now do the SQL update for the table (ONLY DO IT NOW, OTHERWISE $row['is_approved'] IS FUCKED
						$sql = "UPDATE " . SCHOOLS_CONTACT_TABLE . "
								SET " . $db->sql_build_array('UPDATE', $sql_array) . "
								WHERE school_id = $edit_id";
         				$db->sql_query($sql);
         				
						add_log('admin', 'LOG_EDIT_SCHOOL', $row['school_name']);
						trigger_error("Successfully edited " . $approved_text . "school." . adm_back_link($this->u_action)); 
					}
					
					$this->page_title = 'Editing ' . $row['school_name'];
					$this->tpl_name = 'acp_registration_edit';
					
					$template->assign_vars(array(
						'SCHOOL_NAME'		=> $row['school_name'],
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
				}
				else if ($delete_id > 0)
				{
					// Do the confirm box thing whatever before deleting
					if (confirm_box(true))
					{
						$sql = "DELETE FROM " . SCHOOLS_CONTACT_TABLE . "
								WHERE school_id = $delete_id";
						$db->sql_query($sql);
						 
						trigger_error('Successfully deleted school' . adm_back_link($this->u_action));
					}
					else
					{
						$s_hidden_fields = build_hidden_fields(array(
							'submit'    => true,
							)
						);
				
						confirm_box(false, 'Are you sure to want to delete this school?', $s_hidden_fields);
					}
				}
				else
				{
					$this->page_title = 'SSUNS registration management';
					$this->tpl_name = 'acp_registration';
				
					// Shows you all the schools
					$sql = "SELECT school_id, school_name, country, registration_time, number_of_delegates, is_approved, amount_paid, amount_owed
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
							'AMOUNT_PAID'			=> $row['amount_paid'],
							'AMOUNT_OWED'			=> $row['amount_owed'],
							'U_EDIT'				=> $this->u_action . '&amp;edit=' . $row['school_id'],
							'U_DELETE'				=> $this->u_action . '&amp;delete=' . $row['school_id'],
							'U_APPROVE'				=> $this->u_action . '&amp;approve=' . $row['school_id'],
							'U_ASSIGN'				=> $this->u_action . '&amp;mode=assign&amp;id=' . $row['school_id'],
							'U_FINANCES'			=> $this->u_action . '&amp;mode=finances&amp;id=' . $row['school_id'],
						));
					}
				}
            break;
      	}
	}
	   // For creating the new user - from http://www.laughing-buddha.net/php/lib/password
	function generate_random_password($length = 10)
	{
		$password = '';
		$possible_chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$possible_length = strlen($possible_chars);
		
		$i = 0;
		while ($i < $length)
		{
			// Choose a random character
			$char = substr($possible_chars, mt_rand(0, $possible_length-1), 1);
			
			// Have we already used this character?
			if (!strstr($password, $char))
			{ 
		   		// No, so it's OK to add it onto the end of whatever we've already got...
		    	$password .= $char;
		    	$i++;
		    }
		}
		return $password;
	}
}

?>

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
			// A character-committee matrix but the name 'matrix' was already taken
			// Mostly for verifying that all the data got put in correctly
			case 'characters':
				$this->page_title = 'Character-committee matrix';
				$this->tpl_name = 'acp_registration_characters';

				include($phpbb_root_path . '../committees_array.php');

				// Editing a character etc
				// Should only be able to edit that character's name
				$edit_id = request_var('edit', 0);
				if ($edit_id > 0)
				{
					// Update the character's name
					if ($submit)
					{
						$new_name = utf8_normalize_nfc(request_var('character_name', '', true));
						$sql = "UPDATE " . CHARACTERS_TABLE . "
								SET character_name = '$new_name'
								WHERE character_id = $edit_id";
						$db->sql_query($sql);
						trigger_error("Successfully edited character name." . adm_back_link($this->u_action . '&amp;mode=characters'));
					}
					$sql = "SELECT c.*, s.school_name
							FROM " . CHARACTERS_TABLE . " AS c
							LEFT JOIN " . COM_ASSIGNMENTS_TABLE . " AS t
							ON t.character_id = c.character_id
							LEFT JOIN " . SCHOOLS_CONTACT_TABLE . " AS s
							ON t.school_id = s.school_id
							WHERE c.character_id = $edit_id";
					$result = $db->sql_query($sql);
					$row = $db->sql_fetchrow($result);
					$committee_id = $row['committee_id'];
					$character_name = $row['character_name'];

					$this->page_title = 'Editing a character';
					$this->tpl_name = 'acp_registration_char_edit';

					$template->assign_vars(array(
						'COMMITTEE'			=> $committees[$committee_id],
						'CHARACTER_NAME'	=> $character_name,
						'SCHOOL'			=> $row['school_name'],
					));
				}
				else
				{
					// Regular list view

					// Key: committee_id; value: array, character_id, then character_name, fuck the descriptions who needs that
					$characters = array();
					$students = array(); // mapping character ID to student name

					$sql = "SELECT c.*, u.username
							FROM " . CHARACTERS_TABLE . " AS c
							LEFT JOIN " . DELEGATES_TABLE . " AS d
							ON c.character_id = d.position_id
							LEFT JOIN " . USERS_TABLE . " AS u
							ON u.user_id = d.user_id
							WHERE d.is_country = 0 OR d.is_country IS NULL";
					$result = $db->sql_query($sql);
					while ($row = $db->sql_fetchrow($result))
					{
						$committee_id = $row['committee_id'];
						$character_id = $row['character_id'];
						$character_name = $row['character_name'];
						$student_name = $row['username'];
						$students[$character_id] = $student_name;
						if (array_key_exists($committee_id, $characters))
						{
							// Add a new element to the array
							$characters[$committee_id][$character_id] = $character_name;
						}
						else
						{
							// Otherwise, create the array
							$characters[$committee_id] = array($character_id => $character_name);
						}
					}

					// Populate the committees loop using the data in committees_array
					for ($i = 1; $i <= 11; $i++)
					{
						$template->assign_block_vars('committees', array(
							'NAME'		=> $committees[$i],
						));

						// Loop through all the characters for this committee, if there are any
						if (array_key_exists($i, $characters))
						{
							foreach ($characters[$i] as $char_id => $char_name)
							{
								$template->assign_block_vars('committees.characters', array(
									'U_EDIT'	=> $this->u_action . '&amp;edit=' . $char_id,
									'NAME'		=> $char_name,
									'STUDENT'	=> $students[$char_id],
								));
							}
						}
					}
				}
			break;
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

				if ($submit)
				{
					// Figure out how many country assignments we need
					$assignment_num = 1;
					$country_assignments = array();

					// Delete all the original country assignments lol
					$sql = "DELETE FROM " . ASSIGNMENTS_TABLE . "
							WHERE school_id = $school_id";
					$db->sql_query($sql);

					while ($country_assignment = request_var('country_assignment_' . $assignment_num, 0)) // assignment, not equality-checking
					{
						if (!in_array($country_assignment, $country_assignments))
						{
							$country_assignments[] = $country_assignment;
						}
						$assignment_num++;
					}

					// Now create another array for use with sql_multi_insert (more efficient etc)
					$insert_array = array();
					foreach ($country_assignments as $country_id)
					{
						$insert_array[] = array(
							'country_id' 	=> $country_id,
							'school_id'		=> $school_id,
						);
					}
					$db->sql_multi_insert(ASSIGNMENTS_TABLE, $insert_array);

					// Delete all the original committee assignments lol
					$sql = "DELETE FROM " . COM_ASSIGNMENTS_TABLE . "
							WHERE school_id = $school_id";
					$db->sql_query($sql);

					// Now the characters ... same thing as above
					$char_num = 1;
					$char_assignments = array();
					while ($char_assignment = request_var('char_assignment_' . $char_num, 0))
					{
						if (!in_array($char_assignment, $char_assignments))
						{
							// Don't try to assign something twice, etc
							$char_assignments[] = $char_assignment;
						}
						$char_num++;
					}

					// Another insert array ... why again? I think this is probably stupid
					$insert_array = array();
					foreach ($char_assignments as $char_id)
					{
						$insert_array[] = array(
							'character_id'	=> $char_id,
							'school_id'		=> $school_id,
						);
					}
					$db->sql_multi_insert(COM_ASSIGNMENTS_TABLE, $insert_array);
					
					trigger_error("Successfully edited country assignment information for " . $row['school_name'] . "." . adm_back_link($this->u_action . '&amp;mode=overview'));
				}

				$template->assign_vars(array(
					'U_ACTION'					=> $this->u_action . '&amp;id=' . $school_id,
					'NUMBER_OF_DELEGATES'		=> $row['number_of_delegates'],
				));

				for ($i = 1; $i <= 10; $i++)
				{
					$country_id = $row['country_choice_' . $i];
					$template->assign_block_vars('countries', array(
						'ID' => $country_id,
						'CHOICE' => $fake_delegations[$country_id],
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

				// Get all the possible characters that can be assigned
				// Only one SQL query, like a boss
				// Note: see if the other query can be condensed into one using a join as well
				$sql = "SELECT m.character_id, m.committee_id, m.character_name, a.school_id, s.school_name
						FROM " . CHARACTERS_TABLE . " as m
						LEFT JOIN " . COM_ASSIGNMENTS_TABLE . " as a
						ON m.character_id = a.character_id
						LEFT JOIN " . SCHOOLS_CONTACT_TABLE . " as s
						ON a.school_id = s.school_id";
				$result = $db->sql_query($sql);

				$characters = array();
				$chars_already_assigned = array();
				$this_school_chars = array(); // chars assigned to this school, so we don't have to do another query
				while ($row = $db->sql_fetchrow($result))
				{
					$committee_id = $row['committee_id'];
					$char_id = $row['character_id'];
					$char_name = $row['character_name'];
					$school_name = $row['school_name'];
					$this_school_id = $row['school_id'];
					if (array_key_exists($committee_id, $characters))
					{
						$characters[$committee_id][$char_id] = $char_name;
					}
					else
					{
						$characters[$committee_id] = array($char_id => $char_name);
					}

					// If the school_id is not null, it has already been assigned
					if ($this_school_id > 0)
					{
						$chars_already_assigned[$char_id] = array('id' => $this_school_id, 'name' => $school_name);
					}

					if ($school_id == $this_school_id)
					{
						$this_school_chars[] = $char_id;
					}
				}

				// Now loop through all the committees
				// No schools already assigned ...
				if (count($this_school_chars) == 0)
				{
					$template->assign_block_vars('char_assignments', array(
						'ID'	=> 1,
					));

					for ($i = 1; $i <= 11; $i++)
					{

						$template->assign_block_vars('char_assignments.groups', array(
							'COMMITTEE'		=> $committees[$i], // use js to do total calculations etc
						));

						$com_chars = $characters[$i];
						foreach ($com_chars as $char_id => $char_name)
						{
							if (array_key_exists($char_id, $chars_already_assigned))
							{
								$assigned_name = $chars_already_assigned[$char_id]['name'];
								$assigned_id = $chars_already_assigned[$char_id]['id']; // for faster comparison etc
							}
							$template->assign_block_vars('char_assignments.groups.chars', array(
								'ID'				=> $char_id,
								'TEXT'				=> (strlen($char_name) > 80) ? substr($char_name, 0, 80) . ' ...' : $char_name, // don't want it to be too long
								'ALREADY_ASSIGNED'	=> (array_key_exists($char_id, $chars_already_assigned) && $assigned_id != $school_id) ?$assigned_name : '',
								'SELECTED'			=> (array_key_exists($char_id, $chars_already_assigned) && $assigned_id == $school_id) ? 'selected="selected"' : '',// much better (although the initial comparison should not be necessary ... it is atm though)
							));
						}
					}
				}
				else
				{
					// Ughhhhhhh
					$num_char_dropdowns = count($this_school_chars);
					for ($i = 1; $i <= $num_char_dropdowns; $i++)
					{
						$template->assign_block_vars('char_assignments', array(
							'ID'	=> $i,
						));

						for ($j = 1; $j <= 11; $j++)
						{

							$template->assign_block_vars('char_assignments.groups', array(
								'COMMITTEE'		=> $committees[$j], // use js to do total calculations etc
							));

							$com_chars = $characters[$j];
							foreach ($com_chars as $char_id => $char_name)
							{
								if (array_key_exists($char_id, $chars_already_assigned))
								{
									$assigned_name = $chars_already_assigned[$char_id]['name'];
									$assigned_id = $chars_already_assigned[$char_id]['id']; // for faster comparison etc
								}
								$template->assign_block_vars('char_assignments.groups.chars', array(
									'ID'				=> $char_id,
									'TEXT'				=> (strlen($char_name) > 80) ? substr($char_name, 0, 80) . ' ...' : $char_name, // don't want it to be too long
									'ALREADY_ASSIGNED'	=> (array_key_exists($char_id, $chars_already_assigned) && $assigned_id != $school_id) ? $assigned_name : '',
									'SELECTED'			=> ($this_school_chars[$i-1] == $char_id) ? 'selected="selected"' : '',// much better (although the initial comparison should not be necessary ... it is atm though)
								));
							}
						}
					}
				}

				// Now for country assignment
				// First get all the POSSIBLE COUNTRIES and the total number of delegates for each
				// Store them in an array, they need to be referenced later
				$sql = "SELECT SUM(num_delegates) as sum, country_id
						FROM " . CCM_TABLE . "
						GROUP BY country_id";
				$result = $db->sql_query($sql);

				$country_delegates = array();
				while ($row = $db->sql_fetchrow($result))
				{
					$country_delegates[$row['country_id']] = $row['sum'];
				}

				// First get all the countries that have already been assigned to OTHER schools
				// Seems like a stupid way to do this ... think about it another time when it's not 4AM
				// Don't delete them from view, just say that they have already been assigned in an error message
				$sql = "SELECT a.country_id, a.school_id, s.school_name
						FROM " . ASSIGNMENTS_TABLE . " AS a
						LEFT JOIN " . SCHOOLS_CONTACT_TABLE . " AS s
						ON a.school_id = s.school_id
						WHERE a.school_id <> $school_id";
				$result = $db->sql_query($sql);

				$already_assigned = array();
				while ($row = $db->sql_fetchrow($result))
				{
					$already_assigned[$row['country_id']] = array('id' => $row['school_id'], 'name' => $row['school_name']);
				}

				// Now get all the countries that have already been assigned to this school
				$sql = "SELECT country_id
						FROM " . ASSIGNMENTS_TABLE . "
						WHERE school_id = $school_id";
				$result = $db->sql_query($sql);
				$num_assignments = 0;

				while ($row = $db->sql_fetchrow($result))
				{
					$this_country = $row['country_id'];
					$num_assignments++;
					$template->assign_block_vars('assignments', array(
						'ID'	=> $num_assignments,
					));

					// Now loop through all the possible countries that have NOT yet been assigned ???
					// Actually, list them all, but if it has been assigned put the country in the data-already-assigned attribute
					foreach ($country_delegates as $country_id => $sum)
					{
						if (array_key_exists($country_id, $already_assigned))
						{
							$assigned_name = $already_assigned[$country_id]['name'];
							$assigned_id = $already_assigned[$country_id]['id']; // for faster comparison etc
						}
						$template->assign_block_vars('assignments.ca', array(
							'SELECTED' => ($this_country == $country_id) ? 'selected="selected"' : '',
							'ID' => $country_id,
							'TEXT' => $delegations[$country_id],
							'ALREADY_ASSIGNED' => (array_key_exists($country_id, $already_assigned) && $assigned_id != $school_id) ? $assigned_name : '',
							'NUM_DELEGATES' => $sum, // use js to do total calculations etc
						));
					}
				}

				// Make the first select thing show up anyway if there are no assignments so far
				if ($num_assignments == 0)
				{
					$template->assign_block_vars('assignments', array(
						'ID'	=> 1,
					));

					// Ughhhh copy + paste
					foreach ($country_delegates as $country_id => $sum)
					{
						if (array_key_exists($country_id, $already_assigned))
						{
							$assigned_name = $already_assigned[$country_id]['name'];
							$assigned_id = $already_assigned[$country_id]['id']; // for faster comparison etc
						}
						$template->assign_block_vars('assignments.ca', array(
							'ID' => $country_id,
							'TEXT' => $delegations[$country_id],
							'ALREADY_ASSIGNED' => (array_key_exists($country_id, $already_assigned) && $assigned_id != $school_id) ? $assigned_name : '',
							'NUM_DELEGATES' => $sum, // use js to do total calculations etc
						));
					}
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
					'REMAINING_OWED'			=> $row['amount_owed'] - $row['amount_paid'], // because, lol
				));
			break;
			case 'delegates':
				$this->page_title = 'Delegate overview';
				$this->tpl_name = 'acp_registration_delegates';
				$approve_id = request_var('approve', 0);
				if ($approve_id > 0)
				{
					include_once($phpbb_root_path . 'includes/functions_user.php');
					$delegate_name = delegate_add($approve_id);
					add_log('admin', 'LOG_USER_ADDED', $delegate_name);
					trigger_error("Successfully approved " . $delegate_name . "." . adm_back_link($this->u_action)); 
				}
				$unassign_id = request_var('unassign', 0);
				if ($unassign_id > 0)
				{
					// Deletes the user, changes the user_id in the delegates table to NULL
					// But first get the user ID lol
					$sql = "SELECT user_id, delegate_name
							FROM " . DELEGATES_TABLE . "
							WHERE delegate_id = $unassign_id";
					$result = $db->sql_query($sql);
					$row = $db->sql_fetchrow($result);
					$delegate_name = $row['delegate_name'];
					$this_user_id = $row['user_id'];
					$sql = "UPDATE " . DELEGATES_TABLE . "
							SET user_id = NULL
							WHERE delegate_id = $unassign_id";
					$db->sql_query($sql);
					include_once($phpbb_root_path . 'includes/functions_user.php');
					user_delete('remove', $this_user_id);
					trigger_error("Successfully deleted " . $delegate_name . " from the assigned position, whatever it was." . adm_back_link($this->u_action)); 
				}

				include($phpbb_root_path . '../committees_array.php');
				include($phpbb_root_path . '../delegations_array.php');

				// First get all of the relevant character names and country/committee IDs and whatnot
				// Better than doing a huge 4-table join lol
				$sql = "SELECT character_id, character_name, committee_id
						FROM " . CHARACTERS_TABLE . "
						JOIN " . DELEGATES_TABLE . "
						ON character_id = position_id
						WHERE is_country = 0";
				$result = $db->sql_query($sql);

				$characters = array();
				while ($row = $db->sql_fetchrow($result))
				{
					$characters[$row['character_id']] = array('name' => $row['character_name'], 'committee_id' => $row['committee_id']);
				}

				$sql = "SELECT position_id, country_id, committee_id, num_delegates
						FROM " . CCM_TABLE . "
						JOIN " . DELEGATES_TABLE . "
						ON id = position_id
						WHERE is_country = 1";
				$result = $db->sql_query($sql);

				$countries = array();
				while ($row = $db->sql_fetchrow($result))
				{
					$countries[$row['position_id']] = array('country_id' => $row['country_id'], 'committee_id' => $row['committee_id'], 'num_delegates' => $row['num_delegates']);
				}

				$sql = "SELECT d.delegate_id, s.school_name, d.delegate_name, d.delegate_email, d.is_country, d.position_id, d.user_id
						FROM " . DELEGATES_TABLE . " AS d
						JOIN " . SCHOOLS_CONTACT_TABLE . " AS s
						ON d.school_id = s.school_id";
				$result = $db->sql_query($sql);

				while ($row = $db->sql_fetchrow($result))
				{
					$position_id = $row['position_id'];
					$template->assign_block_vars('delegates', array(
						'SCHOOL'		=> $row['school_name'],
						'POSITION'		=> ($row['is_country']) ? $delegations[$countries[$position_id]['country_id']] :  $characters[$position_id]['name'],
						'COMMITTEE'		=> ($row['is_country']) ? $ccm_committees[$countries[$position_id]['committee_id']] : $committees[$characters[$position_id]['committee_id']],
						'NAME'			=> $row['delegate_name'],
						'EMAIL'			=> $row['delegate_email'],
						'APPROVE_DELETE'=> ($row['user_id']) ? '<a href="' . $this->u_action . '&amp;unassign=' . $row['delegate_id'] . '">Unassign</a>' : '<strong><a href="' . $this->u_action . '&amp;approve=' . $row['delegate_id'] . '">Approve</a></strong>',
					));
				}
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
							include($phpbb_root_path . 'includes/functions_user.php');
							$password = generate_random_password();
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
						foreach ($fake_delegations as $delegation)
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
						include_once($phpbb_root_path . 'includes/functions_user.php');
						$sql = "DELETE FROM " . SCHOOLS_CONTACT_TABLE . "
								WHERE school_id = $delete_id";
						$db->sql_query($sql);
						
						// Oshit also delete the committee and country assignments etc
						$sql = "DELETE FROM " . ASSIGNMENTS_TABLE . "
								WHERE school_id = $delete_id";
						$db->sql_query($sql);
						
						$sql = "DELETE FROM " . COM_ASSIGNMENTS_TABLE . "
								WHERE school_id = $delete_id";
						$db->sql_query($sql);

						// Bye bye, any delegates that might be assigned (this is mainly for testing lol)
						$sql = "SELECT user_id FROM " . DELEGATES_TABLE . "
								WHERE school_id = $delete_id";
						$result = $db->sql_query($sql);
						while ($row = $db->sql_fetchrow($result))
						{
							if ($row['user_id'])
							{
								user_delete('remove', $row['user_id']);
							}
						}
						$sql = "DELETE FROM " . DELEGATES_TABLE . "
								WHERE school_id = $delete_id";
						$db->sql_query($sql);
						 
						trigger_error('Successfully deleted school BUT DELETE THE SCHOOL USER TOO OKAY' . adm_back_link($this->u_action));
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

					$total_assigned = array();

					// First get the number of positions assigned to each school (countries and characters)
					// Countries (ccm) first
					$sql = "SELECT SUM(m.num_delegates) AS total_num, a.school_id
							FROM " . CCM_TABLE . " AS m
							JOIN " . ASSIGNMENTS_TABLE . " AS a
							ON a.country_id = m.country_id
							GROUP BY a.school_id";
					$result = $db->sql_query($sql);
					while ($row = $db->sql_fetchrow($result))
					{
						$total_assigned[$row['school_id']] = $row['total_num'];
					}

					// Now the character assignments (make sure to check if the array already has the key first)
					$sql = "SELECT COUNT(character_id) AS total_num, school_id
							FROM " . COM_ASSIGNMENTS_TABLE . "
							GROUP BY school_id";
					$result = $db->sql_query($sql);
					while ($row = $db->sql_fetchrow($result))
					{
						$this_school_id = $row['school_id'];
						$num_so_far = (array_key_exists($this_school_id, $total_assigned)) ? $total_assigned[$this_school_id] : 0;
						$total_assigned[$this_school_id] = $num_so_far + $row['total_num'];
					}
				
					// Shows you all the schools
					$sql = "SELECT school_id, school_name, country, registration_time, number_of_delegates, is_approved, amount_paid, amount_owed
							FROM " . SCHOOLS_CONTACT_TABLE . "
							ORDER BY registration_time DESC";
					$result = $db->sql_query($sql);
				
					while ($row = $db->sql_fetchrow($result))
					{
						$template->assign_block_vars('schools', array(
							'ID'					=> $row['school_id'],
							'NAME'					=> $row['school_name'],
							'COUNTRY'				=> $row['country'],
							'REGISTRATION_TIME'		=> $user->format_date($row['registration_time']),
							'NUMBER_OF_DELEGATES'	=> $row['number_of_delegates'],
							'IS_APPROVED'			=> $row['is_approved'],
							'AMOUNT_PAID'			=> $row['amount_paid'],
							'AMOUNT_OWED'			=> $row['amount_owed'],
							'NUM_ASSIGNED'			=> (array_key_exists($row['school_id'], $total_assigned)) ? $total_assigned[$row['school_id']] : 0,
							'U_EDIT'				=> $this->u_action . '&amp;edit=' . $row['school_id'],
							'U_DELETE'				=> $this->u_action . '&amp;delete=' . $row['school_id'],
							'U_APPROVE'				=> $this->u_action . '&amp;approve=' . $row['school_id'],
							'U_ASSIGN'				=> $this->u_action . '&amp;mode=assign&amp;id=' . $row['school_id'],
							'U_FINANCES'			=> $this->u_action . '&amp;mode=finances&amp;id=' . $row['school_id'],
						));
					}
				}
            break;
            case 'papers':
          		$this->page_title = 'Position papers';
				$this->tpl_name = 'acp_registration_papers';
				// First get a bunch of other data and store it in an array sigh sigh sigh
				$sql = "SELECT p.position_id, p.is_country, p.timestamp, m.country_id, m.committee_id AS committee_id, c.character_name, s.school_name AS character_school, s2.school_name AS country_school
						FROM " . POSITION_PAPERS_TABLE . " AS p
						LEFT JOIN " . CCM_TABLE . " AS m
						ON p.position_id = m.id
						LEFT JOIN " . CHARACTERS_TABLE . " AS c
						ON p.position_id = c.character_id
						LEFT JOIN " . COM_ASSIGNMENTS_TABLE . " AS a
						ON c.character_id = a.character_id
						LEFT JOIN " . ASSIGNMENTS_TABLE . " AS a2
						ON m.country_id = a2.country_id
						LEFT JOIN " . SCHOOLS_CONTACT_TABLE . " AS s
						ON a.school_id = s.school_id
						LEFT JOIN " . SCHOOLS_CONTACT_TABLE . " AS s2
						ON a2.school_id = s2.school_id"; // kill me
				$result = $db->sql_query($sql);
				include_once($phpbb_root_path . '../delegations_array.php');
				include_once($phpbb_root_path . '../committees_array.php');
				while ($row = $db->sql_fetchrow($result))
				{
					$is_country = $row['is_country'];
					$school = ($is_country) ? $row['country_school'] : $row['character_school'];
					$committee = ($is_country) ? $ccm_committees[$row['committee_id']] : $committees[$row['committee_id']];
					$position = ($is_country) ? $delegations[$row['country_id']] : $row['character_name'];
					// Oops forgot to store the format lol (either doc or docx)
					$file_begin = $phpbb_root_path . '../papers/' . $row['position_id'] . '_' . $is_country;
					$u_download = (file_exists($file_begin . '.doc')) ? $file_begin . '.doc' : $file_begin . '.docx';
					$template->assign_block_vars('papers', array(
						'SCHOOL'		=> $school,
						'COMMITTEE'		=> $committee,
						'POSITION'		=> $position,
						'TIMESTAMP'		=> $user->format_date($row['timestamp']),
						'U_DOWNLOAD'	=> $u_download,
					));
				}
            break;
      	}
	}
}

?>

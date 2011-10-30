<?php
/**
*
* @package SSUNS shit
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

class ucp_faculty
{
	var $u_action;

	function main($id, $mode)
	{
		global $template, $user, $db, $config, $phpEx, $phpbb_root_path;
		
		$submit = (isset($_POST['submit'])) ? true : false;
		$username = $user->data['username'];

		switch ($mode)
		{
			case 'assignments':
				$this->page_title = 'Delegation assignments';
				$this->tpl_name = 'ucp_faculty_assignments';

				$sql = "SELECT school_id
						FROM " . SCHOOLS_CONTACT_TABLE . "
						WHERE school_name = \"$username\""; // Idiot ...
				$result = $db->sql_query($sql);
				$row = $db->sql_fetchrow($result);
				$school_id = $row['school_id'];

				include($phpbb_root_path . '../delegations_array.php');
				include($phpbb_root_path . '../committees_array.php');

				// First do a query to see what delegates from this school have already been assigned
				$sql = "SELECT delegate_name, delegate_email, is_country, position_id, user_id
						FROM " . DELEGATES_TABLE . "
						WHERE school_id = $school_id";
				$result = $db->sql_query($sql);
				// Keys are in the form [POSITION_ID]_[COUNTRY_OR_CHAR] - 1 if country, 0 if char, fuck it idc 
				$assigned = array();
				while ($row = $db->sql_fetchrow($result))
				{
					$position_id = $row['position_id'];
					$is_country = $row['is_country'];
					$delegate_name = $row['delegate_name'];
					$delegate_email = $row['delegate_email'];
					$delegate_user_id = $row['user_id'];
					$key = $position_id . '_' . $is_country;

					// If it's already in the array, then we're talking about the Suez Crisis committee
					// I fucking hate that committee it is the bane of my existence
					// Any passing resemblance to "simple" and "logical" this code might have had is gone due to that fucking committee 
					// In any case, if it's already there, add it to the array
					if (array_key_exists($key, $assigned))
					{
						$assigned[$key][] = array(
							'name'	=> $delegate_name,
							'email'	=> $delegate_email,
							'id'	=> $delegate_user_id,
						);
					} else {
						// Yes nested arrays
						$assigned[$key] = array(array(
							'name'		=> $delegate_name,
							'email'		=> $delegate_email,
							'id'		=> $delegate_user_id,
						));
					}
				}


				// Now get all the shit that has been assigned to this school
				// First, countries and committees
				$assignments = array();
				$sql = "SELECT a.school_id, c.num_delegates, c.country_id, c.committee_id, c.id
						FROM " . ASSIGNMENTS_TABLE . " AS a
						LEFT JOIN " . CCM_TABLE . " AS c
						ON a.country_id = c.country_id
						WHERE a.school_id = $school_id
						AND c.num_delegates > 0";
				$result = $db->sql_query($sql);
				while ($row = $db->sql_fetchrow($result))
				{
					$country_id = $row['country_id'];
					$num_delegates = $row['num_delegates'];
					$committee_id = $row['committee_id'];
					for ($i = 1; $i <= $num_delegates; $i++)
					{
						// Because there can be multiple delegates per country and committee FUCK YOU SUEZ CRISIS
						$this_input_name = 'country_' . $row['id'] . '_' . $i;
						$assignee_key = $row['id'] . '_1';
						if (array_key_exists($assignee_key, $assigned) and array_key_exists($i-1, $assigned[$assignee_key])) // in case one suez spot is not assigned
						{
							$assignee_pointer = $assigned[$assignee_key][$i-1]; // the element in the array etc
							// The _1 because it's a counry, and the $i-1 because it starts indexing from 0 yeah
							$assignee = $assignee_pointer['name'];
							$assignee_email = $assignee_pointer['email'];
							$assignee_id = $assignee_pointer['id'];
						}
						else
						{
							$assignee_email = $assignee = $assignee_id = '';
						}
						$assignments[] = array('position' => $delegations[$country_id], 'committee' => $ccm_committees[$committee_id], 'name' => $this_input_name, 'assignee' => $assignee, 'assignee_email' => $assignee_email, 'user_id' => $assignee_id);
					}
				}

				// Another query to fetch the character assignments
				$sql = "SELECT c.character_id, c.character_name, c.committee_id
						FROM " . CHARACTERS_TABLE . " AS c
						JOIN " . COM_ASSIGNMENTS_TABLE . " AS a
						ON c.character_id = a.character_id
						WHERE a.school_id = $school_id";
				$result = $db->sql_query($sql);
				while ($row = $db->sql_fetchrow($result))
				{
					$char_id = $row['character_id'];
					$char_name = $row['character_name'];
					$committee_id = $row['committee_id'];
					$assignee_key = $char_id . '_0';
					if (array_key_exists($assignee_key, $assigned))
					{
						$assignee_pointer = $assigned[$assignee_key][0]; // because it's always a singleton for chars
						// The _1 because it's a counry, and the $i-1 because it starts indexing from 0 yeah
						$assignee = $assignee_pointer['name'];
						$assignee_email = $assignee_pointer['email'];
						$assignee_id = $assignee_pointer['id'];
					}
					else
					{
						$assignee_email = $assignee = $assignee_id = '';
					}
					$assignments[] = array('position' => $char_name, 'committee' => $committees[$committee_id], 'name' => 'char_' . $char_id, 'assignee' => $assignee, 'assignee_email' => $assignee_email, 'user_id' => $assignee_id);
				}

				if ($submit)
				{
					// Make sure we get enough inputted data
					// Go through the name variable in $assignments
					$insert_array = array(); // for sql_multi_insert
					foreach ($assignments as $assignment)
					{
						$exploded_name = explode('_', $assignment['name']);
						$delegate_name = utf8_normalize_nfc(request_var($assignment['name'] . '_name', '', true));
						$delegate_email = utf8_normalize_nfc(request_var($assignment['name'] . '_email', '', true));
						$which_type = $exploded_name[0];
						if ($delegate_name == '' || $delegate_email == '')
						{
							// If only one is filled in, give an error
							if ($delegate_name == '' xor $delegate_email == '')
							{
								$error = true;
							}
							// If they're both empty, just ignore it ...
						} else {
							$insert_array[] = array(
								// The user_id field is NULL for now because the account has not been created
								'position_id'		=> $exploded_name[1], // so either the id in the ccm table or the character id (not necc. unique)
								'delegate_name'		=> $delegate_name,
								'delegate_email'	=> $delegate_email,
								'school_id'			=> $school_id,
								'is_country'		=> ($which_type == 'country') ? 1 : 0,
							);
						}
					}
					if (!isset($error))
					{
						// Whooo no delegates missing, update the delegate assignments table
						// Note: they still have to be approved, after which the accounts will be created
						// Will be done in the ACP. Separate process obviously.
						$db->sql_multi_insert(DELEGATES_TABLE, $insert_array);
						$u_previous = append_sid("{$phpbb_root_path}ucp.$phpEx", "i=faculty&amp;mode=assignments");
						trigger_error('Your assignments have been saved.<br /><br /><a href="' . $u_previous . '">Return to previous page</a>');
					}
				}

				foreach ($assignments as $array)
				{
					$template->assign_block_vars('assignments', array(
						'POSITION'			=> $array['position'],
						'COMMITTEE'			=> $array['committee'],
						'NAME'				=> $array['name'],
						'ASSIGNEE'			=> $array['assignee'],
						'ASSIGNEE_EMAIL'	=> $array['assignee_email'],
						'DEL_NAME'			=> utf8_normalize_nfc(request_var($array['name'] . '_name', '', true)), // in case the faculty advisor is missing one
						'DEL_EMAIL'			=> utf8_normalize_nfc(request_var($array['name'] . '_email', '', true)), // don't want to force re-entering of everything
						'STATUS'			=> ($array['user_id']) ? 'Approved' : 'Awaiting approval',
					));
				}
				$template->assign_vars(array(
					'ERROR'		=> (isset($error)) ? 'Please fill in both a name and email for every position you wish to assign. You can leave both fields empty if you would prefer to complete the assignment later.' : '',
				));
			break;
			case 'papers':
				include($phpbb_root_path . '../delegations_array.php');
				include($phpbb_root_path . '../committees_array.php');
				$this->page_title = 'Position papers';
				$this->tpl_name = 'ucp_faculty_papers';
				// Need to make this some sort of helper function sigh
				$sql = "SELECT school_id, amount_owed, amount_paid FROM " . SCHOOLS_CONTACT_TABLE . "
						WHERE school_name = \"$username\"";
				$result = $db->sql_query($sql);
				$row = $db->sql_fetchrow($result);
				$school_id = $row['school_id'];
				// Now get all the APPROVED assigned delegates (may be up to 2 per position ... i'm looking at you suez crisis)
				$sql = "SELECT d.delegate_name, d.position_id, d.is_country, m.country_id, m.committee_id AS country_com, c.character_name, c.committee_id AS char_com, p.timestamp
						FROM " . DELEGATES_TABLE . " AS d
						LEFT JOIN " . CCM_TABLE . " AS m
						ON m.id = d.position_id
						LEFT JOIN " . CHARACTERS_TABLE . " AS c
						ON c.character_id = d.position_id
						LEFT JOIN " . POSITION_PAPERS_TABLE . " AS p
						ON p.position_id = d.position_id AND p.is_country = d.is_country
						WHERE d.school_id = $school_id
						AND d.user_id IS NOT NULL";
				$result = $db->sql_query($sql);
				$positions = array(); // So if there are two delegates just join them with "and" lol
				while ($row = $db->sql_fetchrow($result))
				{
					$position_id = $row['position_id'];
					$is_country = $row['is_country'];
					$key = $position_id . '_' . $is_country; // sighhard
					$delegate_name = $row['delegate_name'];

					if (array_key_exists($key, $positions))
					{
						$positions[$key]['name'] .= ' and ' . $delegate_name;
					}
					else
					{
						$position = ($is_country) ? $delegations[$row['country_id']] : $row['character_name'];
						$committee = ($is_country) ? $ccm_committees[$row['country_com']] : $committees[$row['char_com']];
						$positions[$key] = array(
							'name'			=> $delegate_name,
							'position'		=> $position,
							'committee' 	=> $committee,
							'input_name'	=> $key,
							'already'		=> ($row['timestamp']) ? true : false,
							'timestamp'		=> $row['timestamp'],
						);
					}
				}

				if ($submit)
				{
					$u_previous = append_sid("{$phpbb_root_path}ucp.$phpEx", "i=faculty&amp;mode=papers");
					// Check which position papers are being uploaded
					$uploaded = array();
					foreach ($positions as $key => $position)
					{
						$input_name = $position['input_name'];
						if (array_key_exists($input_name, $_FILES))
						{
							$this_file = $_FILES[$input_name];
							if ($this_file['name'] == '')
							{
								continue; // skip
							}
							// Extension is either doc or docx lol
							$this_filetype = $this_file['type'];
							if ($this_filetype == 'application/msword')
							{
								$extension = '.doc';
							}
							else if ($this_filetype == 'application/vnd.openxmlformats-officedocument.wordprocessingml.document') // wtf
							{
								$extension = '.docx';
							}
							else
							{
								trigger_error('All position papers must be uploaded in .doc or .docx format. Please contact it@ssuns.org if you encounter any problems.<br /><br /><a href="' . $u_previous . '">Return to previous page</a>');
							}

							// Otherwise, it works, upload and add an entry to the position papers table
							move_uploaded_file($this_file['tmp_name'], $phpbb_root_path . '../papers/' . $input_name . $extension);
							$exploded_key = explode('_', $input_name); // bah
							$sql = "INSERT INTO " . POSITION_PAPERS_TABLE . " (position_id, is_country, timestamp) 
									VALUES (" . $exploded_key[0] . ", " . $exploded_key[1] . ", " . time() . ")"; // wtv
							$db->sql_query($sql);
							$uploaded[] = $input_name;
						}
						// If the $_FILES array has the input name, then yes, that one is being uploaded
						// Otherwise, pass
					}
					if (count($uploaded) > 0)
					{
						// Now send ONE email detailing all the new position papers uploaded
						include($phpbb_root_path . 'includes/functions_messenger.php');
						$messenger = new messenger(false);

						// Now send off an email to the faculty advisor informing him/her of the registration
						$messenger->template('position_papers_uploaded');
						$messenger->to($user->data['user_email'], $user->data['username']);
						$messenger->subject('Position papers uploaded');
						$messenger->from("it@ssuns.org");
				
						$messenger->assign_vars(array(
							'USERNAME'				=> $user->data['username'],
							'NUM_UPLOADED'			=> count($uploaded))
						);
						
						// Now list all the position papers that have been uploaded ...
						foreach ($uploaded as $upload)
						{
							$messenger->assign_block_vars('papers', array(
								'NAME'		=> $positions[$upload]['name'],
								'POSITION'	=> $positions[$upload]['position'],
								'COMMITTEE'	=> $positions[$upload]['committee']
							));
						}

						// Should send a copy to it@ssuns.org as well
						$messenger->bcc('it@ssuns.org');
						$messenger->send();
						
						trigger_error('Your position papers have been successfully uploaded. You will receive a confirmation email shortly.<br /><br /><a href="' . $u_previous . '">Return to previous page</a>');
					}
					else
					{
						trigger_error('You did not specify any documents to upload.<br /><br /><a href="' . $u_previous . '">Return to previous page</a>');
					}
				}

				// Now go through all the positions and do shit
				foreach ($positions as $key => $position)
				{
					$template->assign_block_vars('positions', array(
						'NAME'			=> $position['name'],
						'POSITION'		=> $position['position'],
						'COMMITTEE'		=> $position['committee'],
						'INPUT_NAME'	=> $position['input_name'],
						'ALREADY'		=> $position['already'],
						'TIMESTAMP'		=> ($position['already']) ? $user->format_date($position['timestamp']) : 0, // only needed if the user has already uploaded
					));
				}
			break;
			case 'overview':
				$this->page_title = "Faculty advisor panel";
				$this->tpl_name = 'ucp_faculty_overview';
				// Figure out the school id from the username ... terrible way to do it but whatever
				// Get financial information too
				$sql = "SELECT school_id, amount_owed, amount_paid FROM " . SCHOOLS_CONTACT_TABLE . "
						WHERE school_name = \"$username\""; // sigh
				$result = $db->sql_query($sql);
				$row = $db->sql_fetchrow($result);
				$school_id = $row['school_id'];
				$amount_owed = $row['amount_owed'];
				$amount_paid = $row['amount_paid'];

				// Now get all the country assignments
				include($phpbb_root_path . '../delegations_array.php');
				include($phpbb_root_path . '../committees_array.php');
				$sql = "SELECT a.country_id, c.committee_id, c.num_delegates FROM " . ASSIGNMENTS_TABLE . " AS a
						RIGHT JOIN " . CCM_TABLE . " AS c
						ON a.country_id = c.country_id
						WHERE a.school_id = $school_id";
				$result = $db->sql_query($sql);
				$num_assigned = 0;
				$assigned = array(); // use an array to store all the country assignment info
				// Each entry in the array is an array holding the country info
				while ($row = $db->sql_fetchrow($result))
				{
					$country_id = $row['country_id'];
					$committee_id = $row['committee_id'];
					$num_delegates = $row['num_delegates'];
					if (array_key_exists($country_id, $assigned))
					{
						// Already exists - just add the committee to the committee array
						// God so many arrays
						$assigned[$row['country_id']]['committees'][] = array(
							'name' => $ccm_committees[$committee_id],
							'num'	=> $num_delegates,
						);
						// Update the sum
						$assigned[$row['country_id']]['sum'] += $num_delegates;
					}
					else
					{
						// Does not exist yet, add the array
						$assigned[$row['country_id']] = array(
							'name' => $delegations[$country_id],
							'committees' => array(
								array(
									'name' => $ccm_committees[$committee_id],
									'num'	=> $num_delegates,
								),
							),
							'sum' => $num_delegates, // total number of delegates, updated each time
						);
					}
				}

				// Now display all the assigned countries and the relevant committees
				foreach ($assigned as $key => $value)
				{
					$template->assign_block_vars('countries', array(
						'NAME'	=> $delegations[$key],
						'NUM'	=> $value['sum'],
					));

					// Now loop through all the committees this country is a part of
					$committees = $value['committees'];
					foreach ($committees as $committee)
					{
						// Only include those with >= 1 delegate ...
						if ($committee['num'] > 0)
						{
							$template->assign_block_vars('countries.committees', array(
								'NAME'	=> $committee['name'],
								'NUM'	=> $committee['num'],
							));
						}
					}
					$num_assigned++;
				}

				// Get all the character assignments
				$sql = "SELECT c.character_name, c.committee_id
						FROM " . CHARACTERS_TABLE . " AS c
						JOIN " . COM_ASSIGNMENTS_TABLE . " as a
						ON a.character_id = c.character_id
						WHERE a.school_id = $school_id";
				$result = $db->sql_query($sql);

				while ($row = $db->sql_fetchrow($result))
				{
					$template->assign_block_vars('characters', array(
						'CHAR'		=> $row['character_name'],
						'COMMITTEE'	=> $char_committees[$row['committee_id']],
					));
				}

				$template->assign_vars(array(
					'NUM_ASSIGNED'	=> $num_assigned,
					'AMOUNT_OWED'	=> $amount_owed,
					'AMOUNT_PAID'	=> $amount_paid,
				));
			break;
			case 'events':
				$this->page_title = "Event registration";
				$this->tpl_name = 'ucp_faculty_events';
				$sql = "SELECT *
						FROM " . SCHOOLS_CONTACT_TABLE . "
						WHERE school_name = \"$username\""; // Idiot ...
				$result = $db->sql_query($sql);
				$row = $db->sql_fetchrow($result);
				$school_id = $row['school_id'];
				$template->assign_vars(array(
					'MCGILL_NUM_FACADS'		=> $row['mcgill_num_facads'],
					'MCGILL_NUM_STUDENTS'	=> $row['mcgill_num_students'],
					'MONT_NUM_FACADS'		=> $row['mont_num_facads'],
					'MONT_NUM_STUDENTS'		=> $row['mont_num_students'],
					'MONT_HEALTH'			=> $row['mont_health'],
					'GALA_NUM_VEG'			=> $row['gala_num_veg'],
					'GALA_NUM_NON_VEG'		=> $row['gala_num_non_veg'],
					'GALA_PREFS'			=> $row['gala_prefs'],
				));
				if ($submit)
				{
					$sql_array = array(
						'mcgill_num_facads' => request_var('mcgill_num_facads', 0),
						'mcgill_num_students' => request_var('mcgill_num_students', 0),
						'mont_num_facads' => request_var('mont_num_facads', 0),
						'mont_num_students' => request_var('mont_num_students', 0),
						'mont_health' => request_var('mont_health', ''),
						'gala_num_veg' => request_var('gala_num_veg', 0),
						'gala_num_non_veg' => request_var('gala_num_non_veg', 0),
						'gala_prefs' => request_var('gala_prefs', ''),
					);
					$sql = "UPDATE " . SCHOOLS_CONTACT_TABLE . "
							SET " . $db->sql_build_array('UPDATE', $sql_array) . "
							WHERE school_id = " . $school_id;
					$db->sql_query($sql);
					$u_previous = append_sid("{$phpbb_root_path}ucp.$phpEx", "i=faculty&amp;mode=events");
					trigger_error('Submissions saved.<br /><br /><a href="' . $u_previous . '">Return to previous page</a>');
				}
			break;
		}


		$template->assign_vars(array(
			'U_ACTION'	=> $this->u_action,
			'L_TITLE' 	=> $this->page_title)
		);

	}
}

?>

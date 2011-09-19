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

class acp_staff {
   var $u_action;
   var $new_config;
  
   
   function main($id, $mode)
   {
		global $phpbb_root_path, $db, $phpEx, $auth, $user, $template, $config;
		
     	$submit = (isset($_POST['submit'])) ? true : false;
		switch($mode)
		{
			case 'overview':
				$assign_id = request_var('assign', 0);

				if ($assign_id > 0)
				{
					// Assigning positions to some applicant
					// Do this query first in case we need information etc
					$sql = "SELECT *
							FROM " . STAFF_APPS_TABLE . "
							WHERE id = $assign_id";
					$result = $db->sql_query($sql);
					$row = $db->sql_fetchrow($result);

					// If we're submitting ... update the position
					if (isset($_POST['submit']))
					{
						$position = request_var('position', '');
						$sql = "UPDATE " . STAFF_APPS_TABLE . "
								SET position = \"$position\"
								WHERE id = $assign_id";
						$db->sql_query($sql);

						// Send out an email informing the applicant of the position assignment
						// Send it out in case of update as well
						// Only if the position is not empty
						if ($position != '')
						{
							include($phpbb_root_path . 'includes/functions_messenger.php');
							$messenger = new messenger(false);

							$messenger->template('staff_assigned_position');
							$messenger->to($row['email'], $row['name']);
							$messenger->subject('SSUNS staff application');
							$messenger->from("it@ssuns.org");

							// Figure out the text to display in place of REAL_POSITION ...
							$real_position = ($row['is_logistical']) ? 'the position of ' . $position : 'a position in the committee "' . $position . '"';
							$messenger->assign_vars(array(
								'APPLICANT_NAME'				=> $row['name'],
								'CONTACT_PERSON'				=> ($row['is_logistical']) ? 'Dana Al Zaben, Chief of Staff at staff@ssuns.org' : 'Rida Malik, Undersecretary-General - Committee Affairs at committees@ssuns.org',
								'REAL_POSITION'					=> $real_position)
							);

							$messenger->send();
						}
						add_log('admin', 'LOG_ASSIGN_POSITION', $row['name']);
						trigger_error("Successfully assigned position to " . $row['name'] . "." . adm_back_link($this->u_action)); 
						
					}
					
					$this->page_title = 'Assigning positions for ' . $row['name'];
					$this->tpl_name = 'acp_staff_assign';
					$template->assign_vars(array(
						'APPLICANT_NAME'		=> $row['name'],
						'PROGRAM_YEAR'			=> $row['program'],
						'TELEPHONE'				=> $row['telephone'],
						'APPLICANT_EMAIL'		=> $row['email'],
						'ATTEND_TRAINING'		=> $row['attend_training'],
						'ATTEND_SSUNS'			=> $row['attend_ssuns'],
						'MUN_EXPERIENCE'		=> $row['mun_experience'],
						'LEADERSHIP_EXPERIENCE'	=> $row['leadership_experience'],
						'GOOD_CANDIDATE'		=> $row['good_candidate'],
						'IS_LOGISTICAL'			=> $row['is_logistical'],
						'FIRST_CHOICE'			=> $row['choice_1'],
						'SECOND_CHOICE'			=> $row['choice_2'],
						'THIRD_CHOICE'			=> $row['choice_3'],
						'POSITION'				=> $row['position'],
					));
				}
				else
				{
					$this->page_title = 'SSUNS staff';
					$this->tpl_name = 'acp_staff_overview';
					$sql = "SELECT id, name, is_logistical, position
							FROM " . STAFF_APPS_TABLE . "
							ORDER BY position ASC";
					$result = $db->sql_query($sql);

					while ($row = $db->sql_fetchrow($result)) {
						$template->assign_block_vars('staff', array(
							'ID'					=> $row['id'],
							'NAME'					=> $row['name'],
							'IS_LOGISTICAL'			=> $row['is_logistical'],
							'POSITION'				=> $row['position'],
							'U_ASSIGN'				=> $this->u_action . '&amp;assign=' . $row['id'])
						);
					}
				}
            break;
      	}
	}
}

?>

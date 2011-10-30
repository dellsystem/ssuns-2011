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

class ucp_paper
{
	var $u_action;

	function main($id, $mode)
	{
		global $template, $user, $db, $config, $phpEx, $phpbb_root_path;
		
		$submit = (isset($_POST['submit'])) ? true : false;

		switch ($mode)
		{
			case 'main':
				$this->page_title = 'Position paper upload';
				$this->tpl_name = 'ucp_paper';
				$sql = "SELECT text, timestamp
						FROM " . FINAL_PAPERS_TABLE . "
						WHERE user_id = " . $user->data['user_id'];
				$result = $db->sql_query($sql);
				$position_paper = $timestamp = '';
				while ($row = $db->sql_fetchrow($result))
				{
					$position_paper = $row['text'];
					$timestamp = $row['timestamp'];
				}

				if ($submit)
				{
					$u_previous = append_sid("{$phpbb_root_path}ucp.$phpEx", "i=240");
					// If the user has already submitted ...
					if ($position_paper)
					{
						trigger_error("You've already submitted your position paper. If you need to change something please contact it@ssuns.org.<br /><br /><a href=\"$u_previous\">Return to previous page</a>");
					}
					$text = utf8_normalize_nfc(request_var('paper', '', true));
					$sql_array = array(
						'user_id'	=> $user->data['user_id'],
						'text'		=> str_replace("\n", "<br />", $text),
						'timestamp'	=> time(),
					);
					$sql = 'INSERT INTO ' . FINAL_PAPERS_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_array);
					$db->sql_query($sql);

					// for the red button thing lol
					$sql = "UPDATE " . USERS_TABLE . "
							SET paper_uploaded = 1
							WHERE user_id = " . $user->data['user_id'] . "
							LIMIT 1";
					$db->sql_query($sql);
					$user->data['paper_uploaded'] = 1;

					// Email confirmation lol
					include($phpbb_root_path . 'includes/functions_messenger.php');
					$messenger = new messenger(false);

					// Now send off an email to the faculty advisor informing him/her of the registration
					$messenger->template('position_paper_saved');
					$messenger->to($user->data['user_email'], $user->data['username']);
					$messenger->subject('Position paper saved');
					$messenger->from("it@ssuns.org");
			
					$messenger->assign_vars(array(
						'USERNAME'		=> $user->data['username']
					));

					// Should send a copy to it@ssuns.org as well
					$messenger->bcc('it@ssuns.org');
					$messenger->send();

					trigger_error('Your position paper has been saved. You will receive an email confirmation shortly.<br /><br /><a href="' . $u_previous . '">Return to previous page</a>');
				}

				$template->assign_vars(array(
					'POSITION_PAPER'	=> $position_paper,
					'TIMESTAMP'			=> $timestamp,
					'U_ACTION'			=> $this->u_action,
					'L_TITLE' 			=> $this->page_title)
				);
			break;
		}
	}
}

?>

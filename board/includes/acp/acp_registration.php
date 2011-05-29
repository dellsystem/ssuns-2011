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
            break;
      	}
	}
}

?>

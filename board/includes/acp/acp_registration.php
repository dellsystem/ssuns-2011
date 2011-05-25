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
            break;
      	}
	}
}

?>

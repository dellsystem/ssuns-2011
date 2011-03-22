<?php
/**
*
* @package acp
* @version $Id$
* @copyright (c) 2005 phpBB Group
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

/* CUSTOM PAGES ACP MODULE - dellsystem */

class acp_custom_pages {
   var $u_action;
   var $new_config;
   function main($id, $mode)
   {
      global $db, $user, $auth, $template;
      global $config, $phpbb_root_path, $phpbb_admin_path, $phpEx;
      switch($mode)
      {
         case 'overview':
            $this->page_title = 'Custom pages overview';
            $this->tpl_name = 'acp_custom_pages_overview';
            
            // Do a SQL query to fetch all the custom pages (just the titles etc, no content)
            $sql = "SELECT *
            break;
         case 'add':
         	$this->page_title = 'Add a new custom page';
         	$this->tpl_name = 'acp_custom_pages_add';
         	break;
         case 'edit':
         	$this->page_title = 'Edit a custom page';
         	$this->tpl_name = 'acp_custom_pages_edit';
         	break;
      }

   }
}

?>

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
            $sql = "SELECT page_id, page_title, page_name, last_modified, page_template
            		FROM " . CUSTOM_PAGES_TABLE . "
            		ORDER BY page_id ASC";
           	$result = $db->sql_query($sql);
           	
			while ( $row = $db->sql_fetchrow($result) ) {
				$template->assign_block_vars('pages', array(
					'PAGE_ID' 		=> $row['page_id'],
					'PAGE_TITLE' 	=> $row['page_title'],
					'TEMPLATE_FILE' => $row['page_template'],
					'PAGE_NAME'		=> $row['page_name'],
					'LAST_MODIFIED'	=> $user->format_date($row['last_modified']),
					'U_EDIT'		=> append_sid("{$phpbb_admin_path}index.$phpEx", 'i=custom_pages&mode=edit&id=' . $row['page_id']),
					'U_PAGE'		=> $phpbb_root_path . '../' . $row['page_name'])
				);
			}
            break;
         case 'add':
         	$this->page_title = 'Add a new custom page';
         	$this->tpl_name = 'acp_custom_pages_add';
         	
         	// Only really need to do stuff if we're submitting
         	if ( isset($_POST['submit']) ) {
         		// First check to make sure that none of the elements are empty
         		$new_title = request_var('page_title', '');
         		$new_name = request_var('page_name', '');
         		$new_content = request_var('page_content', '');
         		// Eventually this should be a dropdown menu - all the template files in the dir? prefixed with cp_
         		$new_template = request_var('page_template', '');
         		
         		if ( $new_title == '' || $new_name == '' || $new_content == '' ) {
         			trigger_error('None of the first three fields can be empty!' . adm_back_link($this->u_action), E_USER_WARNING);
         		}
         	
         		
         		if ( !($this->unique_name($new_name)) ) {
         			trigger_error('Your page name (URL) is not unique!' . adm_back_link($this->u_action), E_USER_WARNING);
         		}

         		// Otherwise, might as well add the page ... ignore page_id, that autoincrements
         		$sql = "INSERT INTO " . CUSTOM_PAGES_TABLE . " (page_title, page_content, page_name, last_modified, page_template)
         				VALUES ('$new_title', '$new_content', '$new_name', " . time() . ", '$new_template')";
         		$db->sql_query($sql);
         		
         		add_log('Added custom page /' . $new_name . adm_back_link($this->u_action));
         		trigger_error('Your page has been successfully added' . adm_back_link($this->u_action));
         	}
         	break;
         case 'edit':
         	$this->page_title = 'Edit a custom page';
         	// Why doesn't add use the same template file?
         	$this->tpl_name = 'acp_custom_pages_edit';
         	
         	$id_to_edit = intval($_GET['id']);
         	
        	
         	// First try to query the database looking for this page
         	$sql = "SELECT *
         			FROM " . CUSTOM_PAGES_TABLE . "
         			WHERE page_id = $id_to_edit
         			LIMIT 1";
         	$result = $db->sql_query($sql);
         	$row = $db->sql_fetchrow($result);
         	
         	// dunno what other way to check it lol
         	$page_nonexistent = ( intval($row['page_id']) == 0 ) ? true : false;
         	
         	// If we're submitting and the page exists (it should ...)
         	if ( isset($_POST['submit']) && !$page_nonexistent ) {
				// Update the table:
				$sql = "UPDATE " . CUSTOM_PAGES_TABLE . "	
						SET page_title = '" . $_POST['page_title'] . "',
							page_name = '" . $_POST['page_name'] . "',
							page_content = '" . $_POST['page_content'] . "',
							last_modified = " . time() . ",
							page_template = '" . request_var('page_template', '') . "'
						WHERE page_id = $id_to_edit";
				$db->sql_query($sql);         	
         	
         		add_log('admin', 'Edited custom page /' . $row['page_name']);

				trigger_error('Successfully updated custom page' . adm_back_link($this->u_action . "&amp;id=$id_to_edit"));
         	}
         	
         	$template->assign_vars(array(
         		'PAGE_ID'			=> $row['page_id'],
         		'CUSTOM_PAGE_TITLE'	=> $row['page_title'], // otherwise it conflicts lol
         		'PAGE_NAME'			=> $row['page_name'],
         		'PAGE_CONTENT'		=> $row['page_content'],
         		'LAST_MODIFIED'		=> $user->format_date($row['last_modified']),
         		'PAGE_TEMPLATE'     => $row['page_template'],
         		'PAGE_NONEXISTENT'	=> $page_nonexistent,)
         	);
         	break;
      	}
     }
		// Helper function for determining if the page_name is unique - returns true or false
		function unique_name($name_to_check) {
   			global $db;
   			$sql = "SELECT page_id
   					FROM " . CUSTOM_PAGES_TABLE . "
   					WHERE page_name = '$name_to_check'";
   			$result = $db->sql_query($sql);
   			$row = $db->sql_fetchrow($result);
   			if ( intval($row['page_id']) > 0 ) {
   				return false;
   			} else {
   				return true;
   			}
   			return false;
	}
}

?>

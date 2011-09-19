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

/* CUSTOM PAGES ACP MODULE INFO PAGE - dellsystem */

class acp_custom_pages_info {
    function module() {
        return array(
            'filename'    => 'acp_custom_pages',
            'title'        => 'Custom Pages',
            'version'    => '1.0.0',
            'modes'        => array(
            	// Uses the same auth as board for now, probably not worth it to change it
                'overview'      => array('title' => 'Custom pages overview', 'auth' => 'acl_a_cp', 'cat' => array('')),
                'pages'			=> array('title' => 'Add a new custom page', 'auth' => 'acl_a_cp', 'cat' => array('')),
                'menu'			=> array('title' => 'Edit a custom page', 'auth' => 'acl_a_cp', 'cat' => array('')),
            ),
        );
    }

    function install()
    {
    }

    function uninstall()
    {
    	// ONCE YOU INSTALL YOU CAN NEVER GO BACK MUAHHAHA
    }
}

?>

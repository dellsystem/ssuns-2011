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

class acp_staff_info
{
    function module()
    {
        return array(
            'filename'    => 'acp_staff',
            'title'        => 'Staff apps',
            'version'    => '1.0.0',
            'modes'        => array(
            	// Hopefully this will work
                'overview'      => array('title' => 'Overview', 'auth' => 'acl_a_staff', 'cat' => array('ssuns')),
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

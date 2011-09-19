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

class acp_registration_info
{
    function module()
    {
        return array(
            'filename'    => 'acp_registration',
            'title'        => 'Registration',
            'version'    => '1.0.0',
            'modes'        => array(
            	// Please work
                'overview'      => array('title' => 'Overview', 'auth' => 'acl_a_schools', 'cat' => array('ssuns')),
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

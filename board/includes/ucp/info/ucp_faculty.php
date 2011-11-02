<?php
/**
*
* @package ucp
* @version $Id$
* @copyright (c) 2005 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

/**
* @package module_install
*/
class ucp_faculty_info
{
	function module()
	{
		return array(
			'filename'	=> 'ucp_faculty',
			'title'		=> 'Faculty advisor control panel',
			'version'	=> '1.0.0',
			'modes'		=> array(
				'overview'		=> array('title' => 'Overview', 'auth' => 'acl_u_faculty', 'cat' => array('ssuns')),
				'assignments'	=> array('title' => 'Assignments', 'auth' => 'acl_u_faculty', 'cat' => array('ssuns')),
				'papers'		=> array('title' => 'Position papers', 'auth' => 'acl_u_faculty', 'cat' => array('ssuns')),
				'events'		=> array('title' => 'Event registration', 'auth' => 'acl_u_faculty', 'cat' => array('ssuns')),
				'monitor'		=> array('title' => 'Monitor position paper uploads', 'auth' => 'acl_u_faculty', 'cat' => array('ssuns')),
			),
		);
	}

	function install()
	{
	}

	function uninstall()
	{
	}
}

?>

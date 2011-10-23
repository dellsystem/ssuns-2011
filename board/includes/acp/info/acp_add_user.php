<?php
/**
* @author Rich McGirr (RMcGirr83) http://phpbbmodders.net
* @author David Lewis (Highway of Life) http://phpbbacademy.com
*
* @package acp
* @version $Id:
* @copyright (c) 2011 phpBB Modders
* @copyright (c) 2007 Star Trek Guide Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*
*/

class acp_add_user_info
{	
	function module()
	{		
		return array(
			'filename'	=> 'acp_add_user',
			'title'		=> 'ACP_ADD_USER',
			'version'	=> '1.1.0',
			'modes'		=> array(
				'add_user'	=> array('title' => 'ACP_ADD_USER', 'auth' => 'acl_a_user', 'cat' => array('ACP_CAT_USERS'),
				),
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
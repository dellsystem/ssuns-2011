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
class ucp_paper_info
{
	function module()
	{
		return array(
			'filename'	=> 'ucp_paper',
			'title'		=> 'Position paper upload',
			'version'	=> '1.0.0',
			'modes'		=> array(
				'main'		=> array('title' => 'Upload', 'auth' => 'acl_u_paper', 'cat' => array('ssuns')),
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

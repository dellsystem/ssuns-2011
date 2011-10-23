<?php
/**
*
* @author Original Author twosheds@twosheds.com
*
* @package privmsgs_mod
* @version $Id:1.5.4
* @copyright (c) 2009 TwoSheds
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

/**
* @package privmsgs_mod
*/
class acp_privmsgs_mod_info
{
	function module()
	{
		return array(
			'filename'	=> 'acp_privmsgs_mod',
			'title'		=> 'ACP_PRIVMSGS_MOD_PAGETITLE',
			'version'	=> '1.5.4',
			'modes'		=> array(
				'moderate'		=> array('title' => 'ACP_PRIVMSGS_MOD_MODERATE', 'auth' => 'acl_a_privmsgs', 'cat' => array('ACP_PRIVMSGS_MOD_PAGETITLE')),
				'configure'		=> array('title' => 'ACP_PRIVMSGS_MOD_CONFIG', 'auth' => 'acl_a_privmsgs', 'cat' => array('ACP_PRIVMSGS_MOD_PAGETITLE'))
			)
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

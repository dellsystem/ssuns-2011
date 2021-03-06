<?php
/**
*
* @package Dynamo (Dynamic Avatar MOD for phpBB3)
* @version $Id: info_acp_dynamo.php ilostwaldo@gmail.com$
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

// Create the lang array if it does not already exist
if (empty($lang) || !is_array($lang))
{
	$lang = array();
}

// Merge the following language entries into the lang array
$lang = array_merge($lang, array(
	
	// Log shit
	'LOG_EDIT_SCHOOL'			=> '<strong>Altered school information</strong><br />» %s',
	'LOG_FINANCES_SCHOOL'		=> '<strong>Altered school finances information</strong><br />» %s',
	'LOG_APPROVE_SCHOOL'		=> '<strong>Approved school</strong><br />» %s',
));

?>

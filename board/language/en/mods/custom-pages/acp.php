<?php
/**
*
* @package Dynamo (Dynamic Avatar MOD for phpBB3)
* @version $Id: acp.php ilostwaldo@gmail.com$
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
	'CUSTOM_PAGES_OVERVIEW'			=> 'Custom pages overview',
	'CUSTOM_PAGES_OVERVIEW_EXPLAIN'	=> 'Welcome to the custom pages MOD for phpBB. This MOD provides an easy-to-use, ACP-based way of managing the non-forum pages of your site, along with a way of easily creating a menu bar to facilitate navigation. You can add a new page or menu item or view existing ones using the links on the left.',
	'CUSTOM_PAGES_RECENT'			=> 'Recently updated custom pages',
));

?>

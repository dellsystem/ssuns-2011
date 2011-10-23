<?php
/**
*
* prime_notify [English]
*
* @package language
* @version $Id: prime_notify.php,v 1.0.3 2008/08/16 11:24:00 primehalo Exp $
* @copyright (c) 2007-2008 Ken F. Innes IV
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

/**
* DO NOT CHANGE
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
	$lang = array();
}

// DEVELOPERS PLEASE NOTE
//
// All language files should use UTF-8 as their encoding and the files must not contain a BOM.
//
// Placeholders can now contain order information, e.g. instead of
// 'Page %s of %s' you can (and should) write 'Page %1$s of %2$s', this allows
// translators to re-order the output of data while ensuring it remains correct
//
// You do not need this where single placeholders are used, e.g. 'Message %d' is fine
// equally where a string contains only two placeholders which are used to wrap text
// in a url you again do not need to specify an order e.g., 'Click %sHERE%s' is fine
//
// Some characters you may want to copy&paste:
// ’ » “ ” …
//

$lang = array_merge($lang, array(
	'PRIME_NOTIFY_POST_CONTENT'			=> 'Notify post content',
	'PRIME_NOTIFY_POST_CONTENT_EXPLAIN'	=> 'Include the post\'s content in the notification email?',
	'PRIME_NOTIFY_PM_CONTENT'			=> 'Notify PM content',
	'PRIME_NOTIFY_PM_CONTENT_EXPLAIN'	=> 'Include the private message in the notification email?',
	
	// Inserted into e-mail template (so keep trailing space)
	'PRIME_NOTIFY_FORUM_VISIT_MSG'		=> 'No more notifications will be sent until you visit the forum. ',
	'PRIME_NOTIFY_TOPIC_VISIT_MSG'		=> 'No more notifications will be sent until you visit the topic. ',
));

?>
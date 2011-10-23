<?php
/**
*
* acp_privmsgs_mod [English]
*
* @author Two Sheds twosheds@twosheds.com
*
* @package privmsgs_mod
* @version $Id:1.5.4
* @copyright (c) 2009 TwoSheds
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

$lang = array_merge($lang, array(
	'acl_a_privmsgs'						=> array('lang' => 'Can moderate private messages', 'cat' => 'misc'),
	'ACP_PRIVMSGS_MOD_ALL_BY_USER'			=> 'View all private messages written by this user',
	'ACP_PRIVMSGS_MOD_ALL_TO_USER'			=> 'View all private messages received by this user',
	'ACP_PRIVMSGS_MOD_AND'					=> 'and',
	'ACP_PRIVMSGS_MOD_BLANK'				=> '',
	'ACP_PRIVMSGS_MOD_CONFIG'				=> 'Configure Defaults',
	'ACP_PRIVMSGS_MOD_CONFIG_EXPLAIN'		=> 'Here you can set default values that will control the initial appearance and behaviour of the Private Messages plugin.  You will be able to change these settings while you are using the plugin by making option selections at the bottom of the Moderate Messages screen.  What you are doing here is setting the initial default values for these options when you first open the plug-in.',
	'ACP_PRIVMSGS_MOD_CONFIG_UPDATED'		=> 'Private Messages plugin defaults successfully updated.',
	'ACP_PRIVMSGS_MOD_CONFIRM_DELETE'		=> 'Do you really want to delete the private message(s) you marked?',
	'ACP_PRIVMSGS_MOD_CONVERSATION'			=> 'Show the conversation between',
	'ACP_PRIVMSGS_MOD_DATETIME'				=> 'DATE/TIME',
	'ACP_PRIVMSGS_MOD_DEBUG_MSG'			=> 'Debug Message:',
	'ACP_PRIVMSGS_MOD_DELETE'				=> 'Delete',
	'ACP_PRIVMSGS_MOD_ERROR'				=> 'ERROR:',
	'ACP_PRIVMSGS_MOD_ERROR_INVALID_USER'	=> 'is an invalid user name',
	'ACP_PRIVMSGS_MOD_EXPLAIN'				=> 'You may view and delete your board&rsquo;s private messages from this page.  Messages are initially displayed in descending order from newest to oldest.  You may select a different sort order or a particular author at the bottom of the page.  You may delete messages by marking them, selecting &ldquo;Delete&rdquo; in the &ldquo;Action&rdquo; list at the bottom of the page, and clicking the &ldquo;Submit&rdquo; button.',
	'ACP_PRIVMSGS_MOD_FILTER_EXPLAIN'		=> 'This option can be used to block the display of messages that contain a specific text string.  This could be useful to filter out messages that are generated automatically by other phpBB utilities, for example.  Do not include HTML, special characters or BBcode in this string.',
	'ACP_PRIVMSGS_MOD_FILTER'				=> 'Filter out messages containing this string',
	'ACP_PRIVMSGS_MOD_FROM'					=> 'FROM',
	'ACP_PRIVMSGS_MOD_LOG_DELETE'			=> '<strong>Deleted private messages</strong><br />',
    'ACP_PRIVMSGS_MOD_MARK'					=> 'MARK',
	'ACP_PRIVMSGS_MOD_META_TITLE'			=> 'Moderating Private Messages',
	'ACP_PRIVMSGS_MOD_MODERATE'				=> 'Moderate Messages',
	'ACP_PRIVMSGS_MOD_MSG_ID'				=> '#',
	'ACP_PRIVMSGS_MOD_MSGS_PER_PAGE'		=> 'Messages per page:',
	'ACP_PRIVMSGS_MOD_NO_MESSAGES'			=> 'No private messages found',
	'ACP_PRIVMSGS_MOD_NO'					=> 'No',
	'ACP_PRIVMSGS_MOD_NOT_A_NUMBER'			=> 'Messages displayed per page must be set to an integer number.',
	'ACP_PRIVMSGS_MOD_OUTBOX'				=> 'OutBox',
	'ACP_PRIVMSGS_MOD_PAGEFOOTER'			=> 'Private Messages Moderation module',
	'ACP_PRIVMSGS_MOD_PAGETITLE'			=> 'Private Messages',
	'ACP_PRIVMSGS_MOD_PER_PAGE_EXPLAIN'		=> 'Enter the number of private messages per page you would like to see listed on the Moderate Messages screen.',
	'ACP_PRIVMSGS_MOD_PER_PAGE'				=> 'Messages displayed per page',
	'ACP_PRIVMSGS_MOD_POPUP_CLOSE'			=> 'Close window',
	'ACP_PRIVMSGS_MOD_POPUP_TITLE'			=> 'Private Message',
	'ACP_PRIVMSGS_MOD_REGEXP_WARNING'		=> 'The database that you are using with phpBB does not support the use of regular expressions.  This may cause these search results to contain extra records that do not match your search criteria.',
	'ACP_PRIVMSGS_MOD_REMOVED_DEFINITIONS'	=> 'Successfully removed the existing Private Messages Moderation module from your Administration Control Panel',
	'ACP_PRIVMSGS_MOD_RENAMED_CONFIG_VARS'	=> 'Private Messages plugin configuration variables successfully renamed.',
	'ACP_PRIVMSGS_MOD_SEARCH_EXPLAIN'		=> 'Search is not case sensitive, use * as wildcard',
	'ACP_PRIVMSGS_MOD_SEARCH_LABEL'			=> 'Search:',
	'ACP_PRIVMSGS_MOD_SENTBOX'				=> 'SentBox',
	'ACP_PRIVMSGS_MOD_SHOWTEXT'				=> 'Show message text:',
	'ACP_PRIVMSGS_MOD_SHOW_TXT_EXPLAIN'		=> 'Do you want the message text displayed in the list of PMs on the Moderate Messages screen?  If you select "Yes", the message text for each message will be presented in a scrollable box in the message list.  If you select "No", you will need to click the Subject line of a message in order to view the complete text of that message.  That message text will appear in a pop-up window when you click on the Subject line.',
	'ACP_PRIVMSGS_MOD_SHOW_TXT'				=> 'Show message text',
	'ACP_PRIVMSGS_MOD_SORTOPT_ASC'			=> 'Ascending',
	'ACP_PRIVMSGS_MOD_SORTOPT_DESC'			=> 'Descending',
	'ACP_PRIVMSGS_MOD_SORTOPT_MESSAGE_TIME'	=> 'Date/Time',
	'ACP_PRIVMSGS_MOD_SORTOPT_MSG_ID'		=> 'Message ID #',
	'ACP_PRIVMSGS_MOD_SORTOPT_USERNAME'		=> 'Sender',
	'ACP_PRIVMSGS_MOD_SUBJECT'				=> 'SUBJECT',
	'ACP_PRIVMSGS_MOD_SUBMIT'				=> 'Submit',
	'ACP_PRIVMSGS_MOD_TAB'					=> 'Private Messages',
	'ACP_PRIVMSGS_MOD_TO'					=> 'TO',
	'ACP_PRIVMSGS_MOD_UNKNOWN_USER'			=> '(unknown user)',
	'ACP_PRIVMSGS_MOD_VIEW_MSG_TEXT'		=> 'Click to view this message',
	'ACP_PRIVMSGS_MOD_YES'					=> 'Yes',
	'INSTALL_PRIVMSGS_MOD_CONFIRM'			=> 'Do you want to update the ' . MODULES_TABLE . ' database table so that the Private Messages Moderation module will be available in your Administration Control Panel?',
	'INSTALL_PRIVMSGS_MOD'					=> 'Installing the Private Messages Moderation Adminstration Control Panel module',
	'UNINSTALL_PRIVMSGS_MOD_CONFIRM'		=> 'Do you want to remove the Private Messages Moderation module from your Administration Control Panel?',
	'UNINSTALL_PRIVMSGS_MOD'				=> 'Uninstalling the Private Messages Moderation Adminstration Control Panel module',
	'UPDATE_PRIVMSGS_MOD_CONFIRM'			=> 'Do you want to update the ' . MODULES_TABLE . ' database table so that the Private Messages Moderation module will be available in your Administration Control Panel?',
	'UPDATE_PRIVMSGS_MOD'					=> 'Updating the Private Messages Moderation Adminstration Control Panel module',
));

?>

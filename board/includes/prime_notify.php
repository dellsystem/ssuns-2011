<?php
/**
*
* @package phpBB3
* @version $Id: prime_notify.php,v 1.0.6 2009/10/15 02:24:00 primehalo Exp $
* @copyright (c) 2007-2009 Ken F. Innes IV
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

/*
* Include only once.
*/
global $prime_notify;
if (!defined('INCLUDES_PRIME_NOTIFY'))
{
	/**
	* Options
	*/
	define('PRIME_NOTIFY_POST_ENABLED', true);	// Insert the post message into the notification e-mail?
	define('PRIME_NOTIFY_PM_ENABLED', true);	// Insert the private message into the notification e-mail?
	define('PRIME_NOTIFY_BBCODES', true);		// Keep BBCodes (helps show how the message is supposed to be formatted)?
	define('PRIME_NOTIFY_ALWAYS', true);		// Notify user even if they've already received a previous notification and have not yet visited the forum to read it?

	// Only set this to true if you installed "User's Choice" add-on.
	define('PRIME_NOTIFY_USER_CHOICE', false);

	// Private Message Specific
	define('PRIME_NOTIFY_REPLY_TO_SENDER',	true);	// Set the PM notification reply-to address to be that of the sender (only if their e-mail address is set to be visible).
	define('PRIME_NOTIFY_REPLY_TO_ADDRESS',	'');	// If reply-to-sender is off or not available, you can enter an address here to override the board default (eg. do-not-reply@domain.com)

	/**
	* Constants
	*/
	define('INCLUDES_PRIME_NOTIFY', true);

	/**
	* Class declaration
	*/
	class prime_notify
	{
		var $enabled = true;
		var $author = '';
		var $message = '';
		var $visit_msg = array();

		/**
		* Constructor
		*/
		function prime_notify()
		{
			$this->enabled = true;
			$this->author = '';
			$this->message = '';
			$this->visit_msg = array();
		}

		/**
		* Format the message for e-mail
		*/
		function format_message(&$text, $uid_param = '', $keep_bbcodes = true)
		{
			global $user;

			$uid = $uid_param ? $uid_param : '[0-9a-z]{5,}';

			// If there is a spoiler, remove the spoiler content.
			$search = '@\[spoiler(?:=[^]]*)?:' . $uid . '\](.*?)\[/spoiler:' . $uid . '\]@s';
			$replace = /*!$keep_bbcodes ? '----- (spoiler removed) -----' :*/ '[spoiler](' . $user->lang['NA'] . ')[/spoiler]';
			$text = preg_replace($search, $replace, $text);

			if ($keep_bbcodes)
			{
				// Strip unique ids out of BBCodes
				$text = preg_replace("#\[(\/?[a-z0-9\*\+\-]+(?:=.*?)?(?::[a-z])?)(\:?$uid)\]#", '[\1]', $text);

				// If there is a URL between BBCode URL tags, then add spacing so
				// the email program won't think the BBCode is part of the URL.
				$text = preg_replace('@](http://.*?)\[@', '] $1 [', $text);
			}
			else
			{
				// Change quotes
				$text = preg_replace('@\[quote=(?:"|&quot;)([^"]*)(?:"|&quot;):' . $uid . '\]@', "[quote=\"$1\"]", $text);
				$text = preg_replace('@\[code=([a-z]+):' . $uid . '\]@', "[code=$1]", $text);
				$text = preg_replace('@\[(/)?(quote|code):' . $uid . '\]@', "[$1$2]", $text);

				// Change lists (quick & dirty, no checking if we're actually in a list, much less if it's ordered or unordered)
				$text = str_replace('[*]', '* ', $text);
				$text = $uid_param ? str_replace('[*:' . $uid . ']', '* ', $text) : preg_replace('/\[\*:' . $uid . ']/', '* ', $text);

				// Change [url=http://www.example.com]Example[/url] to Example (http://www.example.com)
				$text = preg_replace('@\[url=([^]]*):' . $uid . '\]([^[]*)\[/url:' . $uid . '\]@', '$2 ($1)', $text);

				// Remove all remaining BBCodes
				//strip_bbcode($text, $uid_param); // This function replaces BBCodes with spaces, which we don't want
				$text = preg_replace("#\[\/?[a-z0-9\*\+\-]+(?:=(?:&quot;.*&quot;|[^\]]*))?(?::[a-z])?(\:$uid)\]#", '', $text);
				$match = get_preg_expression('bbcode_htm');
				$replace = array('\1', '\1', '\2', '\1', '', '');
				$text = preg_replace($match, $replace, $text);
			}

			// Change HTML smiley images to text smilies
			$text = preg_replace('#<!-- s[^ >]* --><img src="[^"]*" alt="([^"]*)" title="[^"]*" /><!-- s[^ >]* -->#', ' $1 ', $text);

			// Change HTML links to text links
			$text = preg_replace('#<!-- [lmw] --><a .*?href="([^"]*)">.*?</a><!-- [lmw] -->#', '$1', $text);

			// Change HTML e-mail links to text links
			$text = preg_replace('#<!-- e --><a .*?href="[^"]*">(.*?)</a><!-- e -->#', '$1', $text);

			// Transform special BBCode characters into human-readable characters
			$transform = array('&lt;' => '<', '&gt;' => '>', '&#91;' => '[', '&#93;' => ']', '&#46;' => '.', '&#58;' => ':');
			$text = str_replace(array_keys($transform), array_values($transform), $text);

			// Remove backslashes that appear directly before single quotes
			$text = stripslashes(trim($text));
		}

		/**
		* Alter the SQL statement to fit our needs
		*/
		function alter_post_sql(&$sql)
		{
			if ($this->enabled)
			{
				// Always notify, so don't check if a notification was already sent
				if (PRIME_NOTIFY_ALWAYS)
				{
					$sql = str_replace('AND w.notify_status = 0', '', $sql);
					$sql = str_replace('AND fw.notify_status = 0', '', $sql);
				}

				// Check for user's choice
				if (PRIME_NOTIFY_USER_CHOICE)
				{
					$sql = substr_replace($sql, ', u.user_notify_content ', strpos($sql, 'FROM'), 0);
				}
			}
		}


		/**
		* Initial setup for including message text in the new post notification e-mail
		*/
		function setup_post(&$data)
		{
			global $user, $phpEx;

			$this->enabled = PRIME_NOTIFY_POST_ENABLED;
			if ($this->enabled && isset($data['message']) && $user->page['page_name'] == "posting.$phpEx")
			{
				$this->author  = $user->data['username']; // This is the name, not the ID
				$this->message = empty($data['message']) ? '' : $data['message'];

				// If BBCodes are not enabled for this post, then we keep them because they do not represent formatting
				$keep_bbcodes = empty($data['enable_bbcode']) ? true : PRIME_NOTIFY_BBCODES;

				// Format the message
				$uid = empty($data['bbcode_uid']) ? '' : $data['bbcode_uid'];
				$this->format_message($this->message, $uid, $keep_bbcodes);

				// If something went wrong, then just go back to using the default e-mail notification template
				if (empty($this->author) && empty($this->message))
				{
					$this->enabled = false;
				}
			}
			else
			{
				$this->enabled = false;
			}
		}

		/**
		* Specify our template
		*/
		function setup_post_template(&$notify_row, &$row)
		{
			$user_enabled = (!isset($row['user_notify_content']) || (PRIME_NOTIFY_USER_CHOICE && !empty($row['user_notify_content'])));
			if ($this->enabled && $user_enabled)
			{
				$notify_row['template'] = ($notify_row['template'] == 'forum_notify') ? 'prime_notify_forum' : ($notify_row['template'] == 'topic_notify' ? 'prime_notify_topic' : 'prime_notify_newtopic');
			}
		}

		/**
		* Setup the template variables that will be inserted into the e-mail
		*/
		function setup_post_vars(&$messenger, $msg_lang, $template)
		{
			if ($this->enabled && (strpos($template, 'prime_notify') === 0))
			{
				$visit_msg = '';
				if (!PRIME_NOTIFY_ALWAYS)
				{
					global $phpbb_root_path, $phpEx, $config;

					$msg_lang = !trim($msg_lang) ? basename($config['default_lang']) : $msg_lang;
					$msg_type = ($template == 'prime_notify_topic') ? 'PRIME_NOTIFY_TOPIC_VISIT_MSG' : 'PRIME_NOTIFY_FORUM_VISIT_MSG';
					if (!isset($this->visit_msg[$msg_lang][$msg_type]))
					{
						@include("{$phpbb_root_path}language/$msg_lang/mods/prime_notify.$phpEx");
						$this->visit_msg[$msg_lang][$msg_type] = isset($lang[$msg_type]) ? $lang[$msg_type] : '';
					}
					$visit_msg = $this->visit_msg[$msg_lang][$msg_type];
				}
				$messenger->assign_vars(array(
					'AUTHOR'	=> htmlspecialchars_decode($this->author),
					'MESSAGE'	=> htmlspecialchars_decode($this->message),
					'VISIT_MSG'	=> htmlspecialchars_decode($visit_msg),
				));
			}
		}

		/**
		* Initial setup for including message text in the private message notification e-mail
		*/
		function setup_pm(&$data)
		{
			$this->enabled = PRIME_NOTIFY_PM_ENABLED;
			if ($this->enabled && isset($data['message']))
			{
				$this->message = $data['message'];

				// If BBCodes are not enabled for this post, then we keep them because they do not represent formatting
				$keep_bbcodes = empty($data['enable_bbcode']) ? true : PRIME_NOTIFY_BBCODES;

				// Format the message
				$uid = empty($data['bbcode_uid']) ? '' : $data['bbcode_uid'];
				$this->format_message($this->message, $uid, $keep_bbcodes);
			}
			else
			{
				$this->enabled = false;
			}
		}

		/**
		* Setup the template variables that will be inserted into the e-mail
		*/
		function setup_pm_vars(&$messenger, &$addr)
		{
			global $user;

			if ($this->enabled)
			{
				$messenger->template('prime_notify_privmsg', $addr['lang']);
				if (PRIME_NOTIFY_REPLY_TO_SENDER && $user->data['user_allow_viewemail'])
				{
					$messenger->replyto($user->data['user_email']);
				}
				else if (PRIME_NOTIFY_REPLY_TO_ADDRESS)
				{
					$messenger->replyto(PRIME_NOTIFY_REPLY_TO_ADDRESS);
				}
				$messenger->assign_vars(array(
					'MESSAGE'	=> htmlspecialchars_decode($this->message),
				));
			}
		}

		/**
		*/
		function setup_ucp_prefs(&$data, $submit)
		{
			if (PRIME_NOTIFY_USER_CHOICE)
			{
				global $user, $template;

				$user->add_lang('mods/prime_notify');
				$data['notify_content'] = request_var('notify_content', $user->data['user_notify_content']);
				if (!$submit)
				{
					$template->assign_var('S_PRIME_NOTIFY_POST_CONTENT', true);
					$template->assign_var('S_NOTIFY_POST_CONTENT', $data['notify_content']);
				}
			}
		}

		/**
		*/
		function alter_ucp_prefs_sql(&$data, &$sql_ary)
		{
			if (PRIME_NOTIFY_USER_CHOICE)
			{
				$sql_ary['user_notify_content'] = $data['notify_content'];
			}
		}
	}
	// End class

	$prime_notify = new prime_notify();
}
?>

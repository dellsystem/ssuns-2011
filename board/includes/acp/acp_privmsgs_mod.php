<?php
/**
*
* @author Two Sheds twosheds@twosheds.com
*
* @package privmsgs
* @version $Id:1.5.4
* @copyright (c) 2009 TwoSheds
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

/**
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

/**
* @package privmsgs
*/
class acp_privmsgs_mod
{

	//  Translate quotes in a $text string into HTML symbols
	function dequote($text, $javascript = false)
	{
		if ($javascript)
		{
			$from = array("/\'/", "/\"/", "/\n/", "/\//", '/</');
			$to = array("&lsquo;", '&quot;', '', '\/', '~~!!');
		} else {
			$from = array("/\'/", "/\n/");
			$to = array('&lsquo;', '');
		}
		return preg_replace($from, $to, $text);
	}

	//  Given a username, look up the user_id
	function get_userid($username)
	{
		global $config, $db, $user, $auth, $template, $cache;
		global $phpbb_root_path, $phpEx, $table_prefix;

		$sql = 'SELECT user_id
			FROM ' . USERS_TABLE . '
			WHERE username_clean = "' . $db->sql_escape(utf8_clean_string($username)) . '"';
		$result = $db->sql_query($sql, 600);   //  Cache this query for 10 minutes
		$id = $db->sql_fetchfield('user_id');
		$db->sql_freeresult($result);
		if ($id)
		{
			return "u_$id";
		}
		else
		{
			return FALSE;
		}
	}	

	//  For the "configure" mode of this module
	function configure()
	{
		global $config, $db, $user, $auth, $template, $cache;
		global $phpbb_root_path, $phpEx, $table_prefix;
		
		$error_message = "";
		$debug_message = "";

		//  Update the configuration table when they send us a post
		if (isset($_POST['submit']) && check_form_key('privmsgs'))
		{
			//  Get request variable values
			$per_page = request_var('per_page', $config['topics_per_page']);
			$show_txt = request_var('show_txt', 'y');
			$filter = request_var('filter', '');

			//  Do data entry validation
			$error = '';
			if ($per_page == 0)
			{
				$error = 'ACP_PRIVMSGS_MOD_NOT_A_NUMBER';
			}

			//  If everything is ok, update the config array which is also
			//  persisted in the database.  Otherwise show the error message.
			if (empty($error))
			{
				set_config('acp_privmsgs_mod_per_page',	$per_page);
				set_config('acp_privmsgs_mod_show_txt',	$show_txt);
				set_config('acp_privmsgs_mod_filter',	$filter);

				trigger_error($user->lang['ACP_PRIVMSGS_MOD_CONFIG_UPDATED'] . adm_back_link($this->u_action));
			} else	{
				trigger_error($user->lang($error) . adm_back_link($this->u_action), E_USER_WARNING);
			}

		} 
		else if (isset($_POST['submit']) && !check_form_key('privmsgs'))
		{
			trigger_error($user->lang['FORM_INVALID'] . adm_back_link($this->u_action), E_USER_WARNING);
		}

		add_form_key('privmsgs');

		//  Display the configuration form, populated with the current settings
		if (!isset($config['acp_privmsgs_mod_show_txt'])) 
		{ 
			$config['acp_privmsgs_mod_show_txt'] = 'y';
		}

		$template->assign_vars(array(
			'ERROR_MESSAGE'			=> "$error_message",
			'DEBUG_MESSAGE'			=> "$debug_message",
			'U_CONFIG'				=> $this->u_action . '&amp;mode=configure',
			'PER_PAGE'				=> (!empty($config['acp_privmsgs_mod_per_page'])) ? $config['acp_privmsgs_mod_per_page'] : $config['topics_per_page'],
			'SHOW_TXT_CHECKED_YES'	=> ($config['acp_privmsgs_mod_show_txt'] == 'y') ? 'checked="checked"' : '',
			'SHOW_TXT_CHECKED_NO'	=> ($config['acp_privmsgs_mod_show_txt'] == 'n') ? 'checked="checked"' : '',
			'FILTER'				=> (!empty($config['acp_privmsgs_mod_filter'])) ? $config['acp_privmsgs_mod_filter'] : '',
			'S_VERSION'				=> (isset($config['acp_privmsgs_mod_version'])) ? 'v' . $config['acp_privmsgs_mod_version'] : ''
		));

		//  Specify the page template name
		$this->tpl_name = 'acp_privmsgs_mod';
		$this->page_title = $user->lang['ACP_PRIVMSGS_MOD_CONFIG'];
	}	

	//  The main function either manages the PMs or calls the configure function
	//  when the configure mode is specified	
	function main($id, $mode)
	{
		global $config, $db, $dbms, $user, $auth, $template, $cache;
		global $phpbb_root_path, $phpEx, $table_prefix;

		//  If they've clicked the configure link, do defaults configuration
		if ($mode == 'configure')
		{
			$this->configure();
			return;
		}

		$error_message = "";
		$debug_message = "";

		include($phpbb_root_path . 'includes/functions_privmsgs.' . $phpEx);
		include($phpbb_root_path . 'includes/message_parser.' . $phpEx);

		$user->add_lang('mods/info_acp_privmsgs_mod');

		// Get variables passed in the URL
		$action = request_var('action', '');
		$mark = (isset($_REQUEST['mark'])) ? request_var('mark', array(0)) : array();
		$start = request_var('start', 0);
		$sort = request_var('sort', '');
		$submit = isset($_POST['submit']);
		$showtext = request_var('showtext', '');
		$sortkey = request_var('sortkey', 'msg_id');
		$sortdir = request_var('sortdir', 'd');
		$per_page = request_var('messages_per_page', '');
		$all_by_user = utf8_normalize_nfc(request_var('all_by_user', 'All', true));
		$all_to_user = utf8_normalize_nfc(request_var('all_to_user', 'All', true));
		$search_str = utf8_normalize_nfc(request_var('search_str', '', true));

		//  If we didn't get specific values for these variables, then use the value from
		//  the $config array or, failing that, use a default
		$per_page = (is_numeric($per_page)) ? $per_page : '';
		if ($per_page == '')
		{
			if (isset($config['acp_privmsgs_mod_per_page']))
			{
				$per_page = $config['acp_privmsgs_mod_per_page'];
			} 
			else 
			{
				$per_page = $config['topics_per_page'];
			}
		}
		if ($showtext == '')
		{
			if (isset($config['acp_privmsgs_mod_show_txt']))
			{
				$showtext = $config['acp_privmsgs_mod_show_txt'];
			}
			else
			{
				$showtext = 'y';
			}
		}

		//  Use a form key for security reasons
		$form_key = 'acp_privmsgs_mod';
		add_form_key($form_key);

		/**
		* Handle form actions
		*/

		if ($submit && sizeof($mark))
		{
			if ($action !== 'delete' && !check_form_key($form_key))
			{
				trigger_error($user->lang['FORM_INVALID'] . adm_back_link($this->u_action), E_USER_WARNING);
			}

			switch ($action)
			{
				case 'delete':
					if (confirm_box(true))
					{
						foreach ($mark as $msg_id)
						{
							//  Get all the user_id/folder_id combinations that this message lives in
							//  and delete it from each of those
							$sql_array = array(
								'SELECT'	=> 't.user_id, t.folder_id, u.username',
								'FROM'		=> array(PRIVMSGS_TO_TABLE => 't'),
								'LEFT_JOIN'	=> array (
									array(
										'FROM'	=> array(USERS_TABLE => 'u'),
										'ON'	=> 't.user_id = u.user_id'
									)
								),
								'WHERE'		=> 't.msg_id = ' . $msg_id
							);
							$sql = $db->sql_build_query('SELECT', $sql_array);
							$result = $db->sql_query($sql);
							while ($row = $db->sql_fetchrow($result))
							{
								delete_pm($row['user_id'], $msg_id, $row['folder_id']);
								if (empty($log_data))
								{
									$log_data = $row['username'] . ': ' . $msg_id;
								} 
								else 
								{
									$log_data .= ', ' . $row['username'] . ': ' . $msg_id;
								}
							}
							$db->sql_freeresult($result);
						}
						add_log('admin', 'ACP_PRIVMSGS_MOD_LOG_DELETE', $log_data);
					} else {
						$s_hidden_fields = array(
							'mode'				=> $mode,
							'action'			=> $action,
							'mark'				=> $mark,
							'submit'			=> 1,
							'start'				=> $start,
							'sortkey'			=> $sortkey,
							'sortdir'			=> $sortdir,
							'messages_per_page'	=> $per_page,
							'showtext'			=> $showtext,
							'all_by_user'		=> $all_by_user,
							'all_to_user'		=> $all_to_user,
						);
						confirm_box(false, $user->lang['ACP_PRIVMSGS_MOD_CONFIRM_DELETE'], build_hidden_fields($s_hidden_fields));
					}
				break;
			}
		}

		/**
		* List the private messages
		*/

		//  We don't want to see messages that are already marked as deleted
		$sql_where = 't.pm_deleted != 1';
		
		//  Get the id numbers for the users who were specified				
		if (($all_by_user !== '') && (strtoupper($all_by_user) !== 'ALL'))
		{
			$by_id = $this->get_userid($all_by_user);
			if (!$by_id)
			{
				if (! empty($error_message))
				{
					$error_message .= ' and ';
				}
				$error_message .= "\"$all_by_user\" {$user->lang['ACP_PRIVMSGS_MOD_ERROR_INVALID_USER']}";
			}
		}
		if (($all_to_user !== '') && (strtoupper($all_to_user) !== 'ALL'))
		{
			$to_id = $this->get_userid($all_to_user);
			if (!$to_id)
			{
				if (! empty($error_message))
				{
					$error_message .= ' and ';
				}
				$error_message .= "\"$all_to_user\" {$user->lang['ACP_PRIVMSGS_MOD_ERROR_INVALID_USER']}";
			}
		}
		
		//  When both $all_by_user and $all_to_user are filled in, get the
		//  conversation between the two of them
		if (!empty($by_id) && !empty($to_id))
		{
			if (! empty($sql_where))
			{
				$sql_where .= " AND ";
			}
			switch ($dbms)
			{
				case 'mysql':
				case 'mysqli':
					$sql_where .= '(( UCASE(u.username) = "' . strtoupper($db->sql_escape($all_by_user)) . '" AND ( CAST(p.to_address AS CHAR) REGEXP "[[:<:]]' .
						$to_id . '[[:>:]]" OR CAST(p.bcc_address AS CHAR) REGEXP "[[:<:]]' . $to_id . '[[:>:]]" ) ) OR ( UCASE(u.username) = "' .
						strtoupper($db->sql_escape($all_to_user))  . '" AND ( CAST(p.to_address AS CHAR) REGEXP "[[:<:]]' . $by_id .
						'[[:>:]]" OR CAST(p.bcc_address AS CHAR) REGEXP "[[:<:]]' . $to_id . '[[:>:]]" ) ))';
					break;
				case 'postgres':
					$sql_where .= '(( UCASE(u.username) = "' . strtoupper($db->sql_escape($all_by_user)) . '" AND ( CAST(p.to_address AS CHAR) ~ "[[:<:]]' . $to_id . 
						'[[:>:]]" OR CAST(p.bcc_address AS CHAR) ~ "[[:<:]]' . $to_id . '[[:>:]]" ) ) OR ( UCASE(u.username) = "' . 
						strtoupper($db->sql_escape($all_to_user))  . '" AND ( CAST(p.to_address AS CHAR) ~ "[[:<:]]' . $by_id .
						'[[:>:]]" OR CAST(p.bcc_address AS CHAR) ~ "[[:<:]]' . $to_id . '[[:>:]]" ) ))';
					break;
				case 'oracle':
					$sql_where .= '(( UCASE(u.username) = "' . strtoupper($db->sql_escape($all_by_user)) . '" AND ( REGEXP_LIKE(CAST(p.to_address AS CHAR), "\b' .
						$to_id . '\b") OR REGEXP_LIKE(CAST(p.bcc_address AS CHAR), "\b' . $to_id . '\b") ) ) OR ( UCASE(u.username) = "' .
						strtoupper($db->sql_escape($all_to_user))  . '" AND ( REGEXP_LIKE(CAST(p.to_address AS CHAR), "\b' . $by_id .
						'\b") OR REGEXP_LIKE(CAST(p.bcc_address AS CHAR), "\b' . $to_id . '\b") ) ))';
					break;
				default:
					$error_message = $user->lang['ACP_PRIVMSGS_MOD_REGEXP_WARNING'];
					$sql_where .= '(( UCASE(u.username) = "' . strtoupper($db->sql_escape($all_by_user)) . '" AND ( CAST(p.to_address AS CHAR) ' .
						$db->sql_like_expression($db->any_char . $to_id . $db->any_char) .  ' OR CAST(p.bcc_address AS CHAR) ' .
						$db->sql_like_expression($db->any_char . $to_id . $db->any_char) .  ') ) OR ( UCASE(u.username) = "' .
						strtoupper($db->sql_escape($all_to_user))  . '" AND ( CAST(p.to_address AS CHAR) ' . $db->sql_like_expression($db->any_char . $by_id .
						$db->any_char) . ' OR CAST(p.bcc_address AS CHAR) ' . $db->sql_like_expression($db->any_char . $by_id . $db->any_char) . ') ))';
					break;
			}
		} 
		else 
		{
			//  Select a specific author
			if (!empty($by_id))
			{
				if (! empty($sql_where))
				{
					$sql_where .= " AND ";
				}
				$sql_where .= 'UCASE(u.username) = "' . strtoupper($db->sql_escape($all_by_user)) . '"';
			}
		
			//  Select a specific recipient
			if (!empty($to_id))
			{
				if (! empty($sql_where))
				{
					$sql_where .= " AND ";
				}
				switch ($dbms)
				{
					case 'mysql':
					case 'mysqli':  
						$sql_where .= '( CAST(p.to_address AS CHAR) REGEXP "[[:<:]]' . $to_id . '[[:>:]]" OR CAST(p.bcc_address AS CHAR) REGEXP "[[:<:]]' .
							$to_id . '[[:>:]]" )';
						break;
					case 'postgres':
						$sql_where .= '( CAST(p.to_address AS CHAR) ~ "[[:<:]]' . $to_id . '[[:>:]]" OR CAST(p.bcc_address AS CHAR) ~ "[[:<:]]' . $to_id .
							'[[:>:]]" )';
						break;
					case 'oracle':
						$sql_where .= '( REGEXP_LIKE(CAST(p.to_address AS CHAR), "\b' . $to_id . '\b") OR REGEXP_LIKE(CAST(p.bcc_address AS CHAR), "[[:<:]]' .
							$to_id . '[[:>:]]") )';
						break;
					default:
						$error_message = $user->lang['ACP_PRIVMSGS_MOD_REGEXP_WARNING'];
						$sql_where .= '( CAST(p.to_address AS CHAR) ' . $db->sql_like_expression($db->any_char . $to_id . $db->any_char) . 
							' OR CAST(p.bcc_address AS CHAR) ' . $db->sql_like_expression($db->any_char . $to_id . $db->any_char) . ' )';
						break;
				}
			}
		}

		//  If they specified a filter string in the config, filter out 
		//  messages containing that string
		if (!empty($config['acp_privmsgs_mod_filter']))
		{
			if (! empty($sql_where))
			{
				$sql_where .= " AND ";
			}
			$sql_where .= 'p.message_text NOT ' . $db->sql_like_expression($db->any_char . $config['acp_privmsgs_mod_filter'] . $db->any_char);
		}

		//  If they submitted a search string, find the messages that contain that string
		if ($search_str !== '')
		{
			if (! empty($sql_where))
			{
				$sql_where .= " AND ";
			}
			$search_string = $db->sql_like_expression($db->any_char . str_replace('*', $db->any_char, $db->sql_escape(strtoupper(trim(trim($search_str), '*')))) . $db->any_char);
			$sql_where .= "( UCASE(p.message_text) $search_string OR UCASE(p.message_subject) $search_string )";
		}

		//  Get the total number of private messages so that we can set up 
		//  the pagination control
		$sql_array = array(
			'SELECT'	=> 'COUNT(DISTINCT p.msg_id) AS total_msgs',
			'FROM'		=> array(PRIVMSGS_TABLE => 'p'),
			'LEFT_JOIN'	=> array (
				array(
					'FROM'	=> array(PRIVMSGS_TO_TABLE => 't'),
					'ON'	=> 'p.msg_id = t.msg_id'
				),
				array(
					'FROM'	=> array(USERS_TABLE => 'u'),
					'ON'	=> 'p.author_id = u.user_id'
				)
			),
			'WHERE'		=> $sql_where
		);
		$sql = $db->sql_build_query('SELECT', $sql_array);
		$result = $db->sql_query($sql);
		$total_messages = (float) $db->sql_fetchfield('total_msgs');
		$db->sql_freeresult($result);

		//  Make sure that $start isn't set too high
		while ($start >= $total_messages)
		{
			$start = $start - $per_page;
		}
		if ($start < 0) {
			$start = 0;
		}

		//  Initiate a message parser to interpret BBcode in message text
		$message_parser = new parse_message();
		
		//  Pull a page of private messages.
		switch ($sortkey)
		{
			case 'username': 
				$sql_sort = 'username'; 
				break;
			case 'message_time': 
				$sql_sort = 'message_time'; 
				break;
			default: 
				$sql_sort = 'msg_id'; 
				break;
		}
		$sql_sort .= ($sortdir == 'a') ? ' ASC' : ' DESC';
		$sql_array = array(
			'SELECT'	=> 'p.*, u.username, t.user_id, t.folder_id AS to_folder_id', 
			'FROM'		=> array(PRIVMSGS_TABLE => 'p'),
			'LEFT_JOIN'	=> array (
				array(
					'FROM'	=> array(USERS_TABLE => 'u'),
					'ON'	=> 'p.author_id = u.user_id'
				),
				array(
					'FROM'	=> array(PRIVMSGS_TO_TABLE => 't'),
					'ON'	=> 'p.msg_id = t.msg_id'
				),
			),
			'WHERE'		=> $sql_where,
			'ORDER_BY'	=> $sql_sort,
			'GROUP_BY'	=> 't.msg_id'
		);
		$sql = $db->sql_build_query('SELECT', $sql_array);
		$result = $db->sql_query_limit($sql, $per_page, $start);
		while ($row = $db->sql_fetchrow($result))
		{
		    //  Build the URL that we'll use to do searches on individual users
			$link = $this->u_action;
			$link .= (empty($start)) ? '' : "&amp;start=$start";
			$link .= (empty($sortkey)) ? '' : "&amp;sortkey=$sortkey";
			$link .= (empty($sortdir)) ? '' : "&amp;sortdir=$sortdir";
			$link .= (empty($per_page)) ? '' : "&amp;messages_per_page=$per_page";
			$link .= (empty($showtext)) ? '' : "&amp;showtext=$showtext";
			$link .= (empty($search_str)) ? '' : "&amp;search_str=$search_str";
		    
			//  Load the message parser and parse the message
			$message_parser->message = $row['message_text'];
			$message_parser->bbcode_uid = $row['bbcode_uid'];
			$message_parser->bbcode_bitfield = $row['bbcode_bitfield'];
			$message_text = $message_parser->format_display($row['enable_bbcode'], $row['enable_magic_url'], $row['enable_smilies'], false);
			
			//  Translate user id numbers from the to_address and bcc_address 
			//  columns into user names wrapped in search links
			$to_array = explode(',', $row['to_address']);
			$bcc_array = explode(',', $row['bcc_address']);
			for ($i = 0; $i < count($bcc_array); $i++)
			{
				if (!empty($bcc_array[$i]))
				{
					$to_array[] = $bcc_array[$i];
				}
			}
			$elements = count($to_array);
			for ($i = 0; $i < $elements; $i++)
			{
				if (empty($to_array[$i]))
				{
					unset($to_array[$i]);
				}
				else
				{
					$id = trim($to_array[$i], 'u_');
					$sql = 'SELECT username
						FROM ' . USERS_TABLE . '
						WHERE user_id = "' . $id . '"';
					$id_result = $db->sql_query($sql, 600);  //  Cache this query for 10 minutes
					$name = $db->sql_fetchfield('username');
					if (empty($name))
					{
						//  Deal with the case where to_address is a group
						$id = trim($to_array[$i], 'g_');
						$sql = 'SELECT group_name
							FROM ' . GROUPS_TABLE . '
							WHERE group_id = "' . $id . '"';
						$id_result = $db->sql_query($sql, 600);  //  Cache this query for 10 minutes
						$name = $db->sql_fetchfield('group_name');
						if (empty($name))
						{
							$to_array[$i] = '<span style="font-style: italic;">Unknown id: ' . $to_array[i] . '</span>';
						}
						{
							$to_array[$i] = $name . '(a group)';
						}
					}
					else
					{
						$to_array[$i] = "<a href=\"" . $link . "&amp;all_to_user=" . $name . "\" title=\"" . $user->lang['ACP_PRIVMSGS_MOD_ALL_TO_USER'] .
							"\">" . $name . "</a>";
					}
					$db->sql_freeresult($id_result);
				}
			}

			//  Build a search link around the author's user name
			$from_link = "<a href=\"" . $link . "&amp;all_by_user=" . $row['username'] . "\" title=\"" . $user->lang['ACP_PRIVMSGS_MOD_ALL_BY_USER'] . "\">" . 
				$row['username'] . "</a>";
				
			//  Add a flag if the only copy of this message is in the author's 
			//  SentBox or OutBox
			$to_folder_array = explode(',', $row['to_folder_id']);
			if (count($to_folder_array) == 1)
			{
				if ($to_folder_array[0] == PRIVMSGS_SENTBOX)
				{
					$from_link .= ' (' . $user->lang['ACP_PRIVMSGS_MOD_SENTBOX'] . ')';
				}
				if ($to_folder_array[0] == PRIVMSGS_OUTBOX)
				{
					$from_link .= ' (' . $user->lang['ACP_PRIVMSGS_MOD_OUTBOX'] . ')';
				}
			}

			//  Load this message into the template
			$template->assign_block_vars('messages', array(
				'MSG_ID' => $row['msg_id'],
				'FROM' => $from_link,
				'TO' => implode(', ', $to_array),
				'DATE' => $user->format_date($row['message_time']),
				'SUBJECT' => $this->dequote($row['message_subject'], false),
				'SUBJECT_JS_STR' => $this->dequote($row['message_subject'], true),
				'TEXT' => $this->dequote($message_text, false),
				'TEXT_JS_STR' => $this->dequote($message_text, true)
			));
		}  //  End of while
		$db->sql_freeresult($result);

		//  Build base URL used to build links in the pagination control
		$pagination_url = $this->u_action;
		$pagination_url .= (empty($sortkey)) ? '' : "&amp;sortkey=$sortkey";
		$pagination_url .= (empty($sortdir)) ? '' : "&amp;sortdir=$sortdir";
		$pagination_url .= (empty($per_page)) ? '' : "&amp;messages_per_page=$per_page";
		$pagination_url .= (empty($showtext)) ? '' : "&amp;showtext=$showtext";
		$pagination_url .= (empty($search_str)) ? '' : "&amp;search_str=$search_str";
		if ($sort !== '')
		{
			$pagination_url .= '&amp;sort=' . $sort;
		}
		if ($all_by_user !== '')
		{
			$pagination_url .= '&amp;all_by_user=' . $all_by_user;
		}
		if ($all_to_user !== '')
		{
			$pagination_url .= '&amp;all_to_user=' . $all_to_user;
		}

		//  Define a list of the "actions" we can take on items that are 
		//  "marked" on the page
		$option_ary = array('delete' => 'ACP_PRIVMSGS_MOD_DELETE');

		//  Define a list of the sorting options
		$sortkey_ary = array('msg_id' => 'ACP_PRIVMSGS_MOD_SORTOPT_MSG_ID', 'username' => 'ACP_PRIVMSGS_MOD_SORTOPT_USERNAME',
			'message_time' => 'ACP_PRIVMSGS_MOD_SORTOPT_MESSAGE_TIME');

		//  Define a list of the sort direction options
		$sortdir_ary = array('a' => 'ACP_PRIVMSGS_MOD_SORTOPT_ASC', 'd' => 'ACP_PRIVMSGS_MOD_SORTOPT_DESC');

		//  Define a list of the show message text options
		$showtext_ary = array('y' => 'ACP_PRIVMSGS_MOD_YES', 'n' => 'ACP_PRIVMSGS_MOD_NO');
		
		//  Load the template array with the rest of the content that it needs
		//  for page controls, forms, etc
		$link = $this->u_action;
		$link .= (empty($start)) ? '' : "&amp;start=$start";
		$link .= (empty($sortkey)) ? '' : "&amp;sortkey=$sortkey";
		$link .= (empty($sortdir)) ? '' : "&amp;sortdir=$sortdir";
		$link .= (empty($per_page)) ? '' : "&amp;messages_per_page=$per_page";
		$link .= (empty($showtext)) ? '' : "&amp;showtext=$showtext";
		$link .= (empty($search_str)) ? '' : "&amp;search_str=$search_str";
		$link .= (empty($all_by_user)) ? '' : "&amp;all_by_user=$all_by_user";
		$link .= (empty($all_to_user)) ? '' : "&amp;all_to_user=$all_to_user";
		$template->assign_vars(array(
			'S_IN_ACP_PRIVMSGS_MOD'	=> true,
			'ERROR_MESSAGE'			=> "$error_message",
			'DEBUG_MESSAGE'			=> "$debug_message",
			'PAGINATION'			=> generate_pagination($pagination_url, $total_messages, $per_page, $start, true),
			'S_ON_PAGE'				=> on_page($total_messages, $per_page, $start),
			'MESSAGES_PER_PAGE'		=> $per_page,
			'S_SORT_KEY'			=> build_select($sortkey_ary, $sortkey),
			'S_SORT_DIR'			=> build_select($sortdir_ary, $sortdir),
			'S_SHOWTEXT'			=> build_select($showtext_ary, $showtext),
			'S_SHOW_MESSAGE_TEXT'	=> ($showtext == 'y') ? 'YES' : '',
			'S_PRIVMSGS_OPTIONS'	=> build_select($option_ary),
			'U_ACTION'				=> $link,
			'U_SEARCH_STR'			=> $search_str,
			'U_FIND_USERNAME'		=> append_sid("{$phpbb_root_path}memberlist.$phpEx", 'mode=searchuser&amp;form=privmsgs&amp;field=all_by_user&amp;select_single=true'),
			'U_ALL_BY_USER'			=> $all_by_user,
			'U_ALL_TO_USER'			=> $all_to_user,
			'U_FIND_TONAME'			=> append_sid("{$phpbb_root_path}memberlist.$phpEx", 'mode=searchuser&amp;form=privmsgs&amp;field=all_to_user&amp;select_single=true'),
			'S_VERSION'				=> (isset($config['acp_privmsgs_mod_version'])) ? 'v' . $config['acp_privmsgs_mod_version'] : '',
			'S_POPUP_TITLE' 		=> $this->dequote($user->lang['ACP_PRIVMSGS_MOD_POPUP_TITLE'], true),
			'S_POPUP_CLOSE' 		=> $this->dequote($user->lang['ACP_PRIVMSGS_MOD_POPUP_CLOSE'], true),
		));

		//  Specify the page template name
		$this->tpl_name = 'acp_privmsgs_mod';
		$this->page_title = $user->lang['ACP_PRIVMSGS_MOD_META_TITLE'];

	}  // end of funtion main

}  // end of class acp_privmsgs_mod

?>

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

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

class acp_add_user
{
	var $u_action;

	function main($id, $mode)
	{
		global $config, $db, $user, $auth, $template;
		global $phpbb_root_path, $phpbb_admin_path, $phpEx;

		include($phpbb_root_path . 'includes/functions_profile_fields.' . $phpEx);
		include($phpbb_root_path . 'includes/functions_user.' . $phpEx);

		$submit 		= (isset($_POST['submit'])) ? true : false;
		$admin_activate = (isset($_POST['activate'])) ? (($config['require_activation'] == USER_ACTIVATION_ADMIN) ? true : false) : false;

		$user->add_lang(array('posting', 'ucp', 'acp/users'));

		add_form_key('acp_add_user');

		$cp = new custom_profile();

		$error = $cp_data = $cp_error = array();

		// Try to automatically determine the timezone and daylight savings time settings
		$timezone = date('Z') / 3600;
		$is_dst = date('I');

		if ($config['board_timezone'] == $timezone || $config['board_timezone'] == ($timezone - 1))
		{
			$timezone = ($is_dst) ? $timezone - 1 : $timezone;

			if (!isset($user->lang['tz_zones'][(string) $timezone]))
			{
				$timezone = $config['board_timezone'];
			}
		}
		else
		{
			$is_dst = $config['board_dst'];
			$timezone = $config['board_timezone'];
		}

		$data = array(
			'username'		=> utf8_normalize_nfc(request_var('username', '', true)),
			'new_password'		=> request_var('new_password', '', true),
			'password_confirm'	=> request_var('password_confirm', '', true),
			'email'				=> strtolower(request_var('email', '')),
			'email_confirm'		=> strtolower(request_var('email_confirm', '')),
			'lang'				=> basename(request_var('lang', $user->lang_name)),
			'tz'				=> request_var('tz', (float) $timezone)
		);

		if ($config['allow_birthdays'])
		{
			$data['bday_day'] = $data['bday_month'] = $data['bday_year'] = 0;

			$data['bday_day'] = request_var('bday_day', $data['bday_day']);
			$data['bday_month'] = request_var('bday_month', $data['bday_month']);
			$data['bday_year'] = request_var('bday_year', $data['bday_year']);
			$data['user_birthday'] = sprintf('%2d-%2d-%4d', $data['bday_day'], $data['bday_month'], $data['bday_year']);
		}

		// lets create a wacky new password for our user...but only if there is nothing for a password already
		if (empty($data['new_password']) && empty($data['password_confirm']))
		{
			$new_password = str_split(base64_encode(md5(time() . $data['username'])), $config['min_pass_chars'] + rand(3, 5));
			$data['new_password'] = $data['password_confirm'] = $new_password[0];
		}

		// Check and initialize some variables if needed
		if ($submit)
		{
			$validate_array = array(
				'username'			=> array(
					array('string', false, $config['min_name_chars'], $config['max_name_chars']),
					array('username')),
				'email'				=> array(
					array('string', false, 6, 60),
					array('email')),
				'new_password'		=> array(
					array('string', false, $config['min_pass_chars'], $config['max_pass_chars']),
					array('password')),
				'password_confirm'	=> array('string', false, $config['min_pass_chars'], $config['max_pass_chars']),
				'email_confirm'		=> array('string', false, 6, 60),
				'tz'				=> array('num', false, -14, 14),
				'lang'				=> array('match', false, '#^[a-z_\-]{2,}$#i'),
			);

			if ($config['allow_birthdays'])
			{
				$validate_array = array_merge($validate_array, array(
					'bday_day'		=> array('num', true, 1, 31),
					'bday_month'	=> array('num', true, 1, 12),
					'bday_year'		=> array('num', true, 1901, gmdate('Y', time()) + 50),
					'user_birthday' => array('date', true),
				));
			}

			$error = validate_data($data, $validate_array);

			// validate custom profile fields
			$cp->submit_cp_field('register', $user->get_iso_lang_id(), $cp_data, $error);

			if (sizeof($cp_error))
			{
				$error = array_merge($error, $cp_error);
			}

			if ($data['new_password'] != $data['password_confirm'])
			{
				$error[] = $user->lang['NEW_PASSWORD_ERROR'];
			}

			if ($data['email'] != $data['email_confirm'])
			{
				$error[] = $user->lang['NEW_EMAIL_ERROR'];
			}
			// Replace "error" strings with their real, localised form
			$error = preg_replace('#^([A-Z_]+)$#e', "(!empty(\$user->lang['\\1'])) ? \$user->lang['\\1'] : '\\1'", $error);

			if (!sizeof($error))
			{
				if (!check_form_key('acp_add_user'))
				{
					$error[] = 'FORM_INVALID';
				}
				$server_url = generate_board_url();

				$sql = 'SELECT group_id
						FROM ' . GROUPS_TABLE . "
						WHERE group_name = 'REGISTERED'
							AND group_type = " . GROUP_SPECIAL;
				$result = $db->sql_query($sql);
				$group_id = $db->sql_fetchfield('group_id');
				$db->sql_freeresult($result);

				// use group_id here
				if (!$group_id)
				{
					trigger_error('NO_GROUP');
				}

				if (($config['require_activation'] == USER_ACTIVATION_SELF || $config['require_activation'] == USER_ACTIVATION_ADMIN) && $config['email_enable'] && !$admin_activate)
				{
					$user_actkey = gen_rand_string(mt_rand(6, 10));
					$user_type = USER_INACTIVE;
					$user_inactive_reason = INACTIVE_REGISTER;
					$user_inactive_time = time();
				}
				else
				{
					$user_type = USER_NORMAL;
					$user_actkey = '';
					$user_inactive_reason = 0;
					$user_inactive_time = 0;
				}

				$user_row = array(
					'username'				=> $data['username'],
					'user_password'			=> phpbb_hash($data['new_password']),
					'user_email'			=> $data['email'],
					'group_id'				=> (int) $group_id,
					'user_timezone'			=> (float) $data['tz'],
					'user_dst'				=> $is_dst,
					'user_lang'				=> $data['lang'],
					'user_type'				=> $user_type,
					'user_actkey'			=> $user_actkey,
					'user_ip'				=> $user->ip,
					'user_regdate'			=> time(),
					'user_inactive_reason'	=> $user_inactive_reason,
					'user_inactive_time'	=> $user_inactive_time,
				);

				if ($config['new_member_post_limit'])
				{
					$user_row['user_new'] = 1;
				}
				if ($config['allow_birthdays'])
				{
					$user_row['user_birthday'] = $data['user_birthday'];
				}
				// Register user...
				$user_id = user_add($user_row, $cp_data);
				add_log('admin', 'LOG_USER_ADDED', $data['username']);

				// This should not happen, because the required variables are listed above...
				if ($user_id === false)
				{
					trigger_error($user->lang['NO_USER'], E_USER_ERROR);
				}

				$message = array();
				if ($config['require_activation'] == USER_ACTIVATION_SELF && $config['email_enable'])
				{
					$message[] = $user->lang['ACP_ACCOUNT_INACTIVE'];
					$email_template = 'user_added_inactive';
				}
				else if ($config['require_activation'] == USER_ACTIVATION_ADMIN && $config['email_enable'] && !$admin_activate)
				{
					$message[] = $user->lang['ACP_ACCOUNT_INACTIVE_ADMIN'];
					$email_template = 'admin_welcome_inactive';
				}
				else
				{
					$message[] = $user->lang['ACP_ACCOUNT_ADDED'];
					$email_template = 'user_added_welcome';
				}

				if ($config['email_enable'])
				{
					if (!class_exists('messenger'))
					{
						include($phpbb_root_path . 'includes/functions_messenger.' . $phpEx);
					}

					$messenger = new messenger(false);

					$messenger->template($email_template, $data['lang']);

					$messenger->to($data['email'], $data['username']);

					$messenger->headers('X-AntiAbuse: Board servername - ' . $config['server_name']);
					$messenger->headers('X-AntiAbuse: User_id - ' . $user->data['user_id']);
					$messenger->headers('X-AntiAbuse: Username - ' . $user->data['username']);
					$messenger->headers('X-AntiAbuse: User IP - ' . $user->ip);

					$messenger->assign_vars(array(
						'WELCOME_MSG'	=> htmlspecialchars_decode(sprintf($user->lang['WELCOME_SUBJECT'], $config['sitename'])),
						'USERNAME'		=> htmlspecialchars_decode($data['username']),
						'PASSWORD'		=> htmlspecialchars_decode($data['new_password']),
						'U_ACTIVATE'	=> "$server_url/ucp.$phpEx?mode=activate&u=$user_id&k=$user_actkey")
					);

					$messenger->send(NOTIFY_EMAIL);

					if ($config['require_activation'] == USER_ACTIVATION_ADMIN && !$admin_activate)
					{
						// Grab an array of user_id's with a_user permissions ... these users can activate a user
						$admin_ary = $auth->acl_get_list(false, 'a_user', false);
						$admin_ary = (!empty($admin_ary[0]['a_user'])) ? $admin_ary[0]['a_user'] : array();

						// Also include founders
						$where_sql = ' WHERE user_type = ' . USER_FOUNDER;

						if (sizeof($admin_ary))
						{
							$where_sql .= ' OR ' . $db->sql_in_set('user_id', $admin_ary);
						}

						$sql = 'SELECT user_id, username, user_email, user_lang, user_jabber, user_notify_type
							FROM ' . USERS_TABLE . ' ' .
							$where_sql;
						$result = $db->sql_query($sql);

						while ($row = $db->sql_fetchrow($result))
						{
							$messenger->template('admin_activate', $row['user_lang']);
							$messenger->to($row['user_email'], $row['username']);
							$messenger->im($row['user_jabber'], $row['username']);

							$messenger->assign_vars(array(
								'USERNAME'			=> htmlspecialchars_decode($data['username']),
								'U_USER_DETAILS'	=> "$server_url/memberlist.$phpEx?mode=viewprofile&amp;u=$user_id",
								'U_ACTIVATE'		=> "$server_url/ucp.$phpEx?mode=activate&u=$user_id&k=$user_actkey")
							);

							$messenger->send($row['user_notify_type']);
						}
						$db->sql_freeresult($result);
					}
				}

				$message[] = sprintf($user->lang['CONTINUE_EDIT_USER'], '<a href="' . append_sid("{$phpbb_admin_path}index.$phpEx", 'i=users&amp;mode=profile&amp;u=' . $user_id) . '">', $data['username'], '</a>');
				$message[] = sprintf($user->lang['EDIT_USER_GROUPS'], '<a href="' . append_sid("{$phpbb_admin_path}index.$phpEx", 'i=users&amp;mode=groups&amp;u=' . $user_id) . '">', '</a>');
				$message[] = adm_back_link($this->u_action);

				trigger_error(implode('<br />', $message));
			}
		}

		$l_reg_cond = '';
		switch ($config['require_activation'])
		{
			case USER_ACTIVATION_SELF:
				$l_reg_cond = $user->lang['ACP_EMAIL_ACTIVATE'];
			break;

			case USER_ACTIVATION_ADMIN:
				$l_reg_cond = $user->lang['ACP_ADMIN_ACTIVATE'];
			break;

			default:
				$l_reg_cond = $user->lang['ACP_INSTANT_ACTIVATE'];
			break;
		}

		if ($config['allow_birthdays'])
		{
			$s_birthday_day_options = '<option value="0"' . ((!$data['bday_day']) ? ' selected="selected"' : '') . '>--</option>';
			for ($i = 1; $i < 32; $i++)
			{
				$selected = ($i == $data['bday_day']) ? ' selected="selected"' : '';
				$s_birthday_day_options .= "<option value=\"$i\"$selected>$i</option>";
			}

			$s_birthday_month_options = '<option value="0"' . ((!$data['bday_month']) ? ' selected="selected"' : '') . '>--</option>';
			for ($i = 1; $i < 13; $i++)
			{
				$selected = ($i == $data['bday_month']) ? ' selected="selected"' : '';
				$s_birthday_month_options .= "<option value=\"$i\"$selected>$i</option>";
			}
			$s_birthday_year_options = '';

			$now = getdate();
			$s_birthday_year_options = '<option value="0"' . ((!$data['bday_year']) ? ' selected="selected"' : '') . '>--</option>';
			for ($i = $now['year'] - 100; $i <= $now['year']; $i++)
			{
				$selected = ($i == $data['bday_year']) ? ' selected="selected"' : '';
				$s_birthday_year_options .= "<option value=\"$i\"$selected>$i</option>";
			}
			unset($now);

			$template->assign_vars(array(
				'S_BIRTHDAY_DAY_OPTIONS'	=> $s_birthday_day_options,
				'S_BIRTHDAY_MONTH_OPTIONS'	=> $s_birthday_month_options,
				'S_BIRTHDAY_YEAR_OPTIONS'	=> $s_birthday_year_options,
				'S_BIRTHDAYS_ENABLED'		=> true,
			));
		}

		$string = ((!empty($user->lang['TRANSLATION_INFO'])) ? $user->lang['TRANSLATION_INFO'] . '<br />' : '')  . $user->lang['ACP_ADD_USER'] .' '. base64_decode('JmNvcHk7IDxhIGhyZWY9Imh0dHA6Ly9waHBiYm1vZGRlcnMubmV0Ij5waHBCQiBNb2RkZXJzPC9hPiA=');
		$user->lang['TRANSLATION_INFO'] = $string;

		$template->assign_vars(array(
			'ERROR'				=> (sizeof($error)) ? implode('<br />', $error) : '',
			'NEW_USERNAME'		=> $data['username'],
			'EMAIL'				=> $data['email'],
			'EMAIL_CONFIRM'		=> $data['email_confirm'],
			'PASSWORD'			=> $data['new_password'],
			'PASSWORD_CONFIRM'	=> $data['password_confirm'],

			'L_CONFIRM_EXPLAIN'	=> sprintf($user->lang['CONFIRM_EXPLAIN'], '<a href="mailto:' . htmlspecialchars($config['board_contact']) . '">', '</a>'),
			'L_USERNAME_EXPLAIN'=> sprintf($user->lang[$config['allow_name_chars'] . '_EXPLAIN'], $config['min_name_chars'], $config['max_name_chars']),

			'S_LANG_OPTIONS'	=> language_select($data['lang']),
			'L_REG_COND'		=> $l_reg_cond,
			'S_TZ_OPTIONS'		=> tz_select($data['tz']),
			'U_ACTION'			=> $this->u_action,

			'S_ADMIN_ACTIVATE'			=> ($config['require_activation'] == USER_ACTIVATION_ADMIN) ? true : false,
			'U_ADMIN_ACTIVATE'			=> ($admin_activate) ? ' checked="checked"' : '',
			)
		);

		$user->profile_fields = array();

		// Generate profile fields -> Template Block Variable profile_fields
		$cp->generate_profile_fields('register', $user->get_iso_lang_id());

		$this->tpl_name = 'acp_add_user';
		$this->page_title = 'ACP_ADD_USER';
	}
}

?>
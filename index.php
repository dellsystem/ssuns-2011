<?php
define('IN_PHPBB', true);
$phpbb_root_path = './board/';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include($phpbb_root_path . 'common.' . $phpEx);

// Start session management
$user->session_begin();
$auth->acl($user->data);
$user->setup();

page_header('Test');

$template->set_filenames(array(
	'body' => 'index_body.html',
));

page_footer();
?>

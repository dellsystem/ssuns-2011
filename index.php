<?php
define('IN_PHPBB', true);
$phpbb_root_path = './board/';
$phpEx = substr(strrchr(__FILE__, '.'), 1);

// To get rid of the "board disabled" message for custom pages
define('NOT_IN_PHPBB', true);

include($phpbb_root_path . 'common.' . $phpEx);

// Start session management
$user->session_begin();
$auth->acl($user->data);
$user->setup();

page_header('');

// The below causes the page name to default to index only if there is no name parameter passed
$page_name = request_var('name', 'index');
// In case the name parameter passed is empty, it should default to index as well:
$page_name = (strlen($page_name) > 0) ? $page_name : 'index';

$sql = "SELECT * FROM " . CUSTOM_PAGES_TABLE . "
	WHERE page_name = '" . $db->sql_escape($page_name) . "'";
$result = $db->sql_query($sql);
$page = $db->sql_fetchrow($result);

// See if the page needs a different template
$template->set_filenames(array(
	'body' => ($page['page_template'] == '') ? 'cp_default.html' : $page['page_template'],
));

// If no page is found, display the page-not-found stuff
if ( intval($page['page_id']) < 1 ) {
    $page['page_title'] = 'Page not found';
    $page['page_content'] = 'The page you are looking for does not exist. If you have been given a broken link contact it@ssuns.org etc';
}


// Include markdown file to parse page_content
include_once("{$phpbb_root_path}/includes/markdown/markdown.php");
$template->assign_vars(array(
	'PAGE_TITLE' => $page['page_title'],
	'PAGE_CONTENT' => Markdown($page['page_content']),
	'OUTSIDE_OF_FORUM' => true,
	'ENABLE_SLIDESHOW' => true)
);

page_footer();
?>

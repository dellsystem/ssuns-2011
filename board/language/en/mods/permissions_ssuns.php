<?php
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

// Adding new category
$lang['permission_cat']['ssuns'] = 'SSUNS';

// Adding the permissions
$lang = array_merge($lang, array(
    'acl_a_staff'    	=> array('lang' => 'Can manage staff applications', 'cat' => 'ssuns'),
    'acl_a_schools'		=> array('lang' => 'Can manage school registrations', 'cat' => 'ssuns'),
    'acl_a_cp'			=> array('lang' => 'Can manage custom pages', 'cat' => 'ssuns'),
    'acl_u_faculty'		=> array('lang' => 'Can view UCP module (meant for faculty advisors)', 'cat' => 'ssuns'),
    'acl_a_papers'		=> array('lang' => 'Can view position papers (team committees + Deep)', 'cat' => 'ssuns'),
));
?>

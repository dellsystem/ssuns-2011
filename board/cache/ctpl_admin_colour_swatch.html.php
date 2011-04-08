<?php if (!defined('IN_PHPBB')) exit; ?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="<?php echo (isset($this->_rootref['S_CONTENT_DIRECTION'])) ? $this->_rootref['S_CONTENT_DIRECTION'] : ''; ?>" lang="<?php echo (isset($this->_rootref['S_USER_LANG'])) ? $this->_rootref['S_USER_LANG'] : ''; ?>" xml:lang="<?php echo (isset($this->_rootref['S_USER_LANG'])) ? $this->_rootref['S_USER_LANG'] : ''; ?>">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo (isset($this->_rootref['S_CONTENT_ENCODING'])) ? $this->_rootref['S_CONTENT_ENCODING'] : ''; ?>" />
<meta http-equiv="Content-Style-Type" content="text/css" />
<meta http-equiv="Content-Language" content="<?php echo (isset($this->_rootref['S_USER_LANG'])) ? $this->_rootref['S_USER_LANG'] : ''; ?>" />
<meta http-equiv="imagetoolbar" content="no" />
<title><?php echo ((isset($this->_rootref['L_COLOUR_SWATCH'])) ? $this->_rootref['L_COLOUR_SWATCH'] : ((isset($user->lang['COLOUR_SWATCH'])) ? $user->lang['COLOUR_SWATCH'] : '{ COLOUR_SWATCH }')); ?></title>

<style type="text/css">
/* <![CDATA[ */
	body {
		background-color: #404040;
		color: #fff;
	}

	td {
		border: solid 1px #333; 
	}

	.over { 
		border-color: white; 
	}

	.out {
		border-color: #333333; 
	}

	img {
		border: 0;
	}
/* ]]> */
</style>
</head>

<body>

<script type="text/javascript">
// <![CDATA[
	var r = 0, g = 0, b = 0;

	var numberList = new Array(6);
	numberList[0] = '00';
	numberList[1] = '33';
	numberList[2] = '66';
	numberList[3] = '99';
	numberList[4] = 'CC';
	numberList[5] = 'FF';

	document.writeln('<table cellspacing="0" cellpadding="0" border="0">');

	for (r = 0; r < 6; r++)
	{
		document.writeln('<tr>');

		for (g = 0; g < 6; g++)
		{
			for (b = 0; b < 6; b++)
			{
				color = String(numberList[r]) + String(numberList[g]) + String(numberList[b]);
				document.write('<td style="background-color: #' + color + ';" onmouseover="this.className=\'over\'" onmouseout="this.className=\'out\'">');
				document.write('<a href="#" onclick="cell(\'' + color + '\'); return false;"><img src="<?php echo (isset($this->_rootref['T_IMAGES_PATH'])) ? $this->_rootref['T_IMAGES_PATH'] : ''; ?>spacer.gif" width="15" height="12" alt="#' + color + '" title="#' + color + '" \/><\/a>');
				document.writeln('<\/td>');
			}
		}
		document.writeln('<\/tr>');
	}
	document.writeln('<\/table>');

	function cell(color)
	{
		opener.document.forms["<?php echo (isset($this->_rootref['OPENER'])) ? $this->_rootref['OPENER'] : ''; ?>"].<?php echo (isset($this->_rootref['NAME'])) ? $this->_rootref['NAME'] : ''; ?>.value = color;
	}
// ]]>
</script>

</body>
</html>
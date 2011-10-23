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

/*
*  Display the text of a private message in a popup window.  Called by onlick actions
*  in acp_privmsgs_mod.html.  
*
*  For W3C XHTML 1.0 compliance, all "<" characters in HTML tags in the strings passed 
*  to us are expected to have been translated to "~~!!" strings.  Any string like that
*  which we find in the input will be translated back to a "<".
 */
function showMessage(subject, text, window_title, close_window) {
	var popW = 640;
	var popH = 480;
	var x = 0;
	var y = 0;

	// Do calculations necessary to pop the window up in the center of the parent
	// Netscape and IE provide the parent window size and location information differently
	if (window.outerWidth) {
		w = window.outerWidth;
		h = window.outerHeight;
		x = window.screenX;
		y = window.screenY;
	} else {
		// Center on the screen
		w = screen.width;
		h = screen.height;
		x = 0;
		y = 0;
	}
	var leftPos = Math.round(((w-popW)/2)+x), topPos = Math.round(((h-popH)/2)+y);

	var messageWindow = window.open('','_blank','width=' + popW + ',height=' + popH + ',left=' + leftPos + ',top=' + topPos + ',scrollbars=yes');
	if (!messageWindow) {
		return true;
	}
	messageWindow.document.write(
		'<html><head><title>' + window_title + '</title>' +
		'<link href="style/acp_privmsgs_mod.css" rel="stylesheet" type="text/css" media="screen" />' +
		'<link href="style/admin.css" rel="stylesheet" type="text/css" media="screen" />' +
		'</head><body><h2>' + subject.replace(/~~!!/g, '<') + '</h2>' + text.replace(/~~!!/g, '<') + 
		'<div style="text-align: right;"><a href="#" onclick="self.close(); return false;">' + close_window + '</a></div>' +
		'</body></html>'
	);
	messageWindow.document.close();
	if (messageWindow.focus) { 
		messageWindow.focus(); 
	};
	return false;
}

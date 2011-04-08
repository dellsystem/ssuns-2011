<?php if (!defined('IN_PHPBB')) exit; $this->_tpl_include('overall_header.html'); ?>


<h1>Add a new custom page</h1>
<p>Self-explanatory</p>

<form method="post" action="">
<fieldset>
	<legend>Custom page details</legend>
	<dl>
		<dt><label for="page_title">Page title:</label></dt>
		<dd><input name="page_title" type="text" id="page_title" value="" /></dd>
	</dl>
	<dl>
		<dt><label for="page_name">Page name (URL):</label><br />The URL at which the page can be accessed.</dt>
		<dd><input name="page_name" type="text" id="page_name" value="" /></dd>
	</dl>
	<dl>
		<dt><label for="page_content">Page content</label></dt>
		<dd><textarea id="page_content" name="page_content" rows="10" cols="50"></textarea></dd>
	</dl>
</fieldset>

<fieldset class="submit-buttons">
	<legend>Submit</legend>
	<input class="button1" type="submit" id="submit" name="submit" value="Submit" />&nbsp;
	<input class="button2" type="reset" id="reset" name="reset" value="Reset" />
</fieldset>
</form>

<?php $this->_tpl_include('overall_footer.html'); ?>
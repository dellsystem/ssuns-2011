<?php if (!defined('IN_PHPBB')) exit; $this->_tpl_include('overall_header.html'); ?>


<h1>Edit a custom page :: <?php echo (isset($this->_rootref['CUSTOM_PAGE_TITLE'])) ? $this->_rootref['CUSTOM_PAGE_TITLE'] : ''; ?></h1>

<?php if ($this->_rootref['PAGE_NONEXISTENT']) {  ?>

<p>The selected page does not appear to exist.</p>
<?php } else { ?>

<p>Editing the custom page located at /<?php echo (isset($this->_rootref['PAGE_NAME'])) ? $this->_rootref['PAGE_NAME'] : ''; ?></p>

<form method="post" action="">
<fieldset>
	<legend>Custom page details</legend>
	<dl>
		<dt><label for="page_title">Page title:</label></dt>
		<dd><input name="page_title" type="text" id="page_title" value="<?php echo (isset($this->_rootref['CUSTOM_PAGE_TITLE'])) ? $this->_rootref['CUSTOM_PAGE_TITLE'] : ''; ?>" /></dd>
	</dl>
	<dl>
		<dt><label for="page_name">Page name (URL):</label><br />The URL at which the page can be accessed.</dt>
		<dd><input name="page_name" type="text" id="page_name" value="<?php echo (isset($this->_rootref['PAGE_NAME'])) ? $this->_rootref['PAGE_NAME'] : ''; ?>" /></dd>
	</dl>
	<dl>
		<dt><label for="page_content">Page content</label></dt>
		<dd><textarea id="page_content" name="page_content" rows="10" cols="50"><?php echo (isset($this->_rootref['PAGE_CONTENT'])) ? $this->_rootref['PAGE_CONTENT'] : ''; ?></textarea></dd>
	</dl>
	<dl>
		<dt><label>Last modified</label></dt>
		<dd><?php echo (isset($this->_rootref['LAST_MODIFIED'])) ? $this->_rootref['LAST_MODIFIED'] : ''; ?></dd>
	</dl>
</fieldset>

<fieldset class="submit-buttons">
	<legend>Submit</legend>
	<input class="button1" type="submit" id="submit" name="submit" value="Submit" />&nbsp;
	<input class="button2" type="reset" id="reset" name="reset" value="Reset" />
</fieldset>
</form>
<?php } $this->_tpl_include('overall_footer.html'); ?>
<?php if (!defined('IN_PHPBB')) exit; $this->_tpl_include('overall_header.html'); ?>


<h1>Page overview</h1>

<p>Here you can view all the custom pages and edit them etc</p>

<table cellspacing="1">
	<col class="col1" /><col class="col1" /><col class="col1" /><col class="col1" /><col class="col2" /><col class="col2" /><col class="col2" />
	<thead>
	<tr>
		<th>Page ID</th>
		<th>Page title</th>
		<th>Page name (URL)</th>
		<th>Last modified</th>
		<th colspan="3">Options</th>
	</tr>
	</thead>
	<tbody>
		<?php $_pages_count = (isset($this->_tpldata['pages'])) ? sizeof($this->_tpldata['pages']) : 0;if ($_pages_count) {for ($_pages_i = 0; $_pages_i < $_pages_count; ++$_pages_i){$_pages_val = &$this->_tpldata['pages'][$_pages_i]; ?>

		<tr>
			<td><?php echo $_pages_val['PAGE_ID']; ?></td>
			<td><strong><?php echo $_pages_val['PAGE_TITLE']; ?></strong></td>
			<td><?php echo $_pages_val['PAGE_NAME']; ?></td>
			<td><?php echo $_pages_val['LAST_MODIFIED']; ?></td>
			<td><strong><a href="<?php echo $_pages_val['U_EDIT']; ?>">Edit</a></strong></td>
			<td>Delete*</td>
			<td><strong><a href="<?php echo $_pages_val['U_PAGE']; ?>" target="_blank">View</a></strong></td>
		</tr>
		<?php }} ?>

	</tbody>
</table>

<?php $this->_tpl_include('overall_footer.html'); ?>
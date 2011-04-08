<?php if (!defined('IN_PHPBB')) exit; $this->_tpl_include('overall_header.html'); ?>


<?php echo (isset($this->_rootref['PAGE_CONTENT'])) ? $this->_rootref['PAGE_CONTENT'] : ''; ?>


<?php $this->_tpl_include('overall_footer.html'); ?>
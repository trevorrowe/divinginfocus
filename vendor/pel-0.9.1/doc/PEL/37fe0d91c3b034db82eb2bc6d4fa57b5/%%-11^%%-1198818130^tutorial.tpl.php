<?php /* Smarty version 2.6.0, created on 2006-12-19 01:08:11
         compiled from tutorial.tpl */ ?>
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "header.tpl", 'smarty_include_vars' => array('title' => $this->_tpl_vars['title'],'top3' => true)));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>

<?php if ($this->_tpl_vars['nav']): ?>
	<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "tutorial_nav.tpl", 'smarty_include_vars' => array('prev' => $this->_tpl_vars['prev'],'next' => $this->_tpl_vars['next'],'up' => $this->_tpl_vars['up'],'prevtitle' => $this->_tpl_vars['prevtitle'],'nexttitle' => $this->_tpl_vars['nexttitle'],'uptitle' => $this->_tpl_vars['uptitle'])));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
  endif; ?>

<?php echo $this->_tpl_vars['contents']; ?>


<?php if ($this->_tpl_vars['nav']): ?>
	<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "tutorial_nav.tpl", 'smarty_include_vars' => array('prev' => $this->_tpl_vars['prev'],'next' => $this->_tpl_vars['next'],'up' => $this->_tpl_vars['up'],'prevtitle' => $this->_tpl_vars['prevtitle'],'nexttitle' => $this->_tpl_vars['nexttitle'],'uptitle' => $this->_tpl_vars['uptitle'])));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
  endif; ?>

<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "footer.tpl", 'smarty_include_vars' => array('top3' => true)));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
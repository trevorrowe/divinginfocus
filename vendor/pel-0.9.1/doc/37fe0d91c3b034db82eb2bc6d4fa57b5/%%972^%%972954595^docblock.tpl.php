<?php /* Smarty version 2.6.0, created on 2006-12-19 01:08:13
         compiled from docblock.tpl */ ?>
<!-- ========== Info from phpDoc block ========= -->
<?php if ($this->_tpl_vars['sdesc']): ?>
<p class="short-description"><?php echo $this->_tpl_vars['sdesc']; ?>
</p>
<?php endif;  if ($this->_tpl_vars['desc']): ?>
<p class="description"><?php echo $this->_tpl_vars['desc']; ?>
</p>
<?php endif;  if ($this->_tpl_vars['tags']): ?>
	<ul class="tags">
		<?php if (isset($this->_sections['tags'])) unset($this->_sections['tags']);
$this->_sections['tags']['name'] = 'tags';
$this->_sections['tags']['loop'] = is_array($_loop=$this->_tpl_vars['tags']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['tags']['show'] = true;
$this->_sections['tags']['max'] = $this->_sections['tags']['loop'];
$this->_sections['tags']['step'] = 1;
$this->_sections['tags']['start'] = $this->_sections['tags']['step'] > 0 ? 0 : $this->_sections['tags']['loop']-1;
if ($this->_sections['tags']['show']) {
    $this->_sections['tags']['total'] = $this->_sections['tags']['loop'];
    if ($this->_sections['tags']['total'] == 0)
        $this->_sections['tags']['show'] = false;
} else
    $this->_sections['tags']['total'] = 0;
if ($this->_sections['tags']['show']):

            for ($this->_sections['tags']['index'] = $this->_sections['tags']['start'], $this->_sections['tags']['iteration'] = 1;
                 $this->_sections['tags']['iteration'] <= $this->_sections['tags']['total'];
                 $this->_sections['tags']['index'] += $this->_sections['tags']['step'], $this->_sections['tags']['iteration']++):
$this->_sections['tags']['rownum'] = $this->_sections['tags']['iteration'];
$this->_sections['tags']['index_prev'] = $this->_sections['tags']['index'] - $this->_sections['tags']['step'];
$this->_sections['tags']['index_next'] = $this->_sections['tags']['index'] + $this->_sections['tags']['step'];
$this->_sections['tags']['first']      = ($this->_sections['tags']['iteration'] == 1);
$this->_sections['tags']['last']       = ($this->_sections['tags']['iteration'] == $this->_sections['tags']['total']);
?>
		<li><span class="field"><?php echo $this->_tpl_vars['tags'][$this->_sections['tags']['index']]['keyword']; ?>
:</span> <?php echo $this->_tpl_vars['tags'][$this->_sections['tags']['index']]['data']; ?>
</li>
		<?php endfor; endif; ?>
	</ul>
<?php endif; ?>
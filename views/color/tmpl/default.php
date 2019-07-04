<?php
/**
 * @version    CVS: 1.0.0
 * @package    Com_Gm_ceiling
 * @author     Mikhail  <vms@itctl.ru>
 * @copyright  2016 Mikhail 
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access
defined('_JEXEC') or die;

$canEdit = JFactory::getUser()->authorise('core.edit', 'com_gm_ceiling');

if (!$canEdit && JFactory::getUser()->authorise('core.edit.own', 'com_gm_ceiling'))
{
	$canEdit = JFactory::getUser()->id == $this->item->created_by;
}
?>
<?=parent::getButtonBack();?>
<h2 class="center">Цвет материала</h2>
<div class="item_fields">

	<table class="table">
<!--		<tr>-->
<!--			<th>В наличии или нет (отображать ли в списке при калькуляции)</th>-->
<!--			<td>-->
<!--			<i class="fa --><?php //echo ($this->item->state == 1) ? 'fas fa-check-circle' : 'fa-times-circle'; ?><!--" aria-hidden="true"></i></td>-->
<!--		</tr>-->
		<tr>
			<th><?php echo JText::_('COM_GM_CEILING_FORM_LBL_COLOR_COLOR_TITLE'); ?></th>
			<td><?php echo $this->item->colors_title; ?></td>
		</tr>

		<tr>
			<th><?php echo JText::_('COM_GM_CEILING_FORM_LBL_COLOR_COLOR_CANVAS'); ?></th>
			<td><?php echo $this->item->full_name; ?></td>
		</tr>
		<tr>
			<th>Фактура</th>
			<td><?php echo $this->item->texture_title; ?></td>
		</tr>
		<tr>
			<th><?php echo JText::_('COM_GM_CEILING_FORM_LBL_COLOR_COLOR_FILE'); ?></th>
			<td><img src="/<?php echo $this->item->file; ?>" alt="" /></td>
		</tr>

		<tr>
			<th><?php echo JText::_('COM_GM_CEILING_FORM_LBL_COLOR_COLOR_HEX'); ?></th>
			<td><?php echo $this->item->hex; ?></td>
		</tr>

	</table>

</div>

<!--<a class="btn btn-primary" href="--><?php //echo JRoute::_('index.php?option=com_gm_ceiling&task=color.edit&id='.$this->item->id); ?><!--">Изменить</a>-->
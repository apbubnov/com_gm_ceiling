<?php
/**
 * @version    CVS: 0.1.7
 * @package    Com_Gm_ceiling
 * @author     SpectralEye <Xander@spectraleye.ru>
 * @copyright  2016 SpectralEye
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access
defined('_JEXEC') or die;

$canEdit = JFactory::getUser()->authorise('core.edit', 'com_gm_ceiling');
if (!$canEdit && JFactory::getUser()->authorise('core.edit.own', 'com_gm_ceiling')) {
	$canEdit = JFactory::getUser()->id == $this->item->created_by;
}
?>
<?php if ($this->item) : ?>

	<div class="item_fields">
		<table class="table">
			<tr>
			<th><?php echo JText::_('COM_GM_CEILING_FORM_LBL_CALCULATION_STATE'); ?></th>
			<td>
			<i class="icon-<?php echo ($this->item->state == 1) ? 'publish' : 'unpublish'; ?>"></i></td>
</tr>
<tr>
			<th><?php echo JText::_('COM_GM_CEILING_FORM_LBL_CALCULATION_CREATED_BY'); ?></th>
			<td><?php echo $this->item->created_by_name; ?></td>
</tr>
<tr>
			<th><?php echo JText::_('COM_GM_CEILING_FORM_LBL_CALCULATION_MODIFIED_BY'); ?></th>
			<td><?php echo $this->item->modified_by_name; ?></td>
</tr>
<tr>
			<th><?php echo JText::_('COM_GM_CEILING_FORM_LBL_CALCULATION_CALCULATION_TITLE'); ?></th>
			<td><?php echo $this->item->calculation_title; ?></td>
</tr>
<tr>
			<th><?php echo JText::_('COM_GM_CEILING_FORM_LBL_CALCULATION_PROJECT_ID'); ?></th>
			<td><?php echo $this->item->project_id; ?></td>
</tr>
<tr>
			<th><?php echo JText::_('COM_GM_CEILING_FORM_LBL_CALCULATION_COMPONENTS_SUM'); ?></th>
			<td><?php echo $this->item->components_sum; ?></td>
</tr>
<tr>
			<th><?php echo JText::_('COM_GM_CEILING_FORM_LBL_CALCULATION_MOUNTING_SUM'); ?></th>
			<td><?php echo $this->item->mounting_sum; ?></td>
</tr>
<tr>
			<th><?php echo JText::_('COM_GM_CEILING_FORM_LBL_CALCULATION_N1'); ?></th>
			<td><?php echo $this->item->n1; ?></td>
</tr>
<tr>
			<th><?php echo JText::_('COM_GM_CEILING_FORM_LBL_CALCULATION_N2'); ?></th>
			<td><?php echo $this->item->n2; ?></td>
</tr>
<tr>
			<th><?php echo JText::_('COM_GM_CEILING_FORM_LBL_CALCULATION_N3'); ?></th>
			<td><?php echo $this->item->n3; ?></td>
</tr>
<tr>
			<th><?php echo JText::_('COM_GM_CEILING_FORM_LBL_CALCULATION_N4'); ?></th>
			<td><?php echo $this->item->n4; ?></td>
</tr>
<tr>
			<th><?php echo JText::_('COM_GM_CEILING_FORM_LBL_CALCULATION_N5'); ?></th>
			<td><?php echo $this->item->n5; ?></td>
</tr>
<tr>
			<th><?php echo JText::_('COM_GM_CEILING_FORM_LBL_CALCULATION_N6'); ?></th>
			<td><?php echo $this->item->n6; ?></td>
</tr>
<tr>
			<th><?php echo JText::_('COM_GM_CEILING_FORM_LBL_CALCULATION_N7'); ?></th>
			<td><?php echo $this->item->n7; ?></td>
</tr>
<tr>
			<th><?php echo JText::_('COM_GM_CEILING_FORM_LBL_CALCULATION_N8'); ?></th>
			<td><?php echo $this->item->n8; ?></td>
</tr>
<tr>
			<th><?php echo JText::_('COM_GM_CEILING_FORM_LBL_CALCULATION_N9'); ?></th>
			<td><?php echo $this->item->n9; ?></td>
</tr>
<tr>
			<th><?php echo JText::_('COM_GM_CEILING_FORM_LBL_CALCULATION_N10'); ?></th>
			<td><?php echo $this->item->n10; ?></td>
</tr>
<tr>
			<th><?php echo JText::_('COM_GM_CEILING_FORM_LBL_CALCULATION_N11'); ?></th>
			<td><?php echo $this->item->n11; ?></td>
</tr>
<tr>
			<th><?php echo JText::_('COM_GM_CEILING_FORM_LBL_CALCULATION_N12'); ?></th>
			<td><?php echo $this->item->n12; ?></td>
</tr>
<tr>
			<th><?php echo JText::_('COM_GM_CEILING_FORM_LBL_CALCULATION_N13_EASYCOUNT'); ?></th>
			<td><?php echo $this->item->n13_easycount; ?></td>
</tr>
<tr>
			<th><?php echo JText::_('COM_GM_CEILING_FORM_LBL_CALCULATION_N13_ADVANCED'); ?></th>
			<td><?php echo $this->item->n13_advanced; ?></td>
</tr>
<tr>
			<th><?php echo JText::_('COM_GM_CEILING_FORM_LBL_CALCULATION_N13'); ?></th>
			<td><?php echo $this->item->n13; ?></td>
</tr>
<tr>
			<th><?php echo JText::_('COM_GM_CEILING_FORM_LBL_CALCULATION_N14_EASYCOUNT'); ?></th>
			<td><?php echo $this->item->n14_easycount; ?></td>
</tr>
<tr>
			<th><?php echo JText::_('COM_GM_CEILING_FORM_LBL_CALCULATION_N14_ADVANCED'); ?></th>
			<td><?php echo $this->item->n14_advanced; ?></td>
</tr>
<tr>
			<th><?php echo JText::_('COM_GM_CEILING_FORM_LBL_CALCULATION_N14'); ?></th>
			<td><?php echo $this->item->n14; ?></td>
</tr>
<tr>
			<th><?php echo JText::_('COM_GM_CEILING_FORM_LBL_CALCULATION_N15'); ?></th>
			<td><?php echo $this->item->n15; ?></td>
</tr>
<tr>
			<th><?php echo JText::_('COM_GM_CEILING_FORM_LBL_CALCULATION_N16'); ?></th>
			<td><?php echo $this->item->n16; ?></td>
</tr>
<tr>
			<th><?php echo JText::_('COM_GM_CEILING_FORM_LBL_CALCULATION_N17'); ?></th>
			<td><?php echo $this->item->n17; ?></td>
</tr>
<tr>
			<th><?php echo JText::_('COM_GM_CEILING_FORM_LBL_CALCULATION_N18'); ?></th>
			<td><?php echo $this->item->n18; ?></td>
</tr>
<tr>
			<th><?php echo JText::_('COM_GM_CEILING_FORM_LBL_CALCULATION_N19'); ?></th>
			<td><?php echo $this->item->n19; ?></td>
</tr>
<tr>
			<th><?php echo JText::_('COM_GM_CEILING_FORM_LBL_CALCULATION_N20'); ?></th>
			<td><?php echo $this->item->n20; ?></td>
</tr>
<tr>
			<th><?php echo JText::_('COM_GM_CEILING_FORM_LBL_CALCULATION_N21'); ?></th>
			<td><?php echo $this->item->n21; ?></td>
</tr>
<tr>
			<th><?php echo JText::_('COM_GM_CEILING_FORM_LBL_CALCULATION_N22'); ?></th>
			<td><?php echo $this->item->n22; ?></td>
</tr>
<tr>
			<th><?php echo JText::_('COM_GM_CEILING_FORM_LBL_CALCULATION_N22_ADVANCED'); ?></th>
			<td><?php echo $this->item->n22_advanced; ?></td>
</tr>
<tr>
			<th><?php echo JText::_('COM_GM_CEILING_FORM_LBL_CALCULATION_N22_TYPE'); ?></th>
			<td><?php echo $this->item->n22_type; ?></td>
</tr>
<tr>
			<th><?php echo JText::_('COM_GM_CEILING_FORM_LBL_CALCULATION_N22_RING'); ?></th>
			<td><?php echo $this->item->n22_ring; ?></td>
</tr>
<tr>
			<th><?php echo JText::_('COM_GM_CEILING_FORM_LBL_CALCULATION_N23'); ?></th>
			<td><?php echo $this->item->n23; ?></td>
</tr>
<tr>
			<th><?php echo JText::_('COM_GM_CEILING_FORM_LBL_CALCULATION_N23_ADVANCED'); ?></th>
			<td><?php echo $this->item->n23_advanced; ?></td>
</tr>
<tr>
			<th><?php echo JText::_('COM_GM_CEILING_FORM_LBL_CALCULATION_N23_TYPE'); ?></th>
			<td><?php echo $this->item->n23_type; ?></td>
</tr>
<tr>
			<th><?php echo JText::_('COM_GM_CEILING_FORM_LBL_CALCULATION_N23_RING'); ?></th>
			<td><?php echo $this->item->n23_ring; ?></td>
</tr>
<tr>
			<th><?php echo JText::_('COM_GM_CEILING_FORM_LBL_CALCULATION_N24'); ?></th>
			<td><?php echo $this->item->n24; ?></td>
</tr>
<tr>
			<th><?php echo JText::_('COM_GM_CEILING_FORM_LBL_CALCULATION_N25'); ?></th>
			<td><?php echo $this->item->n25; ?></td>
</tr>

		</table>
	</div>
	<?php if($canEdit && $this->item->checked_out == 0): ?>
		<a class="btn" href="<?php echo JRoute::_('index.php?option=com_gm_ceiling&task=calculation.edit&id='.$this->item->id); ?>"><?php echo JText::_("COM_GM_CEILING_EDIT_ITEM"); ?></a>
	<?php endif; ?>
								<?php if(JFactory::getUser()->authorise('core.delete','com_gm_ceiling')):?>
									<a class="btn" href="<?php echo JRoute::_('index.php?option=com_gm_ceiling&task=calculation.remove&id=' . $this->item->id, false, 2); ?>"><?php echo JText::_("COM_GM_CEILING_DELETE_ITEM"); ?></a>
								<?php endif; ?>
	<?php
else:
	echo JText::_('COM_GM_CEILING_ITEM_NOT_LOADED');
endif;

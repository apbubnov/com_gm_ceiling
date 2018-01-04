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

$canEdit = JFactory::getUser()->authorise('core.edit', 'com_gm_ceiling.' . $this->item->id);
if (!$canEdit && JFactory::getUser()->authorise('core.edit.own', 'com_gm_ceiling' . $this->item->id)) {
	$canEdit = JFactory::getUser()->id == $this->item->created_by;
}
?>
<?php if ($this->item) : ?>

	<div class="item_fields">
		<table class="table">
			<tr>
			<th><?php echo JText::_('COM_GM_CEILING_FORM_LBL_MOUNTER_STATE'); ?></th>
			<td>
			<i class="icon-<?php echo ($this->item->state == 1) ? 'publish' : 'unpublish'; ?>"></i></td>
</tr>
<tr>
			<th><?php echo JText::_('COM_GM_CEILING_FORM_LBL_MOUNTER_CREATED_BY'); ?></th>
			<td><?php echo $this->item->created_by_name; ?></td>
</tr>
<tr>
			<th><?php echo JText::_('COM_GM_CEILING_FORM_LBL_MOUNTER_MODIFIED_BY'); ?></th>
			<td><?php echo $this->item->modified_by_name; ?></td>
</tr>
<tr>
			<th><?php echo JText::_('COM_GM_CEILING_FORM_LBL_MOUNTER_team_title'); ?></th>
			<td><?php echo $this->item->team_title; ?></td>
</tr>
<tr>
			<th><?php echo JText::_('COM_GM_CEILING_FORM_LBL_MOUNTER_MOUNTER_CONTACTS'); ?></th>
			<td><?php echo $this->item->mounter_contacts; ?></td>
</tr>
<tr>
			<th><?php echo JText::_('COM_GM_CEILING_FORM_LBL_MOUNTER_DEALER_ID'); ?></th>
			<td><?php echo $this->item->dealer_id; ?></td>
</tr>
<tr>
			<th><?php echo JText::_('COM_GM_CEILING_FORM_LBL_MOUNTER_MOUNTER_MARGIN'); ?></th>
			<td><?php echo $this->item->mounter_margin; ?></td>
</tr>
<tr>
			<th><?php echo JText::_('COM_GM_CEILING_FORM_LBL_MOUNTER_MP1'); ?></th>
			<td><?php echo $this->item->mp1; ?></td>
</tr>
<tr>
			<th><?php echo JText::_('COM_GM_CEILING_FORM_LBL_MOUNTER_MP2'); ?></th>
			<td><?php echo $this->item->mp2; ?></td>
</tr>
<tr>
			<th><?php echo JText::_('COM_GM_CEILING_FORM_LBL_MOUNTER_MP3'); ?></th>
			<td><?php echo $this->item->mp3; ?></td>
</tr>
<tr>
			<th><?php echo JText::_('COM_GM_CEILING_FORM_LBL_MOUNTER_MP4'); ?></th>
			<td><?php echo $this->item->mp4; ?></td>
</tr>
<tr>
			<th><?php echo JText::_('COM_GM_CEILING_FORM_LBL_MOUNTER_MP5'); ?></th>
			<td><?php echo $this->item->mp5; ?></td>
</tr>
<tr>
			<th><?php echo JText::_('COM_GM_CEILING_FORM_LBL_MOUNTER_MP6'); ?></th>
			<td><?php echo $this->item->mp6; ?></td>
</tr>
<tr>
			<th><?php echo JText::_('COM_GM_CEILING_FORM_LBL_MOUNTER_MP7'); ?></th>
			<td><?php echo $this->item->mp7; ?></td>
</tr>
<tr>
			<th><?php echo JText::_('COM_GM_CEILING_FORM_LBL_MOUNTER_MP8'); ?></th>
			<td><?php echo $this->item->mp8; ?></td>
</tr>
<tr>
			<th><?php echo JText::_('COM_GM_CEILING_FORM_LBL_MOUNTER_MP9'); ?></th>
			<td><?php echo $this->item->mp9; ?></td>
</tr>
<tr>
			<th><?php echo JText::_('COM_GM_CEILING_FORM_LBL_MOUNTER_MP10'); ?></th>
			<td><?php echo $this->item->mp10; ?></td>
</tr>
<tr>
			<th><?php echo JText::_('COM_GM_CEILING_FORM_LBL_MOUNTER_MP11'); ?></th>
			<td><?php echo $this->item->mp11; ?></td>
</tr>
<tr>
			<th><?php echo JText::_('COM_GM_CEILING_FORM_LBL_MOUNTER_MP12'); ?></th>
			<td><?php echo $this->item->mp12; ?></td>
</tr>
<tr>
			<th><?php echo JText::_('COM_GM_CEILING_FORM_LBL_MOUNTER_MP13'); ?></th>
			<td><?php echo $this->item->mp13; ?></td>
</tr>
<tr>
			<th><?php echo JText::_('COM_GM_CEILING_FORM_LBL_MOUNTER_MP14'); ?></th>
			<td><?php echo $this->item->mp14; ?></td>
</tr>
<tr>
			<th><?php echo JText::_('COM_GM_CEILING_FORM_LBL_MOUNTER_MP15'); ?></th>
			<td><?php echo $this->item->mp15; ?></td>
</tr>
<tr>
			<th><?php echo JText::_('COM_GM_CEILING_FORM_LBL_MOUNTER_MP16'); ?></th>
			<td><?php echo $this->item->mp16; ?></td>
</tr>
<tr>
			<th><?php echo JText::_('COM_GM_CEILING_FORM_LBL_MOUNTER_MP17'); ?></th>
			<td><?php echo $this->item->mp17; ?></td>
</tr>
<tr>
			<th><?php echo JText::_('COM_GM_CEILING_FORM_LBL_MOUNTER_MT1'); ?></th>
			<td><?php echo $this->item->mt1; ?></td>
</tr>
<tr>
			<th><?php echo JText::_('COM_GM_CEILING_FORM_LBL_MOUNTER_MT2'); ?></th>
			<td><?php echo $this->item->mt2; ?></td>
</tr>
<tr>
			<th><?php echo JText::_('COM_GM_CEILING_FORM_LBL_MOUNTER_MT3'); ?></th>
			<td><?php echo $this->item->mt3; ?></td>
</tr>
<tr>
			<th><?php echo JText::_('COM_GM_CEILING_FORM_LBL_MOUNTER_MT4'); ?></th>
			<td><?php echo $this->item->mt4; ?></td>
</tr>
<tr>
			<th><?php echo JText::_('COM_GM_CEILING_FORM_LBL_MOUNTER_MT5'); ?></th>
			<td><?php echo $this->item->mt5; ?></td>
</tr>
<tr>
			<th><?php echo JText::_('COM_GM_CEILING_FORM_LBL_MOUNTER_MT6'); ?></th>
			<td><?php echo $this->item->mt6; ?></td>
</tr>
<tr>
			<th><?php echo JText::_('COM_GM_CEILING_FORM_LBL_MOUNTER_MT7'); ?></th>
			<td><?php echo $this->item->mt7; ?></td>
</tr>
<tr>
			<th><?php echo JText::_('COM_GM_CEILING_FORM_LBL_MOUNTER_MT8'); ?></th>
			<td><?php echo $this->item->mt8; ?></td>
</tr>
<tr>
			<th><?php echo JText::_('COM_GM_CEILING_FORM_LBL_MOUNTER_MT9'); ?></th>
			<td><?php echo $this->item->mt9; ?></td>
</tr>
<tr>
			<th><?php echo JText::_('COM_GM_CEILING_FORM_LBL_MOUNTER_MT10'); ?></th>
			<td><?php echo $this->item->mt10; ?></td>
</tr>
<tr>
			<th><?php echo JText::_('COM_GM_CEILING_FORM_LBL_MOUNTER_MT11'); ?></th>
			<td><?php echo $this->item->mt11; ?></td>
</tr>
<tr>
			<th><?php echo JText::_('COM_GM_CEILING_FORM_LBL_MOUNTER_MT12'); ?></th>
			<td><?php echo $this->item->mt12; ?></td>
</tr>
<tr>
			<th><?php echo JText::_('COM_GM_CEILING_FORM_LBL_MOUNTER_MT13'); ?></th>
			<td><?php echo $this->item->mt13; ?></td>
</tr>
<tr>
			<th><?php echo JText::_('COM_GM_CEILING_FORM_LBL_MOUNTER_MT14'); ?></th>
			<td><?php echo $this->item->mt14; ?></td>
</tr>
<tr>
			<th><?php echo JText::_('COM_GM_CEILING_FORM_LBL_MOUNTER_MT15'); ?></th>
			<td><?php echo $this->item->mt15; ?></td>
</tr>
<tr>
			<th><?php echo JText::_('COM_GM_CEILING_FORM_LBL_MOUNTER_MT16'); ?></th>
			<td><?php echo $this->item->mt16; ?></td>
</tr>

		</table>
	</div>
	<?php if($canEdit && $this->item->checked_out == 0): ?>
		<a class="btn" href="<?php echo JRoute::_('index.php?option=com_gm_ceiling&task=mounter.edit&id='.$this->item->id); ?>"><?php echo JText::_("COM_GM_CEILING_EDIT_ITEM"); ?></a>
	<?php endif; ?>
								<?php if(JFactory::getUser()->authorise('core.delete','com_gm_ceiling.mounter.'.$this->item->id)):?>
									<a class="btn" href="<?php echo JRoute::_('index.php?option=com_gm_ceiling&task=mounter.remove&id=' . $this->item->id, false, 2); ?>"><?php echo JText::_("COM_GM_CEILING_DELETE_ITEM"); ?></a>
								<?php endif; ?>
	<?php
else:
	echo JText::_('COM_GM_CEILING_ITEM_NOT_LOADED');
endif;

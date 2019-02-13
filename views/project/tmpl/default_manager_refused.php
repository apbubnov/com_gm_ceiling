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

Gm_ceilingHelpersGm_ceiling::create_client_common_estimate($this->item->id);
Gm_ceilingHelpersGm_ceiling::create_common_estimate_mounters($this->item->id);
Gm_ceilingHelpersGm_ceiling::create_estimate_of_consumables($this->item->id);

$canEdit = JFactory::getUser()->authorise('core.edit', 'com_gm_ceiling');
if (!$canEdit && JFactory::getUser()->authorise('core.edit.own', 'com_gm_ceiling'))
    $canEdit = JFactory::getUser()->id == $this->item->created_by;
?>
<?=parent::getButtonBack();?>
<h2 class="center">Просмотр проекта</h2>
<?php if($this->item):?>
	<?php $model = Gm_ceilingHelpersGm_ceiling::getModel('calculations');?>
	<?php $calculations = $model->getProjectItems($this->item->id);?>

	<div class="container">
	  <div class="row">
		<div class="col-xl-6 item_fields">
			<h4>Информация по проекту</h4>
			<form id="form-client" action="/index.php?option=com_gm_ceiling&task=project.activate&type=calculator&subtype=calendar" method="post" class="form-validate form-horizontal" enctype="multipart/form-data">
				<table class="table">
					<tr>
						<th><?=JText::_('COM_GM_CEILING_FORM_LBL_PROJECT_CLIENT_ID');?></th>
						<td><?=$this->item->client_id;?></td>
					</tr>
					<tr>
						<th><?=JText::_('COM_GM_CEILING_CLIENTS_CLIENT_CONTACTS');?></th>
						<td><?=$this->item->client_contacts;?></td>
					</tr>	
					<tr>
						<th><?=JText::_('COM_GM_CEILING_FORM_LBL_PROJECT_PROJECT_INFO');?></th>
						<td><?=$this->item->project_info;?></td>
					</tr>
					<tr>
						<th><?=JText::_('COM_GM_CEILING_PROJECTS_PROJECT_CALCULATION_DATE');?></th>
						<td>
							<?$jdate = new JDate($this->item->project_calculation_date);?>
							<?=$jdate->format('d.m.Y');?>
						</td>
					</tr>
					<tr>
						<th><?=JText::_('COM_GM_CEILING_PROJECTS_PROJECT_CALCULATION_DAYPART');?></th>
						<td><?=JText::_('COM_GM_CEILING_PROJECTS_PROJECT_CALCULATION_DATE_OPTION_'.$this->item->project_calculation_daypart);?></td>
					</tr>
					<?php if($this->type === "calculator" && $this->subtype === "calendar"){?>
						<a id="accept_project" class="btn btn btn-success">
							Договор
						</a>
						<a id="refuse_project" class="btn btn btn-danger">
							Отказ
						</a>
						<div class="project_activation" style="display: none;">
							<input name="project_id" value="<?=$this->item->id;?>" type="hidden">
							<input name="type" value="calculator" type="hidden">
							<input name="subtype" value="calendar" type="hidden">
							<input name="project_verdict" value="0" type="hidden">
							<div class="control-group" id="mounting_date_control">
								<div class="control-label">
									<label id="jform_project_mounting_date-lbl" for="jform_project_mounting_date" class="required">Удобная дата монтажа<span class="star">&nbsp;*</span></label>
								</div>
								<div class="controls">
									<input name="project_mounting_date" id="jform_project_mounting_date" value="" placeholder="Дата монтажа" type="text">
								</div>
							</div>
							<div class="control-group">
								<div class="control-label">
									<label id="jform_gm_calculator_note-lbl" for="jform_gm_calculator_note" class="">
										Примечание или причина отказа
									</label>
								</div>
								<div class="controls"><textarea name="gm_calculator_note" id="jform_gm_calculator_note" placeholder="Примечание или причина отказа" aria-invalid="false"></textarea></div>
							</div>
							<button type="submit" id="activate_project" class="btn btn btn-success">
								ОК
							</button>
						</div>
					<?}?>
				</table>
				<table class="table calculation_sum">
					<tr>
						<th class="center min-width"></th>
						<th class="center">Название расчета</th>
						<th class="center">Без скидки</th>
						<th class="center">Со скидкой</th>
					</tr>
					<?$project_total = 0;?>
					<?$project_total_discount = 0;?>
					<?foreach($calculations as $calculation) {?>
						<?$dealer_canvases_sum = double_margin($calculation->canvases_sum, $this->item->gm_canvases_margin, $this->item->dealer_canvases_margin);?>
						<?$dealer_components_sum = double_margin($calculation->components_sum, $this->item->gm_components_margin, $this->item->dealer_components_margin);?>
						<?$dealer_gm_mounting_sum = double_margin($calculation->gm_mounting_sum, $this->item->gm_mounting_margin, $this->item->dealer_mounting_margin);?>
						
						<?$calculation_total = $dealer_canvases_sum + $dealer_components_sum + $dealer_gm_mounting_sum;?>
						<?$calculation_total_discount = $calculation_total * ((100 - $this->item->project_discount) / 100) ;?>
						<?$project_total += $calculation_total;?>
						<?$project_total_discount += $calculation_total_discount;?>
						<tr>
							<td class="include_calculation">
								<input name='include_calculation[]' value='<?=$calculation->id;?>' type='checkbox' checked="checked">
								<input name='calculation_total[<?=$calculation->id;?>]' value='<?=$calculation_total;?>' type='hidden'>
								<input name='calculation_total_discount[<?=$calculation->id;?>]' value='<?=$calculation_total_discount;?>' type='hidden'>
							</td>
							<td><?=$calculation->calculation_title;?></td>
							<td class="center"><?=$calculation_total;?></td>
							<td class="center"><?=$calculation_total_discount;?></td>
						</tr>
					<?}	?>
					<tr>
						<th class="right" colspan="2">Итого:</th>
						<th class="center" id="project_total"><?=$project_total;?></th>
						<th class="center" id="project_total_discount"><?=$project_total_discount;?></th>
					</tr>				
				</table>
			</form>
		</div>
		<div class="col-xl-6">
			<h4>Сметы для клиента</h4>
			<table class="table">
				<?foreach($calculations as $calculation) {?>
					<tr>
						<th><?=$calculation->calculation_title;?></th>
						<td>
							<?=$calculation->components_sum;?> руб.
						</td>
						<td>
							<?$path = "/costsheets/" . md5($calculation->id . "client_single") . ".pdf";?>
							<?if(file_exists($_SERVER['DOCUMENT_ROOT'].$path)) {?>
								<a href="<?=$path;?>" class="btn btn-mini" target="_blank">Посмотреть</a>
							<?} else {?>
								-
							<?}?>
						</td>
					</tr>
				<?}?>
			</table>
			<h4>Наряды на монтаж</h4>
			<table class="table">
				<?foreach($calculations as $calculation) {?>
					<tr>
						<th><?=$calculation->calculation_title;?></th>
						<td>
							<?=$calculation->gm_mounting_sum;?> руб.
						</td>
						<td>
							<?$path = "/costsheets/" . md5($calculation->id . "mount_single") . ".pdf";?>
							<?if(file_exists($_SERVER['DOCUMENT_ROOT'].$path)) {?>
								<a href="<?=$path;?>" class="btn btn-mini" target="_blank">Посмотреть</a>
							<?} else {?>
								-
							<?}?>
						</td>
					</tr>
				<?}?>
			</table>
			<h4>Прочее</h4>
			<table class="table">
				<?/*foreach($calculations as $calculation) {?>
					<tr>
						<th><?=$calculation->calculation_title;?></th>
						<td>
							<?=$calculation->dealer_mounting_sum;?> руб.
						</td>
						<td>
							<?$path = "/costsheets/" . md5($calculation->id . "-1-2") . ".pdf";?>
							<?if(file_exists($_SERVER['DOCUMENT_ROOT'].$path)) {?>
								<a href="<?=$path;?>" class="btn btn-mini" target="_blank">Посмотреть</a>
							<?} else {?>
								-
							<?}?>
						</td>
					</tr>
				<?}*/?>
			</table>
		</div>
	  </div>
	</div>
	
<?php include_once('components/com_gm_ceiling/views/project/common_table.php'); ?>
	
<script type="text/javascript" src="/components/com_gm_ceiling/create_calculation.js"></script>

<script>
	jQuery(document).ready(function(){
		
		jQuery("#jform_project_mounting_date").mask("99.99.9999");

		document.getElementById('add_calc').onclick = function()
        {
            create_calculation(<?php echo $this->item->id; ?>);
        };
	
		jQuery("input[name^='include_calculation']").click(function(){
			if( jQuery( this ).prop("checked") ) {
				jQuery( this ).closest("tr").removeClass("not-checked");
			} else {
				jQuery( this ).closest("tr").addClass("not-checked");
			}
			calculate_total();
		});
		
		jQuery("#accept_project").click(function(){
			jQuery("input[name='project_verdict']").val(1);
			jQuery(".project_activation").show();
			jQuery("#mounting_date_control").show();
		});
		
		jQuery("#refuse_project").click(function(){
			jQuery("input[name='project_verdict']").val(0);
			jQuery(".project_activation").show();
			jQuery("#mounting_date_control").hide();
		});
	});
	
	function calculate_total(){
		var project_total = 0,
			project_total_discount = 0;
			
		jQuery("input[name^='include_calculation']:checked").each(function(){
			var parent = jQuery( this ).closest(".include_calculation"),
				calculation_total = parent.find("input[name^='calculation_total']").val(),
				calculation_total_discount = parent.find("input[name^='calculation_total_discount']").val();
				
			project_total += parseFloat(calculation_total);
			project_total_discount += parseFloat(calculation_total_discount);
		});
		
		jQuery("#project_total").text(project_total.toFixed(2));
		jQuery("#project_total_discount").text(project_total_discount.toFixed(2));
	}
</script>
	
	<?php
else:
	echo JText::_('COM_GM_CEILING_ITEM_NOT_LOADED');
endif;

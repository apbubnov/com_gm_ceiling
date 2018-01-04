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
if (!$canEdit && JFactory::getUser()->authorise('core.edit.own', 'com_gm_ceiling'))
    $canEdit = JFactory::getUser()->id == $this->item->created_by;
?>
<?=parent::getButtonBack();?>
<h2 class="center">Просмотр проекта</h2>
<?if($this->item):?>
	<?$model = Gm_ceilingHelpersGm_ceiling::getModel('calculations');?>
	<?$calculations = $model->getProjectItems($this->item->id);?>

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
						<th>Примечание клиента</th>
						<td>
							<?=$this->item->project_note;?>
						</td>
					</tr>
					<tr>
						<th><?=JText::_('COM_GM_CEILING_PROJECTS_PROJECT_CALCULATION_DAYPART');?></th>
						<td><?=JText::_('COM_GM_CEILING_PROJECTS_PROJECT_CALCULATION_DATE_OPTION_'.$this->item->project_calculation_daypart);?></td>
					</tr>
					<?if($this->type === "calculator" && $this->subtype === "calendar"){?>
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
							<?$path = "/costsheets/" . md5($calculation->id . "-0-0") . ".pdf";?>
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
							<?$path = "/costsheets/" . md5($calculation->id . "-1-2") . ".pdf";?>
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
	
	<?/*if($canEdit && $this->item->checked_out == 0):?>
		<a class="btn" href="<?=JRoute::_('index.php?option=com_gm_ceiling&task=project.edit&id='.$this->item->id);?>">Изменить проект</a>
	<?endif;*/?>

	<a class="btn btn-success" href="<?=JRoute::_('index.php?option=com_gm_ceiling&view=calculationform&type=calculator&subtype=calendar&id=0&project_id='.$this->item->id);?>">
		Создать новый расчет
	</a>
	
	<?if(sizeof($calculations)>0) {?>
		<?="<h3>Расчеты для проекта</h3>";?>
		<!-- Nav tabs -->
		<ul class="nav nav-tabs" role="tablist">
			<?foreach($calculations as $k => $calculation) {?>
				<li class="nav-item">
					<a class="nav-link<?if($k == 0) { echo " active"; }?>" data-toggle="tab" href="#calculation<?=$calculation->id;?>" role="tab"><?=$calculation->calculation_title;?></a>
				</li>
			<?}?>
		</ul>
	<?}?>
	
	<!-- Tab panes -->
	<div class="tab-content">
		<?foreach($calculations as $k => $calculation) {?>
			<?$mounters = json_decode($calculation->mounting_sum);?>
			<?$filename = "/calculation_images/".md5("calculation_sketch".$calculation->id).".png";?>
			<div class="tab-pane<?if($k == 0) { echo " active"; }?>" id="calculation<?=$calculation->id;?>" role="tabpanel">
				<h3><?=$calculation->calculation_title;?></h3>
				<a class="btn btn-primary" href="index.php?option=com_gm_ceiling&view=calculationform&type=calculator&subtype=calendar&id=<?=$calculation->id;?>">Изменить расчет</a>
				<div class="sketch_image_block">
					<h3 class="section_header">
						Чертеж <i class="fa fa-sort-desc" aria-hidden="true"></i>
					</h3>
					<div class="section_content">
                        <img class="sketch_image" src="<?php echo $filename.'?t='.time(); ?>" style="width:80vw;"/>
					</div>
				</div>
				<div class="row-fluid">
					<div class="span6">
						<h4>Материал</h4>
						<div>
							Тип потолка: <?=$calculation->n1;?>
						</div>
						<div>
							Тип фактуры: <?=$calculation->n2;?>
							</div>
						<div>
							Производитель, ширина: <?=$calculation->n3;?>
						</div>
						<?if($calculation->color > 0){?>
							<?$color_model = Gm_ceilingHelpersGm_ceiling::getModel('color');?>
							<?$color = $color_model->getData($calculation->color);?>
							<div>
								Цвет: <?=$color->color_title;?> <img src="/<?=$color->color_file;?>" alt="" />
							</div>
						<?}?>
						<h4>Размеры помещения</h4>
						<div>
							Площадь, м<sup>2</sup>: <?=$calculation->n4;?>
						</div>
						<div>
							Периметр, м: <?=$calculation->n5;?>
						</div>
						<?if($calculation->n6) {?>
							<div>
								Со вставкой: да
							</div>
						<?}?>
						<?if($calculation->transport) {?>
							<h4>Транспортные расходы</h4>
							<div>
								Транспортные расходы, шт.: <?=$calculation->transport;?>
							</div>
						<?}?>
						<?if($calculation->n15) {?>
							<div>
								Шторный карниз, м: <?=$calculation->n15;?>
							</div>
						<?}?>
						<?if($calculation->n16) {?>
							<div>
								Скрытый карниз: <?=$calculation->n16;?>
							</div>
						<?}?>
						<?
							if($calculation->n12_advanced) {
								$n12 = json_decode($calculation->n12, true);
								$n12_num = $n12['n12_num'];
								for($i = 1; $i <= $n12_num; $i++) {
									$n12_type[$i] = $n12["n12_type".$i];
									$n12_count[$i] = $n12["n12_count".$i];
								}
							} else {
								$n12_easycount = $calculation->n12_easycount;
							}							
						?>
						
						<?if($calculation->n12_advanced) {?>
							<h4>Установка люстры</h4>
							<?for($i = 1; $i <= $n12_num; $i++) {?>
								<div>
									<?=JText::_('COM_GM_CEILING_N12_TYPE_TEXT_'.$n12_type[$i]);?>, <?=$n12_count[$i];?> шт.
								</div>
							<?}?>
						<?} else {?>
							<h4>Установка люстры (упрощ)</h4>
							<?=$n12_easycount;?> шт.
						<?}?>
						
						<?
							if($calculation->n13_advanced) {
								$n13 = json_decode($calculation->n13, true);
								$n13_num = $n13['n13_num'];
								for($i = 1; $i <= $n13_num; $i++) {
									$n13_ring[$i] = $n13["n13_ring".$i];
									$n13_platform[$i] = $n13["n13_platform".$i];
									$n13_type[$i] = $n13["n13_type".$i];
									$n13_count[$i] = $n13["n13_count".$i];
								}
							} else {
								$n13_easycount = $calculation->n13_easycount;
							}
						?>
						<?if($calculation->n13_advanced) {?>
							<h4>Установка светильников</h4>
							<?for($i = 1; $i <= $n13_num; $i++) {?>
								<div>
									<?=JText::_('COM_GM_CEILING_N13_RING_TEXT_'.$n13_ring[$i]);?>,
									<?=JText::_('COM_GM_CEILING_N13_PLATFORM_TEXT_'.$n13_platform[$i]);?>,
									<?=JText::_('COM_GM_CEILING_N13_TYPE_TEXT_'.$n13_type[$i]);?>, <?=$n13_count[$i];?> шт.
								</div>
							<?}?>
						<?} else {?>
							<h4>Установка светильников (упрощ)</h4>
							<?=$n13_easycount;?> шт.
						<?}?>
						
						<?
							if($calculation->n14_advanced) {
								$n14 = json_decode($calculation->n14, true);
								$n14_num = $n14['n14_num'];
								for($i = 1; $i <= $n14_num; $i++) {
									$n14_type[$i] = $n14["n14_type".$i];
									$n14_count[$i] = $n14["n14_count".$i];
								}
							} else {
								$n14_easycount = $calculation->n14_easycount;
							}
						?>
						<?if($calculation->ecola1) {?>
							<div>
								Эcola, белый: <?=$calculation->ecola1;?>
							</div>
						<?}?>
						<?if($calculation->ecola2) {?>
							<div>
								Эcola, хром: <?=$calculation->ecola2;?>
							</div>
						<?}?>
						<?if($calculation->ecola3) {?>
							<div>
								Эcola, черный хром: <?=$calculation->ecola3;?>
							</div>
						<?}?>
						<?if($calculation->ecola4) {?>
							<div>
								Эcola, бронза: <?=$calculation->ecola4;?>
							</div>
						<?}?>
						<?if($calculation->ecola5) {?>
							<div>
								Эcola, лампа теплого свечения: <?=$calculation->ecola5;?>
							</div>
						<?}?>
						<?if($calculation->ecola6) {?>
							<div>
								Эcola, лампа холодного свечения: <?=$calculation->ecola6;?>
							</div>
						<?}?>
						<?if($calculation->dop_krepezh) {?>
							<div>
								Эcola, лампа холодного свечения: <?=$calculation->dop_krepezh;?>
							</div>
						<?}?>

						<?if($calculation->n14_advanced) {?>
							<h4>Обвод трубы</h4>
							<?for($i = 1; $i <= $n14_num; $i++) {?>
								<div>
									<?=JText::_('COM_GM_CEILING_N14_TYPE_TEXT_'.$n14_type[$i]);?>, <?=$n14_count[$i];?> шт.
								</div>
							<?}?>
						<?} else {?>
							<h4>Обвод трубы (упрощ)</h4>
							<?=$n14_easycount;?> шт.
						<?}?>
						<?if($calculation->n9) {?>
							<h4>Прочее</h4>
							<div>
								Углы, шт.: <?=$calculation->n9;?>
							</div>
						<?}?>
						<?if($calculation->n10) {?>
							<div>
								Криволинейный вырез, м: <?=$calculation->n10;?>
							</div>
						<?}?>
						<?if($calculation->n11) {?>
							<div>
								Внутренний вырез, м: <?=$calculation->n11;?>
							</div>
						<?}?>
						<?if($calculation->n7) {?>
							<div>
								Крепление в плитку, м: <?=$calculation->n7;?>
							</div>
						<?}?>
						<?if($calculation->n8) {?>
							<div>
								Крепление в керамогранит, м: <?=$calculation->n8;?>
							</div>
						<?}?>
						<?if($calculation->n17) {?>
							<div>
								Закладная брусом, м: <?=$calculation->n17;?>
							</div>
						<?}?>
						<?if($calculation->n19) {?>
							<div>
								Провод, м: <?=$calculation->n19;?>
							</div>
						<?}?>
						<?if($calculation->n20) {?>
							<div>
								Разделитель, м: <?=$calculation->n20;?>
							</div>
						<?}?>
						<?if($calculation->n21) {?>
							<div>
								Пожарная сигнализация, м: <?=$calculation->n21;?>
							</div>
						<?}?>
						<?if($calculation->n22) {?>
							<div>
								Установка вентиляции: <?=$calculation->n22;?>
							</div>
						<?}?>
						<?if($calculation->n23) {?>
							<div>
								Установка электровытяжки: <?=$calculation->n23;?>
							</div>
						<?}?>
						<?if($calculation->n24) {?>
							<div>
								Сложность доступа к месту монтажа, м: <?=$calculation->n24;?>
							</div>
						<?}?>
					</div>
					<div class="span6">

					</div>
				</div>
			</div>
		<?}?>
	</div>
	
<script>
	jQuery(document).ready(function(){
		
		jQuery("#jform_project_mounting_date").mask("99.99.9999");
	
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

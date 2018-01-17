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

$project_total = 0;
$project_total_discount = 0;

$model = Gm_ceilingHelpersGm_ceiling::getModel('calculations');
$calculations = $model->getProjectItems($this->item->id);

foreach($calculations as $calculation) {
	$calculation->dealer_canvases_sum = double_margin($calculation->canvases_sum, $this->item->gm_canvases_margin, $this->item->dealer_canvases_margin);
	$calculation->dealer_components_sum = double_margin($calculation->components_sum, $this->item->gm_components_margin, $this->item->dealer_components_margin);
	$calculation->dealer_gm_mounting_sum = double_margin($calculation->mounting_sum, $this->item->gm_mounting_margin, $this->item->dealer_mounting_margin);

	$calculation->calculation_total = round($calculation->dealer_canvases_sum + $calculation->dealer_components_sum + $calculation->dealer_gm_mounting_sum, 2);
	$calculation->calculation_total_discount = round($calculation->calculation_total * ((100 - $this->item->project_discount) / 100), 2);
	$project_total += $calculation->calculation_total;
	$project_total_discount += $calculation->calculation_total_discount;
}

$project_total = round($project_total, 2);
$project_total_discount = round($project_total_discount, 2);

?>
<?=parent::getButtonBack();?>
<h2 class="center">Просмотр проекта</h2>
<?php if ($this->item) : ?>

	<div class="container">
	  <div class="row">
		<div class="col-xl-6 item_fields">
			<h4>Информация по проекту</h4>
			<form id="form-client" action="/index.php?option=com_gm_ceiling&task=project.activate&type=gmcalculator&subtype=refused" method="post" class="form-validate form-horizontal" enctype="multipart/form-data">
				<table class="table">
					<tr>
						<th><?php echo JText::_('COM_GM_CEILING_FORM_LBL_PROJECT_CLIENT_ID'); ?></th>
						<td><?php echo $this->item->client_id; ?></td>
					</tr>
					<tr>
						<th><?php echo JText::_('COM_GM_CEILING_CLIENTS_CLIENT_CONTACTS'); ?></th>
						<td><?php echo $this->item->client_contacts; ?></td>
					</tr>	
					<tr>
						<th><?php echo JText::_('COM_GM_CEILING_FORM_LBL_PROJECT_PROJECT_INFO'); ?></th>
						<td><?php echo $this->item->project_info; ?></td>
					</tr>
					<tr>
						<th><?php echo JText::_('COM_GM_CEILING_PROJECTS_PROJECT_CALCULATION_DATE'); ?></th>
						<td>
							<?php if($this->item->project_calculation_date == "0000-00-00 00:00:00") { ?>
								-
							<?php } else { ?>
								<?php $jdate = new JDate($this->item->project_calculation_date); ?>
								<?php echo $jdate->format('d.m.Y'); ?>
							<?php } ?>
						</td>
					</tr>
					<tr>
						<th>Примечание клиента</th>
						<td>
							<?php echo $this->item->project_note; ?>
						</td>
					</tr>
					<tr>
						<th><?php echo JText::_('COM_GM_CEILING_PROJECTS_PROJECT_CALCULATION_DAYPART'); ?></th>
						<td><?php if($this->item->project_calculation_date == "0000-00-00 00:00:00") { ?>
								-
							<?php } else { ?>
								<?php $jdate = new JDate($this->item->project_calculation_date); ?>
								<?php echo $jdate->format('H:i'); ?>
							<?php } ?>
						</td>
					</tr>
					<?php if($this->type === "gmcalculator" && $this->subtype === "refused"){ ?>
						<button type="submit" id="return_project" class="btn btn btn-success">
							Вернуть на стадию замера
						</button>
						<div class="project_activation" style="display: none;">
							<input name="project_id" value="<?php echo $this->item->id; ?>" type="hidden">
							<input name="type" value="gmcalculator" type="hidden">
							<input name="subtype" value="refused" type="hidden">
							<input name="project_verdict" value="0" type="hidden">
							<input name="project_status" value="1" type="hidden">
						</div>
					<?php } ?>
				</table>
				<table class="table calculation_sum">
					<tr>
						<th class="center min-width"></th>
						<th class="center">Название расчета</th>
						<th class="center">Без скидки</th>
						<th class="center">Со скидкой</th>
					</tr>

					<?php foreach($calculations as $calculation) { ?>
						<tr>
							<td class="include_calculation">
								<input name='include_calculation[]' value='<?php echo $calculation->id; ?>' type='checkbox' checked="checked">
								<input name='calculation_total[<?php echo $calculation->id; ?>]' value='<?php echo $calculation->calculation_total; ?>' type='hidden'>
								<input name='calculation_total_discount[<?php echo $calculation->id; ?>]' value='<?php echo $calculation->calculation_total_discount; ?>' type='hidden'>
							</td>
							<td><?php echo $calculation->calculation_title; ?></td>
							<td class="center"><?php echo $calculation->calculation_total; ?></td>
							<td class="center"><?php echo $calculation->calculation_total_discount; ?></td>
						</tr>
					<?php }	?>
					<tr>
						<th class="right" colspan="2">Итого:</th>
						<th class="center" id="project_total"><?php echo $project_total; ?></th>
						<th class="center" id="project_total_discount"><?php echo $project_total_discount; ?></th>
					</tr>				
				</table>
			</form>
			
		</div>
		<div class="col-xl-6"></div>
		</div>
		<div class="row">
		<div class="col-xl-6">
			<h4>Сметы для клиента</h4>
			<table class="table">
				<?php foreach($calculations as $calculation) { ?>
					<tr>
						<th><?php echo $calculation->calculation_title; ?></th>
						<td>
							<?php echo $calculation->calculation_total_discount; ?> руб.
						</td>
						<td>
							<?php $path = "/costsheets/" . md5($calculation->id . "-0-0") . ".pdf"; ?>
							<?php if(file_exists($_SERVER['DOCUMENT_ROOT'].$path)) { ?>
								<a href="<?php echo $path; ?>" class="btn btn-secondary" target="_blank">Посмотреть</a>
							<?php } else { ?>
								-
							<?php } ?>
						</td>
					</tr>
				<?php } ?>
			</table>
		</div>
		<div class="col-xl-6"></div>
	  </div>
	</div>
	
	<?php /*if($canEdit && $this->item->checked_out == 0): ?>
		<a class="btn" href="<?php echo JRoute::_('index.php?option=com_gm_ceiling&task=project.edit&id='.$this->item->id); ?>">Изменить проект</a>
	<?php endif;*/ ?>

	<?php if(sizeof($calculations)>0) { ?>
		<?php echo "<h3>Расчеты для проекта</h3>"; ?>
		<!-- Nav tabs -->
		<ul class="nav nav-tabs" role="tablist">
			<?php foreach($calculations as $k => $calculation) { ?>
				<li class="nav-item">
					<a class="nav-link<?php if($k == 0) { echo " active"; } ?>" data-toggle="tab" href="#calculation<?php echo $calculation->id; ?>" role="tab"><?php echo $calculation->calculation_title; ?></a>
				</li>
			<?php } ?>
		</ul>
	<?php } ?>
	
	<!-- Tab panes -->
	<div class="tab-content">
		<?php foreach($calculations as $k => $calculation) { ?>
			<?php $mounters = json_decode($calculation->mounting_sum); ?>
			<?php $filename = "/calculation_images/".md5("calculation_sketch".$calculation->id).".png"; ?>
			<div class="tab-pane<?php if($k == 0) { echo " active"; } ?>" id="calculation<?php echo $calculation->id; ?>" role="tabpanel">
				<h3><?php echo $calculation->calculation_title; ?></h3>
    <? if (!empty($filename)):?>
        <div class="sketch_image_block">
            <h3 class="section_header">
                Чертеж <i class="fa fa-sort-desc" aria-hidden="true"></i>
            </h3>
            <div class="section_content">
                <img class="sketch_image" src="<?php echo $filename.'?t='.time(); ?>" style="width:80vw;"/>
            </div>
        </div>
    <? endif; ?>
    <div class="row-fluid">
        <div class="span6">
            <?if($calculation->n1 && $calculation->n2 && $calculation->n3):?>
                <h4>Материал</h4>
                <div>
                    Тип потолка: <?php echo $calculation->n1; ?>
                </div>
                <div>
                    Тип фактуры: <?php echo $calculation->n2; ?>
                </div>
                <div>
                    Производитель, ширина: <?php echo $calculation->n3; ?>
                </div>

                <?php if ($calculation->color > 0) { ?>
                    <?php $color_model = Gm_ceilingHelpersGm_ceiling::getModel('color'); ?>
                    <?php $color = $color_model->getData($calculation->color); ?>
                    <div>
                        Цвет: <?php echo $color->colors_title; ?> <img src="/<?php echo $color->file; ?>"
                                                                       alt=""/>
                    </div>
                <?php } ?>
                <h4>Размеры помещения</h4>
                <div>
                    Площадь, м<sup>2</sup>: <?php echo $calculation->n4; ?>
                </div>
                <div>
                    Периметр, м: <?php echo $calculation->n5; ?>
                </div>
                <?php if ($calculation->n6 > 0) {?>
                    <div>
                        <h4> Вставка</h4>
                    </div>
                    <? if ($calculation->n6 == 314) {?>
                        <div> Белая </div>
                    <?php } else  {?>
                        <?php $color_model_1 = Gm_ceilingHelpersGm_ceiling::getModel('components'); ?>
                        <?php $color_1 = $color_model_1->getColorId($calculation->n6); ?>
                        <div>
                            Цветная : <?php echo $color_1[0]->title; ?> <img style='width: 50px; height: 30px;' src="/<?php echo $color_1[0]->file; ?>"
                                                                             alt=""/>
                        </div>
                    <?php }?>
                <?} endif; ?>
            <?php if ($calculation->n16) { ?>
                <div>
                    Скрытый карниз: <?php echo $calculation->n16; ?>
                </div>
            <?php } ?>

            <?php if ($calculation->n12) { ?>
                <h4>Установка люстры</h4>
                <?php echo $calculation->n12; ?> шт.
            <?php } ?>

            <?php if ($calculation->n13) { ?>
                <h4>Установка светильников</h4>
                <?php foreach ($calculation->n13 as $key => $n13_item) {
                    echo "<b>Количество:</b> " . $n13_item->n13_count . " шт - <b>Тип:</b>  " . $n13_item->type_title . " - <b>Размер:</b> " . $n13_item->component_title . "<br>";
                    ?>
                <?php }
            } ?>

            <?php if ($calculation->n14) { ?>
                <h4>Обвод трубы</h4>
                <?php foreach ($calculation->n14 as $key => $n14_item) {
                    echo "<b>Количество:</b> " . $n14_item->n14_count . " шт  -  <b>Диаметр:</b>  " . $n14_item->component_title . "<br>";
                    ?>
                <?php }
            } ?>

            <?php if ($calculation->n15) { ?>
                <h4>Шторный карниз Гильдии мастеров</h4>
                <?php foreach ($calculation->n15 as $key => $n15_item) {
                    echo "<b>Количество:</b> " . $n15_item->n15_count . " шт - <b>Тип:</b>   " . $n15_item->type_title . " <b>Длина:</b> " . $n15_item->component_title . "<br>";
                    ?>
                <?php }
            } ?>
            <?php if ($calculation->n27> 0) { ?>
                <h4>Шторный карниз</h4>
                <? if ($calculation->n16) echo "Скрытый карниз"; ?>
                <? if (!$calculation->n16) echo "Обычный карниз"; ?>
                <?php echo $calculation->n27; ?> м.
            <?php } ?>

            <?php if ($calculation->n26) { ?>
                <h4>Светильники Эcola</h4>
                <?php foreach ($calculation->n26 as $key => $n26_item) {
                    echo "<b>Количество:</b> " . $n26_item->n26_count . " шт - <b>Тип:</b>  " . $n26_item->component_title_illuminator . " -  <b>Лампа:</b> " . $n26_item->component_title_lamp . "<br>";
                    ?>
                <?php }
            } ?>

            <?php if ($calculation->n22) { ?>
                <h4>Вентиляция</h4>
                <?php foreach ($calculation->n22 as $key => $n22_item) {
                    echo "<b>Количество:</b> " . $n22_item->n22_count . " шт - <b>Тип:</b>   " . $n22_item->type_title . " - <b>Размер:</b> " . $n22_item->component_title . "<br>";
                    ?>
                <?php }
            } ?>

            <?php if ($calculation->n23) { ?>
                <h4>Диффузор</h4>
                <?php foreach ($calculation->n23 as $key => $n23_item) {
                    echo "<b>Количество:</b> " . $n23_item->n23_count . " шт - <b>Размер:</b>  " . $n23_item->component_title . "<br>";
                    ?>
                <?php }
            } ?>

            <?php if ($calculation->n29) { ?>
                <h4>Переход уровня</h4>
                <?php foreach ($calculation->n29 as $key => $n29_item) {
                    echo "<b>Количество:</b> " . $n29_item->n29_count . " м - <b>Тип:</b>  " . $n29_item->type_title . " <br>";
                    ?>
                <?php }
            } ?>
            <h4>Прочее</h4>
            <?php if ($calculation->n9> 0) { ?>
                <div>
                    Углы, шт.: <?php echo $calculation->n9; ?>
                </div>
            <?php } ?>
            <?php if ($calculation->n10> 0) { ?>
                <div>
                    Криволинейный вырез, м: <?php echo $calculation->n10; ?>
                </div>
            <?php } ?>
            <?php if ($calculation->n11> 0) { ?>
                <div>
                    Внутренний вырез, м: <?php echo $calculation->n11; ?>
                </div>
            <?php } ?>
            <?php if ($calculation->n7> 0) { ?>
                <div>
                    Крепление в плитку, м: <?php echo $calculation->n7; ?>
                </div>
            <?php } ?>
            <?php if ($calculation->n8> 0) { ?>
                <div>
                    Крепление в керамогранит, м: <?php echo $calculation->n8; ?>
                </div>
            <?php } ?>
            <?php if ($calculation->n17> 0) { ?>
                <div>
                    Закладная брусом, м: <?php echo $calculation->n17; ?>
                </div>
            <?php } ?>
            <?php if ($calculation->n19> 0) { ?>
                <div>
                    Провод, м: <?php echo $calculation->n19; ?>
                </div>
            <?php } ?>
            <?php if ($calculation->n20> 0) { ?>
                <div>
                    Разделитель, м: <?php echo $calculation->n20; ?>
                </div>
            <?php } ?>
            <?php if ($calculation->n21> 0) { ?>
                <div>
                    Пожарная сигнализация, м: <?php echo $calculation->n21; ?>
                </div>
            <?php } ?>

            <?php if ($calculation->dop_krepezh> 0) { ?>
                <div>
                    Дополнительный крепеж: <?php echo $calculation->dop_krepezh; ?>
                </div>
            <?php } ?>

            <?php if ($calculation->n24> 0) { ?>
                <div>
                    Сложность доступа к месту монтажа, м: <?php echo $calculation->n24; ?>
                </div>
            <?php } ?>

            <?php if ($calculation->n30> 0) { ?>
                <div>
                    Парящий потолок, м: <?php echo $calculation->n30; ?>
                </div>
            <?php } ?>
            <?php if ($calculation->n32> 0) { ?>
                <div>
                    Слив воды, кол-во комнат: <?php echo $calculation->n32; ?>
                </div>
            <?php } ?>
            <? $extra_mounting = (array) json_decode($calculation->extra_mounting);?>
            <?php if (!empty($extra_mounting) ) { ?>
                <div>
                    <h4>Дополнительные работы</h4>
                    <? foreach($extra_mounting as $dop) {
                        echo "<b>Название:</b> " . $dop->title .  "<br>";
                    }?>
                </div>
            <?php } ?>
        </div>
			</div>
		<?php } ?>
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

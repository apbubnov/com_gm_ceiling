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

$n12_type_text = array(
	"",
	"< 400 мм",
	"> 400 мм"
	
);
$n13_ring_text = array(
	"",
	"кольцо 20-90 мм",
	"кольцо 100-112 мм",
	"кольцо 115-175 мм",
	"кольцо 195-225 мм",
	"кольцо 250-300 мм",
	"кольцо 325-375 мм",
	"кольцо 400-425 мм",
	"кольцо 455-485 мм",
	"кольцо 520-550 мм",
	"кольцо 580-610 мм"
);
$n13_platform_text = array(
	"",
	"Платформа 165-225 мм",
	"Платформа 125-155 мм",
	"Платформа 55-105 мм",
	"Платформа 55-125 мм"
);
$n13_type_text = array(
	"",
	"Круглый",
	"Квадратный"
);

$n14_type_text = array(
	"",
	"105, 115, 125 мм",
	"45-55 мм",
	"22, 27, 32 мм"
);

//$canEdit = JFactory::getUser()->authorise('core.edit', 'com_gm_ceiling');
/*if (!$canEdit && JFactory::getUser()->authorise('core.edit.own', 'com_gm_ceiling')) {
	$canEdit = JFactory::getUser()->id == $this->item->created_by;
}*/
?>
<h2>Просмотр проекта</h2>
<?php if ($this->item) : ?>
	<?php $model = Gm_ceilingHelpersGm_ceiling::getModel('calculations'); ?>
	<?php $calculations = $model->getProjectItems($this->item->id); ?>

	<div class="row-fluid">
		<div class="span6 item_fields">
			<h4>Информация по проекту</h4>
			<?php echo $this->item->id; ?>
			<table class="table">
				<tr>
					<th><?php echo JText::_('COM_GM_CEILING_FORM_LBL_PROJECT_PROJECT_STATUS'); ?></th>
					<td><?php echo $this->item->project_status; ?></td>
				</tr>
				<tr>
					<th><?php echo JText::_('COM_GM_CEILING_FORM_LBL_PROJECT_PROJECT_MOUNTING_DATE'); ?></th>
					<td><?php echo $this->item->project_mounting_date; ?></td>
				</tr>
				<tr>
					<th><?php echo JText::_('COM_GM_CEILING_FORM_LBL_PROJECT_PROJECT_MOUNTING_DAYPART'); ?></th>
					<td><?php echo $this->item->project_mounting_daypart; ?></td>
				</tr>
				<tr>
					<th><?php echo JText::_('COM_GM_CEILING_FORM_LBL_PROJECT_PROJECT_INFO'); ?></th>
					<td><?php echo $this->item->project_info; ?></td>
				</tr>
				<tr>
					<th><?php echo JText::_('COM_GM_CEILING_FORM_LBL_PROJECT_CLIENT_ID'); ?></th>
					<td><?php echo $this->item->client_id; ?></td>
				</tr>
				<tr>
					<th><?php echo JText::_('COM_GM_CEILING_FORM_LBL_PROJECT_PROJECT_MOUNTER'); ?></th>
					<td><?php echo $this->item->project_mounter; ?></td>
				</tr>
				<?php if($this->item->project_status == 0){ ?>
					<a href="/index.php?option=com_gm_ceiling&task=project.activate&project_id=<?php echo $this->item->id; ?>" id="activate_calculation" class="btn btn-large btn-success">
						Запустить в производство
					</a>
				<?php } ?>
			</table>
			<table class="table calculation_sum">
				<tr>
					<th class="center min-width" rowspan="2"></th>
					<th class="center" rowspan="2">Название расчета</th>
					<th class="center" rowspan="2">Комплектующие</th>
					<th class="center" colspan="2">Комплектующие + монтаж</th>
				</tr>
				<tr>
					<th class="center">ГМ</th>
					<th class="center">Дилер</th>
				</tr>
				<?php $components_total = 0; ?>
				<?php $gm_total = 0; ?>
				<?php $dealer_total = 0; ?>
				<?php foreach($calculations as $calculation) { ?>
					<?php $components_total += $calculation->components_sum; ?>
					<?php $gm_total 		+= $calculation->gm_mounting_sum; ?>
					<?php $dealer_total 	+= $calculation->dealer_mounting_sum; ?>
					<?php $gm_all		 	= $calculation->components_sum + $calculation->gm_mounting_sum; ?>
					<?php $dealer_all	 	= $calculation->components_sum + $calculation->dealer_mounting_sum; ?>
					<tr>
						<td class="include_calculation">
							<input name='include_calculation<?php echo $calculation->id; ?>' value='1' type='checkbox' checked="checked">
							<input name='components_sum[<?php echo $calculation->id; ?>]' value='<?php echo $calculation->components_sum; ?>' type='hidden'>
							<input name='gm_mounting_sum[<?php echo $calculation->id; ?>]' value='<?php echo $gm_all; ?>' type='hidden'>
							<input name='dealer_mounting_sum[<?php echo $calculation->id; ?>]' value='<?php echo $dealer_all; ?>' type='hidden'>
						</td>
						<td><?php echo $calculation->calculation_title; ?></td>
						<td class="center"><?php echo $calculation->components_sum; ?></td>
						<td class="center"><?php echo $gm_all; ?></td>
						<td class="center"><?php echo $dealer_all; ?></td>
					</tr>
				<?php }	?>
				<tr>
					<th class="right" colspan="2">Итого:</th>
					<th class="center" id="components_total"><?php echo $components_total; ?></th>
					<th class="center" id="gm_total"><?php echo ($components_total + $gm_total); ?></th>
					<th class="center" id="dealer_total"><?php echo ($components_total + $dealer_total); ?></th>
				</tr>				
			</table>			
		</div>
		<div class="span6">
			<h4>Сметы для клиента</h4>
			<table class="table">
				<?php foreach($calculations as $calculation) { ?>
					<tr>
						<th><?php echo $calculation->calculation_title; ?></th>
						<td>
							<?php echo $calculation->components_sum; ?> руб.
						</td>
						<td>
							<?php $path = "/costsheets/" . md5($calculation->id . "client_single") . ".pdf"; ?>
							<?php if(file_exists($_SERVER['DOCUMENT_ROOT'].$path)) { ?>
								<a href="<?php echo $path; ?>" class="btn btn-mini" target="_blank">Посмотреть</a>
							<?php } else { ?>
								-
							<?php } ?>
						</td>
					</tr>
				<?php } ?>
			</table>
			<h4>Наряды на монтаж</h4>
			<table class="table">
				<?php foreach($calculations as $calculation) { ?>
					<tr>
						<th><?php echo $calculation->calculation_title; ?></th>
						<td>
							<?php echo $calculation->gm_mounting_sum; ?> руб.
						</td>
						<td>
							<?php $path = "/costsheets/" . md5($calculation->id . "mount_single") . ".pdf"; ?>
							<?php if(file_exists($_SERVER['DOCUMENT_ROOT'].$path)) { ?>
								<a href="<?php echo $path; ?>" class="btn btn-mini" target="_blank">Посмотреть</a>
							<?php } else { ?>
								-
							<?php } ?>
						</td>
					</tr>
				<?php } ?>
			</table>
			<h4>Прочее</h4>
			<table class="table">
				<?php /*foreach($calculations as $calculation) { ?>
					<tr>
						<th><?php echo $calculation->calculation_title; ?></th>
						<td>
							<?php echo $calculation->dealer_mounting_sum; ?> руб.
						</td>
						<td>
							<?php $path = "/costsheets/" . md5($calculation->id . "-1-2") . ".pdf"; ?>
							<?php if(file_exists($_SERVER['DOCUMENT_ROOT'].$path)) { ?>
								<a href="<?php echo $path; ?>" class="btn btn-mini" target="_blank">Посмотреть</a>
							<?php } else { ?>
								-
							<?php } ?>
						</td>
					</tr>
				<?php }*/ ?>
			</table>
		</div>
	</div>
	
	<?php /*if($canEdit && $this->item->checked_out == 0): ?>
		<a class="btn" href="<?php echo JRoute::_('index.php?option=com_gm_ceiling&task=project.edit&id='.$this->item->id); ?>">Изменить проект</a>
	<?php endif;*/ ?>

	<a class="btn btn-success" href="/create-calculation?project_id=<?php echo $this->item->id; ?>">
		Создать новый расчет
	</a>
	
	<?php if(sizeof($calculations)>0) { ?>
		<?php echo "<h3>Расчеты для проекта</h3>"; ?>
		<?php echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => 'order'.$calculations[0]->id)); ?>
	<?php } ?>
	
	<?php foreach($calculations as $calculation) { ?>
		<?php $mounters = json_decode($calculation->mounting_sum); ?>
		<?php $filename = "/calculation_images/".md5("calculation_sketch".$calculation->id).".svg"; ?>
		<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'order' . $calculation->id, $calculation->calculation_title); ?>
		<h3><?php echo $calculation->calculation_title; ?></h3>
		<div class="sketch_image_block">
			<h3 class="section_header">
				Чертеж <i class="fa fa-sort-desc" aria-hidden="true"></i>
			</h3>
			<div class="section_content">
				<?php if (file_exists($_SERVER['DOCUMENT_ROOT'].$filename)){ ?>
                    <img class="sketch_image" src="<?php echo $filename; ?>" style="width:80vw;"/>
                <?php } ?>
			</div>
		</div>
		<div class="row-fluid">
			<div class="span6">
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
				
				<h4>Размеры помещения</h4>
				<div>
					Площадь, м<sup>2</sup>: <?php echo $calculation->n4; ?>
				</div>
				<div>
					Периметр, м: <?php echo $calculation->n5; ?>
				</div>
				<?php if($calculation->n6) { ?>
					<div>
						Со вставкой: да
					</div>
				<?php } ?>
				<?php if($calculation->transport) { ?>
					<h4>Транспортные расходы</h4>
					<div>
						Транспортные расходы, шт.: <?php echo $calculation->transport; ?>
					</div>
				<?php } ?>
				<?php if($calculation->n15) { ?>
					<div>
						Шторный карниз, м: <?php echo $calculation->n15; ?>
					</div>
				<?php } ?>
				<?php if($calculation->n16) { ?>
					<div>
						Скрытый карниз: <?php echo $calculation->n16; ?>
					</div>
				<?php } ?>
				<?php 
					$n12 = json_decode($calculation->n12, true);
					$n12_num = $n12['n12_num'];
					for($i = 1; $i <= $n12_num; $i++) {
						$n12_type[$i] = $n12["n12_type".$i];
						$n12_count[$i] = $n12["n12_count".$i];
					}
				?>
				<h4>Установка люстры</h4>
				<?php for($i = 1; $i <= $n12_num; $i++) { ?>
					<div>
						<?php echo $n12_type_text[$n12_type[$i]]; ?>, <?php echo $n12_count[$i]; ?> шт.
					</div>
				<?php } ?>
				
				<?php 
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
				<?php if($calculation->n13_advanced) { ?>
					<h4>Установка светильников</h4>
					<?php for($i = 1; $i <= $n13_num; $i++) { ?>
						<div>
							<?php echo $n13_ring_text[$n13_ring[$i]]; ?>, <?php echo $n13_platform_text[$n13_platform[$i]]; ?>, <?php echo $n13_type_text[$n13_type[$i]]; ?>, <?php echo $n13_count[$i]; ?> шт.
						</div>
					<?php } ?>
				<?php } else { ?>
					<h4>Установка светильников (упрощ)</h4>
					<?php echo $n13_easycount; ?> шт.
				<?php } ?>
				
				<?php 
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
				<?php if($calculation->ecola1) { ?>
					<div>
						Эcola, белый: <?php echo $calculation->ecola1; ?>
					</div>
				<?php } ?>
				<?php if($calculation->ecola2) { ?>
					<div>
						Эcola, хром: <?php echo $calculation->ecola2; ?>
					</div>
				<?php } ?>
				<?php if($calculation->ecola3) { ?>
					<div>
						Эcola, черный хром: <?php echo $calculation->ecola3; ?>
					</div>
				<?php } ?>
				<?php if($calculation->ecola4) { ?>
					<div>
						Эcola, бронза: <?php echo $calculation->ecola4; ?>
					</div>
				<?php } ?>
				<?php if($calculation->ecola5) { ?>
					<div>
						Эcola, лампа теплого свечения: <?php echo $calculation->ecola5; ?>
					</div>
				<?php } ?>
				<?php if($calculation->ecola6) { ?>
					<div>
						Эcola, лампа холодного свечения: <?php echo $calculation->ecola6; ?>
					</div>
				<?php } ?>
				<?php if($calculation->dop_krepezh) { ?>
					<div>
						Эcola, лампа холодного свечения: <?php echo $calculation->dop_krepezh; ?>
					</div>
				<?php } ?>
				<?php if($calculation->n14_advanced) { ?>
					<h4>Обвод трубы</h4>
					<?php for($i = 1; $i <= $n14_num; $i++) { ?>
						<div>
							<?php echo $n14_type_text[$n14_type[$i]]; ?>, <?php echo $n14_count[$i]; ?> шт.
						</div>
					<?php } ?>
				<?php } else { ?>
					<h4>Обвод трубы (упрощ)</h4>
					<?php echo $n14_easycount; ?> шт.
				<?php } ?>
				<?php if($calculation->n9) { ?>
					<h4>Прочее</h4>
					<div>
						Углы, шт.: <?php echo $calculation->n9; ?>
					</div>
				<?php } ?>
				<?php if($calculation->n10) { ?>
					<div>
						Криволинейный вырез, м: <?php echo $calculation->n10; ?>
					</div>
				<?php } ?>
				<?php if($calculation->n11) { ?>
					<div>
						Внутренний вырез, м: <?php echo $calculation->n11; ?>
					</div>
				<?php } ?>
				<?php if($calculation->n7) { ?>
					<div>
						Крепление в плитку, м: <?php echo $calculation->n7; ?>
					</div>
				<?php } ?>
				<?php if($calculation->n8) { ?>
					<div>
						Крепление в керамогранит, м: <?php echo $calculation->n8; ?>
					</div>
				<?php } ?>
				<?php if($calculation->n17) { ?>
					<div>
						Закладная брусом, м: <?php echo $calculation->n17; ?>
					</div>
				<?php } ?>
				<?php if($calculation->n19) { ?>
					<div>
						Провод, м: <?php echo $calculation->n19; ?>
					</div>
				<?php } ?>
				<?php if($calculation->n20) { ?>
					<div>
						Разделитель, м: <?php echo $calculation->n20; ?>
					</div>
				<?php } ?>
				<?php if($calculation->n21) { ?>
					<div>
						Пожарная сигнализация, м: <?php echo $calculation->n21; ?>
					</div>
				<?php } ?>
				<?php if($calculation->n22) { ?>
					<div>
						Установка вентиляции: <?php echo $calculation->n22; ?>
					</div>
				<?php } ?>
				<?php if($calculation->n23) { ?>
					<div>
						Установка электровытяжки: <?php echo $calculation->n23; ?>
					</div>
				<?php } ?>
				<?php if($calculation->n24) { ?>
					<div>
						Сложность доступа к месту монтажа, м: <?php echo $calculation->n24; ?>
					</div>
				<?php } ?>
			</div>
			<div class="span6">

			</div>
		</div>
		<?php echo JHtml::_('bootstrap.endTab'); ?>
	<?php } ?>
	<?php echo JHtml::_('bootstrap.endTabSet'); ?>

<script type="text/javascript" src="/components/com_gm_ceiling/create_calculation.js"></script>
<script>
	jQuery(document).ready(function(){

		create_calculation(<?php echo $this->item->id; ?>);

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
		var components_total = 0;
			gm_total = 0;
			dealer_total = 0;
			
		jQuery("input[name^='include_calculation']:checked").each(function(){
			var parent = jQuery( this ).closest(".include_calculation"),
				components_sum = parent.find("input[name^='components_sum']").val(),
				gm_mounting_sum = parent.find("input[name^='gm_mounting_sum']").val(),
				dealer_mounting_sum = parent.find("input[name^='dealer_mounting_sum']").val();
				
			components_total += parseFloat(components_sum);
			gm_total += parseFloat(gm_mounting_sum);
			dealer_total += parseFloat(dealer_mounting_sum);
		});
		
		jQuery("#components_total").text(components_total.toFixed(2));
		jQuery("#gm_total").text(gm_total.toFixed(2));
		jQuery("#dealer_total").text(dealer_total.toFixed(2));
	}
</script>
	
	<?php
else:
	echo JText::_('COM_GM_CEILING_ITEM_NOT_LOADED');
endif;

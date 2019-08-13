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
?>
<?=parent::getButtonBack();?>


<?php if ($this->item) : ?>
	<?php $model = Gm_ceilingHelpersGm_ceiling::getModel('calculations'); ?>
	<?php $calculations = $model->getProjectItems($this->item->id); ?>

	<div class="container">
	  <div class="row">
          <h1>Если Вы сюда попали, срочно сообщите программистам как Вы это сделали! </h1>
		<!--<div class="item_fields">
			<h4>Информация по проекту</h4>
			<form id="form-client" action="/index.php?option=com_gm_ceiling&task=project.activate&type=manager" method="post" class="form-validate form-horizontal" enctype="multipart/form-data">
				<table class="table">
					<tr>
						<th><?php /*echo JText::_('COM_GM_CEILING_PROJECTS_PROJECT_CALCULATION_DATE'); */?></th>
						<td>
							<?php /*$jdate = new JDate($this->item->project_calculation_date); */?>
							<?php /*echo $jdate->format('d.m.Y'); */?>
						</td>
					</tr>
					<tr>
						<th><?php /*echo JText::_('COM_GM_CEILING_FORM_LBL_PROJECT_PROJECT_INFO'); */?></th>
						<td><?php /*echo $this->item->project_info; */?></td>
					</tr>
					<tr>
						<th><?php /*echo JText::_('COM_GM_CEILING_FORM_LBL_PROJECT_CLIENT_ID'); */?></th>
						<td><?php /*echo $this->item->client_id; */?></td>
					</tr>
					<tr>
						<th><?php /*echo JText::_('COM_GM_CEILING_CLIENTS_CLIENT_CONTACTS'); */?></th>
						<td><?php /*echo $this->item->client_contacts; */?></td>
					</tr>
				</table>
			</form>
		</div>
		<div class="">
			<h4>Информация для менеджера</h4>
			<table class="table">
			<?php /*
					$components_model = Gm_ceilingHelpersGm_ceiling::getModel('components');
					$components_list = $components_model->getFilteredItems();
					foreach($components_list as $i => $component) {
						$components[$component->id] = $component; 
					}*/?>
				<?php /*foreach($calculations as $calculation) { */?>
					<tr>
						<th><?php /*echo $calculation->calculation_title; */?></th>
						<td>
							<?php /*if( $calculation->n9 >= 4) $sum = $calculation->canvases_sum + $components[57]->component_price * $calculation->n10 + $components[58]->component_price * $calculation->n11 + $components[56]->component_price * ($calculation->n9 - 4);
							else $sum = $calculation->canvases_sum + $components[57]->component_price * $calculation->n10 + $components[58]->component_price * $calculation->n11;
							*/?>
							 <?php /*echo $sum; */?> руб.
						</td>
						<td>
							<?php /*$path = "/costsheets/" . md5($calculation->id . "manager") . ".pdf"; */?>
							<?php /*if(file_exists($_SERVER['DOCUMENT_ROOT'].$path)) { */?>
								<a href="<?php /*echo $path; */?>" class="btn btn-secondary" target="_blank">Посмотреть</a>
							<?php /*} else { */?>
								-
							<?php /*} */?>
						</td>
					</tr>
				<?php /*} */?>
			</table>
			<h4>Расходные материалы</h4>
			<table class="table">
				<?php /*foreach($calculations as $calculation) { */?>
					<tr>
						<th><?php /*echo $calculation->calculation_title; */?></th>
						<td>
							<?php /*echo $calculation->gm_mounting_sum; */?> руб.
						</td>
						<td>
							<?php /*$path = "/costsheets/" . md5($calculation->id . "consumables") . ".pdf"; */?>
							<?php /*if(file_exists($_SERVER['DOCUMENT_ROOT'].$path)) { */?>
								<a href="<?php /*echo $path; */?>" class="btn btn-secondary" target="_blank">Посмотреть</a>
							<?php /*} else { */?>
								-
							<?php /*} */?>
						</td>
					</tr>
				<?php /*} */?>
			</table>
			<h4>Наряды на монтаж</h4>
			<table class="table">
				<?php /*foreach($calculations as $calculation) { */?>
					<tr>
						<th><?php /*echo $calculation->calculation_title; */?></th>
						<td>
							<?php /*echo $calculation->gm_mounting_sum; */?> руб.
						</td>
						<td>
							<?php /*$path = "/costsheets/" . md5($calculation->id . "mount_single") . ".pdf"; */?>
							<?php /*if(file_exists($_SERVER['DOCUMENT_ROOT'].$path)) { */?>
								<a href="<?php /*echo $path; */?>" class="btn btn-secondary" target="_blank">Посмотреть</a>
							<?php /*} else { */?>
								-
							<?php /*} */?>
						</td>
					</tr>
				<?php /*} */?>
			</table>
				<button  type="button"  id="run_project"  class="validate btn btn-primary">
			Запустить в производство
			</button>
		</div>-->
	  </div>
	</div>
	
<script>
	jQuery(document).ready(function(){
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
	
	jQuery("#run_project").click(function(){
			jQuery.ajax({
				type: 'POST',
				url: "index.php?option=com_gm_ceiling&task=change_status",
				data: {
					id: <?php echo $this->item->id; ?>,
					project_status: 4
				},
				success: function(data){
					
					location.href = "<?php echo JRoute::_('/index.php?option=com_gm_ceiling&view=projects&type=gmmanager&subtype=clientorders', false); ?>";
				
				},
				dataType: "text",
				timeout: 10000,
				error: function(){
					var n = noty({
						theme: 'relax',
						layout: 'center',
						maxVisible: 5,
						type: "error",
						text: "Ошибка при попытке сохранить запись. Сервер не отвечает"
					});
				}					
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

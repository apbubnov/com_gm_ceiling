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

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');
JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.multiselect');
JHtml::_('formbehavior.chosen', 'select');

$user       = JFactory::getUser();
$userId     = $user->get('id');
$listOrder  = $this->state->get('list.ordering');
$listDirn   = $this->state->get('list.direction');
$canCreate  = $user->authorise('core.create', 'com_gm_ceiling') && file_exists(JPATH_COMPONENT . DIRECTORY_SEPARATOR . 'models' . DIRECTORY_SEPARATOR . 'forms' . DIRECTORY_SEPARATOR . 'projectform.xml');
$canEdit    = $user->authorise('core.edit', 'com_gm_ceiling') && file_exists(JPATH_COMPONENT . DIRECTORY_SEPARATOR . 'models' . DIRECTORY_SEPARATOR . 'forms' . DIRECTORY_SEPARATOR . 'projectform.xml');
$canCheckin = $user->authorise('core.manage', 'com_gm_ceiling');
$canChange  = $user->authorise('core.edit.state', 'com_gm_ceiling');
$canDelete  = $user->authorise('core.delete', 'com_gm_ceiling');
$model = Gm_ceilingHelpersGm_ceiling::getModel('calculations');
?>
<?=parent::getButtonBack();?>
<h2 class = "center">Запущенные проекты</h2>
<form action="<?php echo JRoute::_('index.php?option=com_gm_ceiling&view=projects&type=gmmanager&subtype=runprojects'); ?>" method="post" name="adminForm" id="adminForm">
    <? if (count($this->items) > 0): ?>
        <div class="toolbar">
            <?php echo JLayoutHelper::render('default_filter', array('view' => $this), dirname(__FILE__)); ?>
        </div>
        <table class="table table-striped one-touch-view" id="projectList">
            <thead>
            <tr>
                <th class='center'> 
                </th>
                <th class='center'>
                    <?php echo JHtml::_('grid.sort',  'Номер договора', 'a.id', $listDirn, $listOrder); ?>
                </th>
                <th class='center'>
                    <?php echo JHtml::_('grid.sort',  'Статус', 'a.project_status', $listDirn, $listOrder); ?>
                </th>
                <th class='center'>
                    <?php echo JHtml::_('grid.sort',  'Дата замера', 'a.project_mounting_from', $listDirn, $listOrder); ?>
                </th>
                <th class='center'>
                    <?php echo JHtml::_('grid.sort',  'Дата монтажа', 'a.project_mounting_from', $listDirn, $listOrder); ?>
                </th>
                <th class='center'>
                    <?php echo JHtml::_('grid.sort',  'Дата закрытия проекта', 'a.project_mounting_from', $listDirn, $listOrder); ?>
                </th>
                <th class='center'>
                    <?php echo JHtml::_('grid.sort',  'COM_GM_CEILING_PROJECTS_PROJECT_INFO', 'a.project_info', $listDirn, $listOrder); ?>
                </th>
                <th class='center'>
                    <?php echo JHtml::_('grid.sort',  'Телефоны', 'a.client_contacts', $listDirn, $listOrder); ?>
                </th>
                <th class='center'>
                    <?php echo JHtml::_('grid.sort',  'COM_GM_CEILING_PROJECTS_CLIENT_ID', 'a.client_id', $listDirn, $listOrder); ?>
                </th>
                <th class="center">
                    Дилер
                </th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($this->items as $i => $item) : ?>
                <?php $canEdit = $user->authorise('core.edit', 'com_gm_ceiling'); ?>
                <?php $dealer = JFactory::getUser($item->dealer_id); ?>
                <?php if (!$canEdit && $user->authorise('core.edit.own', 'com_gm_ceiling')): ?>
                    <?php $canEdit = JFactory::getUser()->id == $item->created_by; ?>
                <?php endif; ?>
                <tr data-href="<?php echo JRoute::_('index.php?option=com_gm_ceiling&view=project&type=gmmanager&subtype=run&id='.(int) $item->id); ?>">
                    <td>
                    <!-- <//?php if ($item->project_status == 10 || $item->project_status == 11 ) { ?> -->
                        <button class="btn btn-primary btn-done" data-project_id="<?= $item->id; ?>" type="button">Выполнено</button>
                  <!--   <// } ?> -->
                    <?php if ($item->project_status == 12) { ?>
                         <i class='fa fa-check' aria-hidden='true'></i> Выполнено
                    <? } ?>
                    </td>
                    <td class="center one-touch">
                        <?php echo $item->id;

                            $calculations = $model->new_getProjectItems($item->id);
                            $mounting_sum = 0; $material_sum = 0;
                            foreach ($calculations as $calculation) {
                                $calculation->dealer_canvases_sum = margin($calculation->canvases_sum, 0/*$this->item->gm_canvases_margin*/);
                                $calculation->dealer_components_sum = margin($calculation->components_sum, 0/* $this->item->gm_components_margin*/);
                                $calculation->dealer_gm_mounting_sum = margin($calculation->mounting_sum, 0/* $this->item->gm_mounting_margin*/);
                                $mounting_sum += $calculation->dealer_gm_mounting_sum;
                                $material_sum += $calculation->dealer_components_sum + $calculation->dealer_canvases_sum;
                                }
                                $sum_transport = 0;  $sum_transport_discount = 0;
                                $mountModel = Gm_ceilingHelpersGm_ceiling::getModel('mount');
                                $mount_transport = $mountModel->getDataAll();

                                if($item->transport == 0 ) $sum_transport = 0;
                                if($item->transport == 1 ) $sum_transport = margin($mount_transport->transport * $item->distance_col, $item->gm_mounting_margin);
                                if($item->transport == 2 ) $sum_transport = ($mount_transport->distance * $item->distance + $mount_transport->transport) * $item->distance_col;
                                if($item->transport == 1) {
                                     $min = 100;
                                    foreach($calculations as $d) {
                                        if($d->discount < $min) $min = $d->discount;
                                    }
                                    if  ($min != 100) $sum_transport = $sum_transport * ((100 - $min)/100);
                                    if($sum_transport < margin($mount_transport->transport, $item->gm_mounting_margin) && $sum_transport != 0) {
                                        $sum_transport = margin($mount_transport->transport, $item->gm_mounting_margin);
                                    }
                                }

                                $temp = 0;
                                if($item->check_mount_done == 0) { 
                                    $temp = ($item->new_mount_sum)? $item->new_mount_sum: ($mounting_sum + $sum_transport);
                                    $temp = $temp - $item->new_project_mounting;
                                    $temp_project_sum = $item->project_sum - $item->new_project_sum;
                                    $temp_material_sum = ($material_sum - $item->new_material_sum);
                                    ?>
                                    <input id="<?= $item->id; ?>_project_sum" value="<?php echo ($temp_project_sum <= 0)?0:round(($temp_project_sum), 2); ?>"  hidden>
                                    <input id="<?= $item->id; ?>_mounting_sum" value="<?php echo ($temp <= 0)?0:round($temp, 2); ?>"  hidden>
                                    <input id="<?= $item->id; ?>_material_sum" value="<?php echo ($temp_material_sum <= 0)?0:round($temp_material_sum,2); ?>"  hidden>
                                
                                <?}
                                if($item->check_mount_done == 1) { ?>
                                    <input id="<?= $item->id; ?>_project_sum" value="<?php echo ($item->new_project_sum)?$item->new_project_sum:$item->project_sum; ?>"  hidden>
                                    <input id="<?= $item->id; ?>_mounting_sum" value="<?php echo ($item->new_mount_sum)?$item->new_mount_sum:($mounting_sum + $sum_transport); ?>"  hidden>
                                    <input id="<?= $item->id; ?>_material_sum" value="<?php echo ($item->new_material_sum)?$item->new_material_sum:$material_sum; ?>"  hidden>
                                <?}

                            ?>

                         
                    </td>
                    <td class="center one-touch">
                        <?php echo $item->status; ?>
                        <input id="<?= $item->id; ?>_status" value="<?php echo $item->status; ?>"  hidden>
                    </td>
                    <td class="center one-touch">
                        <?php if($item->project_mounting_date == "0000-00-00 00:00:00") { ?>
                            -
                        <?php } else { ?>
                            <?php $jdate = new JDate($item->project_mounting_date); ?>
                            <?php echo $jdate->format('d.m.Y H:i'); ?>
                        <?php } ?>
                    </td>
                    <td class="center one-touch">
                    <?php if($item->project_calculation_date == "0000-00-00 00:00:00") { ?>
                            -
                        <?php } else { ?>
                            <?php $jdate = new JDate($item->project_calculation_date); ?>
                            <?php echo $jdate->format('d.m.Y H:i'); ?>
                        <?php } ?>
                    </td>
                    <td class="center one-touch">
                        <?php if($item->closed == "0000-00-00") { ?>
                            -
                        <?php } else { ?>
                            <?php $jdate = new JDate($item->closed); ?>
                            <?php echo $jdate->format('d.m.Y'); ?>
                        <?php } ?>
                    </td>
                    <td class="center one-touch">
                        <?php echo $this->escape($item->project_info); ?>
                    </td>
                    <td class="center one-touch">
                        <?php echo $item->client_contacts; ?>
                    </td>
                    <td class="center one-touch">
                        <?php echo $item->client_name; ?>
                    </td>
                    <td class="center one-touch">
                        <?php echo $dealer->name; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <? else: ?>
        <p class="center">
        <h3>У вас еще нет завершенных проектов!</h3>
        </p>
    <? endif; ?>
    <input type="hidden" name="task" value=""/>
    <input type="hidden" name="boxchecked" value="0"/>
    <input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>"/>
    <input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>"/>
    <?php echo JHtml::_('form.token'); ?>
</form>

<script src="https://api.yandex.mightycall.ru/api/v2/sdk/mightycall.webphone.sdk.js"></script>
<script src="/components/com_gm_ceiling/phone.js"></script>

<?php
    $user       = JFactory::getUser();
    $userId     = $user->get('id');
    $user_group = $user->groups;
    $dop_num_model = Gm_ceilingHelpersGm_ceiling::getModel('dop_numbers_of_users');
    $dop_num = $dop_num_model->getData($userId)->dop_number;
    $_SESSION['user_group'] = $user_group;
    $_SESSION['dop_num'] = $dop_num;
?>

<script type="text/javascript">
    // сделать обработку данных, если монтаж был недовыполнен, а сейчас выполнен.
    var $ = jQuery;
    jQuery(document).ready(function () {
        jQuery(".btn-done").click(function(){
			var td = jQuery( this ),
				tr = td.closest("tr");

            var input = jQuery( this ),
				input = input.closest("input"),
				project_sum = input.find(".project_sum");
			var button = jQuery( this );	
			var type = "info",
				value = td.data("value"),
				//new_value = input.data("new_value");
                status = jQuery(this).closest("tr").find("#"+td.data("project_id")+"_status").val(),
                new_value = jQuery(this).closest("tr").find("#"+td.data("project_id")+"_project_sum").val(),
                mouting_sum = jQuery(this).closest("tr").find("#"+td.data("project_id")+"_mounting_sum").val(),
                material_sum = jQuery(this).closest("tr").find("#"+td.data("project_id")+"_material_sum").val();
				var subject = "Отметка стоимости договора №" + td.data("project_id");
				var text = "";
				if (status == "Недовыполнен") {
                    text += "<p><input name='check_mount' onclick='changeDone(this," + new_value + "," + mouting_sum + "," + material_sum + ");' class='radio' id ='done' value='1'  type='radio' checked><label for = 'done'>Монтаж выполнен</label></p>";
                    text += "<p><input name='check_mount' onclick='changeDone(this," + new_value + "," + mouting_sum + "," + material_sum + ");'  class='radio' id ='not_done' value='0'  type='radio'><label for = 'not_done'>Монтаж недовыполнен</label></p>";
                }
                    text += "<div class='dop_info_block' style='font-size:15px;'><div class='center'>Укажите новую стоимость договора</div><div class='center'><input id='input_check' class='noty_input' style='margin-top: 5px;' value='" + new_value + "'/></div></br>";
                    text += "<div class='center'>Укажите новую стоимость расходных материалов</div><div class='center'><input id='input_material' class='noty_input' style='margin-top: 5px;' value='" + material_sum + "'/></div></br>";
                    text += "<div class='center'>Укажите новую стоимость монтажных работ</div><div class='center'><input id='input_mounting' class='noty_input' style='margin-top: 5px;' value='" + mouting_sum + "'/></div>";
                    text += "</div>";
                    
                    //text += "<div class='center'>Укажите новую стоимость договора</div><div class='center'><input id='input_check' class='noty_input' value='" + new_value + "'/></div>";
			



            /* new_value = jQuery("#input_check").val();
             var input_value = jQuery("#input_check").val();
             var input_mounting = jQuery("#input_mounting").val();
             var input_material = jQuery("#input_material").val();*/
			modal({
				type: 'primary',
				title: subject,
				text: text,
				size: 'small',
				buttons: [{
					text: 'Выполнено', //Button Text
					val: 0, //Button Value
					eKey: true, //Enter Keypress
					addClass: 'btn-danger', //Button Classes (btn-large | btn-small | btn-green | btn-light-green | btn-purple | btn-orange | btn-pink | btn-turquoise | btn-blue | btn-light-blue | btn-light-red | btn-red | btn-yellow | btn-white | btn-black | btn-rounded | btn-circle | btn-square | btn-disabled)
					onClick: function(dialog) {
                        /*new_value = jQuery("#input_check").val();
                        mouting_sum = jQuery("#input_mounting").val();
                        material_sum = jQuery("#input_material").val();*/
                        var input_value = jQuery("#input_check").val();
                        var input_mounting = jQuery("#input_mounting").val();
                        var input_mounting_itog = jQuery("#input_mounting_itog").val();
                        var input_material = jQuery("#input_material").val();
                        var check = jQuery("input[name='check_mount']:checked").val();
                        //alert(input_value);
						jQuery.ajax({
							type: 'POST',
							url: "index.php?option=com_gm_ceiling&task=project.done",
							data: {
								project_id : td.data("project_id"),
								new_value : input_value,
                                mouting_sum : input_mounting,
                                mouting_sum_itog : input_mounting_itog,
                                material_sum : input_material,
                                check: check
							},
							success: function(data){
                                if(check == 1) button.closest("td").html("<i class='fa fa-check' aria-hidden='true'></i> Выполнено");
								var n = noty({
									theme: 'relax',
									layout: 'center',
									maxVisible: 5,
									type: "success",
									text: data
								});
                                setInterval(function() { location.reload();}, 1500);
  
							},
							dataType: "text",
							timeout: 10000,
							error: function(data){
                                console.log(data);
								var n = noty({
									theme: 'relax',
									layout: 'center',
									maxVisible: 5,
									type: "error",
									text: "Ошибка при попытке сохранить отметку. Сервер не отвечает"
								});
							}
						});
						return 1;
					}
				},
                {addClass: 'btn', text: 'Отмена', onClick: function($noty) {
							$noty.close();
						}
				}
				],
				callback: null,
				autoclose: false,
				center: true,
				closeClick: true,
				closable: true,
				theme: 'xenon',
				animate: true,
				background: 'rgba(0,0,0,0.35)',
				zIndex: 1050,
				buttonText: {
					ok: 'Поставить',
					cancel: 'Снять'
				},
				template: '<div class="modal-box"><div class="modal-inner"><div class="modal-title"><a class="modal-close-btn"></a></div><div class="modal-text"></div><div class="modal-buttons"></div></div></div>',
				_classes: {
					box: '.modal-box',
					boxInner: ".modal-inner",
					title: '.modal-title',
					content: '.modal-text',
					buttons: '.modal-buttons',
					closebtn: '.modal-close-btn'
				}

			});	

		});
    });


    function changeDone(element, new_value, mouting_sum, material_sum) {
        var text = "";
        element = $(element);
            if ($("#done").is(":checked")) {
                text += "<div class='center'>Укажите новую стоимость договора</div><div class='center'><input id='input_check' class='noty_input' style='margin-top: 5px;' value='" + new_value + "'/></div></br>";
                text += "<div class='center'>Укажите новую стоимость расходных материалов</div><div class='center'><input id='input_material' class='noty_input' style='margin-top: 5px;' value='" + material_sum + "'/></div></br>";
                text += "<div class='center'>Укажите новую стоимость монтажных работ</div><div class='center'><input id='input_mounting' class='noty_input' style='margin-top: 5px;'  value='" + mouting_sum + "'/></div>";
                
                }
            if ($("#not_done").is(":checked")) {
                text += "<div class='center'>Укажите новую стоимость договора</div><div class='center'><input id='input_check' class='noty_input' style='margin-top: 5px;' value='" + new_value + "'/></div></br>";
                text += "<div class='center'>Укажите новую стоимость расходных материалов</div><div class='center'><input id='input_material' class='noty_input' style='margin-top: 5px;' value='" + material_sum + "'/></div></br>";
                text += "<div class='center'>Укажите стоимость монтажных работ!!!</div><br>";
                   // "<div class='center'><input id='input_mounting' class='noty_input' value='" + mouting_sum + "'/></div>";
                text += "<div class='center'><div class='leftBlock' style='width: 41%; float: left;'><span >Начальная сумма</span><input id='input_mounting' style='width: 100%; margin-top: 5px;' value='" + mouting_sum + "'/></div>" +
                    "<div class=\"centerBlock\" style=\"width: 5%;float: left;\"><br><span> - </span></div>"+
                    "<div class='rightBlock' style='width: 54%;float: left;'><span >Недоделанная работа</span><input id='input_mounting_1' style='width: 100%;  margin-top: 5px;' value='0'/></div></div>";
                text += "<div class='center'>Итоговая сумма недоделанного монтажа</div><div class='center'><input id='input_mounting_itog' class='noty_input' style='margin-top: 5px;' value='" + mouting_sum + "'/></div></br>";
            }
            element.closest(".modal-text").find(".dop_info_block").html(text);

        $("#input_mounting_1, #input_mounting").keyup(function(){
            var one = $("#input_mounting").val();
            var two = $("#input_mounting_1").val();
            var result = one - two;
            $("#input_mounting_itog").val(result);
           // alert(one + " - " + two + " = " + result);
        });
    }



    function deleteItem() {
        if (!confirm("<?php echo JText::_('COM_GM_CEILING_DELETE_MESSAGE'); ?>")) {
            return false;
        }
    }
</script>

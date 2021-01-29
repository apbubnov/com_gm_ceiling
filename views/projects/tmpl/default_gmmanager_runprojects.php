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
$model = Gm_ceilingHelpersGm_ceiling::getModel('calculations');
?>
<style>
    .table th {
        padding: 0.5rem;
    }
    .table td {
        padding: 0.5rem;
    }
</style>
<button class="btn btn-primary" id="btn_back"><i class="fa fa-arrow-left" aria-hidden="true"></i>Назад</button>
<h2 class = "center">Запущенные проекты</h2>
<form action="<?php echo JRoute::_('index.php?option=com_gm_ceiling&view=projects&type=gmmanager&subtype=runprojects'); ?>" method="post" name="adminForm" id="adminForm">
    <table class="table table-striped one-touch-view g_table" id="projectList">
        <thead>
            <tr>
                <th class='center'>
                    Номер договора
                </th>
                <th class='center'>
                    Статус
                </th>
                <th class='center'>
                    Дата замера
                </th>
                <th class='center'>
                    Дата монтажа
                </th>
                <th class='center'>
                    Адрес
                </th>
                <th class='center'>
                    Клиент
                </th>
                <th class="center">
                    Дилер
                </th>
            </tr>
        </thead>
        <tbody>
        <?php
            $this_items = $this->items;
            $items_length = count($this_items);
        ?>
        <?php for ($i = 0; $i < $items_length; $i++) {
            $item = $this_items[$i];
            $client_id = $item->client_id;
            $model_client = Gm_ceilingHelpersGm_ceiling::getModel('client');
            $client = $model_client->getClientById($client_id);
            $dealer = JFactory::getUser($client->dealer_id);
        ?>
            <tr data-href="<?php echo JRoute::_('index.php?option=com_gm_ceiling&view=project&type=gmmanager&subtype=run&id='.(int) $item->id); ?>">
                <td class="center one-touch">
                    <input id="<?= $item->id; ?>_id" value="<?php echo $item->id; ?>"  hidden>
                    <?php echo $item->id;?>
                </td>
                <td class="center one-touch">
                    <?php echo $item->status; ?>
                    <input id="<?= $item->id; ?>_status" value="<?php echo $item->status; ?>"  hidden>
                </td>
                <td class="center one-touch">
                    <?php
                        if (empty($item->project_calculation_date) || $item->project_calculation_date == '0000-00-00 00:00:00') {
                            echo '-';
                        } else {
                            echo date('d.m.Y h:i', strtotime($item->project_calculation_date));
                        }
                    ?>
                </td>
                <td class="center one-touch">
                    <?php
                        if (empty($item->project_mounting_date) || $item->project_mounting_date == '00.00.0000 00:00') {
                            echo '-';
                        } else {
                            echo $item->project_mounting_date;
                        }
                    ?>
                </td>
                <td class="center one-touch">
                    <?php
                        if (empty($item->project_info)) {
                            echo '-';
                        } else {
                            echo $this->escape($item->project_info);
                        }
                    ?>
                </td>
                <td class="center one-touch">
                    <?php echo $item->client_name.'<br>'.$item->client_contacts; ?>
                </td>
                <td class="center one-touch">
                    <?php echo $dealer->name; ?>
                </td>
            </tr>
        <?php } ?>
        </tbody>
    </table>
    <input type="hidden" name="task" value=""/>
    <input type="hidden" name="boxchecked" value="0"/>
    <input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>"/>
    <input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>"/>
    <?php echo JHtml::_('form.token'); ?>
</form>

<?php
    $user       = JFactory::getUser();
    $userId     = $user->get('id');
    $user_group = $user->groups;
?>

<script type="text/javascript">
    var $ = jQuery;

    jQuery(document).mouseup(function (e){ // событие клика по веб-документу
        var div = jQuery("#modal_window_del"); // тут указываем ID элемента
        if (!div.is(e.target) // если клик был не по нашему блоку
            && div.has(e.target).length === 0) { // и не по его дочерним элементам
            jQuery("#close").hide();
            jQuery(".modal_window_container").hide();
            jQuery("#modal_window_del").hide();
        }
    });


    function submit_form(e) {
        jQuery(".modal_window_container, .modal_window_container *").show();
        jQuery('.modal_window_container').addClass("submit");
    }

    function click_ok(id) {

        var button = jQuery("[data-project_id='" + id + "']");	
        //console.log(button);
        var project_id = id;
        //td = jQuery( this );
        //var project_id =  td.data("project_id");
        var input_value = jQuery("#input_check").val();
        var input_mounting = jQuery("#input_mounting").val();
        var input_mounting_itog = jQuery("#input_mounting_itog").val();
        var input_material = jQuery("#input_material").val();
        var check = jQuery("input[name='check_mount']:checked").val();
        if (check == undefined) { check = 1; }
        else if (check == 1){ check = 1;}
        else check = 0;

        //alert(input_value);
        jQuery.ajax({
            type: 'POST',
            url: "index.php?option=com_gm_ceiling&task=project.done",
            data: {
                //project_id : td.data("project_id"),
                project_id : project_id,
                new_value : input_value,
                mouting_sum : input_mounting,
                mouting_sum_itog : input_mounting_itog,
                material_sum : input_material,
                check: check
            },
            success: function(data){
                button.closest("td").html("<i class='fa fa-check' aria-hidden='true'></i> Выполнено");
                var n = noty({
                    theme: 'relax',
                    layout: 'center',
                    maxVisible: 5,
                    type: "success",
                    text: data
                });
                $("#modal-window").hide();

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

    function click_cancel(e) {
        jQuery("#modal_window_container_"+e+", #modal_window_container_"+e+" *").hide();
    }

    jQuery(document).ready(function () {
        jQuery('#btn_back').click(function(){
                location.href = "/index.php?option=com_gm_ceiling&task=mainpage";
            });
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
                cost_price = jQuery(this).closest("tr").find("#"+td.data("project_id")+"_cost_price").val(),
                new_project_sum = jQuery(this).closest("tr").find("#"+td.data("project_id")+"_new_project_sum").val(),
                status = jQuery(this).closest("tr").find("#"+td.data("project_id")+"_status").val(),
                new_value = jQuery(this).closest("tr").find("#"+td.data("project_id")+"_project_sum").val(),
                mouting_sum = jQuery(this).closest("tr").find("#"+td.data("project_id")+"_mounting_sum").val(),
                material_sum = jQuery(this).closest("tr").find("#"+td.data("project_id")+"_material_sum").val();
				var subject = "Отметка стоимости договора №" + td.data("project_id");
				var text = "";
				if (status == "Недовыполнен") {
                    text += "<p><input name='check_mount' onclick='changeDone(this," + new_value + "," + mouting_sum + "," + material_sum + ");' class='radio' id ='done' value='1'  type='radio' checked><label for = 'done'>Монтаж выполнен</label></p>";
                    text += "<p><input name='check_mount' onclick='changeDone(this," + new_value + "," + mouting_sum + "," + material_sum + ");'  class='radio' id ='not_done' value='0'  type='radio' ><label for = 'not_done'>Монтаж недовыполнен</label></p>";
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
					//addClass: 'btn-danger', //Button Classes (btn-large | btn-small | btn-green | btn-light-green | btn-purple | btn-orange | btn-pink | btn-turquoise | btn-blue | btn-light-blue | btn-light-red | btn-red | btn-yellow | btn-white | btn-black | btn-rounded | btn-circle | btn-square | btn-disabled)
					onClick: function(dialog) {
                        var input_value = jQuery("#input_check").val();
                        var input_mounting = jQuery("#input_mounting").val();
                        var input_mounting_itog = jQuery("#input_mounting_itog").val();
                        var input_material = jQuery("#input_material").val();
                        var check = jQuery("input[name='check_mount']:checked").val();
                        //Просчет прибыли
                        new_project_sum = (new_project_sum === "")?0:new_project_sum;
                        input_value = (input_value === "")?0:input_value;
                        cost_price = (cost_price === "")?0:cost_price;
                        var profit = parseFloat(new_project_sum) + parseFloat(input_value) - parseFloat(cost_price); 

                        if (profit < 0 && (check == undefined || check == 1)) 
                        {
                            //check = 1; jQuery("#modal_window_container_"+ td.data("project_id")+", #modal_window_container_" + td.data("project_id") +" *").show();
                            if (check == undefined) { check = 1; jQuery("#modal_window_container_"+ td.data("project_id")+", #modal_window_container_" + td.data("project_id") +" *").show();}
                        else if (check == 1){ check = 1; jQuery("#modal_window_container_"+ td.data("project_id")+", #modal_window_container_" + td.data("project_id") +" *").show();}
                        }
                        else { 
                            
                            //check = 0;

                            if (check == undefined) { check = 1;}
                            else if (check == 1){ check = 1;}
                            else check = 0;

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
                                    if(check == 0) setInterval(function() { location.reload();}, 1500);

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


                        /*new_value = jQuery("#input_check").val();
                        mouting_sum = jQuery("#input_mounting").val();
                        material_sum = jQuery("#input_material").val();*/

					
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
                text += "<div class='center'>Укажите остальную сумму договора</div><div class='center'><input id='input_check' class='noty_input' style='margin-top: 5px;' value='" + new_value + "'/></div></br>";
                text += "<div class='center'>Укажите остальную сумму расходных материалов</div><div class='center'><input id='input_material' class='noty_input' style='margin-top: 5px;' value='" + material_sum + "'/></div></br>";
                text += "<div class='center'>Укажите остальную сумму монтажных работ</div><div class='center'><input id='input_mounting' class='noty_input' style='margin-top: 5px;'  value='" + mouting_sum + "'/></div>";
                
                }
            if ($("#not_done").is(":checked")) {
                text += "<div class='center'>Укажите стоимость договора</div><div class='center'><input id='input_check' class='noty_input' style='margin-top: 5px;' value='" + new_value + "'/></div></br>";
                text += "<div class='center'>Укажите стоимость расходных материалов</div><div class='center'><input id='input_material' class='noty_input' style='margin-top: 5px;' value='" + material_sum + "'/></div></br>";
                text += "<div class='center'>Укажите стоимость монтажных работ</div><br>";
                   // "<div class='center'><input id='input_mounting' class='noty_input' value='" + mouting_sum + "'/></div>";
                text += "<div class='center'><div class='leftBlock' style='width: 41%; float: left;'><span >Исходная сумма</span><input id='input_mounting' style='width: 100%; margin-top: 5px;' value='" + mouting_sum + "'/></div>" +
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

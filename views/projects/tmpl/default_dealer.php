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
//JHtml::_('formbehavior.chosen', 'select');

$user = JFactory::getUser();
$userId = $user->get('id');
$listOrder = $this->state->get('list.ordering');
$listDirn = $this->state->get('list.direction');
$canCreate = $user->authorise('core.create', 'com_gm_ceiling') && file_exists(JPATH_COMPONENT . DIRECTORY_SEPARATOR . 'models' . DIRECTORY_SEPARATOR . 'forms' . DIRECTORY_SEPARATOR . 'projectform.xml');
$canEdit = $user->authorise('core.edit', 'com_gm_ceiling') && file_exists(JPATH_COMPONENT . DIRECTORY_SEPARATOR . 'models' . DIRECTORY_SEPARATOR . 'forms' . DIRECTORY_SEPARATOR . 'projectform.xml');
$canCheckin = $user->authorise('core.manage', 'com_gm_ceiling');
$canChange = $user->authorise('core.edit.state', 'com_gm_ceiling');
$canDelete = $user->authorise('core.delete', 'com_gm_ceiling');

?>
<?=parent::getButtonBack(); ?>
<h2 class="center">Договоры</h2>
<form action="<?php echo JRoute::_('index.php?option=com_gm_ceiling&view=projects&type=dealer'); ?>" method="post"
      name="adminForm" id="adminForm">
    <div class="toolbar">
        <?php echo JLayoutHelper::render('default_filter', array('view' => $this), dirname(__FILE__)); ?>
    </div>
    <table class="table" id="projectList_fixed" style="display: none;">
        <thead>
        <tr>
            <th class='center' style="width: 4%">
                №
            </th>
            <th class="center" style="width: 8%">
                Адрес
            </th>
            <th class="center" style="width: 8%">
                Статус
            </th>
            <th class="center">
                Дата замера
            </th>
            <th class="center">
                Дата монтажа
            </th>
            <th class="center">
                Последнее примечание
            </th>
            <th class="center">
                Сумма договора
            </th>
            <th class="center">
                Прибыль
            </th>
        </tr>
        </thead>
    </table>
    <table class="table one-touch-view" id="projectList">
        <thead>
        <tr>
            <th class='center' style="width: 4%">
                №
            </th>
            <th class="center" style="width: 8%">
                Адрес
            </th>
            <th class="center" style="width: 8%">
                Статус
            </th  class="center">
            <th class="center">
                Дата замера
            </th>
            <th class="center">
                Дата монтажа
            </th>
            <th class="center">
                Последнее примечание
            </th>
            <th class="center">
                Сумма договора
            </th>
            <th class="center">
                Прибыль
            </th>
        </tr>
        </thead>

        <tbody>
        <?php foreach ($this->items as $i => $item) : ?>
            <?php
            $canEdit = $user->authorise('core.edit', 'com_gm_ceiling');
            $dealer = JFactory::getUser($item->dealer_id);
            if (!$canEdit && $user->authorise('core.edit.own', 'com_gm_ceiling')) {
                $canEdit = JFactory::getUser()->id == $item->created_by;
            }

            $extra_spend_array = Gm_ceilingHelpersGm_ceiling::decode_extra($item->extra_spend);
            $extra_spend_sum = 0;
            foreach ($extra_spend_array as $each) {
                $extra_spend_sum += (int)$each['value'];
            }


            $model = Gm_ceilingHelpersGm_ceiling::getModel('calculations');
            $calculations = $model->getProjectItems($item->id);
            $canvases_sum = 0;
            $components_sum = 0;
            $mounting_sum = 0;
            $dealer_mounting_sum = 0;
            $project_total = 0;
            $project_total_discount = 0;
            $earn = 0;
            $material_sum = 0;
            foreach ($calculations as $calculation) {
                $calculation->dealer_canvases_sum = double_margin($calculation->canvases_sum, $item->gm_canvases_margin, $item->dealer_canvases_margin);
                $calculation->dealer_components_sum = double_margin($calculation->components_sum, $item->gm_components_margin, $item->dealer_components_margin);
                $calculation->dealer_mounting_sum = double_margin($calculation->mounting_sum, $item->gm_mounting_margin, $item->dealer_mounting_margin);


                $calculation->calculation_total = round($calculation->dealer_canvases_sum + $calculation->dealer_components_sum + $calculation->dealer_mounting_sum, 2);
                $calculation->calculation_total_discount = round($calculation->calculation_total * ((100 - $calculation->discount) / 100), 2);
                $project_total += $calculation->calculation_total;
                $project_total_discount += $calculation->calculation_total_discount;
            }
            foreach ($calculations as $calculation) {
                $canvases_sum += $calculation->canvases_sum;
                $components_sum += $calculation->components_sum;
                $mounting_sum += $calculation->mounting_sum;
               // $dealer_mounting_sum += $calculation->dealer_mounting_sum;
            }
            $sum_transport = 0;  $sum_transport_1 = 0;
            $mountModel = Gm_ceilingHelpersGm_ceiling::getModel('mount');
            $mount_transport = $mountModel->getDataAll();

                  if($item->transport == 0 ) $sum_transport = 0;
            if($item->transport == 1 ) $sum_transport = $mount_transport->transport * $item->distance_col;
            if($item->transport == 2 ) $sum_transport = $mount_transport->distance * $item->distance * $item->distance_col;
            /*$min = 100;
            foreach($calculations as $d) {
                if($d->discount < $min) $min = $d->discount;
            }
            if  ($min != 100) $sum_transport = $sum_transport * ((100 - $min)/100);
            if($sum_transport < $mount_transport->transport && $sum_transport != 0) {
                $sum_transport = $mount_transport->transport;
            }*/

            if($item->transport == 0 ) $sum_transport_1 = 0;
            if($item->transport == 1 ) $sum_transport_1 = double_margin($mount_transport->transport * $item->distance_col, $item->gm_mounting_margin, $item->dealer_mounting_margin);
            if($item->transport == 2 ) $sum_transport_1 = double_margin($mount_transport->distance * $item->distance * $item->distance_col, $item->gm_mounting_margin, $item->dealer_mounting_margin);
            if  ($min != 100) $sum_transport_1 = $sum_transport_1 * ((100 - $min)/100);
            if($sum_transport_1 < double_margin($mount_transport->transport, $item->gm_mounting_margin, $item->dealer_mounting_margin) && $sum_transport_1 != 0) {
                $sum_transport_1 = double_margin($mount_transport->transport, $item->gm_mounting_margin, $item->dealer_mounting_margin);
            }
            $material_sum = $canvases_sum + $components_sum;
            $project_total_discount = $project_total_discount +  $sum_transport_1;
            if ($mounting_sum != 0) $mounting_sum = $mounting_sum + $sum_transport;
            if(!empty($item->new_mount_sum)) $mounting_sum = $item->new_mount_sum;
            if(!empty($item->new_material_sum)) $material_sum = $item->new_material_sum;

            if($canvases_sum > 0) {
                if($project_total <= 3500 && $mounting_sum != 0 && $mounting_sum <= 1500 ) {$project_total = 3500; $mounting_sum = 1500;}
                if($project_total >= 3500 &&  $mounting_sum != 0 && $mounting_sum <= 1500) { $mounting_sum = 1500; }
                if($project_total <= 3500 &&  $mounting_sum != 0 && $mounting_sum >= 1500) { $project_total = 3500; }

                if($project_total_discount <= 3500 && $mounting_sum != 0 && $mounting_sum <= 1500 ) {$project_total_discount = 3500; $mounting_sum = 1500;}
                if($project_total_discount >= 3500 &&  $mounting_sum != 0 && $mounting_sum <= 1500) { $mounting_sum = 1500; }
                if($project_total_discount <= 3500 &&  $mounting_sum != 0 && $mounting_sum >= 1500) { $project_total_discount = 3500; }
            }
            else {
                if($project_total <= 2500 && $mounting_sum != 0 && $mounting_sum <= 1500 ) {$project_total = 2500; $mounting_sum = 1500;}
                if($project_total >= 2500 &&  $mounting_sum != 0 && $mounting_sum <= 1500) { $mounting_sum = 1500; }
                if($project_total <= 2500 &&  $mounting_sum != 0 && $mounting_sum >= 1500) { $project_total = 2500; }

                if($project_total_discount <= 2500 && $mounting_sum != 0 && $mounting_sum <= 1500 ) {$project_total_discount = 2500; $mounting_sum = 1500;}
                if($project_total_discount >= 2500 &&  $mounting_sum != 0 && $mounting_sum <= 1500) { $mounting_sum = 1500; }
                if($project_total_discount <= 2500 &&  $mounting_sum != 0 && $mounting_sum >= 1500) { $project_total_discount = 2500; }
            }
            
            /*if ($item->who_mounting == 0) {
                $earn = $project_total_discount - ($canvases_sum + $components_sum + $mounting_sum) - $extra_spend_sum;
            } else {
                $earn = $project_total_discount - ($canvases_sum + $components_sum + $mounting_sum) - $extra_spend_sum;
            }*/
            if ($mounting_sum == 0) $mounting_sum = $mounting_sum + $sum_transport;
            if($item->new_project_sum > 0) $earn = $item->new_project_sum - ($material_sum + $mounting_sum) - $extra_spend_sum;
            elseif($project_total_discount > 0) $earn = $project_total_discount - ($material_sum + $mounting_sum) - $extra_spend_sum;
            elseif($project_total_discount = 0) $earn = 0;

            if( $earn > 0 && $item->new_project_sum > 0) {
                $earn_procent = round(100 * $earn / $item->new_project_sum, 2);
            }
            else if( $earn > 0 && $project_total_discount > 0)
                $earn_procent = round(100 * $earn / $project_total_discount, 2);
            else  $earn_procent = 0;
           /* if ($earn > 0 && $project_total_discount > 0) {
                $earn_procent = round(100 * $earn / $project_total_discount, 2);
            } else {
                $earn_procent = 0;
            }
*/
            if ($earn_procent < 20) {
                $earn_procent_class = "too_low";
            } else {
                $earn_procent_class = "";
            }
            $spend = $canvases_sum + $components_sum;
            if($item->project_status == 1)  $status_class = "too_low";
            elseif($item->project_status == 2 || $item->project_status == 3 || $item->project_status == 15)  $status_class = "red";
            elseif($item->project_status == 5 || $item->project_status == 10 || $item->project_status == 11)  $status_class = "blue";
            elseif($item->project_status == 12)  $status_class = "green";
            else $status_class =""
            ?>

            <tr data-href="<?php echo JRoute::_('index.php?option=com_gm_ceiling&view=project&type=dealer&id=' . (int)$item->id); ?>"
                class="stat<?php echo $item->id?> status<?php echo $item->project_status;?>  <?php echo $status_class;?>" >
                <td class="center check<?php if ($item->project_check) {
                    echo " checked";
                } ?>" data-type="0" data-project_id="<?php echo $item->id; ?>">
                    <?php echo $item->id; ?>
                </td>
                <td class="center one-touch">
                    <?php echo $item->project_info; ?>
                </td>
                <!-- Статус-->
                <td class="center">
                <?php $projects_model = Gm_ceilingHelpersGm_ceiling::getModel('projects');
                    $status = $projects_model->getStatus();?>
                    <select class="change_status" name="change_status" data-id="<?php echo $item->id; ?>">
                        <?php foreach ($status as $i) { ?>
                        <?if ($i->id == 9 && ($userId == 2 || $userId == 1)) {?>
                            <option value="<?php echo $i->id; ?>"<?php if ($item->project_status == $i->id) {
                                echo " selected";
                            } ?> ><?php echo $i->title; ?></option>
                        <?php } ?>
                        <?if ($i->id != 9 && ($userId != 2 || $userId != 1)) {?>
                            <option value="<?php echo $i->id; ?>"<?php if ($item->project_status == $i->id) {
                                echo " selected";
                            } ?>><?php echo $i->title; ?></option>
                        <?}?>
                    <?}?>
                    </select>
                </td>
                <td>
                    <?php if($item->project_calculation_date== '0000-00-00 00:00:00') { ?>
                        -
                    <?php } else { ?>
                        <?php $jdate = new JDate(JFactory::getDate($item->project_calculation_date)); ?>
                        <?php echo $jdate->format('d.m.Y'); ?>
                    <?php } ?>
                 <?php //echo $item->project_calculation_date ?>
                </td>
                 <td class="center one-touch">
                    <?php $date = $item->project_mounting_date; ?>
                    <? if ($date == "00.00.0000 00:00"): ?> -
                    <? else: ?><?= $date; ?>
                    <? endif; ?>
                </td>
                
                <td>
                    <?php if ($item->dealer_id == 1) {
                        if ($item->gm_chief_note) { ?>
                            <div class="center"><?php echo $item->gm_chief_note; ?></div>
                        <?php } elseif ($item->gm_manager_note) { ?>
                            <div class="center"><?php echo $item->gm_manager_note; ?></div>
                        <?php } elseif ($item->gm_calculator_note) { ?>
                            <div class="center"><?php echo $item->gm_calculator_note; ?></div>
                        <?php } else { ?>
                            <div class=""><?php echo $item->project_note; ?></div>
                        <?php }
                    } else {
                        if ($item->dealer_chief_note) { ?>
                            <div class="center"><?php echo $item->dealer_chief_note; ?></div>
                        <?php } elseif ($item->dealer_manager_note) { ?>
                            <div class="center"><?php echo $item->dealer_manager_note; ?></div>
                        <?php } elseif ($item->dealer_calculator_note) { ?>
                            <div class="center"><?php echo $item->dealer_calculator_note; ?></div>
                        <?php } else { ?>
                            <div class=""><?php echo $item->project_note; ?></div>
                        <?php }
                    } ?>
                </td>
                <td class="center">
                    <?php if ($item->new_project_sum > 0 && $project_total_discount != $item->new_project_sum) { ?>
                        <div class="" style="text-decoration: line-through;"><?php echo $project_total_discount; ?></div>
                        <div class=""><?php echo $item->new_project_sum; ?></div>
                    <?php } else { ?>
                        <div class=""><?php echo $project_total_discount; ?></div>
                    <?php } ?>

                </td>
                <td class="center earn one-touch" data-value="<?php echo round($earn, 2); ?>">
                    <?php echo round($earn, 2); ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <input type="hidden" name="task" value=""/>
    <input type="hidden" name="boxchecked" value="0"/>
    <input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>"/>
    <input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>"/>
    <?php echo JHtml::_('form.token'); ?>
</form>

<script type="text/javascript">

    jQuery(document).ready(function () {
        jQuery(".change_status").change(function () {
            var id = jQuery(this).data("id"),
                status = jQuery(this).val(),
                tr = jQuery(this).closest("tr");
        
            jQuery.ajax({
                type: 'POST',
                url: "index.php?option=com_gm_ceiling&task=change_status&id=" + id + "&project_status=" + status,
                success: function (data) {
                    console.log("index.php?option=com_gm_ceiling&task=change_status&id=" + id + "&project_status=" + status);
                    if (data == 1) {
                        var n = noty({
                            theme: 'relax',
                            layout: 'center',
                            maxVisible: 5,
                            type: "success",
                            text: "Статус изменен"
                        });
                    } else {
                        var n = noty({
                            theme: 'relax',
                            layout: 'center',
                            maxVisible: 5,
                            type: "error",
                            text: "Ошибка при попытке изменить статус."
                        });
                    }
                    
                    if (status == 1) {  jQuery(".stat"+id).attr('class','stat' + id + ' '+'status'+status+' '+ 'too_low'); }
                    else if (status == 2 || status == 3 || status == 15 ) {  jQuery(".stat"+id).attr('class','stat' + id + ' '+'status'+status+' '+ 'red'); }
                    else if (status == 5 || status == 10 || status == 11 ) {  jQuery(".stat"+id).attr('class','stat' + id + ' '+'status'+status+' '+ 'blue'); }
                    else if (status == 12) {  jQuery(".stat"+id).attr('class','stat' + id + ' '+'status'+status+' '+ 'green'); }
                    else { jQuery(".stat"+id).attr('class','stat'+id + ' ' +'status'); }

                },
                dataType: "text",
                timeout: 10000,
                error: function () {
                    var n = noty({
                        theme: 'relax',
                        layout: 'center',
                        maxVisible: 5,
                        type: "error",
                        text: "Ошибка при попытке изменить статус. Сервер не отвечает"
                    });
                }
            });
        });

        jQuery("#projectList_fixed").css("width", jQuery("#projectList_fixed").width());

        jQuery(window).resize(function () {
            jQuery("#projectList_fixed").css("width", jQuery("#projectList_fixed").width());
        });

        jQuery(window).scroll(function () {
            if (jQuery(this).scrollTop() > 250) {
                jQuery("#projectList_fixed").fadeIn();
            } else {
                jQuery("#projectList_fixed").fadeOut();
            }
            jQuery("#projectList_fixed").css("width", jQuery("#projectList_fixed").width());
        });

        jQuery(".check").click(function () {

            var td = jQuery(this),
                tr = td.closest("tr"),
                earn = tr.find(".earn"),
                earn_procent = tr.find(".earn_procent"),
                extra_spend_sum = tr.find(".extra_spend_sum"),
                project_sum = tr.find(".project_sum"),
                spend_sum = tr.find(".spend_sum"),
                mounting_sum = tr.find(".mounting_sum");

            var type = "info",
                value = td.data("value"),
                new_value = td.data("new_value");
            if (td.data("type") == 0) {
                var text = "Отметка договора",
                    subject = "Отметка договора";
            } else if (td.data("type") == 1) {
                var subject = "Отметка суммы договора",
                    text = "<div class='center'>Укажите новую сумму договора</div><div class='center'><input id='input_check' class='noty_input' value='" + new_value + "'/></div>'";
            } else if (td.data("type") == 2) {
                var subject = "Отметка себестоимости договора",
                    text = "<div class='center'>Укажите новую себестоимость договора</div><div class='center'><input id='input_check' class='noty_input' value='" + new_value + "'/></div>'";
            } else if (td.data("type") == 3) {
                var subject = "Отметка суммы монтажникам",
                    text = "<div class='center'>Укажите новую сумму монтажникам</div><div class='center'><input id='input_check' class='noty_input' value='" + new_value + "'/></div>'";
            } else if (td.data("type") == 4) {
                var subject = "Отметка суммы доп. расходов",
                    text = "<div class='center'>Укажите новую сумму доп. расходов</div><div class='center'><input id='input_check' class='noty_input' value='" + new_value + "'/></div>'";
            }

            modal({
                type: 'primary',
                title: subject,
                text: text,
                size: 'small',
                buttons: [{
                    text: 'Снять', //Button Text
                    val: 0, //Button Value
                    eKey: true, //Enter Keypress
                    addClass: 'btn-danger', //Button Classes (btn-large | btn-small | btn-green | btn-light-green | btn-purple | btn-orange | btn-pink | btn-turquoise | btn-blue | btn-light-blue | btn-light-red | btn-red | btn-yellow | btn-white | btn-black | btn-rounded | btn-circle | btn-square | btn-disabled)
                    onClick: function (dialog) {
                        var input_value = jQuery("#input_check").val(),
                            check = 0;
                        jQuery.ajax({
                            type: 'POST',
                            url: "index.php?option=com_gm_ceiling&task=check_project",
                            data: {
                                id: td.data("project_id"),
                                type: td.data("type"),
                                check: check,
                                new_value: input_value
                            },
                            success: function (data) {
                                if (check == 1) {
                                    td.addClass("checked");
                                } else {
                                    td.removeClass("checked");
                                }

                                if (td.data("type") != 0) {
                                    if (input_value > 0) {
                                        td.html("<div class='strikeout'>" + value + "</div><div class=''>" + input_value + "</div>");
                                    } else {
                                        td.html("<div class=''>" + value + "</div>");
                                    }
                                    td.data("value", value).attr("value", value);
                                    td.data("new_value", input_value).attr("new_value", input_value);

                                    if (project_sum.data("new_value") > 0) {
                                        var project_sum_value = project_sum.data("new_value");
                                    } else {
                                        var project_sum_value = project_sum.data("value");
                                    }

                                    if (spend_sum.data("new_value") > 0) {
                                        var spend_sum_value = spend_sum.data("new_value");
                                    } else {
                                        var spend_sum_value = spend_sum.data("value");
                                    }

                                    if (mounting_sum.data("new_value") > 0) {
                                        var mounting_sum_value = mounting_sum.data("new_value");
                                    } else {
                                        var mounting_sum_value = mounting_sum.data("value");
                                    }


                                    if (extra_spend_sum.data("new_value") > 0) {
                                        var extra_spend_sum_value = extra_spend_sum.data("new_value");
                                    } else {
                                        var extra_spend_sum_value = extra_spend_sum.data("value");
                                    }

                                    earn_value = project_sum_value - spend_sum_value - mounting_sum_value - extra_spend_sum_value;


                                    if (earn_value > 0 && project_sum_value > 0) {
                                        earn_procent_value = 100 * earn_value / project_sum_value;
                                    } else {
                                        earn_procent_value = 0;
                                    }
                                    earn.text(earn_value.toFixed(2)).attr("value", earn_value.toFixed(2));

                                    earn_procent.removeClass("too_low");
                                    if (earn_procent_value < 20) {
                                        earn_procent.addClass("too_low");
                                    }
                                    earn_procent.text(earn_procent_value.toFixed(2)).attr("value", earn_procent_value.toFixed(2));
                                }
                            },
                            dataType: "text",
                            timeout: 10000,
                            error: function () {
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
                    {
                        text: 'Поставить', //Button Text
                        val: '1', //Button Value
                        eKey: true, //Enter Keypress
                        addClass: 'btn-light-blue', //Button Classes (btn-large | btn-small | btn-green | btn-light-green | btn-purple | btn-orange | btn-pink | btn-turquoise | btn-blue | btn-light-blue | btn-light-red | btn-red | btn-yellow | btn-white | btn-black | btn-rounded | btn-circle | btn-square | btn-disabled)
                        onClick: function (dialog) {
                            var input_value = jQuery("#input_check").val(),
                                check = 1;
                            jQuery.ajax({
                                type: 'POST',
                                url: "index.php?option=com_gm_ceiling&task=check_project",
                                data: {
                                    id: td.data("project_id"),
                                    type: td.data("type"),
                                    check: check,
                                    new_value: input_value
                                },
                                success: function (data) {
                                    if (check == 1) {
                                        td.addClass("checked");
                                    } else {
                                        td.removeClass("checked");
                                    }

                                    if (td.data("type") != 0) {
                                        if (input_value > 0) {
                                            td.html("<div class='strikeout'>" + value + "</div><div class=''>" + input_value + "</div>");
                                        } else {
                                            td.html("<div class=''>" + value + "</div>");
                                        }
                                        td.data("value", value).attr("value", value);
                                        td.data("new_value", input_value).attr("new_value", input_value);

                                        if (project_sum.data("new_value") > 0) {
                                            var project_sum_value = project_sum.data("new_value");
                                        } else {
                                            var project_sum_value = project_sum.data("value");
                                        }

                                        if (spend_sum.data("new_value") > 0) {
                                            var spend_sum_value = spend_sum.data("new_value");
                                        } else {
                                            var spend_sum_value = spend_sum.data("value");
                                        }

                                        if (mounting_sum.data("new_value") > 0) {
                                            var mounting_sum_value = mounting_sum.data("new_value");
                                        } else {
                                            var mounting_sum_value = mounting_sum.data("value");
                                        }


                                        if (extra_spend_sum.data("new_value") > 0) {
                                            var extra_spend_sum_value = extra_spend_sum.data("new_value");
                                        } else {
                                            var extra_spend_sum_value = extra_spend_sum.data("value");
                                        }

                                        earn_value = project_sum_value - spend_sum_value - mounting_sum_value - extra_spend_sum_value;


                                        if (earn_value > 0 && project_sum_value > 0) {
                                            earn_procent_value = 100 * earn_value / project_sum_value;
                                        } else {
                                            earn_procent_value = 0;
                                        }
                                        earn.text(earn_value.toFixed(2)).attr("value", earn_value.toFixed(2));

                                        earn_procent.removeClass("too_low");
                                        if (earn_procent_value < 20) {
                                            earn_procent.addClass("too_low");
                                        }
                                        earn_procent.text(earn_procent_value.toFixed(2)).attr("value", earn_procent_value.toFixed(2));
                                    }
                                },
                                dataType: "text",
                                timeout: 10000,
                                error: function () {
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
                    }],
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
</script>
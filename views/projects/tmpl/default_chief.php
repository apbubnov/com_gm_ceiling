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

$user = JFactory::getUser();
$userId = $user->get('id');
$groups = $user->get('groups');


foreach ($this->items as $i => $item){
    if(!empty($item->project_mounter)){
        $item->project_mounter = explode(',',$item->project_mounter);
    }
}
usort($this->items,function($a,$b){
    $date1 = new DateTime(explode(',',$a->project_mounting_date)[0]);
    $date2 = new DateTime(explode(',',$b->project_mounting_date)[0]);
    if($date1 == $date2){
        return 0;
    }
    return ($date2>$date1) ? 1 : -1;
});
$model = Gm_ceilingHelpersGm_ceiling::getModel('calculations');
?>
<style type="text/css">
    .table
    {
        border-collapse:separate;
        border-spacing:0 0.5em;
    }

</style>
<?= parent::getButtonBack(); ?>
<h4 class="center" style="margin-bottom: 1em;">Запущенные в производство, назначенные на монтаж</h4>
<div class="container">
    <div class="row ">
        <div class="col-md-1" style="vertical-align:middle"> Заказ: </div>
        <div class="col-md-2">
             оплачен<hr style="background-color:green;color:green;height:2px;">
        </div>
        <div class="col-md-2">
            не оплачен <hr style="background-color:red;color:red;height:2px;">
        </div>
    </div>
</div>
<form action="<?= JRoute::_('index.php?option=com_gm_ceiling&view=projects&type=chief'); ?>" method="post" name="adminForm" id="adminForm">
    <?php if (count($this->items) > 0): ?>
        <table class="table table-striped one-touch-view g_table" id="projectList">
            <?php if ($user->dealer_type != 2): ?>
                <thead>
                    <th class='center'></th>
                    <th class='center'>№</th>
                    <th class="center">Статус</th>
                    <th class='center'>Дата / время монтажа</th>
                    <th class='center'>Адрес</th>
                    <th class='center'>Клиент</th>
                    <th class="center">Сумма</th>
                    <th class="center">Бригада</th>
                    <?php if (in_array("14", $groups)):?>
                        <th class="center">
                            <i class="fas fa-trash-alt" aria-hidden="true"></i>
                        </th>
                    <?php endif;?>
                </thead>
                <tbody>
                <?php
                foreach ($this->items as $i => $item) :
                    if ($userId == $item->dealer_id || $user->dealer_id == $item->dealer_id):
                        $color =  $item->paid ? 'style="outline: green solid 1px; margin-top:15px;"' : 'style="outline: red solid 1px; margin-top:15px;"';?>
                    <tr data-href="<?= JRoute::_('index.php?option=com_gm_ceiling&view=projectform&type=chief&id=' . (int)$item->id);?>" <?php echo $color?> data-status= "<?php echo $item->project_status;?>">
                        <td>
                            <? if ($item->project_status >= 8): ?>
                                <button class="btn btn-primary btn-sm btn-done" data-project_id="<?= $item->id; ?>" type="button"><i class="fa fa-check-circle"></i></button>
                            <? endif; ?>
                        </td>
                        <td class="center one-touch">
                            <input id="<?= $item->id; ?>_id" value="<?php echo $item->id; ?>"  hidden>
                            <?php
                                echo $item->id;
                                $calculations = $model->new_getProjectItems($item->id);
                                $mounting_sum = 0; $material_sum = 0; $cost_price = 0;$sum_transport = 0;$sum_transport_cost = 0;
                                foreach ($calculations as $calculation) {
                                    $calculation->dealer_canvases_sum = margin($calculation->canvases_sum, 0/*$this->item->gm_canvases_margin*/);
                                    $calculation->dealer_components_sum = margin($calculation->components_sum, 0/* $this->item->gm_components_margin*/);
                                    $calculation->dealer_gm_mounting_sum = margin($calculation->mounting_sum, 0/* $this->item->gm_mounting_margin*/);
                                    $mounting_sum += $calculation->dealer_gm_mounting_sum;
                                    $material_sum += $calculation->dealer_components_sum + $calculation->dealer_canvases_sum;
                                }
                            $cost_price = ($mounting_sum + $material_sum);

                            $mountModel = Gm_ceilingHelpersGm_ceiling::getModel('mount');
                            $mount_transport = $mountModel->getDataAll();

                            if($item->transport == 1 ) {
                                $sum_transport = margin($mount_transport->transport * $item->distance_col, $item->gm_mounting_margin);
                                $sum_transport_cost = $mount_transport->transport * $item->distance_col; }
                            if($item->transport == 2 ) {
                                $sum_transport = ($mount_transport->distance * $item->distance + $mount_transport->transport) * $item->distance_col;
                                $sum_transport_cost = $sum_transport; }
                            $cost_price = $cost_price + $sum_transport_cost;

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

                            <?php }
                            if($item->check_mount_done == 1) { ?>
                                <input id="<?= $item->id; ?>_project_sum" value="<?php echo ($item->new_project_sum)?$item->new_project_sum:$item->project_sum; ?>"  hidden>
                                <input id="<?= $item->id; ?>_mounting_sum" value="<?php echo ($item->new_mount_sum)?$item->new_mount_sum:($mounting_sum + $sum_transport); ?>" hidden >
                                <input id="<?= $item->id; ?>_material_sum" value="<?php echo ($item->new_material_sum)?$item->new_material_sum:$material_sum; ?>"  hidden>
                            <?php }

                            ?>
                            <input id="<?= $item->id; ?>_cost_price" value="<?php echo $cost_price; ?>"  hidden>
                            <input id="<?= $item->id; ?>_new_project_sum" value="<?php echo $item->project_sum; ?>" hidden >
                        </td>
                        <td><?=$item->status?></td>
                        <td class="center one-touch">
                            <?= !empty($item->project_mounting_date) ? $item->project_mounting_date : '-'?>
                        </td>
                        <td class="center one-touch"><?= $item->project_info; ?></td>
                        <td class="center one-touch"><?= $item->client_contacts; ?> <br> <?= $item->client_name; ?></td>

                        <td class="center one-touch">
                            <?php
                            $projectSum = 0;
                            if(!empty(floatval($item->new_project_sum))){
                                $projectSum = $item->new_project_sum;
                            }
                            elseif(!empty(floatval($item->project_sum))){
                                $projectSum = $item->project_sum;
                            }
                            else{
                                $dealerInfoModel = Gm_ceilingHelpersGm_ceiling::getModel('dea;er_info');
                                $projectSum += margin($item->canvases_sum,$item->dealer_canvases_margin) +
                                    margin($item->components_sum,$item->dealer_components_margin);
                                $totalMountSum = $item->mounting_sum;
                                if(!empty($item->calcs_mounting_sum)){
                                    $service_sum = json_decode($item->calcs_mounting_sum);
                                    foreach ($service_sum as $sum){
                                        $totalMountSum += $sum;
                                    }
                                }
                                $projectSum += margin($totalMountSum,$item->dealer_mounting_margin);
                            }
                            echo round($projectSum,2);?>
                        </td>
                        <td class="center one-touch">
                            <?php if ($item->project_mounter) {
                                $mounter = "";
                                foreach ($item->project_mounter as $value) {
                                    $mounter .= JFactory::getUser($value)->name."; ";
                                }
                            }
                            else{
                                $mounter = '-';
                            }
                            echo $mounter; ?>
                        </td>
                        <?php if(in_array(14, $groups)){ ?>
                            <td class="center one-touch delete"><button class="btn btn-danger btn-sm delete_btn" data-id = "<?php echo $item->id;?>" type="button"><i class="fas fa-trash-alt" aria-hidden="true"></i></button></td>
                        <?php } ?>
                    </tr>
                <? endif; ?>
                <? endforeach; ?>
                </tbody>
            <? else: ?>
                <thead>
                <tr>
                    <th class='center'>
                        №
                    </th>
                    <th class='center'>
                        Сумма заказа
                    </th>
                    <th class='center'>
                        Кол-во потолков
                    </th>
                    <th class='center'>
                        Статус
                    </th>
                    <th class='center'>
                        Информация
                    </th>
                </tr>
                </thead>
                <tbody>
                <? foreach ($this->items as $i => $item) : ?>
                    <? if ($userId == $item->dealer_id || $user->dealer_id == $item->dealer_id): ?>
                        <tr data-href="<?= JRoute::_('index.php?option=com_gm_ceiling&view=project&type=calculator&subtype=project&id=' . $item->id); ?>">
                            <td class="center one-touch"><?= $item->id; ?></td>
                            <td class="center one-touch"><?= round($item->project_margin_sum, 2); ?></td>
                            <td class="center one-touch"><?= $item->count_ceilings; ?></td>
                            <td class="center one-touch"><?= $item->status; ?></td>
                            <td class="center one-touch">
                                <? if ($item->project_status == 0 || $item->project_status == 1 || $item->status == 4) { ?>
                                    <a class="btn btn-large btn-primary"
                                       href="<?= JRoute::_('/index.php?option=com_gm_ceiling&view=project&type=calculator&subtype=calendar&id=' . $item->id, false); ?>">Дооформить</a>
                                <? } elseif ($item->project_status == 13) {
                                    ?>
                                    Для оплаты кликните по заказу, на открывшейся странице нажмите "Оплатить"
                                <? } ?>
                            </td>
                        </tr>
                    <? endif ?>
                <? endforeach; ?>
                </tbody>
            <? endif; ?>
        </table>
        <input type="hidden" name="task" value=""/>
        <input type="hidden" name="boxchecked" value="0"/>
        <?= JHtml::_('form.token'); ?>
    <? else: ?>
        <p class="center">
        <h3>У вас еще нет заказов!</h3>
    <? endif; ?>
</form>

<script type="text/javascript">

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
                cost_price = jQuery(this).closest("tr").find("#"+td.data("project_id")+"_cost_price").val(),
                new_project_sum = jQuery(this).closest("tr").find("#"+td.data("project_id")+"_new_project_sum").val(),
                status = jQuery(this).closest("tr").data("status"),
                new_value = jQuery(this).closest("tr").find("#"+td.data("project_id")+"_project_sum").val(),
                mouting_sum = jQuery(this).closest("tr").find("#"+td.data("project_id")+"_mounting_sum").val(),
                material_sum = jQuery(this).closest("tr").find("#"+td.data("project_id")+"_material_sum").val();
            console.log(cost_price,new_project_sum,status,new_value,mouting_sum,material_sum);
            var subject = "Отметка стоимости договора №" + td.data("project_id");
            var text = "";

            text += "<div class='dop_info_block' style='font-size:15px;'><div class='center'>Укажите новую стоимость договора</div><div class='center'><input id='input_check' class='noty_input' style='margin-top: 5px;' value='" + new_value + "'/></div></br>";
            text += "<div class='center'>Укажите новую стоимость расходных материалов</div><div class='center'><input id='input_material' class='noty_input' style='margin-top: 5px;' value='" + material_sum + "'/></div></br>";
            text += "<div class='center'>Укажите новую стоимость монтажных работ</div><div class='center'><input id='input_mounting' class='noty_input' style='margin-top: 5px;' value='" + mouting_sum + "'/></div>";
            text += "</div>";

            modal({
                type: 'primary',
                title: subject,
                text: text,
                size: 'small',
                buttons: [{
                    text: 'Выполнено', //Button Text
                    val: 0, //Button Value
                    eKey: true, //Enter Keypress
                    onClick: function(dialog) {
                        var input_value = jQuery("#input_check").val();
                        var input_mounting = jQuery("#input_mounting").val();
                        var input_material = jQuery("#input_material").val();
                        var check = jQuery("input[name='check_mount']:checked").val();
                        //Просчет прибыли

                        var profit = parseFloat(input_value) - (parseFloat(input_mounting)+parseFloat(input_mounting));
                        if (check == undefined) {
                            check = 1;
                        }
                        else check = 0;


                        jQuery.ajax({
                            type: 'POST',
                            url: "index.php?option=com_gm_ceiling&task=project.done",
                            data: {
                                project_id : td.data("project_id"),
                                new_value : input_value,
                                mouting_sum : input_mounting,
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
                },
                    {
                        addClass: 'btn', text: 'Отмена', onClick: function($noty) {
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

    jQuery(".delete_btn").click(function(){
        var project_id = jQuery(this).data('id');
        noty({
            theme: 'relax',
            layout: 'center',
            timeout: false,
            type: "info",
            text: "Вы действительно хотите удалить проект?",
            buttons:[
                {
                    addClass: 'btn btn-primary', text: 'Удалить', onClick: function($noty) {
                        jQuery.ajax({
                            url: "index.php?option=com_gm_ceiling&task=project.delete_by_user",
                            data: {
                                project_id: project_id
                            },
                            dataType: "json",
                            async: true,
                            success: function(data) {
                                jQuery('.btn-danger[data-id ='+project_id+']').closest('.row').remove();
                            },
                            error: function(data) {
                                console.log(data);
                                var n = noty({
                                    timeout: 2000,
                                    theme: 'relax',
                                    layout: 'topCenter',
                                    maxVisible: 5,
                                    type: "error",
                                    text: "Ошибка сервера"
                                });
                            }
                        });
                        $noty.close();
                    }
                },
                {
                    addClass: 'btn btn-primary', text: 'Отмена', onClick: function($noty) {
                        $noty.close();
                    }
                }
            ]
        });
        return false;

    });

</script>

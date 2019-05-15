<?php
/**
 * Created by PhpStorm.
 * User: bubnov
 * Date: 13.05.2019
 * Time: 13:48
 */

$projects = $this->items;
//print_r($projects);
?>

<?=parent::getButtonBack(); ?>
<h2 class="center">Договоры на закрытие</h2>
<table class="table one-touch-view" id="projectList">
    <thead>
    <tr>
        <th class='center' style="width: 4%">
            Закрыть
        </th>
        <th class='center' style="width: 4%">
            №
        </th>
        <th class="center" style="width: 20%">
            Адрес
        </th>
        <th class="center">
            Клиент
        </th>
        <th class="center">
            Дата замера
        </th>
        <th class="center">
            Дата монтажа
        </th>

        <th class="center">
            Сумма договора
        </th>

    </tr>
    </thead>

    <tbody>
        <?php foreach ($projects as $project){
            $project_sum = (!empty(floatval($project->project_sum))) ? $project->project_sum :$project->project_margin_sum+$project->transport_cost;?>
            <tr data-canv_sum ="<?=$project->canvases_sum;?>" data-comp_sum="<?=$project->components_sum;?>" data-mount_sum="<?=$project->mounting_sum?>">
                <td class="center">
                    <button class="btn btn-primary btn-sm btn-done" data-project_id="<?= $project->id; ?>" type="button"><i class="fa fa-check"></i></button>
                </td>
                <td class="center">
                    <?php echo $project->id;?>
                </td>
                <td class="center">
                    <?php echo $project->project_info;?>
                </td>
                <td class="center">
                    <?php echo $project->client_name;?>
                </td>
                <td class="center">
                    <?php echo date('d.m.Y h:i',strtotime($project->project_calculation_date));?>
                </td>
                <td class="center">
                    <?php echo $project->project_mounting_date;?>
                </td>
                <td class="center project_sum">
                    <?php echo round($project_sum,2);?>
                </td>
            </tr>
        <?php }?>
    </tbody>
</table>

<script type="text/javascript">

    jQuery(document).ready(function () {

        jQuery(".btn-done").click(function(){
            var button = jQuery( this ),
                tr = button.closest("tr"),
                project_sum = tr.find(".project_sum"),
                type = "info",
                value = button.data("value"),
                cost_price = jQuery(this).closest("tr").find("#"+button.data("project_id")+"_cost_price").val(),
                new_project_sum = jQuery(this).closest("tr").find("#"+button.data("project_id")+"_new_project_sum").val(),
                status = jQuery(this).closest("tr").data("status"),
                new_value = project_sum[0].innerText,
                mouting_sum = tr.data("mount_sum"),
                material_sum = +tr.data("canv_sum")+ +tr.data("comp_sum");

            var subject = "Отметка стоимости договора <br> № " + button.data("project_id");
            var text = "";

            text += "<div class='dop_info_block' style='font-size:15px;'>";
            text += "<div class='center'>Укажите новую стоимость договора</div><div class='center'><input id='input_check' class='noty_input' style='margin-top: 5px;' value='" + new_value + "'/></div></br>";
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
                        var input_value = jQuery("#input_check").val(),
                            input_mounting = jQuery("#input_mounting").val(),
                            input_material = jQuery("#input_material").val(),
                            profit = parseFloat(input_value) - (parseFloat(input_mounting)+parseFloat(input_mounting));

                        jQuery.ajax({
                            type: 'POST',
                            url: "index.php?option=com_gm_ceiling&task=project.done",
                            data: {
                                project_id : button.data("project_id"),
                                new_value : input_value,
                                mouting_sum : input_mounting,
                                material_sum : input_material,
                                check: 1
                            },
                            success: function(data){
                                button.closest("td").html("<i class='fa fa-check' aria-hidden='true'></i> Закрыт!");
                                var n = noty({
                                    theme: 'relax',
                                    layout: 'center',
                                    maxVisible: 5,
                                    type: "success",
                                    text: data
                                });
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
</script>
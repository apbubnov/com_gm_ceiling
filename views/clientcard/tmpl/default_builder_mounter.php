<?php
$user       = JFactory::getUser();
$userId     = $user->get('id');
$user_group = $user->groups;

$clientcardModel = Gm_ceilingHelpersGm_ceiling::getModel('clientcard');
$client_model = Gm_ceilingHelpersGm_ceiling::getModel('client');
$clients_model = Gm_ceilingHelpersGm_ceiling::getModel('clients');
$projectsMountsModel = Gm_ceilingHelpersGm_ceiling::getModel('projects_mounts');
$mountTypes = $projectsMountsModel->get_mount_types();
unset($mountTypes[1]);
foreach ($mountTypes as $key=>$value){
    $mountTypes[$key] = array("title"=>$value,"status"=>$key+25);
}
$app = JFactory::getApplication();
$jinput = $app->input;

$client = $client_model->getClientById($this->item->id);
$clients_items = $clients_model->getDealersClientsListQuery($client->dealer_id, $this->item->id);
$dealer = JFactory::getUser($client->dealer_id);
if ($dealer->associated_client != $this->item->id)
{
    throw new Exception("this is not dealer", 403);
}
$calcMountModel = Gm_ceilingHelpersGm_ceiling::getModel('calcs_mount');
$stageSums = $calcMountModel->getMounterSum($userId,$dealer->id);

$mountersSalaryModel = Gm_ceilingHelpersGm_ceiling::getModel('MountersSalary');
$closedSums = $mountersSalaryModel->getClosedSumByMounter($userId,$dealer->id);
$payed_sums = $mountersSalaryModel->getDataById($userId,"and builder_id = $dealer->id");
$total_pay =0;
foreach ($payed_sums as $pay){
    $total_pay-=$pay->sum;
}
$rest_sum = $closedSums->sum - $total_pay;
?>
<style>
    .cell{
        border: 1px solid #414099;
        margin-top: 5px;
    }
    .left_half{
        display: inline-block;
        width:49%;
        align-self: left;
    }
    .right_half{
        display: inline-block;
        width:49%;
        align-self: right;
    }
</style>
<button id="back_btn" class="btn btn-primary"><i class="fa fa-arrow-left" aria-hidden="true"></i> Назад</button>

<div class="row">
    <div class="col-md-7"></div>
    <div class="col-md-3">
        <div class="row">
            <div class="right"><b>Сумма взятого объема</b></div>
            <?php foreach($stageSums as $value) {?>
                <div class="right"><?= $value->title.": ".$value->stage_sum;?></div>
            <?php }?>
        </div>
        <div class="row">
            <div class="right"><b>Сумма закрытого объема</b></div>
            <div class="right"><?= $closedSums->sum;?></div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="row">
            <div class="right"><b>Выплачено</b></div>
            <div class="right"><?= $total_pay;?></div>
        </div>
        <div class="row">
            <div class="right"><b>Остаток</b></div>
            <div class="right"><?=$rest_sum;?></div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-3" >
        <div style="height:35px;background:linear-gradient(135deg, white, palevioletred 150%);">
           Выбрано другой бригадой
        </div>
    </div>
    <div class="col-md-3" >
        <div style="height:35px;background:linear-gradient(135deg, white, yellow 150%);">
            Ожидание подтверждения
        </div>
    </div>
    <div class="col-md-3">
        <div style="height:35px;background:linear-gradient(135deg, white, #414099 150%);">
            Выбрано
        </div>
    </div>
</div>

<div class="row">
    <div class="right">
        <button class="btn btn-primary" id="show_detailed">Детализация</button>
    </div>
</div>
<div class="row">
    <ul class="nav nav-tabs" role="tablist">
        <?php foreach ($mountTypes as $k => $mountStage) { ?>
            <li class="nav-item">
                <a class="nav-link mount_stage" data-toggle="tab" data-mount_type="<?php echo $k;?>" data-mount_status="<?php echo $mountStage['status'];?>" role="tab">
                    <?php echo $mountStage['title'] ?>
                </a>
            </li>
        <?php } ?>
    </ul>
    <div id = "report_table" class="row">

    </div>
</div>


<div id="mv_container" class="modal_window_container">
    <button type="button" id="close" class="close_btn"><i class="fa fa-times fa-times-tar" aria-hidden="true"></i></button>
    <div id="take_mount" class="modal_window">
        <input type="hidden" id="selected_project">
        <input type="hidden" id="selected_floor">
        <table id="calcs" class="table_project_analitic" style="margin-left: 5px !important;">
            <thead>
            <tr class="caption_table">
                <td width="10%"></td>
                <td width="90%">
                    Название помещения
                </td>
            </tr>
            <tr class="select_all_tr">
                <td width="10%">
                    <input type="checkbox"  id="select_all"  class="inp-cbx" style="display: none">
                    <label for="select_all" class="cbx">
                            <span>
                                <svg width="12px" height="10px" viewBox="0 0 12 10">
                                    <polyline points="1.5 6 4.5 9 10.5 1"></polyline>
                                </svg>
                            </span>
                        <span></span>
                    </label>
                </td>
                <td width="90%">
                    Выбрать всё
                </td>
            </tr>
            </thead>
            <tbody>

            </tbody>
        </table>
        <div class="row">
            <div class="col-md-12">
                <button class="btn btn-primary" id="save_btn" type="button">Сохранить</button>
            </div>
        </div>
    </div>
    <div id="detailed_salary" class="modal_window">
        <table id="detailed_salary" class="table_project_analitic">
            <thead>
            <tr class="caption_table">
                <td>
                    Сумма
                </td>
                <td>
                    Объект
                </td>
                <td>
                    Время
                </td>
            </tr>
            </thead>
            <tbody>

            </tbody>
        </table>
    </div>
</div>
<script>
    var progressData = [];
    var EDIT_BUTTON = "<button class='btn btn-primary btn-sm edit_mounter'><i class=\"fas fa-edit\" aria-hidden=\"true\"></i></button>",
        ACCEPT_BUTTON = "<button class='btn btn-primary btn-sm accept_mounter'><i class=\"fa fa-check\" aria-hidden=\"true\"></i></button>",
        CHECK_BUTTON = "<div class='row'><div class='col-md-12'><button name='check_btn' class='btn btn-primary btn-sm sum_btn'><i class=\"fa fa-check\" aria-hidden=\"true\"></i></button></div></div>",
        REFRESH_BUTTON = "<div class='row'><div class='col-md-12'><button name='refresh_btn' class='btn btn-primary btn-sm sum_btn'><i class=\"fas fa-sync\" aria-hidden=\"true\"></i></button></div></div>";
    var user_id = '<?php echo $userId ?>';


    jQuery(document).mouseup(function (e){ // событие клика по веб-документу
        var div = jQuery("#take_mount"),
            div1 = jQuery("#detailed_salary"); // тут указываем ID элемента

        if (!div.is(e.target) && div.has(e.target).length === 0&&
            !div1.is(e.target) && div1.has(e.target).length === 0) {
            jQuery("#close").hide();
            jQuery("#mv_container").hide();
            jQuery("#take_mount").hide();
            jQuery("#detailed_salary").hide();
        }
    });

    function drawReportTable(stage){
        getReportData(stage);
        var reportTable = jQuery("#report_table");
        reportTable.empty();
        var temp_sums = [];
        var html = "";
        for(var i=0,elem;i<Object.keys(progressData).length;i++) {
            var floor_sum = 0,
                floor_sq = 0;
            html = '<div class="row" data-client_id = "'+Object.keys(progressData)[i]+'">';
            elem = progressData[Object.keys(progressData)[i]];
            html += '<div class="col-md-1"><div class="row">'+elem.name+'</div><div class="row"> sq_total </div></div>';
            html += '<div class="col-md-11">';

            for (var j = 0, td, val, sum,mounter; j < elem.projects.length; j++) {
                var colIndex = 12;//(elem.projects.length >=12) ? 1 : (12 % (elem.projects.length) == 0) ? 12/(elem.projects.length) : parseInt(12/(elem.projects.length));
                var style;
                jQuery.each(elem.projects[j].calcs,function(index,elem){
                   if(elem.mounters && elem.mounters[0].id == user_id){
                       console.log(elem);
                       if(elem.calc_status == 3){
                           style = 'style = "background:linear-gradient(135deg, white, yellow 150%);"';
                       }
                       if(elem.calc_status == 4){
                           style = 'style = "background:linear-gradient(135deg, white, #414099 150%);"';
                       }

                   }
                   else{
                       if(elem.mounters && elem.mounters[0].id != user_id) {
                           style = 'style = "background:linear-gradient(135deg, white, palevioletred 150%);"';
                       }
                       else{
                           style = '';
                       }
                   }
                });
                html +='<div class="col-md-'+colIndex+' center cell" '+style+' data-proj_id = "'+elem.projects[j].id+'" >';
                val = parseFloat(elem.projects[j].value);
                sum = parseFloat(elem.projects[j].sum);
                floor_sq +=val;
                floor_sum += sum;
                if(temp_sums[elem.name]) {
                    temp_sums[elem.name] += val;
                }
                else{
                    temp_sums[elem.name] = val;

                }
                var value = (stage == 3) ? "S=" : "P=";

                td = '<b>'+elem.projects[j].title +'</b>'+
                    "<div class='row' style='font-size:11pt;font-style:italic;'>" +
                    "<div class='col-xs-5 col-md-5'>" +value+ val.toFixed(2) + "</div><div class='col-xs-7 col-md-7'>(<span class='sum'>" + sum.toFixed(2) + "</span>) </div>" +
                    "</div>";

                html += td;
                html +='</div>';
            }
            html +='</div>';
            html +='</div>';

            html = html.replace("sq_total",value+floor_sq.toFixed(2));
            console.log(html);
            jQuery("#report_table").append(html);

        }

        jQuery(".cell").click(function () {
            var item = jQuery(this);
            jQuery("#mv_container").show();
            jQuery("#take_mount").show("slow");
            jQuery("#close").show();
            jQuery("#calcs > tbody").empty();
            var row = jQuery(this).closest('.row'),
                floorId = row.data('client_id'),
                projectId = item.data('proj_id'),
                project = progressData[floorId].projects.find(function(obj){return obj.id == projectId}),
                calcs = project.calcs;
            jQuery("#selected_floor").val(floorId);
            jQuery("#selected_project").val(projectId);

            jQuery.each(calcs,function (index,elem) {
                var disabled = (!empty(elem.mounters) && elem.mounters[0].id != user_id) ? "disabled" :"";
                var checked = (!empty(elem.mounters) && elem.mounters[0].id == user_id) ? "checked='checked'" : "";
                var tr = "";
                jQuery("#calcs > tbody").append('<tr class="calc_tr"></tr>');
                tr+="<td class='checkbox_td'>" +
                    "<input type=\"checkbox\"  id='calc"+elem.id+"' data-calc_id = "+elem.id+" "+disabled+" "+checked+" class=\"inp-cbx\" style=\"display: none\">\n" +
                    "                <label for='calc"+elem.id+"' class=\"cbx\">\n" +
                    "                                        <span>\n" +
                    "                                            <svg width=\"12px\" height=\"10px\" viewBox=\"0 0 12 10\">\n" +
                    "                                                <polyline points=\"1.5 6 4.5 9 10.5 1\"></polyline>\n" +
                    "                                            </svg>\n" +
                    "                                        </span>\n" +
                    "                    <span></span>\n" +
                    "                </label>"+
                    "</td>"+
                    "<td class='title_td'>"+elem.title+"</td>";
                jQuery("#calcs > tbody > tr:last").append(tr);
            });

            jQuery('.calc_tr').click(function () {
                var checkbox = jQuery(this).find('.inp-cbx'),value;
                if(!checkbox.attr("disabled")) {
                    if (checkbox.attr("checked")) {
                        value = false;
                    }
                    else {
                        value = true;
                    }
                    checkbox.attr("checked", value);
                    jQuery('#select_all').attr("checked", false);
                }
                else{
                    alert("Выбрано другой бригадой!")
                }
            });

            jQuery('.select_all_tr').click(function () {
                var checkbox = jQuery(this).find('.inp-cbx'),value;
                if(checkbox.attr("checked")) {
                    value = false;
                }
                else{
                    value = true;
                }
                checkbox.attr("checked",value);
                var checkboxes = jQuery('.inp-cbx');
                jQuery.each(checkboxes,function (index,elem) {
                    if(!elem.disabled) {
                        jQuery(elem).attr("checked", value);
                    }
                })
            });

            jQuery("#save_btn").click(function () {
                saveMounter();
            });
        });
    }


    function getReportData(stage){
        jQuery.ajax({
            url: "index.php?option=com_gm_ceiling&task=clients.getInfoByFloors",
            data: {
                dealerId: '<?php echo $client->dealer_id; ?>',
                stage: stage
            },
            dataType: "json",
            async: false,
            success: function(data) {
                console.log(data);
                progressData = data;

            },
            error: function(data) {
                var n = noty({
                    timeout: 2000,
                    theme: 'relax',
                    layout: 'center',
                    maxVisible: 5,
                    type: "error",
                    text: "Ошибка получения данных"
                });
            }
        });
    }

    function saveMounter() {
        var mounterId = '<?php echo $userId ?>',
            stage = jQuery('.active.mount_stage').data("mount_type"),
            calcsId = [],
            checkboxes = jQuery('.inp-cbx:checked');
        jQuery.each(checkboxes,function (index,elem) {
            if(!empty(jQuery(elem).data('calc_id')) && !elem.disabled){
                calcsId.push(jQuery(elem).data('calc_id'));
            }
        });
        if(!empty(calcsId)) {
            jQuery.ajax({
                url: "index.php?option=com_gm_ceiling&task=Calcs_mounts.updateMounter",
                data: {
                    calcsId: calcsId,
                    stage: stage,
                    mounterId: mounterId,
                    change_status: stage+1
                },
                dataType: "json",
                async: false,
                success: function (data) {
                    var n = noty({
                        timeout: 2000,
                        theme: 'relax',
                        layout: 'center',
                        maxVisible: 5,
                        type: "success",
                        text: "Сохранено!"
                    });
                },
                error: function (data) {
                    var n = noty({
                        timeout: 2000,
                        theme: 'relax',
                        layout: 'center',
                        maxVisible: 5,
                        type: "error",
                        text: "Ошибка сервера"
                    });
                }
            });
        }
        else{
            //close
        }
    }


    jQuery(document).ready(function () {

        jQuery(jQuery("[name='stage']")[0]).attr("checked", "true");
        var firstTab = jQuery(jQuery(".mount_stage")[0]),
            stage = firstTab.data("mount_type"),
            status = firstTab.data("mount_status");
        firstTab.addClass('active');
        drawReportTable(stage);


        jQuery(".mount_stage").click(function () {
            var stage = jQuery(this).data("mount_type");
            drawReportTable(stage);

        });

        jQuery("#back_btn").click(function () {
            history.go(-1);
        });

        jQuery("#show_detailed").click(function () {
            jQuery("#mv_container").show();
            jQuery("#detailed_salary").show();
            jQuery("#close").show();

            var mounterId = '<?php echo $userId;?>';

            console.log(mounterId);
            jQuery.ajax({
                url: "index.php?option=com_gm_ceiling&task=MountersSalary.getMounterSalaryByBuilder",
                data: {
                    mounterId:mounterId,
                    builder_id: '<?php echo $dealer->id;?>'
                },
                dataType: "json",
                async: false,
                success: function(data) {
                    var total = 0,note = "";
                    jQuery("#detailed_salary > tbody").empty();
                    jQuery.each(data,function (index,el){
                        total += +el.sum;
                        note = (!empty(el.note)) ? el.note : "Выплата";
                        jQuery("#detailed_salary > tbody").append('<tr/>');
                        jQuery("#detailed_salary > tbody > tr:last").append('<td>'+el.sum+'</td><td>'+note+'</td><td>'+el.datetime+'</td>')
                    });
                    jQuery("#detailed_salary > tbody").append('<tr/>');
                    jQuery("#detailed_salary > tbody > tr:last").append('<td align="right"><b>Итого:<b></td><td>'+total+'</td><td></td>');
                },
                error: function(data) {
                    var n = noty({
                        timeout: 2000,
                        theme: 'relax',
                        layout: 'center',
                        maxVisible: 5,
                        type: "error",
                        text: "Ошибка сервера"
                    });
                }
            });





        });
    });


</script>
<?php
/**
 * Created by PhpStorm.
 * User: bubnov
 * Date: 10.09.2019
 * Time: 9:28
 */
$projects_model = Gm_ceilingHelpersGm_ceiling::getModel('Projects');
$monthBegin = date('Y-m-01');
$today = date('Y-m-d');
$projects = $projects_model->getProjectsByHistoryStatus(8,$monthBegin,$today);
?>
<style>
    .Elements {
        min-width: 100%;
        position: relative;
        border-collapse: collapse;
        margin-top: 20px;
    }
    .Elements tr {
        border: 1px solid #414099;
        background-color: #E6E6FA;
        color: #000000;
    }
    .Elements tr td {
        border: 0;
        border-right: 1px solid #414099;
        width: auto;
        height: 30px;
        line-height: 20px;
        padding: 0 5px;
    }
    .Elements tr td.Date {
        min-width: 130px;
    }
    .Elements tr td.Status {
        min-width: 130px;
    }
    .Elements tr td button {
        display: inline-block;
        float: left;
        border: none;
        width: 30px;
        height: 30px;
        background-color: inherit;
        color: rgb(54, 53, 127);
        border-radius: 5px;
        cursor: pointer;
    }
    .Elements thead {
        position: relative;
        top: 0;
        left: 0;
    }
    .Elements thead tr td {
        background-color: #414099;
        color: #ffffff;
        border-color: #ffffff;
        padding: 5px 10px;
        text-align: center;
        min-width: 102px;
    }
    .Elements tbody tr {
        cursor: pointer;
    }
    .Elements tbody tr:hover {
        background-color: #97d8ee;
    }
    .Elements tr td:last-child {
        border-right: 0;
    }
    .Elements .CloneElementsHead {
        position: fixed;
        top: 0;
        left: 0;
    }
    .Elements .CloneElementsHeadTr {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        z-index: 1;
    }
</style>
<div class="row" style="margin-bottom: 1em !important;">
    <?= parent::getButtonBack() ?>
</div>
<div class="row">
    <div class="col-md-3">
        <div class="col-md-2" style="padding-top: 5px;">
            <b><label>C</label></b>
        </div>
        <div class="col-md-10">
            <input type="date" id="date_from" class="date form-control" value="<?=$monthBegin?>">
        </div>
    </div>
    <div class="col-md-3">
        <div class="col-md-2"  style="padding-top: 5px;">
            <b><label>до</label></b>
        </div>
        <div class="col-md-10">
            <input type="date" id="date_to" class="date form-control" value="<?=$today;?>">
        </div>

    </div>
    <div class="col-md-1" style="text-align: right; padding-right: 0;padding-left: 0;padding-top: 5px;">
        <i class="fas fa-search"></i>
        <b><span style="vertical-align: middle">Поиск: </span></b>
    </div>
    <div class="col-md-4" style="text-align: right">
        <input type="text" class="form-control" id="search_text" placeholder="Введите номер, адрес или дилера проекта" />
    </div>
    <div class="col-md-1">
        <button class="btn btn-primary" id="seach_btn">Найти</button>
    </div>
</div>
<table class="table table-stripped table-cashbox">
    <table class="Elements" id="projects_table">
        <thead class="ElementsHead">
        <tr class="ElementsHeadTr">
            <td class="center">#</td>
            <td class="center">Адрес</td>
            <td class="center">Дилер</td>
            <td class="center">Дата</td>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($projects as $project){?>
            <tr data-id ="<?=$project->id?>">
                <td ><?= $project->id?></td>
                <td><?= $project->project_info?></td>
                <td><?= $project->name?></td>
                <td><?= $project->date?></td>
            </tr>
        <?php }?>
    </tbody>
</table>
<script type="text/javascript">
    jQuery(document).ready(function () {
        jQuery('.date').change(function(){
            getFilteredProjects();
        });

        jQuery('#seach_btn').click(function () {
            getFilteredProjects();
        });

        jQuery('#projects_table').on('click','tr',function(){
            location.href = '/index.php?option=com_gm_ceiling&view=stock&type=return&id='+jQuery(this).data('id');
        });

        function getFilteredProjects(){
            var text = jQuery('#search_text').val(),
                dateFrom = jQuery('#date_from').val(),
                dateTo = jQuery('#date_to').val();
            if(dateFrom > dateTo){
                noty({
                    timeout: 2000,
                    theme: 'relax',
                    layout: 'center',
                    maxVisible: 5,
                    type: "Error",
                    text: "Начальная дата не может быть больше конечной!"
                });
                return;
            }

            jQuery.ajax({
                type: 'POST',
                async: false,
                url: "/index.php?option=com_gm_ceiling&task=projects.getProjectsByHistoryStatus",
                data: {
                    filter: text,
                    date_from: dateFrom,
                    date_to: dateTo,
                    status: 8
                },
                success: function (data) {
                    console.log(JSON.parse(data));
                    if(!empty(data)){
                        data = JSON.parse(data);
                        jQuery('#projects_table > tbody').empty();
                        jQuery.each(data,function (i,p) {
                            jQuery('#projects_table > tbody').append('<tr data-id="'+p.id+'">' +
                                '<td>'+p.id+'</td>'+
                                '<td>'+p.project_info+'</td>'+
                                '<td>'+p.name+'</td>'+
                                '<td>'+p.date+'</td>'+
                                '</tr>');
                        });
                    }
                    else{
                        noty({
                            timeout: 2000,
                            theme: 'relax',
                            layout: 'center',
                            maxVisible: 5,
                            type: "warning",
                            text: "по Вашему запросу ничего не найдено!"
                        });
                    }
                },
                error: function (data) {
                    noty({
                        timeout: 2000,
                        theme: 'relax',
                        layout: 'center',
                        maxVisible: 5,
                        type: "error",
                        text: "Ошибка получения данных!"
                    });
                }

            });
        }
    });
</script>
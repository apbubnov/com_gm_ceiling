<?php
/**
 * Created by PhpStorm.
 * User: bubnov
 * Date: 14.10.2019
 * Time: 15:28
 */
$calculationsModel = Gm_ceilingHelpersGm_ceiling::getModel('calculations');

$calcsHistoryModel = Gm_ceilingHelpersGm_ceiling::getModel('calcshistory');

$projectModel = Gm_ceilingHelpersGm_ceiling::getModel('project');
$clientModel = Gm_ceilingHelpersGm_ceiling::getModel('client');

$calculations = $calculationsModel->new_getProjectItems($this->item->id);
$historyData = $calcsHistoryModel->getDatabyProjectId($this->item->id);
$client = $clientModel->getClientById($this->item->id_client);
$dealer = JFactory::getUser($client->dealer_id);
?>

<button class="btn btn-primary" id="btn_back"><i class="fa fa-arrow-left" aria-hidden="true"></i>Назад</button>

<link rel="stylesheet" href="/components/com_gm_ceiling/views/project/css/style.css" type="text/css" />

<h2 class="center">Просмотр проекта</h2>

<div class="container">
    <div class="row">
        <div class="col-md-6">
            <div class="item_fields">
                <h4>Информация по проекту № <?= $this->item->id; ?></h4>
                <form id="form-client"
                      action="/index.php?option=com_gm_ceiling&task=project.activate&type=gmcalculator&subtype=calendar"
                      method="post" class="form-validate form-horizontal" enctype="multipart/form-data">
                    <table class="table">
                        <tr>
                            <th>Объект</th>
                            <td><?php echo $dealer->name; ?></td>
                        </tr>

                        <tr>
                            <th>Этаж</th>
                            <td><?php echo $this->item->client_id; ?></td>
                        </tr>
                        <tr>
                            <th>Квартира</th>
                            <td><?php echo $this->item->project_info; ?></td>
                        </tr>

                    </table>
                </form>
            </div>
            <input name="project_id" value="<?php echo $this->item->id; ?>" type="hidden">
            <input name="client" id="client_id" value="<?php echo $this->item->client_id; ?>" type="hidden">
            <button class = "btn btn-primary" id = "create_pdfs">Сгенерировать сметы</button>
        </div>
        <div class="col-md-6">
            <h4 class="center">Примечания</h4>
            <?php include_once('components/com_gm_ceiling/views/project/project_notes.php'); ?>

        </div>
    </div>
<table class="table table_cashbox">
    <thead>
        <th class="center">
            <i class="fas fa-check-square"></i>
        </th>
        <th class="center">Название</th>
        <th class="center">Раскрой</th>
        <th class="center">Запущен</th>
        <th class="center">Выдан</th>
    </thead>
    <rbody>
        <?php foreach ($calculations as $calculation){
            $history = json_decode($historyData[$calculation->id]['history']);
            $run_date = '-';
            $issued_date = '-';
            foreach ($history as $item){
                if($item->status == 1){
                    $run_date = date('d.m.Y H:i:s',strtotime($item->date_time));
                }
                if($item->status == 2){
                    $issued_date = date('d.m.Y H:i:s',strtotime($item->date_time));
                }
            }
            ?>

            <tr>
                <td>
                    <input name="calculation_to_run" type="checkbox"  id="calc_<?=$calculation->id?>>" data-id="<?= $calculation->id?>"  class="inp-cbx" style="display: none">
                    <label for="calc_<?=$calculation->id?>>" class="cbx">
                            <span>
                                <svg width="12px" height="10px" viewBox="0 0 12 10">
                                    <polyline points="1.5 6 4.5 9 10.5 1"></polyline>
                                </svg>
                            </span>
                        <span></span>
                    </label>
                </td>
                <td><?php echo $calculation->calculation_title;?></td>
                <td>
                    <?php $path = "/costsheets/".md5($calculation->id."cutpdf").".pdf"; ?>
                    <?php if (file_exists($_SERVER['DOCUMENT_ROOT'] . $path)) { ?>
                        <a href="<?php echo $path; ?>" class="btn btn-secondary"
                           target="_blank">Посмотреть</a>
                    <?php } else { ?>
                        -
                    <?php } ?>
                </td>
                <td><?=$run_date?></td>
                <td><?=$issued_date?></td>
            </tr>
        <?php }?>
    </rbody>
</table>
    <button class="btn btn-primary" id="run_selected" type="button">Запустить выбранные</button>
    <button class="btn btn-primary" id="issued_selected" type="button">Выдать  выбранные</button>
<script type="text/javascript">
    jQuery(document).ready(function () {
        jQuery('#run_selected').click(function(){
            var ids = [],
                calcs_to_run = jQuery('[name="calculation_to_run"]:checked');
            jQuery.each(calcs_to_run,function(index,elem){
                ids.push(jQuery(elem).data('id'));
            });

            console.log(ids);
            jQuery.ajax({
                type: 'POST',
                url: "index.php?option=com_gm_ceiling&task=calculations.saveInHistory",
                data: {
                    calcs_ro_run:ids
                },
                success: function (data) {
                    console.log(data);
                },
                dataType: "text",
                timeout: 10000,
                error: function (data) {
                    console.log(data);
                    var n = noty({
                        theme: 'relax',
                        layout: 'center',
                        maxVisible: 5,
                        type: "error",
                        text: "Ошибка сервера"
                    });
                }
            });
        });

        jQuery('#issued_selected').click(function(){
            var ids = [],
                calcs_to_run = jQuery('[name="calculation_to_run"]:checked');
            jQuery.each(calcs_to_run,function(index,elem){
                ids.push(jQuery(elem).data('id'));
            });

            console.log(ids);
            jQuery.ajax({
                type: 'POST',
                url: "index.php?option=com_gm_ceiling&task=calculations.saveInHistory",
                data: {
                    calcs_to_issue:ids
                },
                success: function (data) {
                    console.log(data);
                },
                dataType: "text",
                timeout: 10000,
                error: function (data) {
                    console.log(data);
                    var n = noty({
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
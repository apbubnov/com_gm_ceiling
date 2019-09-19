<?php
/**
 * Created by PhpStorm.
 * User: bubnov
 * Date: 10.09.2019
 * Time: 9:28
 */
$projects_model = Gm_ceilingHelpersGm_ceiling::getModel('Projects');
$projects = $projects_model->getProjectsByHistoryStatus(8);
?>
<?= parent::getButtonBack() ?>
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
        jQuery("#projects_table > tbody > tr").click(function(){
            location.href = '/index.php?option=com_gm_ceiling&view=stock&type=return&id='+jQuery(this).data('id');
        })
    });
</script>
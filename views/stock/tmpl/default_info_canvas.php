<?php
/**
 * @version    CVS: 1.0.0
 * @package    Com_Gm_ceiling
 * @author     Mikhail "Spectral Eye" Vinyukov <vms@itctl.ru>
 * @copyright  2016 Mikhail "Spectral Eye" Vinyukov
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access
defined('_JEXEC') or die;

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');
JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.multiselect');

$app = JFactory::getApplication();
$model = $this->getModel();
$user = JFactory::getUser();
$userId = $user->get('id');
$userGroup = $user->groups;
if (!(array_search('19', $userGroup) || array_search('18', $userGroup))) header('Location: ' . $_SERVER['REDIRECT_URL']);

$id = $app->input->get('id', NULL, 'INT');
$canvasHistory = $model->getHistoryCanvas($id);
$history = $canvasHistory->history;
$info = $canvasHistory->info;
?>
<?= parent::getPreloaderNotJS(); ?>
<?= parent::getButtonBack(); ?>

<style>
    body {
        background-color: #E6E6FA;
    }

    #BackPage {
        float: left;
    }

    .filter {
        display: inline-block;
        width: auto;
        height: 36px;
        float: left;
        margin-left: 10px;
        background-color: white;
        margin-top: 1px;
        box-shadow: 0 0 0 1px #36357f;
        border-radius: 3px;
    }

    .filter *:not(.fa) {
        display: inline-block;
        float: left
    }

    .filter .title {
        width: auto;
        padding: 0 15px;
        height: 36px;
        line-height: 36px;
        background-color: #414099;
        color: #ffffff;
        text-align: right;
    }

    .filter .title-date {
        width: auto;
        padding: 0 15px;
        height: 36px;
        line-height: 36px;
        color: #414099;
        text-align: right;
    }

    .filter .date {
        width: auto;
        height: 26px;
        margin-top: 5px;
        line-height: 26px;
        color: #414099;
        text-align: center;
        border: 1px solid #414099;
        border-radius: 3px;
    }

    .filter .btn-filter {
        border-radius: 0;
        height: 36px;
        float: left;
        margin-left: 5px;
    }

    .info {
        width: 100%;
        margin-top: 20px;
        display: inline-block;
        color: #414099;
    }

    .table {
        margin-top: 20px;
    }

    .table thead tr, .table tfoot tr {
        background-color: #36357f;
        color: #ffffff;
    }

    .table tfoot tr, .table tfoot tr td {
        max-height: 10px;
        line-height: 10px;
        padding: 0;
    }

    .table td {
        text-align: center !important;
    }

    .table tbody tr {
        background-color: #f2f2f2;
        color: #000000;
        cursor: pointer;
    }

    .table tbody tr:hover {
        background-color: #dedede;
    }

    .table tr th {
        box-shadow: inset .5px .5px 0 0 rgba(255, 255, 255, .5);
    }

    .table tr td {
        box-shadow: inset .5px .5px 0 0 rgba(147, 147, 147, 0.5);
    }
</style>

<form class="filter" action="/index.php?option=com_gm_ceiling&task=stock.getHistoryCanvas" method="POST">
    <div class="title"><i class="fa fa-filter" aria-hidden="true"></i> Фильтр</div>
    <div class="title-date">Начало:</div>
    <input type="date" name="filter[start]" id="date_start" class="date">
    <div class="title-date">Конец:</div>
    <input type="date" name="filter[end]" id="date_end" class="date">
    <button class="btn btn-large btn-primary btn-filter" type="button" onclick="getHistoryComponent();">ОК</button>
</form>
<h1 class="info">
    Информация по полотну: <b><?= $info->name; ?></b>
</h1>
<table class="table">
    <thead>
    <tr>
        <td>Дата</td>
        <td>Тип</td>
        <td>Цена</td>
        <td>Квадратура</td>
        <td>Остаток</td>
        <td>Штрих-код</td>
        <td>Клиент</td>
        <td>Дилер</td>
        <td>Кладовщик</td>
    </tr>
    </thead>
    <tbody>
    <? foreach ($history as $h): ?>
        <tr>
            <td><?= $h->date_update; ?></td>
            <td><?= $h->status; ?></td>
            <td><?= $h->price; ?></td>
            <td><?= $h->quad . " " . $info->unit; ?></td>
            <td><?= $h->quad_now . " " . $info->unit; ?></td>
            <td><?= $h->barcode; ?></td>
            <td><?= $h->client; ?></td>
            <td><?= $h->dealer; ?></td>
            <td><?= $h->stock; ?></td>
        </tr>
    <? endforeach; ?>
    </tbody>
    <tfoot>
    <tr>
        <td colspan="9"> </td>
    </tr>
    </tfoot>
</table>

<script type="text/javascript">
    jQuery(document).ready(function () {
        jQuery('.PRELOADER_GM').hide();
    });

    function getHistoryComponent() {
        jQuery('.PRELOADER_GM').show();
        var form = jQuery(".filter"),
            url = form.attr("action"),
            type = form.attr("method"),
            data = {
                id: <?=$id;?>,
                date: {
                    start: form.find("#date_start").val(),
                    end: form.find("#date_end").val()
                }
            },
            tr = jQuery("<tr/>"),
            td = jQuery("<td/>"),
            table = jQuery(".table tbody");

        jQuery.ajax({
            type: type,
            url: url,
            data: data,
            success: function (data) {
                jQuery('.PRELOADER_GM').hide();
                data = JSON.parse(data);
                var info = data.info;
                var history = data.history;

                table.html("");
                history.forEach(function (value) {
                    var new_tr = tr.clone();
                    new_tr.append(td.clone().html(value.date_update));
                    new_tr.append(td.clone().html(value.status));
                    new_tr.append(td.clone().html(value.quad + " " + info.unit));
                    new_tr.append(td.clone().html(value.quad_now + " " + info.unit));
                    new_tr.append(td.clone().html(value.barcode));
                    new_tr.append(td.clone().html(value.client));
                    new_tr.append(td.clone().html(value.dealer));
                    new_tr.append(td.clone().html(value.stock));
                    table.append(new_tr);
                });
            },
            dataType: "text",
            timeout: 10000,
            error: function () {
                jQuery('.PRELOADER_GM').hide();
                var n = noty({
                    theme: 'relax',
                    layout: 'center',
                    timeout: 1500,
                    type: "error",
                    text: "Сервер не отвечает!"
                });
            }
        });
    }
</script>
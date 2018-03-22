<?php
/**
 * Created by PhpStorm.
 * User: popovaa
 * Date: 02.02.2018
 * Time: 12:20
 */

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');
JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.multiselect');

$user       = JFactory::getUser();
$userId     = $user->get('id');
$recoil_map_project_model = Gm_ceilingHelpersGm_ceiling::getModel('recoil_map_project');
$items = $recoil_map_project_model->getData($userId);

$total_sum = 0;// общая сумма потолка
$contributed = 0;//Внесенная сумма
$rest = 0;//Сумма долга или Остаток
foreach ($items as $item) {
    if($item->sum < 0) $total_sum+=$item->sum;
    else $contributed+=$item->sum;
}
$rest = -($total_sum) - $contributed;
?>

<style>
    input {
        border: 1px solid #414099;
        border-radius: 5px;
    }
    .small_table {
        font-size: 13px;
    }
    @media screen and (min-width: 768px) {
        .small_table {
            font-size: 1em !important;
        }
	}
</style>
<!-- <?=parent::getButtonBack();?> -->
<h2 class = "center">Детализация счета</h2>
<div class = "gm-input center"><?php echo $rest ?></div>;
<form action="<?php echo JRoute::_('index.php?option=com_gm_ceiling&view=dealermainpage&type=score'); ?>" method="post" name="adminForm" id="adminForm">
    <div class="row-fluid toolbar" style="margin: 30px 0 0 0;">
        <div class="span3">
            <div class = "date-actions" style="text-align: right; padding-bottom: 1em;">
                Выбрать с <input type = "date" id = "c_date1"> по <input type ="date" id = "c_date2"> <button type="button" class = "btn btn-primary" id = "c_show_all">Показать всё</button>
            </div>
        </div>
    </div>
    <table class="table table-striped table_cashbox one-touch-view small_table" id="ScoreList">
        <thead>
        <tr>
            <th class=''>Дата</th>
            <th class=''>Проект</th>
            <th class=''>Сумма</th>
            <th class=''>Комментарий</th>
        </tr>
        <tr class="row" id="TrClone" data-href="" style="display: none">
            <td class="one-touch date"></td>
            <td class="one-touch project"></td>
            <td class="one-touch sum"></td>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($items as $i => $item) : ?>
            <?php $canEdit = $user->authorise('core.edit', 'com_gm_ceiling'); ?>
            <?php if (!$canEdit && $user->authorise('core.edit.own', 'com_gm_ceiling')): ?>
                <?php $canEdit = JFactory::getUser()->id == $item->created_by; ?>
            <?php endif; ?>
            <tr class="row<?php echo $i % 2; ?>"
                data-href="<?php if($item->project_id != 0) echo JRoute::_('index.php?option=com_gm_ceiling&view=project&type=calculator&subtype=project&id=' . (int)$item->project_id); ?>">
                <td class="one-touch">
                    <?php
                    if ($item->date_time == "0000-00-00 00:00:00") {
                        echo "-";
                    } else {
                        $jdate = new JDate($item->date_time);
                        $date_time = $jdate->format("d.m.Y H:i");
                        echo $date_time;
                    }
                    ?>

                </td>
                <td class="one-touch"><?php echo $item->project_id; ?></td>
                <td class="one-touch"> <?php echo $item->sum; ?> </td>
                <td class="one-touch"><?php echo $item->comment?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr style="border: 1px solid #414099">
                <th class="right" colspan="3"> ИТОГО: </th>
                <th class="center itog"><span><?= $rest ? round(-$rest,2) : 0; ?></span> руб.</th>
            </tr>
        </tfoot>
    </table>

    <input type="hidden" name="task" value=""/>
    <input type="hidden" name="boxchecked" value="0"/>
    <input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>"/>
    <input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>"/>
    <?php echo JHtml::_('form.token'); ?>
</form>
<script type="text/javascript">
    var $ = jQuery;
    jQuery("#c_show_all").click(function ()
    {
        var date1 = jQuery("#c_date1").val();
        var date2 = jQuery("#c_date2").val();
        if(date1 <= date2) {
            jQuery.ajax({
                type: "POST",
                url: "/index.php?option=com_gm_ceiling&task=filterDateScore",
                data: {
                    date1: date1,
                    date2: date2
                },
                dataType: "json",
                async: true,
                cache: false,
                success: function (data) {
                    console.log(data);
                    var list = $("#ScoreList tbody");
                    var itog = 0;
                    list.empty();
                    var trFoot = $("#ScoreList tfoot")
                    var trItog = $("#TrClone2").clone();
                    for(i=0;i<data.length;i++){
                        var tr = $("#TrClone").clone();
                        tr.show();
                        tr.find(".date").text(data[i].date_time);
                        if (data[i].project_id != null){
                            tr.find(".project").text(data[i].project_id);
                            tr.attr("data-href", "/index.php?option=com_gm_ceiling&view=clientcard&id="+data[i].client_id);
                        }
                        else tr.find(".project").text("-");
                        tr.find(".sum").text(data[i].sum);
                        itog += parseFloat(data[i].sum);
                        list.append(tr);
                    }
                    trFoot.find(".itog span").text(itog);
                    list.append(trItog);
                    OpenPage();
                },
                timeout: 50000,
                error: function (data) {
                    console.log(data);
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
        else {
            noty({
                timeout: 2000,
                theme: 'relax',
                layout: 'center',
                maxVisible: 5,
                type: "error",
                text: "Дата начала не должна превышать дату конца!"
            });
        }

    });
    function OpenPage() {
        var e = jQuery("[data-href]");
        jQuery.each(e, function (i, v) {
            jQuery(v).click(function () {
                document.location.href = this.dataset.href;
            });
        });
    }
</script>

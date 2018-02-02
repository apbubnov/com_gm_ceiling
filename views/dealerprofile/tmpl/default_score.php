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

<?=parent::getButtonBack();?>
<h2 class = "center">Детализация счета</h2>

<form action="<?php echo JRoute::_('index.php?option=com_gm_ceiling&view=dealermainpage&type=score'); ?>" method="post" name="adminForm" id="adminForm">
    <div class="row-fluid toolbar">
        <div class="span3">
        </div>
    </div>
    <table class="table table-striped table_cashbox one-touch-view" id="ScoreList">
        <thead>
        <tr>
            <th class=''>Дата</th>
            <th class=''>Проект</th>
            <th class=''>Сумма</th>
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
            </tr>
        <?php endforeach; ?>
        <tr style="border: 1px solid #414099">
            <th class="right" colspan="2"> ИТОГО: </th>
            <th class="center"><?= $rest ? round(-$rest,2) : 0; ?> руб.</th>
        </tr>
        </tbody>
    </table>

    <input type="hidden" name="task" value=""/>
    <input type="hidden" name="boxchecked" value="0"/>
    <input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>"/>
    <input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>"/>
    <?php echo JHtml::_('form.token'); ?>
</form>

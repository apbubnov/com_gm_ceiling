<?php
/**
 * Created by PhpStorm.
 * User: bubnov
 * Date: 03.07.2019
 * Time: 14:52
 */
$jinput = JFactory::getApplication()->input;
$dealerId = $jinput->get('dealer_id',null,'INT');

$user = JFactory::getUser();
$user->groups = $user->get('groups');

$managerGM = in_array(16, $user->groups) || in_array(15, $userDealer->groups);

$pricesModel = Gm_ceilingHelpersGm_ceiling::getModel('prices');
$goodsPrices = json_encode([]);//json_encode($pricesModel->getGoodsPriceForDealer($dealer_id));
?>
<link rel="stylesheet" type="text/css"
      href="/components/com_gm_ceiling/views/canvases/css/style.css?date=<?= date("H.i.s"); ?>">
<div class="Page">
    <div class="Title">
        Прайс товаров<?= (isset($dealer)) ? " для $dealer->name #$dealer->id" : ""; ?>
    </div><div class="Scroll">
        <form action="<?= JRoute::_('index.php?option=com_gm_ceiling&view=canvases' . (!empty($dealer) ? "&dealer=$dealer->id" : "")); ?>"
              method="post"
              name="adminForm" id="adminForm" hidden>
            <input type="hidden" name="filter_order" value="<?= $listOrder; ?>"/>
            <input type="hidden" name="filter_order_Dir" value="<?= $listDirn; ?>"/>
            <?= JHtml::_('form.token'); ?>
        </form>
        <table class="Body">
            <thead>
            <tr class="THead">
                <td><i class="fa fa-bars" aria-hidden="true"></i></td>
                <td><?= JHtml::_('grid.sort', '<i class="fa fa-hashtag" aria-hidden="true"></i>', 'canvas_id', $listDirn, $listOrder); ?></td>
                <td><?= JHtml::_('grid.sort', 'Наименование', 'texture_title', $listDirn, $listOrder); ?></td>
                <td><?= JHtml::_('grid.sort', 'Цвет', 'color_title', $listDirn, $listOrder); ?></td>
                <? if ($managerGM && empty($dealer)): ?>
                    <td><?= JHtml::_('grid.sort', 'Цена', 'canvas_price', $listDirn, $listOrder); ?></td>
                    <td><?= JHtml::_('grid.sort', 'Цена для дилера', 'canvas_price', $listDirn, $listOrder); ?></td>
                    <td><?= JHtml::_('grid.sort', 'Цена для клиента', 'canvas_price', $listDirn, $listOrder); ?></td>
                    <td>Изменить</td>
                <? elseif ($managerGM): ?>
                    <td><?= JHtml::_('grid.sort', 'Цена', 'canvas_price', $listDirn, $listOrder); ?></td>
                    <td><?= JHtml::_('grid.sort', 'Изменение', 'canvas_price', $listDirn, $listOrder); ?></td>
                    <td><?= JHtml::_('grid.sort', 'Цена для дилера', 'canvas_price', $listDirn, $listOrder); ?></td>
                    <td>Изменить</td>
                <? else: ?>
                    <td><?= JHtml::_('grid.sort', 'Себестоймость', 'canvas_price', $listDirn, $listOrder); ?></td>
                    <td><?= JHtml::_('grid.sort', 'Цена для клиента', 'canvas_price', $listDirn, $listOrder); ?></td>
                <? endif; ?>
            </tr>
            </thead>
            <tbody>
            </tbody>
            <tfoot>
            <tr>
                <td colspan="12"></td>
            </tr>
            </tfoot>
        </table>
    </div>
</div>
<table>

</table>
<script type="text/javascript">
    var goodsPrices = JSON.parse('<?php echo $goodsPrices;?>');
    jQuery(document).ready(function () {
       console.log(goodsPrices);
    });
</script>

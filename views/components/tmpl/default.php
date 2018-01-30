<?php
echo parent::getPreloader();
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

$app = JFactory::getApplication();
$model = $this->getModel();

$user = JFactory::getUser();
$user->groups = $user->get('groups');
$user->info = $user->getDealerInfo();

$userDealer = $user;

if (!(in_array(14, $user->groups) || in_array(15, $user->groups))) {
    $userDealer = JFactory::getUser($user->dealer_id);
    $userDealer->groups = $userDealer->get('groups');
    $userDealer->info = $userDealer->getDealerInfo();
}

$stock = in_array(19, $user->groups);
$managerGM = in_array(16, $user->groups) || in_array(15, $userDealer->groups) && !$stock;

$dealer = null;

$stock = $managerGM = false;
$managerGM = true;

if ($managerGM) {
    $dealerId = $app->input->get('dealer', null, 'int');

    if (isset($dealerId)) {
        $dealer = JFactory::getUser($dealerId);
        $dealer->groups = $dealer->get('groups');
        $dealer->info = $dealer->getDealerInfo();
        // $dealer->price = $model->getDealerPrice($dealerId);
    }
}

function margin($value, $margin) { return ($value * 100 / (100 - $margin)); }
function double_margin($value, $margin1, $margin2) { return margin(margin($value, $margin1), $margin2); }

?>
<link rel="stylesheet" type="text/css" href="/components/com_gm_ceiling/views/components/css/style.css">

<div class="Page">
    <div class="Title">
        Прайс компонентов<?=(isset($dealer))?" для $dealer->name #$dealer->id":"";?>.
    </div>
    <div class="Actions">
        <?=parent::getButtonBack();?>
        <?if ($managerGM):?>
        <form class="FormSimple UpdatePrice MarginLeft" action="javascript:UpdatePrice(0);">
            <label for="allPrice">Изменить цену:</label>
            <input type="text" pattern="[+-]{1}\d{1,}%{1}|[+-]{0,1}\d{1,}"  name="allPrice" id="allPrice" placeholder="0"
                   title="Формат: X, +X, -X, +X% или -X%, где X - это значение! Например: +15%."
                   size="5">
            <button type="submit" class="buttonOK">
                <i class="fa fa-paper-plane" aria-hidden="true"></i>
            </button>
        </form>
        <?endif;?>
    </div>
    <table class="Body">
        <thead>
            <tr class="THead">
                <td><i class="fa fa-bars" aria-hidden="true"></i></td>
                <td><i class="fa fa-hashtag" aria-hidden="true"></i></td>
                <td>Наименование</td>
                <td>Кол-во</td>
                <?if($stock):?>
                <td>Заказать</td>
                <td>Цена закупки</td>
                <td><i class="fa fa-cubes" aria-hidden="true"></i></td>
                <td>Изменить</td>
                <?elseif ($managerGM && empty($dealer)):?>
                <td>Цена дилера</td>
                <td>Цена клиента</td>
                <td>Изменить</td>
                <?elseif ($managerGM):?>
                <td>Цена</td>
                <td>Цена дилера</td>
                <td>Изменить</td>
                <?else:?>
                <td>Цена дилера</td>
                <td>Цена клиента</td>
                <?endif;?>
            </tr>
        </thead>
        <tbody>
        <?foreach ($this->items as $key_c => $component):?>
            <tr class="TBody Lavel1">
                <td><i class="fa fa-caret-down" aria-hidden="true"></i></td>
                <td><?=$key_c;?></td>
                <td><?=$component->title;?> <?=$component->unit;?></td>
                <td></td>
                <?if($stock):?>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                <?elseif ($managerGM && empty($dealer)):?>
                    <td></td>
                    <td></td>
                    <td></td>
                <?elseif ($managerGM):?>
                    <td></td>
                    <td></td>
                    <td></td>
                <?else:?>
                    <td></td>
                    <td></td>
                <?endif;?>
            </tr>
        <?foreach ($component->options as $key_o => $option):?>
                <tr class="TBody Lavel2" style="display: none;">
                    <td><i class="fa <?=($stock)?"fa-caret-down":"fa-caret-right";?>" aria-hidden="true"></i></td>
                    <td><?=$key_o;?></td>
                    <td><?=$component->title;?> <?=$option->title;?></td>
                    <td><?=$option->count;?></td>
                    <?if($stock):?>
                        <td></td>
                        <td><?=$option->pprice;?></td>
                        <td></td>
                        <td></td>
                    <?elseif ($managerGM && empty($dealer)):?>
                        <td><?=margin($option->price, $dealer->info->gm_components_margin);?></td>
                        <td><?=double_margin($option->price, $userDealer->info->gm_components_margin, $userDealer->info->dealer_components_margin);?></td>
                        <td></td>
                    <?elseif ($managerGM):?>
                        <td><?=margin($option->price, $dealer->info->gm_components_margin);?></td>
                        <td><?=margin($option->price, $dealer->info->gm_components_margin);?></td>
                        <td></td>
                    <?else:?>
                        <td><?=margin($option->price, $userDealer->info->gm_components_margin);?></td>
                        <td><?=double_margin($option->price, $userDealer->info->gm_components_margin, $userDealer->info->dealer_components_margin);?></td>
                    <?endif;?>
                </tr>
        <?if ($stock) foreach ($option->goods as $key_g => $good):?>
                    <tr class="TBody Lavel3" style="display: none;">
                        <td><i class="fa fa-caret-right" aria-hidden="true"></i></td>
                        <td><?=$key_g;?></td>
                        <td>#<?=$good->barcode;?> @<?=$good->article;?></td>
                        <td><?=$good->count;?></td>
                        <td></td>
                        <td><?=$good->pprice;?></td>
                        <td><?=$good->stock_name;?></td>
                        <td></td>
                    </tr>
        <?endforeach;?>
        <?endforeach;?>
        <?endforeach;?>
        </tbody>
        <tfoot>
        <tr>
            <td colspan="9"></td>
        </tr>
        </tfoot>
    </table>
</div>

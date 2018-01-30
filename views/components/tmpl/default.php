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

$userDealer = JFactory::getUser($user->dealer_id);
$userDealer->groups = $userDealer->get('groups');
$userDealer->info = $userDealer->getDealerInfo();

$stock = in_array(19, $user->groups);
$managerGM = in_array(16, $user->groups) || in_array(15, $userDealer->groups) && !$stock;
$manager = in_array(13, $user->groups) || in_array(14, $userDealer->groups) && !$managerGM && !$stock;

$dealer = null;

if ($managerGM || true) {
    $dealerId = $app->input->get('dealer', null, 'int');

    if (isset($dealerId)) {
        $dealer = JFactory::getUser($dealerId);
        $dealer->groups = $dealer->get('groups');
        $dealer->info = $dealer->getDealerInfo();
        // $dealer->price = $model->getDealerPrice($dealerId);
    }
}

?>
<link rel="stylesheet" type="text/css" href="/components/com_gm_ceiling/views/components/css/style.css">

<div class="Page">
    <div class="Title">
        Прайс компонентов<?=(isset($dealer))?" для $dealer->name #$dealer->id":"";?>.
    </div>
    <div class="Actions">
        <?=parent::getButtonBack();?>
        <?if ($managerGM && empty($dealer)):?>
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
                <td><i class="fa fa-cubes" aria-hidden="true"></i></td>
                <td>Наименование</td>
                <td><i class="fa fa-info" aria-hidden="true"></i></td>
                <td>Кол-во</td>
                <?if($stock):?>
                <td>Заказать</td>
                <td>Цена закупки</td>
                <td>Изменить</td>
                <?elseif ($managerGM && empty($dealer)):?>
                <td>Цена дилера</td>
                <td>Цена клиента</td>
                <td>Изменить</td>
                <?elseif ($managerGM):?>
                <td>Цена</td>
                <td>Цена дилера</td>
                <td>Изменить</td>
                <?endif;?>
            </tr>
        </thead>
        <tbody>

        </tbody>
        <tfoot>
        <tr>
            <td colspan="9"></td>
        </tr>
        </tfoot>
    </table>
</div>

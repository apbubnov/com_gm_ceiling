<?php
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

$model = $this->getModel();

$user = JFactory::getUser();

$userId = $user->get('id');
$groups = $user->get('groups');

$dealer = JFactory::getUser($user->dealer_id);

$listOrder = $this->state->get('list.ordering');
$listDirn = $this->state->get('list.direction');

$canCreate = (in_array(18, $groups) || in_array(19, $groups) || in_array(14, $groups));
$canEdit = (in_array(18, $groups) || in_array(19, $groups) || in_array(14, $groups));
$canDelete = (in_array(18, $groups) || in_array(19, $groups) || in_array(14, $groups));
//$canCheckin = $user->dealer_id == 775;
//$canChange  = $user->dealer_id == 775;
?>

<?= parent::getPreloader(); ?>

<form action="<?= JRoute::_('index.php?option=com_gm_ceiling&view=canvases&type=stock'); ?>" method="post"
      name="adminForm"
      id="adminForm">

    <? /*=JLayoutHelper::render('default_filter', array('view' => $this), dirname(__FILE__));*/ ?>

<?=parent::getButtonBack();?>

    <a class="btn btn-large btn-primary show-hide"><i class="fa fa-angle-down" aria-hidden="true"></i>
        <span>Раскрыть все</span></a>

    <table class="table table-striped" id="canvasList">
        <thead>
        <tr>
            <th class='center'>
                <i class="fa fa-bars" aria-hidden="true"></i>
            </th>
            <th class='center'>
                <?= JHtml::_('grid.sort', 'ID', 'id', $listDirn, $listOrder); ?>
            </th>
            <th class='center'>
                <?= JHtml::_('grid.sort', 'Склад', 'id', $listDirn, $listOrder); ?>
            </th>
            <th class='center'>
                <?= JHtml::_('grid.sort', 'Наименование полотна', 'full_name', $listDirn, $listOrder); ?>
            </th>
            <th class='center'>
                <?= JHtml::_('grid.sort', 'Фактура полотна', 'texture_title', $listDirn, $listOrder); ?>
            </th>
            <th class='center'>
                <?= JHtml::_('grid.sort', 'Цвет полотна', 'color_title', $listDirn, $listOrder); ?>
            </th>
            <th class='center'>
                <?= JHtml::_('grid.sort', 'Квадратура', 'lenght', $listDirn, $listOrder); ?>
            </th>
            <th class='center'>
                <?= JHtml::_('grid.sort', 'Количество', 'count', $listDirn, $listOrder); ?>
            </th>
            <th class='center'>
                <?= JHtml::_('grid.sort', 'Цена закупки', 'purchasing_price', $listDirn, $listOrder); ?>
            </th>
        </tr>
        </thead>

        <tbody>
        <? $cIndex = 0; foreach ($this->items as $key => $category): $cIndex++; ?>
            <tr class="canvases category category_<?= $cIndex; ?>" category="<?= $cIndex; ?>">
                <td class="center one-touch"><i class="fa fa-angle-down show_hide" aria-hidden="true"></i></td>
                <td class="center one-touch" colspan="2"></td>
                <td class="center one-touch"><?= $category['full_name']; ?></td>
                <td class="center one-touch"><?= $category['texture_title']; ?></td>
                <td class="center one-touch custom_color"
                    style="<?= (!empty($category['color_file'])) ? 'background-image: url(\'' . $category['color_file'] . '\')' : 'background-color: ' . $category['color_hex']; ?>">
                    <span style="padding: 2px 4px; background-color: rgba(255,255,255,.8);"><?= $category['color_title']; ?></span>
                </td>
                <td class="center one-touch"><?= $category['lenght']; ?> м²</td>
                <td class="center one-touch"><?= $category['count']; ?></td>
                <td class="center one-touch"></td>
            </tr>
            <? $wIndex = 0; foreach ($category['child'] as $keyWidth => $catWidth): $wIndex++; ?>
                <tr class="canvases catWidth category_<?= $cIndex; ?>  catWidth_<?= $cIndex.'_' .$wIndex; ?>"
                    category="<?= $cIndex; ?>" catWidth="<?= $cIndex.'_'.$wIndex; ?>" style="cursor: pointer; display: none;">
                    <td class="center one-touch"><i class="fa fa-angle-down show_hide" aria-hidden="true"></i></td>
                    <td class="center one-touch"><?= $catWidth['id']; ?></td>
                    <td class="center one-touch"></td>
                    <td class="center one-touch">Ширина: <?= $catWidth['width']; ?></td>
                    <td class="center one-touch" colspan="2"></td>
                    <td class="center one-touch"><?= $catWidth['lenght']; ?> м²</td>
                    <td class="center one-touch"><?= $catWidth['count']; ?></td>
                    <td class="center one-touch"></td>
                </tr>
                <? $aIndex = 0; foreach ($catWidth['child'] as $keyAll => $catAll): $aIndex++; ?>
                    <tr class="canvases catAll category_<?= $cIndex; ?> catWidth_<?= $cIndex.'_'.$wIndex; ?> catAll_<?= $cIndex.'_'.$wIndex.'_'.$aIndex; ?>"
                        data-href="<?= JRoute::_('index.php?option=com_gm_ceiling&view=stock&type=info&subtype=canvas&id=' . intval($catWidth['id'])); ?>"
                        catAll="<?= $cIndex.'_'.$wIndex.'_'.$aIndex; ?>" style="cursor: pointer; display: none;">
                        <td class="center one-touch"></td>
                        <td class="center one-touch"><?= $catAll['id']; ?></td>
                        <td class="center one-touch"><?= $catAll['stock']; ?></td>
                        <td class="center one-touch" colspan="3"></td>
                        <td class="center one-touch"><?= $catAll['lenght']; ?> м²</td>
                        <td class="center one-touch">1</td>
                        <td class="center one-touch"><?= $catAll['purchasing_price']; ?></td>
                    </tr>
                <? endforeach; ?>
            <? endforeach; ?>
        <? endforeach; ?>
        </tbody>
    </table>

    <input type="hidden" name="task" value=""/>
    <input type="hidden" name="boxchecked" value="0"/>
    <input type="hidden" name="filter_order" value="<?= $listOrder; ?>"/>
    <input type="hidden" name="filter_order_Dir" value="<?= $listDirn; ?>"/>
    <?= JHtml::_('form.token'); ?>
</form>

<style>
    table {
        margin-top: 15px;
    }

    .custom_color {
        background-size: cover;
    }

    #canvasList .canvases:not(.catAll) {
        cursor: pointer !important;
    }

    #canvasList .category td:not(.custom_color) {
        background-color: rgba(229, 229, 229, 1) !important;
    }

    #canvasList .category:hover td:not(.custom_color) {
        background-color: rgba(240, 240, 240, 1) !important;
    }

    #canvasList .catWidth {
        background-color: rgba(215, 215, 215, 1) !important;
    }

    #canvasList .catWidth:hover td {
        background-color: rgba(225, 225, 225, 1) !important;
    }

    #canvasList .catAll {
        background-color: rgba(255, 255, 255, 1) !important;
    }

    #canvasList .catAll:hover td {
        background-color: rgba(245, 245, 245, 1) !important;
    }
</style>

<script>
    jQuery(document).ready(function () {
        jQuery(".category").click(function () {
            
            jQuery.each(jQuery(".canvases .show_hide"), function (key, val) {
                jQuery(val).removeClass('fa-angle-up').addClass('fa-angle-down');
            });

            var _this = jQuery(this);
            var id_category = _this.attr("category");
            jQuery(".canvases").filter(":not(.category)").hide();

            if (_this.val() == 0) {
                jQuery(".category").val(0);
                _this.val(1).find(".show_hide").removeClass('fa-angle-down').addClass('fa-angle-up');
                jQuery(".category_" + id_category).filter(".catWidth").show();
            }
            else {
                _this.val(0).find(".show_hide").removeClass('fa-angle-up').addClass('fa-angle-down');
                jQuery(".catWidth").val(0);
                jQuery(".canvases").filter(":not(.category)").hide();
            }

            jQuery(".show-hide").val(0).children("i").removeClass("fa-angle-up").addClass('fa-angle-down');
            jQuery(".show-hide span").html("Раскрыть все");
        });

        jQuery(".catWidth").click(function () {

            jQuery.each(jQuery(".canvases .show_hide"), function (key, val) {
                jQuery(val).removeClass('fa-angle-up').addClass('fa-angle-down');
            });

            var _this = jQuery(this);
            var id_category = _this.attr("category");
            var id_catWidth = _this.attr("catWidth");
            jQuery(".catWidth").filter(":not(.category_"+id_category+")").hide()
            jQuery(".canvases").filter(".catAll").hide();
            if (_this.val() == 0) {
                jQuery(".category").filter(".category_" + id_category).val(1);
                jQuery(".catWidth").val(0);
                _this.val(1).find(".show_hide").removeClass('fa-angle-down').addClass('fa-angle-up');
                jQuery(".catWidth_" + id_catWidth).filter(".catAll").show();
            }
            else {
                _this.val(0).find(".show_hide").removeClass('fa-angle-up').addClass('fa-angle-down');
                jQuery(".canvases").filter(".catAll").hide();
            }

            jQuery(".show-hide").val(0).children("i").removeClass("fa-angle-up").addClass('fa-angle-down');
            jQuery(".show-hide span").html("Раскрыть все");
        });

        jQuery(".show-hide").click(function () {
            var _this = jQuery(this);
            var text = _this.children("span");
            if (_this.val() == 0) {
                _this.val(1).children("i").removeClass("fa-angle-down").addClass('fa-angle-up');
                text.html("Скрыть все");
                jQuery(".canvases").filter(":not(.category)").show();

                jQuery.each(jQuery(".canvases .show_hide"), function (key, val) {
                    jQuery(val).removeClass('fa-angle-down').addClass('fa-angle-up');
                });
            }
            else {
                jQuery(".category").val(0);
                jQuery(".catWidth").val(0);
                _this.val(0).children("i").removeClass("fa-angle-up").addClass('fa-angle-down');
                text.html("Раскрыть все");
                jQuery(".canvases").filter(":not(.category)").hide();

                jQuery.each(jQuery(".canvases .show_hide"), function (key, val) {
                    jQuery(val).removeClass('fa-angle-up').addClass('fa-angle-down');
                });
            }
        });

        jQuery('.table tbody tr.catAll').click(function () {
            window.open(jQuery(this).data('href'), '_top');
        });
    });
</script>
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
$dealer_info = $model->getDealerInfo($user->dealer_id);

$listOrder = $this->state->get('list.ordering');
$listDirn = $this->state->get('list.direction');

$canCreate = $user->dealer_id == 1;
$canEdit = $user->dealer_id == 1;
$canDelete = $user->dealer_id == 1;
//$canCheckin = $user->dealer_id == 1;
//$canChange  = $user->dealer_id == 1;
?>
<?= parent::getPreloader(); ?>
<form action="<?= JRoute::_('index.php?option=com_gm_ceiling&view=components'); ?>" method="post"
      name="adminForm" id="adminForm">

    <? /*=JLayoutHelper::render('default_filter', array('view' => $this), dirname(__FILE__));*/ ?>

   <?=parent::getButtonBack();?>

    <a class="btn btn-large btn-primary show-hide"><i class="fa fa-angle-down" aria-hidden="true"></i>
        <span>Раскрыть все</span></a>
    
    <table class="table table-striped" id="componentList">
        <thead>
        <tr>
            <th class='center'>
                <i class="fa fa-bars" aria-hidden="true"></i>
            </th>
            <th class='center'>
                <?= JHtml::_('grid.sort', 'ID', 'id', $listDirn, $listOrder); ?>
            </th>
            <th class='center'>
                <?= JHtml::_('grid.sort', 'Склад', 'stock', $listDirn, $listOrder); ?>
            </th>
            <th class='center'>
                <?= JHtml::_('grid.sort', 'Наименование', 'full_name', $listDirn, $listOrder); ?>
            </th>
            <th class='center'>
                <?= JHtml::_('grid.sort', 'Единица измерения', 'unit', $listDirn, $listOrder); ?>
            </th>
            <th class="center">
                <?= JHtml::_('grid.sort', 'Количество', 'count_comp', $listDirn, $listOrder); ?>
            </th>
            <!--<th class='center'>
                <?= JHtml::_('grid.sort', 'Цена закупочная', 'purchasing_price', $listDirn, $listOrder); ?>
            </th>-->
            <th class='center'>
                <?= JHtml::_('grid.sort', 'Цена для дилера', 'price', $listDirn, $listOrder); ?>
            </th>
            <th class='center'>
                <?= JHtml::_('grid.sort', 'Цена для клиента', 'price', $listDirn, $listOrder); ?>
            </th>
            <? if ($canEdit): ?>
                <th class="center can">Изменить цену для дилера</th>
            <? endif; ?>
        </tr>
        </thead>
        <tbody>
        <?foreach ($this->items as $id_component => $component) : ?>
            <tr class="type component_type" component="<?= $id_component; ?>" style="cursor: pointer;">
                <td class="center one-touch"><i class="fa fa-angle-down show_hide" aria-hidden="true"></i></td>
                <td class="center one-touch"><?= $component['id']; ?></td>
                <td class="center one-touch"></td>
                <td class="center one-touch"><?= $component['title']; ?></td>
                <td class="center one-touch"><?= $component['unit']; ?></td>
                <td class="center one-touch"><?= $component['count']; ?></td>
                <td class="center one-touch" colspan="3"></td>
            </tr>

            <? foreach ($component['options'] as $id_option => $option): ?>
                <tr class="type component_option type_<?= $id_option; ?> component_<?= $id_component; ?>"
                    style="display: none;">
                    <td class="center one-touch"></td>
                    <td class="center one-touch id"><?= $option['id']; ?></td>
                    <td class="center one-touch"><?= $option['stock']; ?></td>
                    <td class="center one-touch"><?= $option['full_name']; ?></td>
                    <td class="center one-touch"><?= $component['unit']; ?></td>
                    <td class="center one-touch"><?= $option['option_count']; ?></td>
                    <!--<td class="center one-touch"><?= $option['purchasing_price']; ?></td>-->
                    <td class="center one-touch price"><?= $option['price']; ?></td>
                    <td class="center one-touch client_price"><?= (100 * $option['price'])/(100 - $dealer_info->dealer_components_margin); ?></td>
                    <? if ($canEdit): ?>
                        <td class="center update_price">
                            <div class="update_price">
                                <input type="text" class="new_price" value="<?=$option['price'];?>">
                                <button type="submit" onsubmit="saveSum(this);" formaction="javascript:false;" class="save" onclick="saveSum(this);">
                                    <i class="fas fa-save" aria-hidden="true"></i>
                                </button>
                            </div>
                        </td>
                    <? endif; ?>
                </tr>
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

    .component_type {
        background-color: rgba(0, 0, 0, .1) !important;
    }

    .component_option {
        background-color: rgba(255, 255, 255, 1) !important;
        cursor: default !important;
    }

    .component_option:hover td {
        background: none !important;
    }

    .show_hide {
        color: rgb(0, 0, 0);
        font-size: 20px;
    }

    #componentList .update_price {
        width: auto;
        min-width: 100px;
    }
    #componentList .update_price .new_price {
        width: calc(100% - 30px);
        min-width: 50px;
        height: 30px;
        float: left;
        border-radius: 5px 0 0 5px;
        border: none;
        box-shadow: inset 0 0 1px 1px rgb(64, 65, 154);
        padding: 0 5px;
        margin: 0;
    }
    #componentList .update_price .save {
        width: 30px;
        height: 30px;
        float: left;
        border-radius: 0 5px 5px 0;
        background-color: rgb(64, 65, 154);
        color: rgb(255, 255, 255);
        border: none;
        margin: 0;
        cursor: pointer;
    }
</style>

<? if ($canDelete) : ?>
    <script type="text/javascript">

        jQuery(document).ready(function () {
            jQuery('.delete-button').click(deleteItem);
        });

        function deleteItem() {

            if (!confirm("<?=JText::_('COM_GM_CEILING_DELETE_MESSAGE'); ?>")) {
                return false;
            }
        }
    </script>
<? endif; ?>

<script>

    var $ = jQuery;

    function saveSum(e) {
        e = $(e);
        var parent = e.closest("tr"),
            id = parseInt(parent.find(".id").text()),
            price = parseInt(parent.find(".new_price").val());

        jQuery.ajax({
            type: 'POST',
            url: "/index.php?option=com_gm_ceiling&task=components.setPrice",
            data: {id: id, price: price},
            cache: false,
            async: false,
            success: function (data) {
                data = JSON.parse(data);

                if (data.status === "success")
                {
                    parent.find(".price").text(data.data.price);
                    parent.find(".client_price").text(data.data.client_price);
                }

                noty({
                    theme: 'relax',
                    layout: 'center',
                    timeout: 1500,
                    type: data.status,
                    text: data.message
                });
            },
            dataType: "text",
            timeout: 15000,
            error: function () {
                noty({
                    theme: 'relax',
                    layout: 'center',
                    timeout: 1500,
                    type: "error",
                    text: "Сервер не отвечает!"
                });
            }
        });
    }

    jQuery(document).ready(function () {
        jQuery("#change_margin").click(function () {
            jQuery(".new_margin").show();
        });

        jQuery("#update_margin").click(function () {
            jQuery("input[name='ismarginChange']").val(1);
            jQuery(".new_margin").hide();
        });


        jQuery("#update_margin").click(function () {
            jQuery.ajax({
                type: 'POST',
                url: "index.php?option=com_gm_ceiling&task=update_margin",
                data: {
                    new_margin: jQuery("#jform_new_margin").val(),
                    type: 2
                },
                success: function (data) {

                    var answer = "Данные успешно изменены";

                    if (data[0] === '{') {
                        var info = jQuery.parseJSON(data);
                        answer = info.answer_error;
                    }

                    var n = noty({
                        theme: 'relax',
                        modal: true,
                        layout: 'center',
                        text: answer
                    });

                    setTimeout(function () {
                        location.reload();
                    }, 2000);
                },
                dataType: "text",
                timeout: 10000,
                error: function () {
                    var n = noty({
                        theme: 'relax',
                        layout: 'center',
                        maxVisible: 5,
                        type: "error",
                        text: "Ошибка при попытке обновить процент маржинальности. Сервер не отвечает"
                    });
                }
            });
        });

        jQuery(".component_type").click(function () {
            jQuery(".component_option").hide();
            var _this = jQuery(this);
            var id_component = _this.attr("component");
            if (_this.val() == 0) {
                jQuery(".component_type").val(0);
                jQuery.each(jQuery(".show_hide"), function (key, val) {
                    jQuery(val).removeClass('fa-angle-up').addClass('fa-angle-down');
                });
                _this.val(1);
                jQuery(".component_" + id_component).show();
                jQuery(_this).find(".show_hide").removeClass('fa-angle-down').addClass('fa-angle-up');
            }
            else {
                _this.val(0);
                jQuery(".component_" + id_component).hide();
                jQuery(_this).find(".show_hide").removeClass('fa-angle-up').addClass('fa-angle-down');
            }

            jQuery(".show-hide").val(0);
            jQuery(".show-hide span").html("Раскрыть все");
            jQuery(".show-hide i").removeClass('fa-angle-up').addClass('fa-angle-down');
        });

        jQuery(".show-hide").click(function () {
            var _this = jQuery(this);
            var text = _this.children("span");
            if (_this.val() == 0) {
                _this.val(1);
                _this.children("i").removeClass("fa-angle-down").addClass('fa-angle-up');
                text.html("Скрыть все");
                jQuery(".component_option").show();
                jQuery.each(jQuery(".show_hide"), function (key, val) {
                    jQuery(val).removeClass('fa-angle-down').addClass('fa-angle-up');
                });
            }
            else {
                _this.val(0);
                _this.children("i").removeClass("fa-angle-up").addClass('fa-angle-down');
                text.html("Раскрыть все");
                jQuery(".component_option").hide();
                jQuery.each(jQuery(".show_hide"), function (key, val) {
                    jQuery(val).removeClass('fa-angle-up').addClass('fa-angle-down');
                });
            }
        });
    });
</script>
<script language="JavaScript">
    function PressEnter(your_text, your_event) {
        if (your_text != "" && your_event.keyCode == 13)
            jQuery("#update_margin").click();
    }
</script>


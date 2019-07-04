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

$user       = JFactory::getUser();
$userId     = $user->get('id');
$listOrder  = $this->state->get('list.ordering');
$listDirn   = $this->state->get('list.direction');
$canCreate  = $user->dealer_id == 1;
$canEdit    = $user->dealer_id == 1;
$canCheckin = $user->dealer_id == 1;
$canChange  = $user->dealer_id == 1;
$canDelete  = $user->dealer_id == 1;
$dealer = JFactory::getUser($user->dealer_id);
?>
<?= parent::getPreloader(); ?>


<form action="<?= JRoute::_('index.php?option=com_gm_ceiling&view=canvases'); ?>" method="post"
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
            <!--<th class='center'>
                <?= JHtml::_('grid.sort', 'Цена закупки', 'purchasing_price', $listDirn, $listOrder); ?>
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
        <? $cIndex = 0; foreach ($this->items as $key => $category): $cIndex++; ?>
            <tr class="canvases category category_<?= $cIndex; ?>" category="<?= $cIndex; ?>">
                <td class="center one-touch"><i class="fa fa-angle-down show_hide" aria-hidden="true"></i></td>
                <td class="center one-touch"></td>
                <td class="center one-touch"><?= $category['full_name']; ?></td>
                <td class="center one-touch"><?= $category['texture_title']; ?></td>
                <td class="center one-touch custom_color"
                    style="<?= (!empty($category['color_file'])) ? 'background-image: url(\'' . $category['color_file'] . '\')' : 'background-color: ' . $category['color_hex']; ?>">
                    <span style="padding: 2px 4px; background-color: rgba(255,255,255,.8);"><?= $category['color_title']; ?></span>
                </td>
                <td class="center one-touch"><?= $category['lenght']; ?> м²</td>
                <td class="center one-touch" colspan="3"></td>
            </tr>
            <? $wIndex = 0; foreach ($category['child'] as $keyWidth => $catWidth): $wIndex++; ?>
                <tr class="canvases catWidth category_<?= $cIndex; ?>  catWidth_<?= $cIndex.'_' .$wIndex; ?>"
                    category="<?= $cIndex; ?>" catWidth="<?= $cIndex.'_'.$wIndex; ?>" style="cursor: pointer; display: none;">
                    <td class="center one-touch"></td>
                    <td class="center one-touch id"><?= $catWidth['id']; ?></td>
                    <td class="center one-touch">Ширина: <?= $catWidth['width']; ?></td>
                    <td class="center one-touch" colspan="2"></td>
                    <td class="center one-touch"><?= $catWidth['lenght']; ?> м²</td>
                    <!--<td class="center one-touch"><?= $catWidth['one_purchasing_price']; ?></td>-->
                    <td class="center one-touch price"><?= $catWidth['one_price']; ?></td>
                    <td class="center one-touch client_price"><?= $catWidth['one_client_price']; ?></td>
                    <? if ($canEdit): ?>
                        <td class="center update_price">
                            <div class="update_price">
                                <input type="text" class="new_price" value="<?=$catWidth['one_price'];?>">
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

    .show_hide {
        color: rgb(0, 0, 0);
        font-size: 20px;
    }

    #canvasList .update_price {
        width: auto;
        min-width: 100px;
    }
    #canvasList .update_price .new_price {
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
    #canvasList .update_price .save {
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

<script>
    var $ = jQuery;

    function saveSum(e) {
        e = $(e);
        var parent = e.closest("tr"),
            id = parseInt(parent.find(".id").text()),
            price = parseInt(parent.find(".new_price").val());

        jQuery.ajax({
            type: 'POST',
            url: "/index.php?option=com_gm_ceiling&task=canvases.setPrice",
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

        jQuery(".catWidth").click(function () {
            var _this = jQuery(this);
            var id_category = _this.attr("category");

            jQuery(".catWidth").filter(":not(.category_"+id_category+")").hide();
            jQuery(".category").filter(":not(.category_"+id_category+")").val(0);

            jQuery(".show-hide").val(0).find(span).html("Раскрыть все");
            jQuery(".show-hide i").removeClass('fa-angle-up').addClass('fa-angle-down');
        });

        jQuery(".category").click(function () {
            var _this = jQuery(this);
            var id_category = _this.attr("category");

            jQuery(".canvases").filter(":not(.category)").hide();
            jQuery.each(jQuery(".canvases .show_hide"), function (key, val) {
                jQuery(val).removeClass('fa-angle-up').addClass('fa-angle-down');
            });

            if (_this.val() == 0) {
                jQuery(".category").val(0);
                _this.val(1).find(".show_hide").removeClass('fa-angle-down').addClass('fa-angle-up');

                jQuery(".catWidth").val(0);
                jQuery(".category_" + id_category).filter(".catWidth").val(1).show();
            }
            else {
                _this.val(0);
                jQuery(".catWidth").val(0);
            }

            jQuery(".show-hide").val(0).find(span).html("Раскрыть все");
            jQuery(".show-hide i").removeClass('fa-angle-up').addClass('fa-angle-down');
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
                _this.val(0).children("i").removeClass("fa-angle-up").addClass('fa-angle-down');

                text.html("Раскрыть все");
                jQuery(".canvases").filter(":not(.category)").hide();

                jQuery.each(jQuery(".canvases .show_hide"), function (key, val) {
                    jQuery(val).removeClass('fa-angle-up').addClass('fa-angle-down');
                });
            }
            jQuery(".canvases").val(0);
        });

        jQuery("#change_margin").click(function(){
            jQuery(".new_margin").show();
        });

        jQuery("#update_margin").click(function(){
            jQuery("input[name='ismarginChange']").val(1);
            jQuery(".new_margin").hide();
        });
    });

    jQuery("#update_margin").click(function(){
		jQuery.ajax({
			type: 'POST',
			url: "index.php?option=com_gm_ceiling&task=update_margin",
			data: {
				new_margin: jQuery("#jform_new_margin").val(),
				type: 1
			},

			success: function(data){

			    var answer = "Данные успешно изменены";

			    if (data[0] === '{') {
                    var info = jQuery.parseJSON(data);
			        answer = info.answer_error;
			    } else {
                    data = jQuery("<div/>", {"html":data}).find('#canvasList').html()
                    jQuery("#canvasList").html(data);
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
			error: function(){
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
</script>
<script language="JavaScript">
		function PressEnter(your_text, your_event) {
		  if(your_text != "" && your_event.keyCode == 13)
			jQuery("#update_margin").click();
		}
</script>
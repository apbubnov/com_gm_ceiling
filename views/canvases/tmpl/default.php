<?php
echo parent::getPreloaderNotJS();
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
$listOrder = $this->state->get('list.ordering');
$listDirn = $this->state->get('list.direction');

$app = JFactory::getApplication();
$model = $this->getModel();

$user = JFactory::getUser();
$user->groups = $user->get('groups');
$user->getDealerInfo();
$user->getCanvasesPrice();

if (!(in_array(14, $user->groups) || in_array(15, $user->groups))) {
    $userDealer = JFactory::getUser($user->dealer_id);
    $userDealer->groups = $userDealer->get('groups');
    $userDealer->getDealerInfo();
    $userDealer->getCanvasesPrice();
} else {
    $userDealer = $user;
}

$managerGM = in_array(16, $user->groups) || in_array(15, $userDealer->groups);
$stock = in_array(19, $user->groups);

$dealer = null;

if ($managerGM) {
    $dealerId = $app->input->get('dealer', null, 'int');

    if (isset($dealerId)) {
        $dealer = JFactory::getUser($dealerId);
        $dealer->groups = $dealer->get('groups');
        $dealer->getDealerInfo();
        $dealer->getCanvasesPrice();
    }
}

function margin($value, $margin){return ($value * 100 / (100 - $margin));}
function double_margin($value, $margin1, $margin2){return margin(margin($value, $margin1), $margin2);}
function dealer_margin($price, $margin, $objectDealerPrice) {
    $result = 0;
    if (!empty($objectDealerPrice)) {
        $objectDealerPrice->value = floatval($objectDealerPrice->value);
        $objectDealerPrice->price = floatval($objectDealerPrice->price);

        switch ($objectDealerPrice->type)
        {
            case 0: $result = $price; break;
            case 1: $result = $objectDealerPrice->price; break;
            case 2: $result = $price + $objectDealerPrice->value; break;
            case 3: $result = $price + $price * $objectDealerPrice->value / 100; break;
            case 4: $result = $objectDealerPrice->price + $objectDealerPrice->value; break;
            case 5: $result = $objectDealerPrice->price + $objectDealerPrice->price * $objectDealerPrice->value / 100; break;
        }
    } else {
        $result = $price;
    }
    return margin($result, $margin);
}

function update_price($objectDealerPrice, $Price)
{
    $percent = ($objectDealerPrice->type == 3 || $objectDealerPrice->type == 5);
    $value = abs($objectDealerPrice->value);
    $valueSTR = (($objectDealerPrice->value == abs($objectDealerPrice->value))?" + ":" - ") . $value;

    $updatePrice = "";
    if ($objectDealerPrice->price != $Price) $updatePrice .= $objectDealerPrice->price;
    if ($value != 0) $updatePrice .= $valueSTR . (($percent)?"%":"");

    return $updatePrice;
}

?>
<link rel="stylesheet" type="text/css"
      href="/components/com_gm_ceiling/views/canvases/css/style.css?date=<?= date("H.i.s"); ?>">
<div class="Page">
    <div class="Title">
        Прайс полотен<?= (isset($dealer)) ? " для $dealer->name #$dealer->id" : ""; ?>.
    </div>
    <div class="Actions">
        <?= parent::getButtonBack(); ?>
        <button type="button" class="Current ActionTR" id="ActionTR">
            <i class="fa fa-caret-down" aria-hidden="true"></i> <span>Раскрыть все</span>
        </button>
        <? if ($managerGM): ?>
            <form class="FormSimple UpdatePrice MarginLeft" data-type="ALL">
                <label for="Price" title="Изменить все дилерские цены"><i class="fas fa-edit"
                                                                          aria-hidden="true"></i></label>
                <input type="text" pattern="([+-]{0,1}\d+[,.]{0,1}\d*%{0,1})|(\*)" name="Price" id="Price"
                       placeholder="0"
                       title="Формат: *, X, +X, -X, +X% или -X%, где X - это значение, * - очистить! Например: +15%."
                       size="5" required>
                <button type="submit" class="buttonOK">
                    <i class="fa fa-paper-plane" aria-hidden="true"></i>
                </button>
            </form>
        <? endif; ?>
    </div>
    <div class="Scroll">
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
                <td><?= JHtml::_('grid.sort', 'Количество', 'canvas_count', $listDirn, $listOrder); ?></td>
                <? if ($stock): ?>
                    <td>Заказать</td>
                    <td>Цена закупки</td>
                    <td><i class="fa fa-cubes" aria-hidden="true"></i></td>
                    <td>Посмотреть</td>
                <? elseif ($managerGM && empty($dealer)): ?>
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
            <? foreach ($this->items as $key_TC => $canvasTC): ?>
                <tr class="TBody Level1 Action" data-canvas="<?= $key_TC; ?>" data-level="1">
                    <td><i class="fa fa-caret-down" aria-hidden="true"></i></td>
                    <td></td>
                    <td title="Фактура"><?= $canvasTC->texture_title; ?></td>
                    <td></td>
                    <td><?= $canvasTC->count; ?></td>
                    <? if ($stock): ?>
                        <td><?= $canvasTC->ocount; ?></td>
                        <td></td>
                        <td></td>
                        <td></td>
                    <? elseif ($managerGM && empty($dealer)): ?>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td>
                            <form class="FormSimple UpdatePrice MarginLeft" data-id="<?= $key_TC; ?>">
                                <label for="Price" title="Изменить дилерскую цену"><i class="fas fa-edit"
                                                                                      aria-hidden="true"></i></label>
                                <input type="text" pattern="[+-]{0,1}\d+[,.]{0,1}\d*%{0,1}"
                                       name="Price" id="Price"
                                       placeholder="0"
                                       title="Формат: X, +X, -X, +X% или -X%, где X - это значение! Например: +15%."
                                       size="5" required>
                                <button type="submit" class="buttonOK">
                                    <i class="fa fa-paper-plane" aria-hidden="true"></i>
                                </button>
                            </form>
                        </td>
                    <? elseif ($managerGM): ?>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td>
                            <form class="FormSimple UpdatePrice MarginLeft" data-id="<?= $key_TC; ?>">
                                <label for="Price" title="Изменить дилерскую цену"><i class="fas fa-edit"
                                                                                      aria-hidden="true"></i></label>
                                <input type="text" pattern="([+-]{0,1}\d+[,.]{0,1}\d*%{0,1})|(\*)" name="Price" id="Price"
                                       placeholder="0"
                                       title="Формат: *, X, +X, -X, +X% или -X%, где X - это значение, * - очистить! Например: +15%."
                                       size="5" required>
                                <button type="submit" class="buttonOK">
                                    <i class="fa fa-paper-plane" aria-hidden="true"></i>
                                </button>
                            </form>
                        </td>
                    <? else: ?>
                        <td></td>
                        <td></td>
                    <? endif; ?>
                </tr>
                <? foreach ($canvasTC->canvases as $key_N => $canvaseN): ?>
                    <tr class="TBody Level2 Action" style="display: none;" data-canvas="<?= $key_N; ?>" data-level="2">
                        <td><i class="fa fa-caret-down" aria-hidden="true"></i></td>
                        <td></td>
                        <td title="Страна - Название"><?= $canvaseN->country . " - " . $canvaseN->name; ?></td>
                        <td></td>
                        <td><?= $canvaseN->count; ?></td>
                        <? if ($stock): ?>
                            <td><?= $canvaseN->ocount; ?></td>
                            <td></td>
                            <td></td>
                            <td></td>
                        <? elseif ($managerGM && empty($dealer)): ?>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td>
                                <form class="FormSimple UpdatePrice MarginLeft" data-id="<?= $key_TC."/".$key_N; ?>">
                                    <label for="Price" title="Изменить дилерскую цену"><i class="fas fa-edit"
                                                                                          aria-hidden="true"></i></label>
                                    <input type="text" pattern="([+-]{0,1}\d+[,.]{0,1}\d*%{0,1})|(\*)" name="Price" id="Price"
                                           placeholder="0"
                                           title="Формат: *, X, +X, -X, +X% или -X%, где X - это значение, * - очистить! Например: +15%."
                                           size="5" required>
                                    <button type="submit" class="buttonOK">
                                        <i class="fa fa-paper-plane" aria-hidden="true"></i>
                                    </button>
                                </form>
                            </td>
                        <? elseif ($managerGM): ?>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td>
                                <form class="FormSimple UpdatePrice MarginLeft" data-id="<?= $key_TC."/".$key_N; ?>">
                                    <label for="Price" title="Изменить дилерскую цену"><i class="fas fa-edit"
                                                                                          aria-hidden="true"></i></label>
                                    <input type="text" pattern="([+-]{0,1}\d+[,.]{0,1}\d*%{0,1})|(\*)" name="Price" id="Price"
                                           placeholder="0"
                                           title="Формат: *, X, +X, -X, +X% или -X%, где X - это значение, * - очистить! Например: +15%."
                                           size="5" required>
                                    <button type="submit" class="buttonOK">
                                        <i class="fa fa-paper-plane" aria-hidden="true"></i>
                                    </button>
                                </form>
                            </td>
                        <? else: ?>
                            <td></td>
                            <td></td>
                        <? endif; ?>
                    </tr>
                    <? foreach ($canvaseN->canvases as $key_c => $canvas): if ($stock || $canvas->count > 0): ?>
                        <tr class="TBody Level3 <?= ($stock && $canvas->count > 0) ? "Action" : "" ?>"
                            style="display: none;" data-canvas="<?= $key_c; ?>" data-level="3">
                            <td>
                                <i class="fa <?= ($stock && $canvas->count > 0) ? "fa-caret-down" : "fa-caret-right"; ?>"
                                   aria-hidden="true"></i></td>
                            <td><?= $canvas->id; ?></td>
                            <td title="Ширина"><?= $canvas->width; ?></td>
                            <td class="Color" style="
                                    background-color: #<?= $canvas->color_hex; ?>;
                                    background-image: url('<?= $canvas->color_file; ?>');
                                    "><?= $canvas->color_title; ?></td>
                            <td><?= $canvas->count; ?> шт.</td>
                            <? if ($stock): ?>
                                <td><?= $canvas->ocount; ?> шт.</td>
                                <td><?= $canvas->pprice; ?> руб./м<sup>2</sup></td>
                                <td></td>
                                <td>
                                    <a href="/index.php?option=com_gm_ceiling&view=stock&type=info&subtype=canvas&id=<?= $key_c; ?>">Инфо</a>
                                </td>
                            <? elseif ($managerGM && empty($dealer)): ?>
                                <td id="GMPrice"><?= $canvas->price; ?></td>
                                <td id="GMPrice"><?= margin($canvas->price, $dealer->gm_canvases_margin); ?></td>
                                <td id="DealerPrice"><?= double_margin($canvas->price, $userDealer->gm_canvases_margin, $userDealer->dealer_canvases_margin); ?></td>
                                <td>
                                    <form class="FormSimple UpdatePrice MarginLeft" data-id="<?= $key_c; ?>">
                                        <label for="Price" title="Изменить дилерскую цену"><i
                                                    class="fas fa-edit"
                                                    aria-hidden="true"></i></label>
                                        <input type="text" pattern="([+-]{0,1}\d+[,.]{0,1}\d*%{0,1})|(\*)" name="Price" id="Price"
                                               placeholder="0"
                                               title="Формат: *, X, +X, -X, +X% или -X%, где X - это значение, * - очистить! Например: +15%."
                                               size="5" required>
                                        <button type="submit" class="buttonOK">
                                            <i class="fa fa-paper-plane" aria-hidden="true"></i>
                                        </button>
                                    </form>
                                </td>
                            <? elseif ($managerGM): ?>
                                <?
                                $Price = margin($canvas->price, $dealer->gm_canvases_margin);
                                $DealerPrice = dealer_margin($Price, 0, $dealer->CanvasesPrice[$key_c]);
                                $UpdatePrice = $DealerPrice - $Price;
                                ?>
                                <td id="GMPrice"><?= $Price; ?></td>
                                <td id="UpdateDealerPrice"><?= update_price($dealer->CanvasesPrice[$key_c], $Price); ?></td>
                                <td id="DealerPrice"><?= $DealerPrice; ?></td>
                                <td>
                                    <form class="FormSimple UpdatePrice MarginLeft" data-id="<?= $key_c; ?>">
                                        <label for="Price" title="Изменить дилерскую цену"><i
                                                    class="fas fa-edit"
                                                    aria-hidden="true"></i></label>
                                        <input type="text" pattern="([+-]{0,1}\d+[,.]{0,1}\d*%{0,1})|(\*)" name="Price" id="Price"
                                               placeholder="0"
                                               title="Формат: *, X, +X, -X, +X% или -X%, где X - это значение, * - очистить! Например: +15%."
                                               size="5" required>
                                        <button type="submit" class="buttonOK">
                                            <i class="fa fa-paper-plane" aria-hidden="true"></i>
                                        </button>
                                    </form>
                                </td>
                            <? else: ?>
                                <?
                                $TempPrice = margin($canvas->price, $userDealer->gm_canvases_margin);
                                ?>
                                <td><?= dealer_margin($TempPrice, 0, $userDealer->CanvasesPrice[$key_c])?></td>
                                <td><?= dealer_margin($TempPrice, $userDealer->dealer_canvases_margin, $userDealer->CanvasesPrice[$key_c])?></td>
                            <? endif; ?>
                        </tr>
                        <? if ($stock) foreach ($canvas->rollers as $key_r => $roller): ?>
                            <tr class="TBody Level4" style="display: none;" data-canvas="<?= $key_r; ?>"
                                data-roller="<?= $key_r; ?>" data-level="4">
                                <td><i class="fa fa-caret-right" aria-hidden="true"></i></td>
                                <td><?= $roller->id; ?></td>
                                <td title="Штриховой код / Артикль"><?= $roller->barcode; ?>
                                    / <?= $roller->article; ?></td>
                                <td></td>
                                <td><?= $roller->quad; ?> м<sup>2</sup></td>
                                <td></td>
                                <td><?= $roller->pprice; ?> руб./м<sup>2</sup></td>
                                <td><?= $roller->stock_name; ?></td>
                                <td>
                                    <a href="/index.php?option=com_gm_ceiling&view=stock&type=info&subtype=canvas&id=<?= $key_c; ?>&roller=<?= $key_r; ?>">Инфо</a>
                                </td>
                            </tr>
                        <? endforeach; ?>
                    <? endif; endforeach; ?>
                <? endforeach; ?>
            <? endforeach; ?>
            </tbody>
            <tfoot>
            <tr>
                <td colspan="12"></td>
            </tr>
            </tfoot>
        </table>
    </div>
</div>
<script type="text/javascript">
    var $ = jQuery,
        Data = {};

    $(document).ready(Init);
    $(window).resize(Resize);
    $(document).scroll(Scroll);

    function Init() {
        Data.Preloader = $(".PRELOADER_GM");

        Data.Ajax = "/index.php?option=com_gm_ceiling&task=";

        Data.Page = $(".Page");
        Data.Actions = Data.Page.find(".Actions");
        Data.Table = Data.Page.find(".Body");
        Data.Table.THead = Data.Table.find(".THead");
        Data.Table.TBody = Data.Table.find(".TBody");
        Data.Table.Level1 = Data.Table.find(".Level1");
        Data.Table.Level2 = Data.Table.find(".Level2");
        Data.Table.Level3 = Data.Table.find(".Level3");
        Data.Table.Action = Data.Table.find(".Action");

        Data.Table.Action.click(ActionTR);
        Data.Actions.find("#ActionTR").click(AllActionTR);

        Data.Forms = Data.Page.find("form");
        Data.Forms.filter(".UpdatePrice").attr("action", "javascript:null;");
        Data.Forms.filter(".UpdatePrice").submit(UpdatePrice);

        Data.Temp = {};
        Data.Scroll = {};

        Data.Dealer = <?=isset($dealer) ? $dealer->id : "null";?>;

        ScrollInit();
        ResizeHead();
        Resize();

        Data.Preloader.hide();
    }

    function Resize() {
        var WW = $(window).width() + 10;

        var PageScroll = $(".Page .Scroll");
        if (WW > 767) {
            var offset = PageScroll.offset(),
                offsetLeft = (offset.left - 15.0 > 0) ? (offset.left - 15.0) : 0;

            PageScroll.css({
                "left": (-offsetLeft + "px"),
                "width": ("calc(100% + " + (offsetLeft * 2) + "px)")
            });
        } else PageScroll.removeAttr("style");

        ResizeHead();
    }

    function ScrollInit() {
        Data.Scroll.EHead = Data.Table.find("thead");
        Data.Scroll.EHeadTr = Data.Scroll.EHead.find(".THead");
        Data.Scroll.EHeadTrClone = Data.Scroll.EHeadTr.clone();

        Data.Scroll.EHeadTrClone.removeClass("THead").addClass("THeadClone");
        Data.Scroll.EHead.append(Data.Scroll.EHeadTrClone);

        Data.Page.find(".Scroll").scroll(ResizeHead);
    }

    function ResizeHead() {
        Data.Scroll.EHeadTrClone.css("left", (Data.Scroll.EHeadTr.offset().left));

        Data.Scroll.EHeadTrClone.width(Data.Scroll.EHeadTr.width());
        for (var i = 0; i < Data.Scroll.EHeadTr.children().length; i++)
            $(Data.Scroll.EHeadTrClone.children()[i])
                .width($(Data.Scroll.EHeadTr.children()[i]).width());
    }

    function Scroll() {
        var scrollTop = $(window).scrollTop(),
            offset = Data.Scroll.EHeadTr.offset(),
            has = Data.Scroll.EHeadTrClone.hasClass("Show");
        if (scrollTop >= offset.top) {
            if (!has) Data.Scroll.EHeadTrClone.addClass("Show");
        }
        else {
            if (has) Data.Scroll.EHeadTrClone.removeClass("Show");
        }
    }

    function ActionTR(event) {
        if ($(event.target).closest("form").hasClass("UpdatePrice")) return;

        var TR = $(this),
            level = parseInt(this.dataset.level);

        var TRN = TR.next();
        if (TR.hasClass("Active")) {
            TR.removeClass("Active");
            TR.find("td:first-child i").removeClass("fa-caret-up").addClass("fa-caret-down");

            while (TRN.length !== 0 && TRN.data("level") > level) {
                TRN.removeClass("Active");

                if (TRN.hasClass("Action"))
                    TRN.find("td:first-child i").removeClass("fa-caret-up").addClass("fa-caret-down");

                TRN.hide();
                TRN = TRN.next();
            }
        } else {
            TR.addClass("Active");
            TR.find("td:first-child i").removeClass("fa-caret-down").addClass("fa-caret-up");

            while (TRN.length !== 0 && TRN.data("level") > level) {
                if (TRN.hasClass("Level" + (level + 1)))
                    TRN.show();
                TRN = TRN.next();
            }
        }

        ResizeHead();
    }

    function AllActionTR() {
        var Button = $(this),
            TR = Data.Table.TBody;
        if (Button.hasClass("Active")) {
            TR.removeClass("Active");
            TR.filter(":not(.Level1)").hide();
            TR.filter(".Action").find("td:first-child i").removeClass("fa-caret-up").addClass("fa-caret-down");
            Button.removeClass("Active");
            Button.find("i").removeClass("fa-caret-up").addClass("fa-caret-down");
            Button.find("span").text("Раскрыть все");
        } else {
            TR.filter(".Action").addClass("Active");
            TR.show();
            TR.filter(".Action").find("td:first-child i").removeClass("fa-caret-down").addClass("fa-caret-up");
            Button.addClass("Active");
            Button.find("i").removeClass("fa-caret-down").addClass("fa-caret-up");
            Button.find("span").text("Скрыть все");
        }

        ResizeHead();
    }

    /**
     * @return {boolean}
     */
    function UpdatePrice() {
        Data.Preloader.show();
        var _this = $(this),
            TR = _this.closest("tr"),
            values = JSON.serialize(this);

        values.id = this.dataset.id;
        values.dealer = Data.Dealer;
        values.level = (TR) ? TR.data("level") : 0;
        console.log(values);
        jQuery.ajax({
            type: 'POST',
            url: Data.Ajax + "canvases.UpdatePrice",
            data: values,
            cache: false,
            async: false,
            dataType: "json",
            timeout: 5000,
            success: function (data) {
                $.each(data.elements, function (i, v) {
                    Data.Page.find(v.name).text(v.value);
                });

                Noty(data.status, data.message);
            },
            error: Noty
        });

        $(this).find("input:not(:disabled)").val("");
        Data.Preloader.hide();

        return false;
    }

    function Noty(status = "error", message = "Сервер не отвечает, попробуйте снова!", time = 2000) {
        noty({
            theme: 'relax',
            layout: 'center',
            timeout: time,
            type: status,
            text: message
        });
    }

    JSON.serialize = function (obj) {
        var inputs = $(obj).find("input:not(:disabled)"),
            datas = $(obj).find("[data-JsonSend]").filter("[id]"),
            values = $(obj).find("[data-Send]").filter("[id]"),
            result = {jsons: {}, values: {}};

        $.each(inputs, function (i, v) {
            result[v.name] = v.value;
        });

        $.each(datas, function (i, v) {
            result.jsons[v.id] = JSON.parse(v.dataset.jsonsend);
        });

        $.each(values, function (i, v) {
            result.values[v.id] = v.dataset.send;
        });

        return result;
    }
</script>

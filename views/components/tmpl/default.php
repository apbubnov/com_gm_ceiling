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
$user->getComponentsPrice();

if (!(in_array(14, $user->groups) || in_array(15, $user->groups))) {
    $userDealer = JFactory::getUser($user->dealer_id);
    $userDealer->groups = $userDealer->get('groups');
    $userDealer->getDealerInfo();
    $userDealer->getComponentsPrice();
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
        $dealer->getComponentsPrice();
    }
}

function margin($value, $margin){return ($value * 100 / (100 - $margin));}
function double_margin($value, $margin1, $margin2){return margin(margin($value, $margin1), $margin2);}
function dealer_margin($price, $margin, $objectDealerPrice) {
    $result = 0;

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
    return margin($result, $margin);
}
?>
<link rel="stylesheet" type="text/css" href="/components/com_gm_ceiling/views/components/css/style.css">
<div class="Page">
    <div class="Title">
        Прайс компонентов<?=(isset($dealer))?" для $dealer->name #$dealer->id":"";?>.
    </div>
    <div class="Actions">
        <?=parent::getButtonBack();?>
        <button type="button" class="Current ActionTR" id="ActionTR">
            <i class="fa fa-caret-down" aria-hidden="true"></i> <span>Раскрыть все</span>
        </button>
        <?if ($managerGM):?>
            <form class="FormSimple UpdatePrice MarginLeft">
                <label for="Price" title="Изменить все дилерские цены"><i class="fa fa-pencil-square-o" aria-hidden="true"></i></label>
                <input type="text" pattern="[+-]{1}\d+[,.]{0,1}\d+%{1}|[+-]{0,1}\d+[,.]{0,1}\d+|*" name="Price" id="Price"
                       placeholder="0"
                       title="Формат: *, X, +X, -X, +X% или -X%, где X - это значение, * - очистить! Например: +15%."
                       size="5" required>
                <button type="submit" class="buttonOK">
                    <i class="fa fa-paper-plane" aria-hidden="true"></i>
                </button>
            </form>
        <?endif;?>
        <?if(!($managerGM || $stock)):?>
            <button type="button" class="Current Basket" id="Basket" onclick="ModalAction('PayList');">
                <i class="fa fa-shopping-basket"></i> <span class="sum">0</span> руб.
            </button>
        <?endif;?>
    </div>
    <div class="Scroll">
        <form action="<?= JRoute::_('index.php?option=com_gm_ceiling&view=components' . (!empty($dealer) ? "&dealer=$dealer->id" : "")); ?>" method="post"
              name="adminForm" id="adminForm" hidden>
            <input type="hidden" name="filter_order" value="<?= $listOrder; ?>"/>
            <input type="hidden" name="filter_order_Dir" value="<?= $listDirn; ?>"/>
            <?= JHtml::_('form.token'); ?>
        </form>
    <table class="Body">
        <thead>
            <tr class="THead">
                <td><i class="fa fa-bars" aria-hidden="true"></i></td>
                <td><?=JHtml::_( 'grid.sort', '<i class="fa fa-hashtag" aria-hidden="true"></i>', 'component_id', $listDirn, $listOrder);?></td>
                <td><?=JHtml::_('grid.sort', 'Наименование', 'component_title', $listDirn, $listOrder);?></td>
                <?if($stock):?>
                    <td><?=JHtml::_('grid.sort', 'Количество', 'option_count', $listDirn, $listOrder);?></td>
                    <td>Заказать</td>
                    <td>Цена закупки</td>
                    <td><i class="fa fa-cubes" aria-hidden="true"></i></td>
                    <td>Посмотреть</td>
                <?elseif ($managerGM && empty($dealer)):?>
                    <td><?=JHtml::_('grid.sort', 'Количество', 'option_count', $listDirn, $listOrder);?></td>
                    <td><?=JHtml::_( 'grid.sort', 'Цена', 'option_price', $listDirn, $listOrder);?></td>
                    <td><?=JHtml::_( 'grid.sort', 'Цена для дилера', 'option_price', $listDirn, $listOrder);?></td>
                    <td><?=JHtml::_( 'grid.sort', 'Цена для клиента', 'option_price', $listDirn, $listOrder);?></td>
                    <td>Изменить</td>
                <?elseif ($managerGM):?>
                    <td><?=JHtml::_('grid.sort', 'Количество', 'option_count', $listDirn, $listOrder);?></td>
                    <td><?=JHtml::_( 'grid.sort', 'Цена', 'option_price', $listDirn, $listOrder);?></td>
                    <td><?=JHtml::_( 'grid.sort', 'Изменение', 'option_price', $listDirn, $listOrder);?></td>
                    <td><?=JHtml::_( 'grid.sort', 'Цена для дилера', 'option_price', $listDirn, $listOrder);?></td>
                    <td>Изменить</td>
                <?else:?>
                    <td><?=JHtml::_( 'grid.sort', 'Себестоимость', 'option_price', $listDirn, $listOrder);?></td>
                    <td><?=JHtml::_( 'grid.sort', 'Цена для клиента', 'option_price', $listDirn, $listOrder);?></td>
                    <td>Купить</td>
                <?endif;?>
            </tr>
        </thead>
        <tbody>
        <?foreach ($this->items as $key_c => $component):?>
            <tr class="TBody Level1 Action" data-component="<?=$key_c;?>" data-level="1">
                <td><i class="fa fa-caret-down" aria-hidden="true"></i></td>
                <td><?=$key_c;?></td>
                <td><?=$component->title;?> <?=$component->unit;?></td>
                <?if($stock):?>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                <?elseif ($managerGM && empty($dealer)):?>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                <?elseif ($managerGM):?>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                <?else:?>
                    <td></td>
                    <td></td>
                    <td></td>
                <?endif;?>
            </tr>
        <?foreach ($component->options as $key_o => $option):?>
                <tr class="TBody Level2 <?=($stock)?"Action":""?>" style="display: none;" data-component="<?=$key_c;?>"
                    data-option="<?=$key_o;?>" data-level="2">
                    <td><i class="fa <?=($stock)?"fa-caret-down":"fa-caret-right";?>" aria-hidden="true"></i></td>
                    <td><?=$key_o;?></td>
                    <td class="ComponentName"><?=$component->title;?> <?=$option->title;?></td>
                    <?if($stock):?>
                        <td><?=$option->count;?></td>
                        <td><?=$option->ocount;?></td>
                        <td><?=$option->pprice;?></td>
                        <td></td>
                        <td><a href="/index.php?option=com_gm_ceiling&view=stock&type=info&subtype=component&id=<?=$key_o;?>">Инфо</a></td>
                    <?elseif ($managerGM && empty($dealer)):?>
                        <td><?=$option->count;?></td>
                        <td id="GMPrice"><?=$option->price;?></td>
                        <td id="GMPrice"><?=margin($option->price, $dealer->gm_components_margin);?></td>
                        <td id="DealerPrice"><?=double_margin($option->price, $userDealer->gm_components_margin, $userDealer->dealer_components_margin);?></td>
                        <td>
                            <form class="FormSimple UpdatePrice MarginLeft" data-id="<?=$key_o;?>">
                                <label for="Price" title="Изменить дилерскую цену"><i class="fa fa-pencil-square-o" aria-hidden="true"></i></label>
                                <input type="text" pattern="[+-]{1}\d+[,.]{0,1}\d+%{1}|[+-]{0,1}\d+[,.]{0,1}\d+|*" name="Price" id="Price"
                                       placeholder="0"
                                       title="Формат: *, X, +X, -X, +X% или -X%, где X - это значение, * - очистить! Например: +15%."
                                       size="5" required>
                                <button type="submit" class="buttonOK">
                                    <i class="fa fa-paper-plane" aria-hidden="true"></i>
                                </button>
                            </form>
                        </td>
                    <?elseif ($managerGM):?>
                        <td><?=$option->count;?></td>
                        <?
                        $Price = margin($option->price, $dealer->gm_components_margin);
                        $DealerPrice = dealer_margin($Price, 0, $dealer->ComponentsPrice[$key_o]);
                        $UpdatePrice = $DealerPrice - $Price;
                        ?>
                        <td id="GMPrice"><?= $Price; ?></td>
                        <td id="UpdateDealerPrice"><?= (($UpdatePrice >= 0)?"+":"").$UpdatePrice; ?></td>
                        <td id="DealerPrice"><?= $DealerPrice; ?></td>
                        <td>
                            <form class="FormSimple UpdatePrice MarginLeft" data-id="<?=$key_o;?>">
                                <label for="Price" title="Изменить дилерскую цену"><i class="fa fa-pencil-square-o" aria-hidden="true"></i></label>
                                <input type="text" pattern="[+-]{1}\d+[,.]{0,1}\d+%{1}|[+-]{0,1}\d+[,.]{0,1}\d+|*" name="Price" id="Price"
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
                        $TempPrice = margin($option->price, $userDealer->gm_components_margin);
                        ?>
                        <td><?= dealer_margin($TempPrice, 0, $userDealer->ComponentsPrice[$key_o]);?></td>
                        <td><?= dealer_margin($TempPrice, $userDealer->dealer_components_margin, $userDealer->ComponentsPrice[$key_o]);?></td>
                        <td>
                            <form class="FormSimple Pay MarginLeft" data-id="<?=$key_o;?>" action="javascript:Pay(<?=$key_o;?>);">
                                <label for="CountPay" title="Введите количество"><i class="fa fa-cubes"></i></label>
                                <input type="number" name="CountPay" id="CountPay" placeholder="0"
                                       title="Введите количество" size="5" min="1"
                                       data-JsonSend='{
                                       "id": "<?=$key_o;?>",
                                       "price": "<?=dealer_margin($TempPrice, 0, $userDealer->ComponentsPrice[$key_o]);?>"
                                       }' required>
                                <button type="submit" class="buttonOK">
                                    <i class="fa fa-paper-plane" aria-hidden="true"></i>
                                </button>
                            </form>
                        </td>
                    <?endif;?>
                </tr>
        <?if ($stock) foreach ($option->goods as $key_g => $good):?>
                    <tr class="TBody Level3" style="display: none;" data-component="<?=$key_c;?>"
                        data-option="<?=$key_o;?>" data-good="<?=$key_g;?>" data-level="3">
                        <td><i class="fa fa-caret-right" aria-hidden="true"></i></td>
                        <td><?=$key_g;?></td>
                        <td>#<?=$good->barcode;?> @<?=$good->article;?></td>
                        <td><?=$good->count;?></td>
                        <td></td>
                        <td><?=$good->pprice;?></td>
                        <td><?=$good->stock_name;?></td>
                        <td><a href="/index.php?option=com_gm_ceiling&view=stock&type=info&subtype=component&id=<?=$key_o;?>&good=<?=$key_g;?>">Инфо</a></td>
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
    <div class="Modal" style="display: none;">
        <div class="ModalClose"></div>
        <form class="ModalPage PayList" style="display: none;" action="/index.php?option=com_gm_ceiling&task=payComponents" method="post">
            <table class="PayListTable">
                <thead>
                <tr>
                    <td>Название</td>
                    <td>Стоимость</td>
                    <td>Количество</td>
                    <td>Цена</td>
                </tr>
                <tr class="ComponentPay" hidden>
                    <td class="ComponentPayName"></td>
                    <td class="ComponentPayCost"></td>
                    <td class="ComponentPayCount"></td>
                    <td class="ComponentPayItog"></td>
                    <td hidden>
                        <input class="ComponentPayID" type="number" name="Сomponents[]" readonly>
                        <input class="ComponentPayC" type="number" name="Сount[]" readonly>
                    </td>
                </tr>
                </thead>
                <tbody class="ComponentsList">
                </tbody>
                <tfoot>
                <tr>
                    <td colspan="2"></td>
                    <td>Итого:</td>
                    <td class="ComponentsPayItog"></td>
                </tr>
                </tfoot>
            </table>
            <div class="FormPay">
                <div class="FormSimple col-fs-2">
                    <label for="Comment"><i class="fa fa-comment"></i></label>
                    <input type="text" id="Comment" name="Comment" placeholder="Комментарий" title="Комментарий">
                    <label for="Date"><i class="fa fa-calendar"></i></label>
                    <input type="datetime-local" id="Date" name="Date" title="Дата к которой заказ должен быть готов" required>
                    <button type="submit" title="Отправить в производство"><i class="fa fa-money"></i></button>
                </div>
            </div>
        </form>
    </div>
</div>
<script type="text/javascript">
    var $ = jQuery,
        Data = {};

    $(document).ready(Init);
    $(window).resize(Resize);
    $(document).scroll(Scroll);

    function Init() {
        Data.Pay = [];

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
        Data.Forms.filter(".UpdatePrice").attr("action","javascript:null;");
        Data.Forms.filter(".UpdatePrice").submit(UpdatePrice);

        Data.Temp = {};
        Data.Scroll = {};

        Data.Dealer = <?=isset($dealer)?$dealer->id:"null";?>;

        Data.Modal = Data.Page.find(".Modal");
        Data.Modal.find(".ModalClose").click(ModalAction);

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
        if (scrollTop >= offset.top) { if (!has) Data.Scroll.EHeadTrClone.addClass("Show"); }
        else { if (has) Data.Scroll.EHeadTrClone.removeClass("Show"); }
    }

    function ActionTR() {
        var TR = $(this),
            level = parseInt(this.dataset.level),
            data = {};

        switch (level)
        {
            case 1: data.title = "component"; break;
            case 2: data.title = "option"; break;
            case 3: data.title = "good"; break;
        }

        data.id = this.dataset[data.title];

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
        var values = JSON.serialize(this);
        values.id = this.dataset.id;
        values.dealer = Data.Dealer;

        jQuery.ajax({
            type: 'POST',
            url: Data.Ajax + "components.UpdatePrice",
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
            result = {jsons:{}, values:{}};

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

    function Pay(id) {
        var form = $("form[data-id='" + id + "']"),
            serialize = JSON.serialize(form),
            json = serialize.jsons.CountPay;

        json.count = serialize.CountPay;

        Data.Pay[id] = json;
        Data.Pay.sum = 0;
        Data.Pay.count = 0;

        $.each(Data.Pay, function (i, v) {
            if (v != null) {
                Data.Pay.sum += parseFloat(v.price) * parseInt(v.count);
                Data.Pay.count++;
            }
        });

        $("#Basket .sum").text(Data.Pay.sum);
    }

    function ModalAction(page = null) {
        var modal = Data.Modal,
            modalPages = modal.find(".ModalPage");

        if (modal.is(":visible")){
            modalPages.hide();
            modal.hide();
        } else if (page !== null) {
            modal.show();
            modal.find("." + page).show();
            window[page]();
        }
    }

    var s = false;
    function PayList() {
        var $this = Data.Modal.find(".PayList"),
            tr = $this.find("thead tr.ComponentPay").clone().removeAttr("hidden"),
            tbody = $this.find("tbody.ComponentsList"),
            itog = $this.find(".ComponentsPayItog");

        tbody.empty();
        $.each(Data.Pay, function (i, v) {
            if (v != null) {
                console.log(v);
                var tr_clone = tr.clone(),
                    name = $("[data-option='" + i + "']").find(".ComponentName").text();
                tr_clone.find(".ComponentPayName").text(name);
                tr_clone.find(".ComponentPayCost").text(v.price + " р.");
                tr_clone.find(".ComponentPayCount").text(v.count);
                tr_clone.find(".ComponentPayItog").text(v.price * v.count + " р.");
                tr_clone.find(".ComponentPayID").val(i);
                tr_clone.find(".ComponentPayC").val(v.count);
                tbody.append(tr_clone);
            }
        });

        itog.text(Data.Pay.sum + " р.");
    }
</script>

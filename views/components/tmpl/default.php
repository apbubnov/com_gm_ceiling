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

$app = JFactory::getApplication();
$model = $this->getModel();

$user = JFactory::getUser();
$user->groups = $user->get('groups');

$userDealer = $user;

if (!(in_array(14, $user->groups) || in_array(15, $user->groups))) {
    $userDealer = JFactory::getUser($user->dealer_id);
    $userDealer->groups = $userDealer->get('groups');
}

$stock = in_array(19, $user->groups);
$managerGM = in_array(16, $user->groups) || in_array(15, $userDealer->groups) && !$stock;

$dealer = null;

if ($managerGM) {
    $dealerId = $app->input->get('dealer', null, 'int');

    if (isset($dealerId)) {
        $dealer = JFactory::getUser($dealerId);
        $dealer->groups = $dealer->get('groups');
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
        <button type="button" class="Current ActionTR" id="ActionTR">
            <i class="fa fa-caret-down" aria-hidden="true"></i> <span>Раскрыть все</span>
        </button>
        <?if ($managerGM):?>
        <form class="FormSimple UpdatePrice MarginLeft">
            <label for="Price" title="Изменить все дилерские цены"><i class="fa fa-pencil-square-o" aria-hidden="true"></i></label>
            <input type="text" pattern="[+-]{1}\d{1,}%{1}|[+-]{0,1}\d{1,}" name="Price" id="Price" placeholder="0"
                   title="Формат: X, +X, -X, +X% или -X%, где X - это значение! Например: +15%."
                   size="5" required>
            <button type="submit" class="buttonOK">
                <i class="fa fa-paper-plane" aria-hidden="true"></i>
            </button>
        </form>
        <?endif;?>
    </div>
    <div>
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
                <td>Посмотреть</td>
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
            <tr class="TBody Level1 Action" data-component="<?=$key_c;?>" data-level="1">
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
                    <td>
                    </td>
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
                <tr class="TBody Level2 <?=($stock)?"Action":""?>" style="display: none;" data-component="<?=$key_c;?>"
                    data-option="<?=$key_o;?>" data-level="2">
                    <td><i class="fa <?=($stock)?"fa-caret-down":"fa-caret-right";?>" aria-hidden="true"></i></td>
                    <td><?=$key_o;?></td>
                    <td><?=$component->title;?> <?=$option->title;?></td>
                    <td><?=$option->count;?></td>
                    <?if($stock):?>
                        <td><?=$option->ocount;?></td>
                        <td><?=$option->pprice;?></td>
                        <td></td>
                        <td><a href="/index.php?option=com_gm_ceiling&view=stock&type=info&subtype=component&id=<?=$key_o;?>">Инфо</a></td>
                    <?elseif ($managerGM && empty($dealer)):?>
                        <td id="GMPrice"><?=margin($option->price, $dealer->gm_components_margin);?></td>
                        <td id="DealerPrice"><?=double_margin($option->price, $userDealer->gm_components_margin, $userDealer->dealer_components_margin);?></td>
                        <td>
                            <form class="FormSimple UpdatePrice MarginLeft" data-id="<?=$key_o;?>">
                                <label for="Price" title="Изменить дилерскую цену"><i class="fa fa-pencil-square-o" aria-hidden="true"></i></label>
                                <input type="text" pattern="[+-]{1}\d{1,}%{1}|[+-]{0,1}\d{1,}" name="Price" id="Price" placeholder="0"
                                       title="Формат: X, +X, -X, +X% или -X%, где X - это значение! Например: +15%."
                                       size="5" required>
                                <button type="submit" class="buttonOK">
                                    <i class="fa fa-paper-plane" aria-hidden="true"></i>
                                </button>
                            </form>
                        </td>
                    <?elseif ($managerGM):?>
                        <td id="GMPrice"><?=margin($option->price, $dealer->gm_components_margin);?></td>
                        <td id="DealerPrice"><?=margin($option->price, $dealer->gm_components_margin);?></td>
                        <td>
                            <form class="FormSimple UpdatePrice MarginLeft" data-id="<?=$key_o;?>">
                                <label for="Price" title="Изменить дилерскую цену"><i class="fa fa-pencil-square-o" aria-hidden="true"></i></label>
                                <input type="text" pattern="[+-]{1}\d{1,}%{1}|[+-]{0,1}\d{1,}" name="Price" id="Price" placeholder="0"
                                       title="Формат: X, +X, -X, +X% или -X%, где X - это значение! Например: +15%."
                                       size="5" required>
                                <button type="submit" class="buttonOK">
                                    <i class="fa fa-paper-plane" aria-hidden="true"></i>
                                </button>
                            </form>
                        </td>
                    <?else:?>
                        <td><?=margin($option->price, $userDealer->gm_components_margin);?></td>
                        <td><?=double_margin($option->price, $userDealer->gm_components_margin, $userDealer->dealer_components_margin);?></td>
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
        Data.Forms.filter(".UpdatePrice").attr("action","javascript:null;");
        Data.Forms.filter(".UpdatePrice").submit(UpdatePrice);

        Data.Temp = {};
        Data.Scroll = {};

        Data.Dealer = <?=isset($dealer)?$dealer->id:"null";?>;

        ScrollInit();
        ResizeHead();
        Resize();

        Data.Preloader.hide();
    }

    function Resize() {
        ResizeHead();
    }

    function ScrollInit() {
        Data.Scroll.EHead = Data.Table.find("thead");
        Data.Scroll.EHeadTr = Data.Scroll.EHead.find(".THead");
        Data.Scroll.EHeadTrClone = Data.Scroll.EHeadTr.clone();

        Data.Scroll.EHeadTrClone.removeClass("THead").addClass("THeadClone");
        Data.Scroll.EHead.append(Data.Scroll.EHeadTrClone);

        Data.Page.scroll(ResizeHead);
    }

    function ResizeHead() {
        Data.Scroll.EHeadTrClone.css("left", (Data.Scroll.EHeadTr.offset().left));

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
            success: function (data) {
                data = JSON.parse(data);

                $.each(data.elements, function (i, v) {
                    Data.Page.find(v.name).text(v.value);
                });

                Noty(data.status, data.message);
            },
            dataType: "text",
            timeout: 5000,
            error: Noty
        });

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
        var inputs = $(obj).find("[value]:not(:disabled)"),
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
</script>

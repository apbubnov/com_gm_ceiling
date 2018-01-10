<?php
/**
 * @version    CVS: 1.0.0
 * @package    Com_Gm_ceiling
 * @author     Mikhail  <vms@itctl.ru>
 * @copyright  2016 Mikhail
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access
defined('_JEXEC') or die;
JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');
JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.multiselect');
JHtml::_('formbehavior.chosen', 'select');

$user = JFactory::getUser();
$model = $this->getModel();

$userId = $user->get('id');
$groups = $user->get('groups');

$canCreate = (in_array(18, $groups) || in_array(19, $groups));
$canEdit = (in_array(18, $groups) || in_array(19, $groups));
$canDelete = (in_array(18, $groups) || in_array(19, $groups));

$app = JFactory::getApplication();
$id = $app->input->get('id', 0, 'int');
$project = Gm_ceilingHelpersGm_ceiling::getModel('Project')->getProjectForGuild($id);
$employees = Gm_ceilingHelpersGm_ceiling::getModel('Guild')->getEmployees();

$calculations = $project->calculations;

$status = $project->status;
if ($status == 5) $status = "Раскроен";
else if ($status == 19) $status = "Выдан";
else $status = null;
?>

<link rel="stylesheet" href="http://<?= str_replace("/home/srv112238/", "", __DIR__); ?>/style.css" type="text/css">


<?= parent::getButtonBack(); ?>
<h1 class="root_title">Раскрои проекта №<?= $id; ?></h1>

<div class="Cut">
    <? foreach ($calculations as $calc): ?>
        <div class="Ceiling" id="<?=$calc->id;?>">
            <input class="CeilingData" type="text" name="data[]" value='<?= $calc->id; ?>' disabled hidden>
            <div class="CeilingInfo">
                <div class="CeilingName"><?= $calc->title; ?></div>
            </div>
            <div class="CeilingImage">
                <div class="CeilingImg Img"
                     style="background-image: url('/cut_images/<?= md5("cut_sketch" . $calc->id) ?>.png');"></div>
                <div class="CeilingLoupe" onclick="ModalShow(this);">
                    <i class="fa fa-search-plus" aria-hidden="true"></i>
                </div>
            </div>
        </div>
    <? endforeach; ?>
</div>
<form class="SendResult" action="/index.php?option=com_gm_ceiling&task=guild.sendWorks" method="post">
    <input type="number" name="Id" value="<?= $id; ?>" readonly hidden>
    <div class="DivTableWorking">
        <table class="TableWorking">
            <thead>
            <tr>
                <td>Работа</td>
                <td>Объем</td>
                <td>Цена</td>
            </tr>
            </thead>
            <tbody>
            <? $sum = 0.0;
            $sumAll = 0.0; ?>
            <? foreach ($calculations as $calc): ?>
                <tr class="TableCeiling">
                    <td colspan="2">
                        <? $sum += $calc->sumWork; ?>
                        <span><?= $calc->title; ?></span> -
                        <a href="<?= '/costsheets/' . md5($calc->id . 'cutpdf' . -2) . '.pdf'; ?>" target="_blank">Посмотреть
                            раскрой</a>
                    </td>
                    <td><span><?= $calc->sumWork;
                            $sumAll += $calc->sumWork; ?></span> р.
                    </td>
                </tr>
                <? foreach ($calc->works as $work): ?>
                    <tr>
                        <td><?= $work->name; ?>:</td>
                        <td><?= $work->count . " " . $work->unit; ?></td>
                        <td><span><?= $work->sum; ?></span> р.</td>
                    </tr>
                <? endforeach; ?>
            <? endforeach; ?>
            <tr class="TableCeiling">
                <td colspan="2">
                    <span>Итого:</span>
                </td>
                <td><span><?= $sum . " / " . $sumAll; ?></span> р.</td>
            </tr>
            </tbody>
        </table>
    </div>
</form>
<div class="Modal ModalCeiling">
    <div class="ModalLeft">
        <div class="ModalName">
            <b></b>
        </div>
        <div class="ModalClose" onclick="ModalClose();">
            <i class="fa fa-times" aria-hidden="true"></i>
        </div>
        <div class="ModalImage Img"></div>
    </div>
    <div class="ModalLines">
        <div class="ModalLine ModalLineSuper Name">
            <b>Название:</b> <span></span>
        </div>
        <div class="ModalLine Canvas">
            <b>Полотно:</b> <span></span>
        </div>
        <div class="ModalLine Perimeter">
            <b>Периметр:</b> <span></span> м
        </div>
        <div class="ModalLine Quad">
            <b>Площадь:</b> <span></span> м2
        </div>
        <div class="ModalLine Square">
            <b>Обрезки:</b> <span></span> м2
        </div>
        <div class="ModalLine Percent">
            <b>Процент обрезков:</b> <span></span>
        </div>
        <div class="ModalLine Angles">
            <b>Кол-во углов:</b> <span></span>
        </div>
        <div class="ModalLine CalcData">
            <div class="Line"><b>Стороны и диоганали:</b></div>
            <span></span>
        </div>
        <div class="ModalLine Ceiling">
            <div class="Line"><b>Полотна / Координаты</b></div>
            <div class="Lines"></div>
        </div>
        <div class="ModalLine Works">
            <div class="Line"><b>Работа</b></div>
            <div class="Lines"></div>
        </div>
        <div class="ModalLine Actions">
            <button type="submit" class="Action btn btn-primary Pred Arrow" onclick="PredCeiling();">
                <i class="fa fa-arrow-left" aria-hidden="true"></i>
            </button>
            <? if (!empty($status)): ?>
                <button type="submit" class="Action btn btn-primary Submit">
                    <i class="fa fa-check-square" aria-hidden="true"></i> <?= $status; ?>
                </button>
            <? endif; ?>
            <button type="submit" class="Action btn btn-primary Next Arrow" onclick="NextCeiling();" style="float: right">
                <i class="fa fa-arrow-right" aria-hidden="true"></i>
            </button>
        </div>
    </div>
</div>

<script>
    var $ = jQuery,
        Data = {
            ImageMousePosition: {}
        },
        ceilings = [
            <?foreach ($calculations as $key => $c):?>
            "<?=$key;?>", <?=json_encode($c);?>,
            <?endforeach;?>
        ],
        employees = <?=(empty($employees))?"[]":json_encode($employees);?>;

    $(document).ready(Init);
    $(window).resize();

    function Init() {
        $(".Actions .Employees").width($(".Actions .Employees .ButtonButInp").outerWidth(true));
        $(".ModalCeiling .ModalImage")
            .mousedown(function (event) {
                Data.ImageMousePosition = {X : event.pageX, Y : event.pageY};
            })
            .mousemove(function (event) {
                var element = $(this);
                if (Data.ImageMousePosition !== null)
                {
                    var mouse = "move",
                        cursor = "move";

                    if (event.pageX - 50 >= Data.ImageMousePosition.X && Data.Cursor !== "right") {
                        mouse = "url(/files/cursors/right.cur), e-resize";
                        cursor = "right";
                        element.css({"cursor":mouse});
                        console.log(mouse);
                    }
                    else if (event.pageX + 50 <= Data.ImageMousePosition.X && Data.Cursor !== "left")
                    {
                        mouse = "url(/files/cursors/left.cur), w-resize";
                        cursor = "left";
                        element.css({"cursor":mouse});
                        console.log(mouse);
                    }
                    else if (event.pageX - 50 < Data.ImageMousePosition.X && event.pageX + 50 > Data.ImageMousePosition.X && Data.Cursor !== "move")
                    {
                        element.css({"cursor":mouse});
                        cursor = "move";
                        console.log(mouse);
                    }

                    Data.Cursor = cursor;
                } else element.css({"cursor":"default"});
            })
            .mouseup(function (event) {
                if (event.pageX - 50 >= Data.ImageMousePosition.X) NextCeiling();
                else if (event.pageX + 50 <= Data.ImageMousePosition.X) PredCeiling();
                Data.ImageMousePosition = null;
            })
    }

    function ModalOpenText() {
        var ModalInfo = $(".ModalInfo");
        if (ModalInfo.val() == false) {
            ModalInfo.css("top", "30px");
            ModalInfo.val(true);
            ModalInfo.find(".Button").html('<i class="fa fa-angle-double-down" aria-hidden="true"></i>');
        }
        else {
            ModalInfo.css("top", "calc(100vh - 90px)");
            ModalInfo.val(false);
            ModalInfo.find(".Button").html('<i class="fa fa-angle-double-up" aria-hidden="true"></i>');
        }
    }

    function ModalClose() {
        $(".ModalCeiling").removeClass("notransition");

        setTimeout(function () {
            $(".ModalCeiling").css({
                "transform": "scale(0, 0)",
                "top": Data.PostionY + "px",
                "left": Data.PostionX + "px"
            });
            $("body").removeClass("hidden");
        }, 1)
    }

    function ModalShow(o) {
        var type = typeof o;
        if (type === "string") o = $("#" + o);
        else o = $(o);

        var Modal = $(".ModalCeiling"),
            Img = Modal.find(".ModalImage"),
            Block = o.closest(".Ceiling"),
            Input = Block.find("input"),
            id = ceilings.indexOf(Input.val()),
            json = ceilings[id + 1];

        Modal.val(json.id);
        Modal.find(".ModalName b").text(json.title);
        Modal.find(".Name span").text(json.title);
        Modal.find(".Canvas span").text(json.canvas_name);
        Modal.find(".Quad span").text(json.quad);
        Modal.find(".Perimeter span").text(json.perimeter);
        Modal.find(".Square span").text(json.square);
        Modal.find(".Percent span").text(json.percent);
        Modal.find(".Angles span").text(json.angles);
        Modal.find(".CalcData span").text(json.calc_data);

        var BlockName = $("<div class=\"Line\"><b></b></div>"),
            BlockData = $("<div class=\"Data\"></div>"),
            BlockSelect = $("<select multiple name=\"employees\" size=\"3\"></select>"),
            BlockOption = $("<option></option>"),
            Ceilings = $("<div></div>"),
            Works = $("<div></div>");

        $.each(json.cut_data, function (i, v) {
            BlockName.find("b").text(v.title);
            var Name = BlockName.clone(),
                Data = BlockData.clone().text(v.data);
            Ceilings.append(Name);
            Ceilings.append(Data);
        });

        $.each(json.works, function (i, v) {
            BlockName.find("b").text(v.name + " - " + v.count + " " + v.unit + " - " + v.sum + " р.");
            $.each(employees, function (i, v) {
                BlockSelect.append(BlockOption.text(v.name).val(v.id).clone());
            });
            BlockData.append(BlockSelect.clone());
            var Name = BlockName.clone();
            Works.append(Name);
            Works.append(BlockData.clone());
            BlockData.empty();
            BlockSelect.empty();
        });

        Modal.find(".Ceiling .Lines").html(Ceilings.html());

        Modal.find(".Works .Line b").html("Работа - " + json.sumWork + " р.");
        Modal.find(".Works .Lines").html(Works.html());

        $(".Modal #ModalNameCeilingName").text(Block.find(".CeilingName").text());

        Data.PostionY = ((o.offset().top - $(window).scrollTop()) * 2 + o.height() - $(window).height()) / 2;
        Data.PostionX = ((o.offset().left - $(window).scrollLeft()) * 2 + o.width() - $(window).width()) / 2;

        if (type === "string") {

        } else {
            Modal.addClass("notransition");
            Modal.css({"top": Data.PostionY + "px", "left": Data.PostionX + "px"});
            Img.attr("style", o.siblings(".CeilingImg").attr("style"));

            setTimeout(function () {
                Modal.removeClass("notransition");
                Modal.css({"transform": "scale(1, 1)", "top": "0px", "left": "0px"});
                setTimeout(function () {
                    $("body").addClass("hidden");
                    Modal.addClass("notransition");
                }, 500)
            }, 1);
        }
    }

    function GetList(e, select, like) {
        var input = $(e),
            Selects = input.siblings(".Selects"),
            ID = input.attr("id"),
            parent = input.closest(".Form"),
            filter = {
                select: {},
                where: {like: {}},
                group: [],
                order: [],
                page: null
            },
            Select = $('<div/>').addClass("Select"),
            Item = $('<div/>').addClass("Item").attr("onclick", "SelectItem(this);");

        input.attr({"clear": "true", "add": "false"});
        Selects.empty();
        Selects.append(Select);
        var Select = Selects.find(".Select");

        $.each(select, function (i, v) {
            filter.select[v] = parent.find("#" + v).attr("NameDB");
        });

        $.each(like, function (i, v) {
            var NameDB = parent.find("#" + v).attr("NameDB"),
                Value = parent.find("#" + v).val(),
                Attr = parent.find("#" + v).attr("add");
            if (Attr !== "true") filter.where.like[NameDB] = "'%" + Value + "%'";
        });

        filter.group.push(input.attr('NameDB'));
        filter.order.push(input.attr('NameDB'));
        filter.page = input.closest(".Form").attr("Page");


        if (input.is(":focus")) {
            jQuery.ajax({
                type: 'POST',
                url: filter.page,
                data: {filter: filter},
                success: function (data) {
                    data = JSON.parse(data);

                    $.each(data, function (i, v) {
                        var I = Item.clone();
                        $.each(v, function (id, s) {
                            if (s === null) s = "Нет";
                            I.attr(id, s);
                            if (id == ID) I.html(s);
                        });
                        Select.append(I);
                    });
                },
                dataType: "text",
                timeout: 10000,
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
    }

    function NextCeiling() {
        var Modal = $(".ModalCeiling"),
            id = Modal.val(),
            index = ceilings.indexOf(id),
            indexNew = (index + 3 >= ceilings.length)?1:index + 3,
            json = ceilings[indexNew];
        ModalShow(json.id);
    }

    function PredCeiling() {
        var Modal = $(".ModalCeiling"),
            id = Modal.val(),
            index = ceilings.indexOf(id),
            indexNew = (index - 1 <= 0)?ceilings.length - 1:index - 1,
            json = ceilings[indexNew];
        ModalShow(json.id);
    }
</script>
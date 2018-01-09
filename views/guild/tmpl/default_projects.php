<?php
echo parent::getPreloaderNotJS();
/**
 * @version    CVS: 1.0.0
 * @package    Com_Gm_ceiling
 * @author     Mikhail "Spectral Eye" Vinyukov <vms@itctl.ru>
 * @copyright  2016 Mikhail "Spectral Eye" Vinyukov
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access
defined('_JEXEC') or die;

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');
JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.multiselect');

$user = JFactory::getUser();
$userId = $user->get('id');
$userGroup = $user->groups;
if (!(array_search('19', $userGroup) || array_search('18', $userGroup))) header('Location: ' . $_SERVER['REDIRECT_URL']);

$calculations = Gm_ceilingHelpersGm_ceiling::getModel('Guild')->getCuts();
$employees = Gm_ceilingHelpersGm_ceiling::getModel('Guild')->getEmployees();

?>
<link rel="stylesheet" href="http://<?= str_replace("/home/srv112238/", "", __DIR__); ?>/style.css" type="text/css">
<script type="text/javascript" src="/files/library/touchwipe.js"></script>

<page>
    <h1>Раскрои</h1>
    <actions>
        <?= parent::getButtonBack(); ?>
        <a class="btn btn-large btn-primary" id="Create" href="/index.php?option=com_gm_ceiling&view=guild&type=create">
            <i class="fa fa-pencil-square-o" aria-hidden="true"></i> Создать раскрой
        </a>
    </actions>
    <cuts class="container">
        <types class="row">
            <? foreach ($calculations as $k => $calc): ?>
                <div class="block_1" id="<?= $k; ?>">
                    <line class="gm line"><?= $calc->name; ?></line>
                    <type class="type">
                        <? foreach ($calc->I as $k => $type): ?>
                            <div class="block_2" id="p<?= $k; ?>">
                                <line class="line"><?= $type->canvases; ?></line>
                                <ceilings class="ceilings row">
                                    <? foreach ($type->I as $cut): ?>
                                        <ceiling class="block_3 col-12 col-sm-12 col-md-6 col-lg-4 col-xl-3"
                                                 id="<?= $cut->id; ?>">
                                            <input name="data" value='<?= json_encode($cut); ?>' disabled hidden>
                                            <div class="ceiling">
                                                <name><?= $cut->title; ?></name>
                                                <div class="image" style="background-image: url('<?= $cut->cut_image; ?>')"></div>
                                            </div>
                                            <div class="loupe" onclick="ModalShow(this);">
                                                <i class="fa fa-search-plus" aria-hidden="true"></i>
                                            </div>
                                        </ceiling>
                                    <? endforeach; ?>
                                </ceilings>
                            </div>
                        <? endforeach; ?>
                    </type>
                </div>
            <? endforeach; ?>
    </cuts>
</page>

<form class="Modal ModalCeiling" action="javascript:CutOut();">
    <div class="ModalPage">
        <div class="ModalName">
            <b></b>
        </div>
        <div class="ModalClose" onclick="ModalClose();">
            <i class="fa fa-times" aria-hidden="true"></i>
        </div>
        <div class="ModalImage Img mouseMove"></div>
    </div>
    <div class="ModalLines">
        <div class="ModalLine ModalLineSuper ProjectId">
            <b>Проект №<span></span></b>
        </div>
        <div class="ModalLine ModalLineSuper CloseProject">
            <b>Срок до:</b> <span></span>
        </div>
        <div class="ModalLine ModalLineSuper Name">
            <b>Название:</b> <span></span>
        </div>
        <div class="ModalLine ModalLineSuper PDF">
            <a href="" target="_blank"><b>Распечатать раскрой</b></a>
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
            <button type="button" class="Action btn btn-primary Pred Arrow" onclick="PredCeiling();">
                <i class="fa fa-arrow-left" aria-hidden="true"></i>
            </button>
            <button type="submit" class="Action btn btn-primary Submit">
                <i class="fa fa-check-square" aria-hidden="true"></i> <span></span>
            </button>
            <button type="button" class="Action btn btn-primary Next Arrow" onclick="NextCeiling();"
                    style="float: right">
                <i class="fa fa-arrow-right" aria-hidden="true"></i>
            </button>
        </div>
        <input type="text" id="KeyPress">
        <input type="text" name="Data" id="Data" readonly hidden>
    </div>
</form>

<script>
    var $ = jQuery,
        Data = {
            calculations: [
                <?$i = 0; foreach ($calculations as $calc):foreach ($calc->I as $type):foreach ($type->I as $c):?>
                <?=$c->id;?>,
                <?$i++; endforeach;endforeach;endforeach;?>
            ],
            cursor: null,
            ceilingPosition: {X: 0, Y: 0},
            employees: <?=(empty($employees)) ? "[]" : json_encode($employees);?>,
        };

    $(document).ready(Init);

    function Init() {
        Data.block1 = $('<div class="block_1">' +
            '<line class="gm line"></line>' +
            '<type class="type"></type>' +
            '</div>');

        Data.block2 = $('<div class="block_2">' +
            '<line class="line"></line>' +
            '<ceilings class="ceilings row"></ceilings>' +
            '</div>');

        Data.block3 = $('<ceiling class="block_3 col-12 col-sm-12 col-md-6 col-lg-4 col-xl-3">' +
            '<input name="data" disabled hidden>' +
            '<div class="ceiling">' +
            '<name></name>' +
            '<div class="image"></div>' +
            '</div>' +
            '<div class="loupe" onclick="ModalShow(this);">' +
            '<i class="fa fa-search-plus" aria-hidden="true"></i>' +
            '</div>' +
            '</ceiling>');

        $(".snowfall-flakes").remove();
        $(".ModalCeiling .ModalImage")
            .touchwipe({
                wipeLeft: NextCeiling,
                wipeRight: PredCeiling,
                wipeUp: ModalClose
            })
            .mousedown(function (event) {
                Data.ImageMousePosition = {X: event.pageX, Y: event.pageY};
            })
            .mouseup(function (event) {
                if (event.pageX - 50 >= Data.ImageMousePosition.X) PredCeiling();
                else if (event.pageX + 50 <= Data.ImageMousePosition.X) NextCeiling();
                else if (event.pageY - 50 >= Data.ImageMousePosition.Y) ModalClose();
                Data.ImageMousePosition = null;
            });

        $("#KeyPress").keyup(function (e) {
            if (e.which === 37) PredCeiling();
            else if (e.which === 39) NextCeiling();
            else if (e.which === 40 || e.which === 27) ModalClose();
        });
        $(".ModalCeiling, .ModalCeiling *").click(function () {
            if ($(window).width() >= 728) $("#KeyPress").focus();
        });

        $(".PRELOADER_GM").hide();
        $("cuts").show();

        setIntervalNew();
    }

    function setIntervalNew() {
        if (Data.Interval !== null)
            clearInterval(Data.Interval);

        Data.Interval = setInterval(function () {
            TestList(false);
        }, 5000);
    }

    function ModalShow(o) {
        if (Data.Interval !== null)
            clearInterval(Data.Interval);

        if ($(window).width() >= 728) $("#KeyPress").focus();

        var type = typeof o;
        if (type === "number") o = $("#" + o);
        else o = $(o);

        var Modal = $(".ModalCeiling"),
            Img = Modal.find(".ModalImage"),
            Block = o.closest("ceiling"),
            Input = Block.find("input"),
            json = JSON.parse(Input.val());

        Modal.val(json.id);
        Modal.find(".ModalName b").text(json.title);
        Modal.find(".ProjectId span").text(json.project + ((json.quickly === "A") ? " - Срочно" : ""));
        Modal.find(".CloseProject span").text(json.ready_time);
        Modal.find(".Name span").text(json.title);
        Modal.find(".Canvas span").text(json.canvas_name);
        Modal.find(".Quad span").text(json.quad);
        Modal.find(".Perimeter span").text(json.perimeter);
        Modal.find(".Square span").text(json.square);
        Modal.find(".Percent span").text(json.percent);
        Modal.find(".Angles span").text(json.angles);
        Modal.find(".CalcData span").text(json.calc_data);
        Modal.find(".Submit span").text(json.status);
        Modal.find(".PDF a").attr("href", json.cut_pdf);
        Modal.find("#Data").val(Input.val());

        var BlockName = $("<div class=\"Line\"><b></b></div>"),
            BlockData = $("<div class=\"Data\"></div>"),
            BlockSelect = $("<select multiple required></select>"),
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
            BlockName.find("b").html(v.name + "<br/>" + v.count + " " + v.unit + " - " + v.sum + " р.");
            var size = 0;
            $.each(Data.employees, function (j, val) {
                BlockSelect.append(BlockOption.text(val.name).val(val.id).clone());
                console.log(Data.employees.lenght);
                size++;
            });
            BlockSelect.attr({"name": "employees[" + v.id + "]", "size": (size < 5) ? size : 5});
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

        Data.ceilingPosition.X = ((o.offset().left - $(window).scrollLeft()) * 2 + o.width() - $(window).width()) / 2;
        Data.ceilingPosition.Y = ((o.offset().top - $(window).scrollTop()) * 2 + o.height() - $(window).height()) / 2;

        Img.css({"background-image": "url('" + json.cut_image + "')"});

        if (type === "number") {

        } else {
            Modal.addClass("notransition");

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

    function ModalClose() {
        setIntervalNew();

        $("#KeyPress").blur();
        $(".ModalCeiling")
            .removeClass("notransition")
            .find("#Data").val("");

        setTimeout(function () {
            var botton = $(window).scrollTop() + $(window).height(),
                top = $(window).scrollTop(),
                Y = Data.ceilingPosition.Y;

            $(window).scrollTop(top + Y);
            $(".ModalCeiling").css({
                "transform": "scale(0, 0)",
                "left": Data.ceilingPosition.X + "px",
                "top": "0px"
            });
            $("body").removeClass("hidden");
        }, 1)
    }

    function NextCeiling() {
        if (Data.Interval !== null)
            clearInterval(Data.Interval);

        var Modal = $(".ModalCeiling"),
            id = Modal.val(),
            index = Data.calculations.indexOf(parseInt(id)),
            indexNew = index;
        do {
            indexNew = (indexNew + 1 >= Data.calculations.length) ? 0 : indexNew + 1
        } while (typeof Data.calculations[indexNew] === "undefined");
        ModalShow(Data.calculations[indexNew]);
    }

    function PredCeiling() {
        if (Data.Interval !== null)
            clearInterval(Data.Interval);

        var Modal = $(".ModalCeiling"),
            id = Modal.val(),
            index = Data.calculations.indexOf(parseInt(id)),
            indexNew = index;
        do {
            indexNew = (indexNew - 1 < 0) ? Data.calculations.length - 1 : indexNew - 1;
        } while (typeof Data.calculations[indexNew] === "undefined");
        ModalShow(Data.calculations[indexNew]);
    }

    function CutOut() {
        $(".PRELOADER_GM").show();

        var Modal = $(".ModalCeiling"),
            id = Modal.val(),
            serialize = Modal.serialize();

        jQuery.ajax({
            type: 'POST',
            url: "/index.php?option=com_gm_ceiling&task=guild.sendWork",
            data: serialize,
            cache: false,
            success: function (data) {
                data = JSON.parse(data);

                noty({
                    theme: 'relax',
                    layout: 'center',
                    timeout: 1500,
                    type: data.status,
                    text: data.message
                });

                if (data.status === "success") {
                    NextCeiling();
                    delete Data.calculations[Data.calculations.indexOf(parseInt(id))];
                    $("#" + id).remove();
                    TestList();
                }
                $(".PRELOADER_GM").hide();
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
                $(".PRELOADER_GM").hide();
            }
        });
    }

    function TestList(preloader = true) {
        if (preloader)
            $(".PRELOADER_GM").show();

        jQuery.ajax({
            type: 'POST',
            url: "/index.php?option=com_gm_ceiling&task=guild.testCuts",
            data: {"cuts": Data.calculations},
            cache: false,
            success: function (data) {
                data = JSON.parse(data);

                $.each(data, function (i1, b1) {
                    var blocks1 = $(".block_1"),
                        fblocks1 = blocks1.filter("#"+i1);

                    console.log(fblocks1);
                    if (fblocks1.length < 1) {
                        var Pred = "", Next = "", Temp = null;
                        blocks1.each(function () {
                            if (i1 <= Next) return;

                            Temp = $(this);
                            console.log(Temp.attr('id'));

                            Pred = Next;
                            Next = Temp.attr('id');
                        });

                        if (Temp !== null && i1 > Temp.attr('id')) {
                            Pred = Next;
                            Next = "";
                        }

                        console.log(Pred + " < " + i1 + " < " + Next);

                        Temp = (Next !== "") ? $("#" + Next) : Data.block1;
                        var T = Temp.clone();

                        T.attr("id", i1);
                        T.find("line.gm").text(b1.name);
                        T.find("type").empty();

                        if (Next !== "") T.insertBefore(Temp);
                        else $("types").append(T);
                    }

                    $.each(b1.I, function (i2, b2) {
                        i2 = "p" + i2;
                        var block1 = $("#"+i1),
                            blocks2 = block1.find(".block_2"),
                            fblocks2 = blocks2.filter("#"+i2);

                        console.log(fblocks2);
                        if (fblocks2.length < 1) {
                            var Pred = "", Next = "", Temp = null;
                            blocks2.each(function () {
                                if (parseInt(i2.replace("p","")) <= parseInt(Next.replace("p",""))) return;

                                Temp = $(this);
                                console.log(Temp.attr('id'));

                                Pred = Next;
                                Next = Temp.attr('id');
                            });

                            if (Temp !== null && parseInt(i2.replace("p","")) > parseInt(Temp.attr('id').replace("p",""))) {
                                Pred = Next;
                                Next = "";
                            }

                            console.log(Pred + " < " + i2 + " < " + Next);

                            Temp = (Next !== "") ? $("#" + Next) : Data.block2;
                            var T = Temp.clone();

                            T.attr("id", i2);
                            T.find("line").text(b2.canvases);
                            T.find("ceilings").empty();

                            if (Next !== "") T.insertBefore(Temp);
                            else block1.find("type").append(T);
                        }

                        $.each(b2.I, function (i3, b3) {
                            i3 = parseInt(b3.id);

                            var block2 = block1.find("#"+i2),
                                blocks3 = block2.find(".block_3"),
                                fblocks3 = blocks3.filter("#"+i3);

                            console.log(fblocks3);
                            if (fblocks3.length < 1) {
                                var Pred = "", Next = "", Temp = null;
                                blocks3.each(function () {
                                    if (parseInt(i3) <= parseInt(Next)) return;

                                    Temp = $(this);
                                    console.log(Temp.attr('id'));

                                    Pred = Next;
                                    Next = Temp.attr('id');
                                });

                                if (Temp !== null && parseInt(i3) > parseInt(Temp.attr('id'))) {
                                    Pred = Next;
                                    Next = "";
                                }

                                console.log(Pred + " < " + i3 + " < " + Next);

                                Temp = (Next !== "") ? block2.find("#" + Next) : Data.block3;
                                var T = Data.block3.clone();

                                T.attr("id", i3);
                                T.find("input").val(JSON.stringify(b3));
                                T.find("name").text(b3.title);
                                T.find(".image").attr("style","background-image: url('" + b3.cut_image + "'");

                                if (Next !== "") { T.insertBefore(Temp); var index = Data.calculations.indexOf(Next); Data.calculations.splice(index, 0, i3); }
                                else { block2.find("ceilings").append(T); Data.calculations.push(i3); }
                                console.log("------------------------");
                                console.log(Data.calculations);
                                console.log("------------------------");
                            }
                        });
                    });
                });

                $(".PRELOADER_GM").hide();
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
                $(".PRELOADER_GM").hide();
            }
        });
    }
</script>
<? if (false): ?>
    <h1>Проекты</h1>
    <div class="Actions">

        <a class="btn btn-large btn-primary" id="Create"
           href="<?php echo JRoute::_('/index.php?option=com_gm_ceiling&view=guild&type=project&subtype=create', false, 2); ?>"
           style="margin-left: 10px;"><i class="fa fa-pencil-square-o" aria-hidden="true"></i> Создать раскрой</a>
    </div>
    <div class="Projects">
        <table class="Elements">
            <thead class="ElementsHead">
            <tr class="ElementsHeadTr">
                <td class="Id">№</td>
                <td class="Name">Наименование</td>
                <td class="Client">Клиент</td>
                <td class="Dealer">Дилер</td>
                <td class="Status">Статус</td>
                <td class="Date">Дата создания</td>
            </tr>
            </thead>
            <tbody>
            <? foreach ($projects as $p): ?>
                <tr data-href="<?= JRoute::_('index.php?option=com_gm_ceiling&view=guild&type=project&id=' . (int)$p->id); ?>">
                    <td class="Id"><?= intval($p->id); ?></td>
                    <td class="Name"><?= $p->name; ?></td>
                    <td class="Client"><?= $p->client; ?></td>
                    <td class="Dealer"><?= $p->dealer; ?></td>
                    <td class="Status"><?= $p->status_title; ?></td>
                    <td class="Date"><?= date("d.m.Y", strtotime($p->created)); ?></td>
                </tr>
            <? endforeach; ?>
            </tbody>
        </table>
    </div>

    <script>
        var $ = jQuery,
            SCROLL = {};

        $(document).ready(Init);
        $(document).scroll(Scroll);
        $(window).resize(Resize);

        function Init() {
            $('.Projects .Elements tbody tr').click(function () {
                document.location.href = $(this).data('href');
            });

            ScrollInit();
            Resize();
        }

        function Resize() {
            ResizeHead();
        }

        function ScrollInit() {
            SCROLL.EHead = $(".Projects .Elements .ElementsHead");
            SCROLL.EHeadTr = SCROLL.EHead.find(".ElementsHeadTr");
            SCROLL.EHeadTrClone = SCROLL.EHeadTr.clone();

            SCROLL.EHeadTrClone.removeClass("ElementsHeadTr").addClass("CloneElementsHeadTr");
            SCROLL.EHead.append(SCROLL.EHeadTrClone);

            $(".Projects").scroll(ResizeHead);
        }

        function ResizeHead() {
            SCROLL.EHeadTrClone.css("left", (SCROLL.EHeadTr.offset().left));

            for (var i = 0; i < SCROLL.EHeadTr.children().length; i++)
                $(SCROLL.EHeadTrClone.children()[i]).width($(SCROLL.EHeadTr.children()[i]).width() - ((i === 0) ? 1 : 0));
        }

        function Scroll() {
            var scrollTop = $(window).scrollTop(),
                offset = SCROLL.EHeadTr.offset(),
                has = SCROLL.EHeadTrClone.hasClass("Show");
            if (scrollTop >= offset.top) {
                if (!has) SCROLL.EHeadTrClone.addClass("Show");
            }
            else {
                if (has) SCROLL.EHeadTrClone.removeClass("Show");
            }
        }
    </script>

    <? if (false): ?>
        <style>
            .Projects {
                margin-top: 20px;
            }

            .table {
                margin-top: 20px;
            }

            body {
                background-color: #E6E6FA;
            }

            .Projects .table thead tr, .Projects .table tfoot tr {
                background-color: #36357f;
                color: #ffffff;
            }

            .Projects .table tbody tr {
                background-color: #f2f2f2;
                color: #000000;
                cursor: pointer;
            }

            .Projects .table tbody tr:hover {
                background-color: #dedede;
            }

            .Projects .table tr th {
                box-shadow: inset .5px .5px 0 0 rgba(255, 255, 255, .5);
            }

            .Projects .table tr td {
                box-shadow: inset .5px .5px 0 0 rgba(147, 147, 147, 0.5);
            }
        </style>

        <div class="List Projects">
            <h1>Проекты на сборку</h1>
            <table class="table table-striped" id="ProjectsTable">
                <thead id="ProjectsHead">
                <tr>
                    <th class="center">Наименование</th>
                    <th class="center">Клиент</th>
                    <th class="center">Дилер</th>
                    <th class="center">Статус</th>
                    <th class="center">Дата создания</th>
                </tr>
                </thead>
                <tbody id="ProjectsBody">
                <? foreach ($projects as $p): ?>
                    <tr data-href="<?= JRoute::_('index.php?option=com_gm_ceiling&view=stock&type=realization&subtype=project&id=' . (int)$p->id); ?>">
                        <td class="center"><?= $p->name; ?></td>
                        <td class="center"><?= $p->client; ?></td>
                        <td class="center"><?= $p->dealer; ?></td>
                        <td class="center"><?= $p->status_title; ?></td>
                        <td class="center"><?= date("d.m.Y", strtotime($p->created)); ?></td>
                    </tr>
                <? endforeach; ?>
                </tbody>
                <tfoot id="ProjectsFoot">
                <tr>
                    <th colspan="5"></th>
                </tr>
                </tfoot>
            </table>
        </div>

        <script>
            jQuery(document).ready(function () {
                jQuery('.table tbody tr').click(function () {
                    document.location.href = jQuery(this).data('href');
                });
            })
        </script>
    <? endif; ?>
<? endif; ?>

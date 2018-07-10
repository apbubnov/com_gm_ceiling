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
$groups = $user->groups;

$chief = (in_array(23, $groups));
$employee = (in_array(18, $groups));

$calculations = Gm_ceilingHelpersGm_ceiling::getModel('Guild')->getCuts();
$employees = Gm_ceilingHelpersGm_ceiling::getModel('Guild')->getWorkingEmployees();
$server_name = $_SERVER['SERVER_NAME'];
?>
<? if (!($chief || $employee)): ?>
<h1>К сожалению данный кабинет вам не доступен!</h1>
<p>Что бы получить доступ, обратитесь к IT отделу. Через <span>5</span> секунды вы вернетесь на предыдущую страницу!
</p>
<div style="display: none;"><?= parent::getButtonBack(); ?></div>
<script type="text/javascript">
    var $ = jQuery;
    $(function () {
        $(".PRELOADER_GM").hide();
        setTimeout(function () {
            $("#BackPage").click();
        }, 5000);
        setInterval(function () {
            var span = $("p span"),
                text = span.text();
            span.text(parseInt(text) - 1);
        }, 1000);
    });
</script>
<?else:?>
<link rel="stylesheet" href="http://<?= str_replace("/home/srv112238/", "", __DIR__); ?>/style.css" type="text/css">
<script type="text/javascript" src="/files/library/touchwipe.js"></script>

<page>
    <h1>Раскрои</h1>
    <actions>
        <?= parent::getButtonBack(); ?>
        <!--<a class="btn btn-large btn-primary" id="Create" href="/index.php?option=com_gm_ceiling&view=guild&type=create">
            <i class="fa fa-pencil-square-o" aria-hidden="true"></i> Создать раскрой
        </a>-->
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
                                            <input name="data" id="Data" value='<?= json_encode($cut); ?>' disabled hidden>
                                            <div class="ceiling">
                                                <name><?= $cut->title; ?></name>
                                                <div class="image" style="background-image: url('<?= $cut->cut_image . $cut->cut_image_dop; ?>')"></div>
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
        <div class="ModalUpdate" onclick="ModalUpdate();">
            <i class="fa fa-pencil-square-o" aria-hidden="true"></i>
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

<iframe class="redactor">
</iframe>

<script>
    var server_name = '<?php echo $server_name;?>'
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
            works: null
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
            '<input name="data" id="Data" disabled hidden>' +
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

        window.addEventListener("message", function(e) {
            console.log(e);
            if (e.data === 'update') ModalUpdateData();
            $(".redactor").hide();
        }, false);

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

        jQuery.ajax({
            type: 'POST',
            url: "/index.php?option=com_gm_ceiling&task=guild.getData",
            data: {Type: "EmployeeWorking"},
            cache: false,
            async: false,
            success: function (data) {
                Data.employees = JSON.parse(data);
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

        $.each(json.works, function (i, v) {
            BlockName.find("b").html(v.name + "<br/>" + v.count + " " + v.unit + " - " + v.sum + " р.");
            var size = 0;
            $.each(Data.employees, function (j, val) {
                if (val.Work == 1)
                {
                    BlockSelect.append(BlockOption.text(val.name).val(val.id).clone());
                    size++;
                }
            });
            BlockSelect.attr({"name": "employees[" + v.id + "][]", "size": (size < 5) ? size : 5});
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

        Img.css({"background-image": "url('" + json.cut_image + json.cut_image_dop + "')"});

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
                    delete Data.calculations[Data.calculations.indexOf(parseInt(id))];
                    var calc = Data.calculations;
                    Data.calculations = [];

                    for(var i = 0; i < calc.length; i++)
                        if (calc[i])
                            Data.calculations.push(calc[i]);

                    var block_3 = $("#" + id),
                        block_2 = block_3.closest(".block_2"),
                        block_1 = block_2.closest(".block_1");

                    block_3.remove();

                    if (block_2.find(".block_3").length < 1)
                        block_2.remove();

                    if(block_1.find(".block_2").length < 1)
                        block_1.remove();

                    NextCeiling();
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

                    if (fblocks1.length < 1) {
                        var Pred = "", Next = "", Temp = null;
                        blocks1.each(function () {
                            if (i1 <= Next) return;

                            Temp = $(this);

                            Pred = Next;
                            Next = Temp.attr('id');
                        });

                        if (Temp !== null && i1 > Temp.attr('id')) {
                            Pred = Next;
                            Next = "";
                        }

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

                        if (fblocks2.length < 1) {
                            var Pred = "", Next = "", Temp = null;

                            blocks2.each(function () {
                                if (parseInt(i2.replace("p","")) <= parseInt(Next.replace("p",""))) return;

                                Temp = $(this);

                                Pred = Next;
                                Next = Temp.attr('id');
                            });

                            if (Temp !== null && parseInt(i2.replace("p","")) > parseInt(Next.replace("p",""))) {
                                Pred = Next;
                                Next = "";
                            }

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

                            if (fblocks3.length < 1) {
                                var Pred = "", Next = "", Temp = null;
                                blocks3.each(function () {
                                    if (parseInt(i3) <= parseInt(Next)) return;

                                    Temp = $(this);

                                    Pred = Next;
                                    Next = Temp.attr('id');
                                });

                                if (Temp !== null && parseInt(i3) > parseInt(Temp.attr('id'))) {
                                    Pred = Next;
                                    Next = "";
                                }

                                Temp = (Next !== "") ? block2.find("#" + Next) : Data.block3;
                                var T = Data.block3.clone();

                                T.attr("id", i3);
                                T.find("input").val(JSON.stringify(b3));
                                T.find("name").text(b3.title);
                                T.find(".image").attr("style","background-image: url('" + b3.cut_image + b3.cut_image_dop + "'");

                                if (Next !== "") { T.insertBefore(Temp); var index = Data.calculations.indexOf(Next); Data.calculations.splice(index, 0, i3); }
                                else { block2.find("ceilings").append(T); Data.calculations.push(i3); }

                                noty({
                                    theme: 'relax',
                                    layout: 'center',
                                    timeout: 5000,
                                    type: "success",
                                    text: "Пришел новый заказ!\n" + b1.date + ((b1.quickly === "A")?" Срочно":"") + "<br>" + b2.canvases + "<br>" + b3.title + " - " + b3.quad
                                });
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

    /**
     * Метод для запуска редактора
     *
     * @constructor
     * @author  {CEH4TOP}
     * @this    {ModalUpdate}
     * @type    {function}
     * @return  {null}
     */
    function ModalUpdate() {
        var Modal = $(".Modal"),
            dataModal = JSON.parse(Modal.find("#Data").val()),
            redactor = $(".redactor");

        redactor.attr({"src":"http://"+server_name+"/index.php?option=com_gm_ceiling&view=guild&type=redactor&id=" + dataModal.id});
        redactor.show();
    }

    /**
     * Метод для обновления данных из БД, после редактора
     *
     * @constructor
     * @author  {CEH4TOP}
     * @this    {ModalUpdateData}
     * @type    {function}
     * @return  {null}
     */
    function ModalUpdateData() {
        $(".PRELOADER_GM").show();

        var Modal = $(".ModalCeiling"),
            ModalData = JSON.parse(Modal.find("#Data").val());

        /* Загрузка новых данных */
        jQuery.ajax({
            type: 'POST',
            url: "/index.php?option=com_gm_ceiling&task=guild.getCut",
            data: {id: ModalData.id},
            cache: false,
            async: false,
            success: function (data) {
                data = JSON.parse(data);
                console.log(data);

                $.each(data, function (i1, b1) {
                    var blocks1 = $(".block_1"),
                        fblocks1 = blocks1.filter("#"+i1);

                    if (fblocks1.length < 1) {
                        var Pred = "", Next = "", Temp = null;
                        blocks1.each(function () {
                            if (i1 <= Next) return;

                            Temp = $(this);

                            Pred = Next;
                            Next = Temp.attr('id');
                        });

                        if (Temp !== null && i1 > Temp.attr('id')) {
                            Pred = Next;
                            Next = "";
                        }

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

                        if (fblocks2.length < 1) {
                            var Pred = "", Next = "", Temp = null;

                            blocks2.each(function () {
                                if (parseInt(i2.replace("p","")) <= parseInt(Next.replace("p",""))) return;

                                Temp = $(this);

                                Pred = Next;
                                Next = Temp.attr('id');
                            });

                            if (Temp !== null && parseInt(i2.replace("p","")) > parseInt(Next.replace("p",""))) {
                                Pred = Next;
                                Next = "";
                            }

                            Temp = (Next !== "") ? $("#" + Next) : Data.block2;
                            var T = Temp.clone();

                            T.attr("id", i2);
                            T.find("line").text(b2.canvases);
                            T.find("ceilings").empty();

                            if (Next !== "") { T.insertBefore(Temp); }
                            else { block1.find("type").append(T); }
                        }

                        $.each(b2.I, function (i3, b3) {
                            i3 = parseInt(b3.id);

                            var block2 = block1.find("#"+i2),
                                blocks3 = block2.find(".block_3"),
                                fblocks3 = blocks3.filter("#"+i3);

                            if (true) {
                                var Pred = "", Next = "", Temp = null;
                                blocks3.each(function () {
                                    if (parseInt(i3) <= parseInt(Next)) return;

                                    Temp = $(this);

                                    Pred = Next;
                                    Next = Temp.attr('id');
                                });

                                if (Temp !== null && parseInt(i3) > parseInt(Temp.attr('id'))) {
                                    Pred = Next;
                                    Next = "";
                                }

                                Temp = (Next !== "") ? block2.find("#" + Next) : Data.block3;
                                var T = $("#" + i3);

                                var block_3 = T,
                                    block_2 = block_3.closest(".block_2"),
                                    block_1 = block_2.closest(".block_1");

                                T.attr("id", i3);
                                T.find("input").val(JSON.stringify(b3));
                                T.find("name").text(b3.title);
                                T.find(".image").attr("style","background-image: url('" + b3.cut_image + b3.cut_image_dop + "'");
                                console.log(T.find(".image"));

                                delete Data.calculations[Data.calculations.indexOf(parseInt(i3))];
                                if (Next !== "") { T.insertBefore(Temp); var index = Data.calculations.indexOf(parseInt(Next)); Data.calculations.splice(index, 0, parseInt(i3)); }
                                else { block2.find("ceilings").append(T); Data.calculations.push(parseInt(i3)); }

                                if (block_2.find(".block_3").length < 1)
                                    block_2.remove();

                                if(block_1.find(".block_2").length < 1)
                                    block_1.remove();
                            }
                        });
                    });
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

        ModalShow(parseInt(ModalData.id));

        $(".PRELOADER_GM").hide();
    }
</script>
<?endif;?>
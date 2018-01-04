<?php
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
?>

<style>
    .SELECT_CUSTOM {
        width: 200px;
        height: 30px;
        background-color: #F08080;
        border-radius: 3px;
        border: 1px solid rgba(162, 86, 86, .5);
        padding: 0 5px;
        z-index: 1;
    }

    .SELECT_CUSTOM .VALUE {
        width: 100%;
        height: 100%;
        cursor: pointer;
    }

    .SELECT_CUSTOM .RELATIVE {
        position: relative;
        width: calc(100% + 10px);
        height: 0;
        overflow: visible;
        margin: 0 -5px;
    }

    .SELECT_CUSTOM .OPTIONS_CUSTOM {
        display: none;
        position: absolute;
        top: 100%;
        left: 0;
        width: 100%;
        max-height: 115px;
        float: left;
        overflow-x: hidden;
        overflow-y: auto;
        border-radius: 3px;
        background-color: antiquewhite;
        border: 1px solid rgba(162, 86, 86, .5);
        z-index: 100;
    }

    .SELECT_CUSTOM .OPTIONS_CUSTOM .OPTION_CUSTOM {
        display: inline-block;
        width: 100%;
        height: 70px;
        cursor: pointer;
        border-top: 1px solid rgba(241, 128, 128, 0.5);
        padding: 0 5px;
    }
</style>


<div class="SELECT_CUSTOM" onclick="SELECT_CUSTOM_CLICK(this);" onmouseleave="SELECT_CUSTOM_BLUR(this);">
    <input class="HIDDEN" type="text" name="select" value="1" hidden>
    <div class="VALUE"></div>
    <div class="RELATIVE">
        <div class="OPTIONS_CUSTOM"></div>
    </div>
</div>
<button id="add_level" class="btn btn-primary" type="button">Добавить</button>
<script>
    var level = [];
    jQuery(document).ready(function () {
        jQuery.getJSON("index.php?option=com_gm_ceiling&task=getListProfil", function (data) {
            jQuery.each(data, function (key, val) {
                var image = (val.image) ? "data:image/gif;base64," + val.image : "";
                var value = "value=\"" + val.id + "\"";
                var option = "<div class='OPTION_CUSTOM' onclick=\"OPTION_CUSTOM_CLICK(this);\" " + value + " > " + val.title + "<img src='" + image + "'alt='' width='40px' height='60px'    style = 'float: right;'  class=arrow /></div>";

                level.push(option);
            });
            jQuery("#add_level").trigger('click');
        });

        jQuery("#add_level").click(function () {
            var element = jQuery(this),
                select = element.siblings(".SELECT_CUSTOM"),
                list = select.find(".OPTIONS_CUSTOM");
            list.html(level.join(''));
            select.find(".HIDDEN").val(select.find(".OPTION_CUSTOM:first-child").attr("value"));
            select.find(".VALUE").html(select.find(".OPTION_CUSTOM:first-child").html());
        });

        SELECT_CUSTOM_INIT();
    });

    function SELECT_CUSTOM_INIT() {
        var SELECT_CUSTOM = jQuery(".SELECT_CUSTOM");
        SELECT_CUSTOM.find("div").css({"line-height": SELECT_CUSTOM.height() + "px"});
        SELECT_CUSTOM.find(".HIDDEN").val(SELECT_CUSTOM.find(".OPTION_CUSTOM:first-child").attr("value"));
        SELECT_CUSTOM.find(".VALUE").html(SELECT_CUSTOM.find(".OPTION_CUSTOM:first-child").html());
        SELECT_CUSTOM.val(true);
    }

    function SELECT_CUSTOM_CLICK(e) {
        var element = jQuery(e);
        if (element.val()) element.find(".OPTIONS_CUSTOM").show();
        else element.find(".OPTIONS_CUSTOM").hide();
        element.val(!element.val())
    }

    function OPTION_CUSTOM_CLICK(e) {
        var element = jQuery(e);
        var root = element.closest(".SELECT_CUSTOM");
        root.find(".HIDDEN").val(element.attr("value"));
        root.find(".VALUE").html(element.html());
    }

    function SELECT_CUSTOM_BLUR(e) {
        var element = jQuery(e);
        element.find(".OPTIONS_CUSTOM").hide();
        element.val(true);
    }

</script>


<div>Ответ</div>
<div id="answer">


</div>
<button type="button" onclick="test();">Проверить</button>


<form style="margin-top: 20px;" id="form-client"
      action="/index.php?option=com_gm_ceiling&task="
      method="post" class="form-validate form-horizontal" enctype="multipart/form-data">
    <table>
        <tr>
            <td>Номер телефона
            <td><input name="phone">
        <tr>
            <td><br/>

        <tr>
            <td>Код подтверждения
            <td><input name="code" size="6">&nbsp;
                <input type="submit" name="sendsms" value="Выслать код">
        <tr>
            <td><br/>

        <tr>
            <td><input type="submit" name="ok" value="Подтвердить">
    </table>
</form>
<style>
    div#selectBox {
        width: 250px;
        position: relative;
        height: 50px;
        border-radius: 3px;
        border: solid 1px lightgrey;
        background-color: #fff;
        color: #333;
        cursor: pointer;
        overflow: hidden;
        transition: .3s;
    }

    div#selectBox p.valueTag {
        padding: 15px;
        cursor: pointer;
        transition: .2s;
        height: 40px;
    }

    div#selectBox > img.arrow {
        position: absolute;
        right: 0;
        width: 50px;

        padding: 15px;
    }

    /*
            для пользователей Safari, Chrome и Opera приятный бонус — стилизованный скролл-бар.
    */
    ::-webkit-scrollbar {
        background: transparent;
        width: 0.5em;
        position: absolute;
    }

    ::-webkit-scrollbar-track {
        background: transparent;
        position: absolute;
        z-index: -2;
    }

    ::-webkit-scrollbar-thumb {
        border-radius: 100px;
        background: #888;
    }

    ul#selectMenuBox {
        background: #fff;
        transition: .3s;
        width: 100%;
        height: 200px;
        overflow-y: auto;
        overflow-x: hidden !important;
        position: absolute;
        margin-top: 00px;
        display: block;

    }

    ul#selectMenuBox > li {
        display: block;
        padding: 10px;
        border-radius: 00px;
        cursor: pointer;
    }

    ul#selectMenuBox > li.option {
        color: gray;
        padding: 10px;

    }

    ul#selectMenuBox > li.option:hover {
        color: #333;
        background: #e1e1e1;
        transition: .2s;
    }
</style>

<div id=selectBox>
    <!-- стрелка по правому краю для анимации, показывающая, что div-блок можно развернуть -->
    <img src="http://test1.gm-vrn.ru/templates/gantry/images/clip.png" alt="" width='15px' height='15px' class=arrow/>
    <!--    <i class="fa fa-arrow-left" aria-hidden="true"></i>-->
    <!-- текст, который будет виден в боксе -->
    <p class=valueTag name=select>Месяц</p>
    <!-- тот самый выпадающий список -->
    <ul id=selectMenuBox>
        <li class=option>Январь</li>
        <li class=option>Февраль</li>
        <li class=option>Март</li>
        <li class=option>Апрель</li>
        <li class=option>Май</li>
        <li class=option>Июнь</li>
        <li class=option>Июль</li>
        <li class=option>Август</li>
        <li class=option>Сентябрь</li>
        <li class=option>Октябрь</li>
        <li class=option>Ноябрь</li>
        <li class=option>Декабрь</li>
    </ul>
</div> <!-- конец бокса -->

<div>

</div>
<script type="text/javascript">
    var i = 48000;

    function test() {
        jQuery.ajax({
            type: 'POST',
            url: 'https://market.yandex.ru/api/quiz/blackFriday/product/iphone?sk=ua2ba1621cc0e9bc4f8b975f97d95ffa0<?/* JRoute::_('index.php?option=com_gm_ceiling&task=addProjectCalculationFromAndroid');*/?>',
            data: {price: i++},//{project: {"checked_by":null,"client_id":"455","project_sum":null,"project_mounting_start":null,"project_status":"1","dealer_manager_note":null,"new_project_mounting":null,"spend_check":null,"dealer_calculator_note":null,"new_extra_spend":null,"project_calculation_date":"7 \u043d\u043e\u044f\u0431\u0440\u044f 2017 \u0433. 16:00-17:00","project_calculator":null,"mounting_check":null,"project_discount":"0","gm_manager_note":null,"dealer_id":"775","approved_by":null,"new_project_sum":null,"checked_out_time":null,"project_info":"\u043e\u043e\u043e","cost_check":null,"modified_by":"801","salary_sum":null,"closed":null,"sum_check":null,"read_by_manager":null,"dealer_components_margin":"","buh_note":null,"gm_mounting_margin":"","project_verdict":null,"calculated_by":null,"dealer_chief_note":null,"gm_calculator_note":null,"gm_chief_note":null,"extra_spend":null,"dealer_mounting_margin":"","new_project_spend":null,"created_by":"801","project_check":null,"state":null,"penalty":null,"project_note":"","gm_components_margin":"","project_mounting_date":null,"project_mounting_end":null,"who_calculate":null,"bonus":null,"dealer_canvases_margin":"","checked_out":null,"ordering":null,"created":"7 \u043d\u043e\u044f\u0431\u0440\u044f 2017 \u0433.","gm_canvases_margin":""}},
            success: function (data) {
                data = JSON.parse(data);
                jQuery("#answer").html(JSON.stringify(data));
            },
            dataType: "text",
            timeout: 10000,
            error: function () {
                jQuery("#answer").html("Ошибка");
                var n = noty({
                    theme: 'relax',
                    layout: 'center',
                    timeout: 1500,
                    type: "error",
                    text: "Сервер не отвечает! Попробуйте снова!"
                });
            }
        });
    }

    //    (function( jQuery ) {
    //        jQuery.fn.selectbox = function() {
    //
    //            // начальные параметры
    //            // задаем стандартную высоту div'a.
    //            var selectDefaultHeight = $('#selectBox').height();
    //            // угол поворота изображения в div'e
    //            var rotateDefault = "rotate(0deg)";
    //
    //            // после нажатия кнопки срабатывает функция, в которой
    //            // вычисляется исходная высота нашего div'a.
    //            // очень удобно для сравнения с входящими параметрами (то, что задается в начале скрипта)
    //            jQuery('#selectBox > p.valueTag').click(function() {
    //                // вычисление высоты объекта методом height()
    //                var currentHeight = jQuery('#selectBox').height();
    //                // проверка условия на совпадение/не совпадение с заданной высотой вначале,
    //                // чтобы понять. что делать дальше.
    //                if (currentHeight < 100 || currentHeight == selectDefaultHeight) {
    //                    // если высота блока не менялась и равна высоте, заданной по умолчанию,
    //                    // тогда мы открываем список и выбираем нужный элемент.
    //                    jQuery('#selectBox').height("250px");  // «точка остановки анимации»
    //                    // здесь стилизуем нашу стрелку и делаем анимацию средствами CSS3
    //                    jQuery('img.arrow').css({borderRadius: "1000px", transition: ".2s", transform: "rotate(180deg)"});
    //                }
    //
    //
    //                // иначе если список развернут (высота больше или равна 250 пикселям),
    //                // то при нажатии на абзац с классом valueTag, сворачиваем наш список и
    //                // и присваиваем блоку первоначальную высоту + поворот стрелки в начальное положение
    //                if (currentHeight >= 250) {
    //                    jQuery('#selectBox').height(selectDefaultHeight);
    //                    jQuery('img.arrow').css({transform: rotateDefault});
    //                }
    //            });
    //
    //            // так же сворачиваем список при выборе нужного элемента
    //            // и меняем текст абзаца на текст элемента в списке
    //            jQuery('li.option').click(function() {
    //                jQuery('#selectBox').height(selectDefaultHeight);
    //                jQuery('img.arrow').css({transform: rotateDefault});
    //                jQuery('p.valueTag').text(jQuery(this).text());
    //            });
    //        };
    //    })( jQuery );
    //    jQuery('selector').selectbox();

    jQuery("#selectBox").click(function () {
        jQuery("#selectMenuBox").show();
    });
</script>
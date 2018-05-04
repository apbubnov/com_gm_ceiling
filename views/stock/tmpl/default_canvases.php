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
?>
<?= parent::getPreloaderNotJS(); ?>
    <style>
        body {
            background-color: #E6E6FA;
        }

        .inventory {
            width: 100%;
            height: auto;
            overflow-x: auto;
            overflow-y: hidden;
        }

        .inventory .Elements {
            min-width: 100%;
            position: relative;
            border-collapse: collapse;
        }

        .inventory .Elements thead {
            position: relative;
            top: 0;
            left: 0;
        }

        .inventory .Elements tr {
            border: 1px solid #4e4cb7;
            background-color: #E6E6FA;
            color: #000000;
        }

        .inventory .Elements tr td {
            border: 0;
            border-right: 1px solid #4e4cb7;
            width: auto;
            height: 30px;
            line-height: 20px;
            padding: 0 5px;
        }

        .inventory .Elements thead tr td {
            background-color: #4e4cb7;
            color: #ffffff;
            padding: 5px 10px;
            text-align: center;
            border-color: #ffffff;
            min-width: 102px;
        }

        .inventory .Elements tr td:last-child {
            border-right: 0;
        }

        .inventory .Elements tbody tr:hover,
        .inventory .Elements tbody tr:hover td input {
            background-color: #00c685;
            color: #ffffff;
        }

        .inventory .Elements tbody tr.active,
        .inventory .Elements tbody tr.active td input {
            background-color: #ea5900;
            color: #ffffff;
        }

        /*
        .inventory .Elements tbody tr:hover td,
        .inventory .Elements tbody tr.active td {
            border-color: #ffffff;
        }
        */

        .inventory .Elements tbody tr td input {
            display: inline-block;
            float: left;
            width: calc(100% + 10px);
            height: 100%;
            line-height: 20px;
            padding: 0 5px;
            color: #000000;
            background-color: #E6E6FA;
            margin: 0 -5px;
            border: none;
        }

        .inventory .Elements tbody tr td input:invalid {
            background-color: #d81500;
            color: #ffffff;
        }

        .inventory .Elements .CloneElementsHead {
            position: fixed;
            top: 0;
            left: 0;
        }

        .inventory .Elements .CloneElementsHeadTr {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1;
        }

        .inventory .Show {
            display: inline-block !important;
        }

        .inventory .Buttons {
            display: inline-block;
            width: 100%;
            float: left;
            height: auto;
            text-align: right;
            margin-top: 20px;
        }

        .inventory .Buttons button,
        .inventory .Buttons div {
            display: inline-block;
        }
        .inventory .Buttons .Info {
            float: left;
            text-align: left;
            height: 38px;
            line-height: 19px;
        }
    </style>

    <h1>Инвентаризация полотен и обрезков</h1>
    <form class="inventory" action="javascript:SendTable();"
          page="<?php echo JRoute::_('index.php?option=com_gm_ceiling&task=stock.inventory'); ?>"
          method="post" class="form-validate form-horizontal" enctype="multipart/form-data" style="display: none;">
        <table class="Elements">
            <thead class="ElementsHead">
            <tr class="ElementsHeadTr">
                <td>Название</td>
                <td>Штрих-код</td>
                <td>Артикул</td>
                <td>Квадратура</td>
                <td>Отклонение</td>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td hidden><input name="Id" class="IdCanvas" hidden></td>
                <td class="Name"></td>
                <td class="Barcode"></td>
                <td class="Article"></td>
                <td class="Quad"></td>
                <td><input placeholder="Введите значение:" name="Quad" pattern="[-+]{0,1}\d+|[-+]{0,1}\d+[,\.]\d+"
                           autocomplete="off"></td>
            </tr>
            </tbody>
        </table>
        <div class="Buttons">
            <div class="Info"></div>
            <div class="buttonCancel"><?= parent::getButtonBack("Отмена", false); ?></div>
            <button type="submit" onmousedown="Submit(this);" class="buttonComplite btn btn-primary">
                <i class="fa fa-paper-plane" aria-hidden="true"> Отправить</i>
        </div>
    </form>
    <script>
        var $ = jQuery;
        $(document).ready(Init);
        $(window).resize(Resize);

        var DATA = {};

        function Init() {
            DATA.INFO = {start: 0, pages: 0};

            DATA.INVENTORY = $(".inventory");
            DATA.PRELOADER = $(".PRELOADER_GM");
            DATA.TABLE = {};
            DATA.TABLE.TBODY = $(".inventory .Elements tbody");
            DATA.TABLE.TR = DATA.TABLE.TBODY.find("tr").clone();
            DATA.TABLE.TBODY.empty();

            GetTable();

            cloneHead();
            Scroll();
            cssAdd();
            Resize();
        }

        function Resize() {
            resizeHead();
        }

        function Submit(e) {
            e = $(e);
            var inputCount = e.closest('form').find('[name=\'Quad\']');
            $.each(inputCount, function (i, input) {
                $(input).prop('required', true)
            });
        }

        function cloneHead() {
            var h = $(".ElementsHeadTr"),
                ch = h.clone();

            ch.removeClass("ElementsHeadTr").addClass("CloneElementsHeadTr");
            h.parent().append(ch);

            $(".inventory").scroll(resizeHead);
        }

        function resizeHead() {
            var h = $(".ElementsHeadTr"),
                ch = $(".CloneElementsHeadTr");

            ch.css("left", (h.offset().left));

            for (var i = 0; i < h.children().length; i++)
                $(ch.children()[i]).width($(h.children()[i]).width() - ((i === 0) ? 1 : 0));
        }

        function cssAdd() {
            $(".inventory .Elements tbody tr td input").focus(function () {
                var scrollTop = $(window).scrollTop();
                $(this).closest("tr").addClass("active");
                console.log(Math.round($(this).offset().top) + " - " + (scrollTop + 30));
                if (Math.round($(this).offset().top) <= scrollTop + 30) $(window).scrollTop(scrollTop - 30)
            }).blur(function () {
                $(this).closest("tr").removeClass("active");
            });
        }

        function GetTable(page = null) {
            DATA.INVENTORY.hide();
            DATA.PRELOADER.show();

            DATA.INFO.type = 'GetCanvases';
            var data = {info: DATA.INFO};

            jQuery.ajax({
                type: 'POST',
                url: DATA.INVENTORY.attr("page"),
                data: data,
                success: function (data) {
                    data = JSON.parse(data);
                    DATA.INFO = data.info;
                    DATA.TABLE.TBODY.empty();
                    $.each(data.canvases, function (i, item) {
                        var TR = DATA.TABLE.TR.clone();
                        TR.find(".IdCanvas").val(item.Id);
                        TR.find(".Name").text(item.Name);
                        TR.find(".Barcode").text(item.Barcode);
                        TR.find(".Article").text(item.Article);
                        TR.find(".Quad").text(item.Quad+" m²");
                        DATA.TABLE.TBODY.append(TR);
                    });

                    DATA.INVENTORY.show();
                    DATA.PRELOADER.hide();
                    resizeHead();
                    cssAdd();
                    Scroll();

                    noty({
                        theme: 'relax',
                        layout: 'center',
                        timeout: 1500,
                        type: "success",
                        text: "Инвентаризация: " + DATA.INFO.page + " страница из " + (DATA.INFO.pages) + "<br>Количество: " + data.canvases.length
                    });
                    $(".inventory .Buttons .Info").html("Инвентаризация: " + DATA.INFO.page + " страница из " + (DATA.INFO.pages) + "<br>Количество: " + data.canvases.length);

                    if (DATA.INFO.page === DATA.INFO.pages) $(".buttonComplite i").text(" Отправить на печать");
                    else $(".buttonComplite i").text(" Отправить");
                },
                dataType: "text",
                timeout: 10000,
                error: function () {
                    DATA.INVENTORY.show();
                    DATA.PRELOADER.hide();
                    noty({
                        theme: 'relax',
                        layout: 'center',
                        timeout: 3000,
                        type: "error",
                        text: "Сервер не отвечает! Обратитесть к администратору!"
                    });
                }
            });
        }

        function SendTable() {
            DATA.INVENTORY.hide();
            DATA.PRELOADER.show();

            DATA.INFO.type = 'SendCanvases';
            var data = {info: DATA.INFO};
            data.canvases = [];
            $.each(DATA.TABLE.TBODY.find('tr'), function (i, v) {
                v = $(v);
                var id = v.find("[name='Id']").val(),
                    quad = v.find("[name='Quad']").val();
                data.canvases.push({id: id, quad: quad});
            });

            jQuery.ajax({
                type: 'POST',
                url: DATA.INVENTORY.attr("page"),
                data: data,
                success: function (data) {
                    data = JSON.parse(data);
                    if (data.document != null)
                    {
                        document.location.href = data.document;
                        return;
                    }

                    if (data.error == null) GetTable();

                    DATA.INVENTORY.show();
                    DATA.PRELOADER.hide();

                    noty({
                        theme: 'relax',
                        layout: 'center',
                        timeout: 3000,
                        type: "success",
                        text: (data.error === null) ? "Инвентарицзация полотен произошла успешно!" : data.error
                    });
                },
                dataType: "text",
                timeout: 10000,
                error: function () {
                    DATA.INVENTORY.show();
                    DATA.PRELOADER.hide();

                    noty({
                        theme: 'relax',
                        layout: 'center',
                        timeout: 3000,
                        type: "error",
                        text: "Сервер не отвечает! Обратитесть к администратору!"
                    });
                }
            });
        }

        $(document).scroll(Scroll);

        function Scroll() {
            var scrollTop = $(window).scrollTop(),
                offset = $(".ElementsHead").offset(),
                has = $(".CloneElementsHeadTr").hasClass("Show");
            if (scrollTop >= offset.top) {
                if (!has) $(".CloneElementsHeadTr").addClass("Show");
            }
            else {
                if (has) $(".CloneElementsHeadTr").removeClass("Show");
            }
        }
    </script>
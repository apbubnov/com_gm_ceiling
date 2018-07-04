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
$numberProject = $app->input->get('id', 0, 'int');
$jcookie = $app->input->cookie;

$data = Gm_ceilingHelpersGm_ceiling::getModel('Project')->getProjectForStock($numberProject);
$stocks = $model->getStocks();

$goods = $data->goods;
$customer = $data->customer;
$status = floatval($customer->Status);
$statusNumber = $status;
if ($status == 5) $status = "Укомплектован";
else if ($status == 6 || $status == 7) $status = "Собран";
else if ($status == 19) $status = "Выдан";
$server_name = $_SERVER['SERVER_NAME'];
?>


<?= parent::getPreloaderNotJS(); ?>
    <style>
        body {
            background-color: #E6E6FA;
        }

        .Actions {
            line-height: 42px;
        }

        .Actions .ButInp {
            height: 38px;
            position: relative;
            display: inline-block;
            margin-bottom: -15px;
        }

        .Actions .ButInp .InputButInp {
            width: 100%;
            height: 100%;
            float: left;
            border: none;
            background: transparent;
            border-radius: 25%;
        }

        .Actions .ButInp .ButtonButInp {
            position: absolute;
            left: 0;
            top: 0;
        }

        .Actions .Stock {
            color: #fff;
            background-color: #414099;
            border: 1px solid #414099;
            width: auto;
            padding: 0 5px;
            border-radius: 4px;
            height: 38px;
            position: relative;
            display: inline-block;
            margin-bottom: -15px;
            bottom: -2px;
            cursor: pointer;
        }

        .CustomerInfo {
            display: inline-block;
            width: calc(100% - 2px);
            margin-top: 10px;
            float: left;
            border-radius: 5px;
            overflow: hidden;
            box-shadow: 0 0 0 1px #414099;
        }

        .CustomerInfo .CustomerInfoTable {
            width: 100%;
            position: relative;
            border-collapse: collapse;
        }

        .CustomerInfo .CustomerInfoTable tr {
            height: 30px;
            line-height: 30px;
        }

        .CustomerInfo .CustomerInfoTable tr td {
            padding: 0 15px;
            min-width: 102px;
        }

        .CustomerInfo .CustomerInfoTable tr .Name {
            background-color: #414099;
            color: #ffffff;
            max-width: 80px;
            text-align: center;
        }

        .CustomerInfo .CustomerInfoTable tr td label {
            height: 8px;
            margin-right: -17px;
        }

        .CustomerInfo .CustomerInfoTable tr:not(:first-child) td:not(.Name) {
            border-top: 1px solid #414099;
        }

        .CustomerInfo .CustomerInfoTable tr:not(:first-child) td.Name {
            border-top: 1px solid #ffffff;
        }

        .CustomerInfo .CustomerInfoTable tr.Pay td.Value div{
            display: inline-block;
            float: left;
        }
        .CustomerInfo .CustomerInfoTable tr.Pay td.Value div.Radio {
            margin-right: 50px;
            line-height: 30px;
        }
        .CustomerInfo .CustomerInfoTable tr.Pay td.Value div.Radio div.RUB {
            margin-right: 15px;
        }
        .CustomerInfo .CustomerInfoTable tr.Pay td.Value div.Radio * {
            cursor: pointer;
        }
        .CustomerInfo .CustomerInfoTable tr.Pay td.Value span {
            font-weight: bold;
        }

        .Realization {
            display: inline-block;
            width: 100%;
            float: left;
        }
        .Realization .Table {
            margin-top: 10px;
            width: 100%;
            display: inline-block;
            border-radius: 5px 5px 0 0;
            overflow: hidden;
            overflow-x: auto;
        }
        .Realization .Elements {
            min-width: 100%;
            position: relative;
            border-collapse: collapse;
            float: left;
        }

        .Realization .Elements tr {
            border: 1px solid #414099;
            background-color: #E6E6FA;
            color: #000000;
        }

        .Realization .Elements tr td {
            border: 0;
            border-right: 1px solid #414099;
            width: auto;
            height: 30px;
            line-height: 20px;
            padding: 0 5px;
        }

        .Realization .Elements tr td button {
            display: inline-block;
            float: left;
            border: none;
            width: 30px;
            height: 30px;
            background-color: inherit;
            color: rgb(54, 53, 127);
            border-radius: 5px;
            cursor: pointer;
        }

        .Realization .Elements thead {
            position: relative;
            top: 0;
            left: 0;
        }

        .Realization .Elements thead tr td {
            background-color: #414099;
            color: #ffffff;
            border-color: #ffffff;
            padding: 5px 10px;
            text-align: center;
            min-width: 102px;
        }

        .Realization .Elements tbody tr {
            cursor: pointer;
        }

        .Realization .Elements tbody tr:hover {
            background-color: #97d8ee;
        }

        .Realization .Elements thead tr .ButtonTD {
            min-width: 0;
        }

        .Realization .Elements tbody tr .ButtonTD {
            width: 30px;
        }

        .Realization .Elements tr td:last-child {
            border-right: 0;
        }

        .Realization .Elements .CloneElementsHead {
            position: fixed;
            top: 0;
            left: 0;
        }

        .Realization .Elements .CloneElementsHeadTr {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1;
        }

        .Realization .Show {
            display: inline-block !important;
        }

        .Modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background-color: rgba(0, 0, 0, .8);
            z-index: 1;
            display: inline-block;
        }

        .Modal .Form {
            position: absolute;
            width: 320px;
            background-color: rgb(229, 229, 229);
            display: inline-block;
        }

        .Modal .Form.Provider {
            width: 640px;
        }

        .Modal .Form .Area {
            display: inline-block;
            float: left;
            width: 310px;
            height: auto;
            overflow: visible;
            margin: 0 5px;
            margin-bottom: 10px;
            position: relative;
        }

        .Modal .Form .Title {
            display: inline-block;
            width: 100%;
            height: 45px;
            line-height: 45px;
            background-color: rgb(54, 53, 127);
            color: rgb(255, 255, 255);
            text-align: center;
            font-weight: bold;
            font-size: 18px;
            margin-bottom: 10px;
        }

        .Modal .Form .Area .Input {
            display: inline-block;
            float: left;
            width: 100%;
            height: 30px;
            padding-left: 5px;
            border: none;
        }

        .Modal .Form .Area .Selects {
            display: inline-block;
            position: relative;
            float: left;
            width: 100%;
            height: 0;
            z-index: 2;
        }

        .Modal .Form .Area .Selects .Select {
            position: absolute;
            top: -1px;
            left: 0;
            display: inline-block;
            float: left;
            width: 100%;
            border: 1px solid rgb(169, 169, 169);
            border-top: 0;
            height: auto;
            background-color: rgb(54, 53, 127);
            color: rgb(255, 255, 255);
            max-height: 90px;
            overflow-y: scroll;
            overflow-x: hidden;
        }

        .Modal .Form .Area .Selects .Select .Item {
            display: inline-block;
            float: left;
            width: 100%;
            padding-left: 5px;
            height: auto;
            line-height: 25px;
            font-size: 14px;
            border-top: 1px solid rgb(255, 255, 255);
            cursor: pointer;
        }

        .Modal .Form .Area .Selects .Select .Item:hover {
            background-color: rgb(31, 30, 70);
        }

        .Modal .Form .Action {
            display: inline-block;
            float: left;
            width: calc(100% - 10px);
            height: auto;
            overflow: visible;
            margin: 0 5px;
            margin-bottom: 5px;
            position: relative;
        }

        .Modal .Form .Action .Button {
            display: inline-block;
            float: left;
            width: calc(70% - 5px);
            height: 30px;
            overflow: visible;
            margin-right: 10px;
            position: relative;
            cursor: pointer;
            background-color: rgb(54, 53, 127);
            color: #ffffff;
            border: none;
        }

        .Modal .Form .Action .Button.Cancel {
            width: calc(30% - 5px);
            margin-right: 0;
        }

        .Modal .Form .Line {
            display: inline-block;
            float: left;
            width: 100%;
            height: 5px;
            background-color: rgb(54, 53, 127);
        }

        .Input:hover + .Message,
        .Input:focus + .Message {
            display: inline-block;
        }

        .Message {
            position: absolute;
            top: -25px;
            left: 0;
            width: calc(100% - 2px);
            margin-left: 1px;
            height: 20px;
            line-height: 20px;
            font-size: 14px;
            font-weight: bold;
            text-align: center;
            background-color: #36357f;
            color: #ffffff;
            border-radius: 3px;
            box-shadow: 0 0 0 1px rgb(0, 0, 0);
            display: none;
        }

        .Message:before {
            content: '▼';
            position: absolute;
            left: calc(50% - 3px);
            top: 12px;
            color: #36357f;
            text-shadow: 0 1px 0 rgb(0, 0, 0);
            font-size: 8px;
        }

        .OGRN .Message {
            top: -45px;
            height: 40px;
        }

        .OGRN .Message:before {
            top: 32px;
        }

        .ModalDoc {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background-color: rgba(0, 0, 0, .8);
            z-index: 1;
            display: inline-block;
        }

        .ModalDoc .Document {
            position: absolute;
            left: 25px;
            top: 25px;
            width: calc(100vw - 50px);
            height: calc(100vh - 50px);
            overflow: hidden;
            background-color: rgb(54, 53, 127);
            color: rgb(255,255,255);
            z-index: 1;
            display: inline-block;
        }

        .ModalDoc .Document .iFrame {
            display: inline-block;
            width: 100%;
            height: calc(100vh - 90px);
            float: left;
        }

        .ModalDoc .Document .Actions {
            display: inline-block;
            width: 100%;
            height: 40px;
            line-height: 40px;
            float: left;
            padding: 0 10px;
        }

        .ModalDoc .Document .Actions .CheckBox {
            display: inline-block;
            width: auto;
            height: 40px;
            float: left;
        }

        .ModalDoc .Document .Actions .CheckBox .Name {
            display: inline-block;
            float: left;
            height: 40px;
            width: auto;
            margin-left: 10px;
        }

        .ModalDoc .Document .Actions .CheckBox input[type="checkbox"] {
            display: inline-block;
            float: left;
            width: 20px;
            height: 20px;
            margin: 10px;
            cursor: pointer;
        }

        .ModalDoc .Document .Actions .Right {
            display: inline-block;
            width: auto;
            height: 40px;
            float: right;
        }

        .ModalDoc .Document .Actions .Right button[type="button"] {
            position: relative;
            display: inline-block;
            width: 30px;
            height: 30px;
            margin: 5px;
            float: left;
            border: none;
            background-color: rgba(0,0,0,0);
            color: rgb(255,255,255);
            cursor: pointer;
            box-shadow: inset 0 0 0 1px rgb(255,255,255);
            border-radius: 3px;
        }

        .ModalDoc .Document .Actions .Right button[type="button"] i:before {
            position: absolute;
            left: 0;
            top: 0;
            width: 30px;
            height: 30px;
            line-height: 30px;
            text-align: center;
        }

    </style>

    <h1>Реализация проекта №<?=$numberProject;?></h1>
    <form class="Realization" action="javascript:Realization();">
        <input type="number" name="project" value="<?=$numberProject;?>" hidden>
        <input type="number" name="status" value="<?=$statusNumber;?>" hidden>
        <div class="Actions">
            <?= parent::getButtonBack(); ?>
            <div class="Action ButInp Customer">
                <input type="text" name="customer" class="InputButInp" required>
                <button type="button" class="btn btn-primary ButtonButInp" onclick="OpenModalCustomer(this)">
                    <i class="fa fa-user" aria-hidden="true"></i> Покупатель
                </button>
            </div>
            <button type="button" class="Action btn btn-primary Add Canvas" onclick="ShowModal(this)">
                <i class="fa fa-plus" aria-hidden="true"></i> Полотно
            </button>
            <button type="button" class="Action btn btn-primary Add Component" onclick="ShowModal(this)">
                <i class="fa fa-plus" aria-hidden="true"></i> Компонент
            </button>
            <select class="Action Stock" name="stock">
                <?foreach ($stocks as $s):?>
                    <option value="<?=$s->id;?>"><?=$s->name;?></option>
                <?endforeach;?>
            </select>
            <button type="submit" class="Action btn btn-primary Submit" <?php if ($data->customer->status == 8) echo "style='display:none;'"?>>
                <i class="fa fa-shopping-cart" aria-hidden="true"></i> <?=$status;?>
            </button>
        </div>
        <div class="CustomerInfo">
            <table class="CustomerInfoTable">
                <tbody>
                <tr class="Pay">
                    <td class="Name">Оплата:</td>
                    <td class="Value">
                        <div class="Radio">
                            <div class="RUB"><span class="RV">0</span> <i class="fa fa-rub"></i></div>
                        </div>
                    </td>
                </tr>
                <tr class="Client" style="display: none;">
                    <td class="Name">Клиент:</td>
                    <td class="Value">Романов Роман Романович</td>
                </tr>
                <tr class="Dealer" style="display: none;">
                    <td class="Name">Дилер:</td>
                    <td class="Value">Иванов Иван Иванович</td>
                </tr>
                </tbody>
            </table>
        </div>
        <div class="Table">
            <table class="Elements">
                <thead class="ElementsHead">
                <tr class="ElementsHeadTr">
                    <td>Название</td>
                    <td>Стоимость</td>
                    <td>Значение</td>
                    <td>Цена</td>
                    <td colspan="2" class="ButtonTD">Функции</td>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td hidden><input class="Good" name="goods[]" type="text" value="" hidden></td>
                    <td class="Text" onclick="OpenModal(this)"></td>
                    <td class="Price" onclick="OpenModal(this)"></td>
                    <td class="Value" onclick="OpenModal(this)"></td>
                    <td class="Itog" onclick="OpenModal(this)"></td>
                    <td class="ButtonTD">
                        <button type="button" class="Clone" onclick="CloneLine(this)">
                            <i class="fa fa-clone" aria-hidden="true"></i>
                        </button>
                    </td>
                    <td class="ButtonTD">
                        <button type="button" class="Remove" onclick="RemoveLine(this)">
                            <i class="fa fa-trash-o" aria-hidden="true"></i>
                        </button>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
    </form>
    <div class="Modal" style="display: none;">
        <form class="Form Component" action="javascript:AddElement('Component');"
              Page="index.php?option=com_gm_ceiling&task=componentform.getComponents">
            <div class="Title">Введите данные по компоненту:</div>
            <div class="Area Type">
                <input type="text" class="Input Type" name="Type" id="Type" placeholder="Введите тип:"
                       autocomplete="off"
                       NameDB="components.title"
                       onclick="GetList(this, ['Type','Unit'], ['Type','Name','Unit']);"
                       onkeyup="GetList(this, ['Type','Unit'], ['Type','Name','Unit']);"
                       onblur="ClearSelect(this)"
                       required>
                <div class="Message Type">Тип</div>
                <div class="Selects Type"></div>
            </div>
            <div class="Area Name">
                <input type="text" class="Input Name" name="Name" id="Name" placeholder="Введите название:"
                       autocomplete="off"
                       NameDB="options.title"
                       onclick="GetList(this, ['Type','Name','Unit','Price','CountUnit'], ['Type','Name','Unit']);"
                       onkeyup="GetList(this, ['Type','Name','Unit','Price','CountUnit'], ['Type','Name','Unit']);"
                       onblur="ClearSelect(this)"
                       required>
                <div class="Message Name">Название</div>
                <div class="Selects Name"></div>
            </div>
            <div class="Area Unit">
                <input type="text" class="Input Unit" name="Unit" id="Unit" placeholder="Введите название размерности:"
                       NameDB="components.unit"
                       onclick="GetList(this, ['Unit'], ['Type','Name','Unit']);"
                       onkeyup="GetList(this, ['Unit'], ['Type','Name','Unit']);"
                       onblur="ClearSelect(this)"
                       autocomplete="off" required>
                <div class="Message Unit">Название размерности</div>
                <div class="Selects Unit">
                </div>
            </div>
            <div class="Area Count">
                <input type="text" class="Input Count" name="Count" placeholder="Введите количество:" autocomplete="off"
                       onblur="CheckInput(this, 'CountUnit')" pattern="\d+|\d+[,\.]\d+" required>
                <div class="Message Count">Количество</div>
                <div class="Selects Count"></div>
            </div>
            <input type="text" class="Input CountUnit" name="CountUnit" id="CountUnit" NameDB="options.count_sale"
                   autocomplete="false" pattern="\d+|\d+[,\.]\d+" required hidden>
            <input type="text" class="Input Price" name="Price" id="Price" NameDB="options.price"
                   autocomplete="false" pattern="\d+|\d+[,\.]\d+" required hidden>
            <div class="Action">
                <button type="submit" class="Button Add Component">
                    Добавить
                </button>
                <button type="button" class="Button Cancel" onclick="CancelElement(this)">
                    Закрыть
                </button>
            </div>
            <div class="Line"></div>
        </form>
        <form class="Form Canvas" action="javascript:AddElement('Canvas');"
              Page="index.php?option=com_gm_ceiling&task=canvasform.getCanvases">
            <div class="Title">Введите данные по полотну:</div>
            <div class="Area Name">
                <input type="text" class="Input Name" name="Name" id="Name" placeholder="Введите название:"
                       autocomplete="off"
                       NameDB="canvases.name"
                       onclick="GetList(this, ['Name','Country', 'Price'], ['Color','Texture','Country','Name','Width']);"
                       onkeyup="GetList(this, ['Name','Country', 'Price'], ['Color','Texture','Country','Name','Width']);"
                       onblur="ClearSelect(this)"
                       required>
                <div class="Message Name">Название</div>
                <div class="Selects Name"></div>
            </div>
            <div class="Area Country">
                <input type="text" class="Input Country" name="Country" id="Country" placeholder="Введите страну:"
                       autocomplete="off"
                       NameDB="canvases.country"
                       onclick="GetList(this, ['Country', 'Price'], ['Color','Texture','Country','Name','Width']);"
                       onkeyup="GetList(this, ['Country', 'Price'], ['Color','Texture','Country','Name','Width']);"
                       onblur="ClearSelect(this)"
                       required>
                <div class="Message Country">Страна</div>
                <div class="Selects Country"></div>
            </div>
            <div class="Area Width">
                <input type="text" class="Input Width" name="Width" id="Width" placeholder="Введите ширину:"
                       autocomplete="off"
                       NameDB="canvases.width"
                       onclick="GetList(this, ['Width', 'Price'], ['Color','Texture','Country','Name','Width']);"
                       onkeyup="GetList(this, ['Width', 'Price'], ['Color','Texture','Country','Name','Width']);"
                       onblur="ClearSelect(this)"
                       pattern="\d+|\d+[,\.]\d+" required>
                <div class="Message Width">Ширина</div>
                <div class="Selects Width"></div>
            </div>
            <div class="Area Texture">
                <input type="text" class="Input Texture" name="Texture" id="Texture" placeholder="Введите текстуру:"
                       autocomplete="off"
                       NameDB="textures.texture_title"
                       onclick="GetList(this, ['Texture', 'Price'], ['Color','Texture','Country','Name','Width']);"
                       onkeyup="GetList(this, ['Texture', 'Price'], ['Color','Texture','Country','Name','Width']);"
                       onblur="ClearSelect(this)" required>
                <div class="Message Width">Текстура</div>
                <div class="Selects Width"></div>
            </div>
            <div class="Area Color">
                <input type="text" class="Input Color" name="Color" id="Color" placeholder="Введите цвет:"
                       autocomplete="off"
                       NameDB="colors.title"
                       onclick="GetList(this, ['Color', 'Price'], ['Color','Texture','Country','Name','Width']);"
                       onkeyup="GetList(this, ['Color', 'Price'], ['Color','Texture','Country','Name','Width']);"
                       onblur="ClearSelect(this);" required>
                <div class="Message Width">Цвет</div>
                <div class="Selects Width"></div>
            </div>
            <div class="Area Quad">
                <input type="text" class="Input Quad" name="Quad" id="Quad" placeholder="Введите квадратуру:"
                       NameDB="rollers.quad"
                       onblur="$(this).val(Float($(this).val(),2));"
                       autocomplete="off" pattern="\d+|\d+[,\.]\d+" required>
                <div class="Message Quad">Квадратура</div>
                <div class="Selects Quad"></div>
            </div>
            <input type="text" class="Input Price" name="Price" id="Price" NameDB="canvases.price"
                   autocomplete="false" pattern="\d+|\d+[,\.]\d+" required hidden>
            <div class="Action">
                <button type="submit" class="Button Add Canvas">
                    Добавить
                </button>
                <button type="button" class="Button Cancel" onclick="CancelElement(this)">
                    Закрыть
                </button>
            </div>
            <div class="Line"></div>
        </form>
        <form class="Form Customer" action="javascript:AddCustomer('Customer');"
              Page="index.php?option=com_gm_ceiling&task=stock.getCustomer">
            <div class="Title">Данные покупателя:</div>
            <div class="Area Name">
                <input type="text" class="Input Name" name="Name" id="Name" placeholder="Нет имени:"
                       autocomplete="off"
                       readonly
                       required>
                <div class="Message Name">Имя покупателя</div>
                <div class="Selects Name"></div>
            </div>
            <div class="Area Email">
                <input type="text" class="Input Email" name="Email" id="Email" placeholder="Нет эл. почты:"
                       autocomplete="off"
                       readonly
                       required>
                <div class="Message Email">Электронная почта</div>
                <div class="Selects Email"></div>
            </div>
            <div class="Area Phone">
                <input type="text" class="Input Phone" name="Phone" id="Phone" placeholder="Нет номера телефона:"
                       autocomplete="off"
                       readonly
                       required>
                <div class="Message Phone">Номер телефона</div>
                <div class="Selects Phone"></div>
            </div>
            <input type="text" class="Input JSON" name="JSON" id="JSON" NameDB="customer.JSON" hidden>
            <input type="text" class="Input Margin" name="Margin" id="Margin" NameDB="customer.margin" hidden>
            <div class="Action">
                <button type="button" class="Button Cancel" onclick="CancelElement(this)">
                    Закрыть
                </button>
            </div>
            <div class="Line"></div>
        </form>
    </div>
    <div class="ModalDoc" style="display: none;">
        <div class="Document">
            <iframe class="iFrame" >

            </iframe>
            <div class="Actions">
                <div class="CheckBox">
                    <div class="Name">Товарная накладная</div>
                    <input type="checkbox" id="PackingList" name="page">
                </div>
                <div class="CheckBox">
                    <div class="Name">Приходный кассовый ордер</div>
                    <input type="checkbox" id="RetailCashOrder" name="page">
                </div>
                <div class="CheckBox">
                    <div class="Name">Расходная накладная</div>
                    <input type="checkbox" id="SalesInvoice" name="page">
                </div>
                <div class="Right">
                    <button type="button" onclick="print_frame();"><i class="fa fa-print" aria-hidden="true"></i></button>
                    <button type="button" onclick="save_frame();"><i class="fa fa-floppy-o" aria-hidden="true"></i></button>
                    <button type="button" onclick="close_frame();"><i class="fa fa-times" aria-hidden="true"></i></button>
                </div>
            </div>
        </div>
    </div>

    <script>
        var $ = jQuery;
        var server_name ='<?php echo $server_name;?>';
        $(document).ready(Init);
        $(document).scroll(Scroll);
        $(window).resize(Resize);

        var Modal = {},
            Element = {},
            Scroll = {},
            Data = {margin: {canvas: 0, component: 0}},
            Calc = false,
            Customer = <?=json_encode($customer);?>,
            Goods = <?=json_encode($goods);?>;

        function Init() {
            $(".Actions .Customer").width($(".Actions .Customer .ButtonButInp").outerWidth(true));
            $('.chosen-container').remove();
            $('select').removeAttr("style");
            $(".ModalDoc .Document .Actions .CheckBox input[type=\"checkbox\"]").change(LoadPDF);


            ModalInit();
            ElementsInit();

            Goods.forEach(function (t, i) { AddElementPHP(t) });
            AddCustomer();

            ScrollInit();
            Resize();

            $(".PRELOADER_GM").hide();
        }

        function AddElementPHP(s, i) {
            var TopSum = $(".CustomerInfoTable .Pay"),
                element = Element.tr.clone();

            element.find(".Good").val(JSON.stringify(s));

            var line = (s.page === "Canvas") ? "Полотно: " : "Компонент: ",
                value;
            if (s.page === "Canvas") {
                if (s.Quad < 1)  return;
                line += s.Country + " " + s.Name + " " + s.Width + " - Т: " + s.Texture + " Ц: " + s.Color;
                value = s.Quad + " m²";
            }
            else {
                if (s.Name == null) s.Name = "Нет";
                line += s.Type + " " + s.Name;
                value = s.Count + " " + s.Unit;
            }

            element.find(".Text").html(line);
            element.find(".Value").html(value);
            element.find(".Price").html(s.PriceM);
            element.find(".Itog").html(s.Itog);

            Element.tbody.append(element);
        }

        function Resize() {
            ResizeHead();
        }

        function ModalInit() {
            Modal.canvas = $(".Modal form.Canvas").clone();
            Modal.components = $(".Modal form.Component").clone();
            Modal.customer = $(".Modal form.Customer").clone();
            Modal.modal = $(".Modal");
            Modal.modal.empty();
        }

        function AddElement(e) {
            var form = $(".Modal ." + e),
                type = form.find(".Add").text(),
                element = (type === "Изменить") ? Element.tr : Element.tr.clone(),
                elements = form.find(".Input"),
                TopSum = $(".CustomerInfoTable .Pay"),
                s = {};

            $.each(elements, function (i, v) {
                v = $(v);
                s[v.attr('name')] = v.val();
            });
            s.page = e;
            element.find(".Good").val(JSON.stringify(s));

            var line = (e === "Canvas") ? "Полотно: " : "Компонент: ",
                value;
            if (form.hasClass("Canvas")) {
                s.Width = Float(s.Width);
                s.Quad = Float(s.Quad);
                s.Price = Float(s.Price);

                line += s.Country + " " + s.Name + " " + s.Width + " - Т: " + s.Texture + " Ц: " + s.Color;
                value = s.Quad + " m²";
                s.PriceM = Margin(s.Price, Data.margin.canvas);
                s.Itog = Math.round(s.Quad * s.Price * 100) / 100;
            }
            else {
                s.Count = Float(s.Count);
                s.Price = Float(s.Price);

                line += s.Type + " " + s.Name;
                value = s.Count + " " + s.Unit;
                s.PriceM = Margin(s.Price, Data.margin.component);
                s.Itog = Math.round(s.Count * s.Price * 100) / 100;
            }
            element.find(".Text").html(line);
            element.find(".Value").html(value);
            element.find(".Price").html(s.PriceM);
            element.find(".Itog").html(s.Itog);

            Modal.components.find("input")
                .not(':button, :submit, :reset, :hidden')
                .val('')
                .removeAttr('checked')
                .removeAttr('selected');

            Modal.canvas.find("input")
                .not(':button, :submit, :reset, :hidden')
                .val('')
                .removeAttr('checked')
                .removeAttr('selected');

            if (type === "Изменить") CancelElement();
            else Element.tbody.append(element);

            Resize();
            Calculate();
        }

        function AddCustomer() {
            var element = $(".Realization .Actions .Customer"),
                cusromer = $(".CustomerInfo .CustomerInfoTable"),
                Temp = Customer;

            element.find(".InputButInp").val(JSON.stringify(Temp));

            if (Temp.client !== null)
            {
                var client = cusromer.find('.Client');
                client.show();
                client.find('.Value').text(Temp.client.name);

                Data.margin = Temp.Margin;
            }
            else {
                cusromer.find('.Client').hide();
                Data.margin = {canvas: 0, component: 0};
            }

            var dealer = cusromer.find('.Dealer');
            dealer.show();
            dealer.find('.Value').text(Temp.dealer.name);

            Calculate();
        }

        function Calculate() {
            var Elements = $("[name='goods[]']"),
                TopSum = $(".CustomerInfoTable .Pay"),
                Sum = 0.0;

            $.each(Elements, function (i, e) {
                e = $(e);
                var s = JSON.parse(e.val()),
                    tr = e.closest("tr");

                if (s.page === "Canvas") {
                        s.PriceM = Margin(Float(s.Price), Float(Data.margin.canvas));
                        s.Itog = Float(s.PriceM * s.Quad);
                } else {
                    try {
                        s.PriceM = Customer.dealer.dealerPrice[s.id];
                    } catch (e) {
                        s.PriceM = Margin(Float(s.Price), Float(Data.margin.component));
                    }
                    s.Itog = Float(s.PriceM * s.Count);
                }

                e.val(JSON.stringify(s));

                tr.find(".Price").html(Float(s.PriceM));
                tr.find(".Itog").html(Float(s.Itog));

                Sum += Float(s.Itog);
            });

            TopSum.find(".RUB .RV").html(Float(Sum));

            Calc = true;
        }

        function CancelElement(e = null) {
            Modal.components.find("input")
                .not(':button, :submit, :reset, :hidden')
                .val('')
                .removeAttr('checked')
                .removeAttr('selected');

            Modal.canvas.find("input")
                .not(':button, :submit, :reset, :hidden')
                .val('')
                .removeAttr('checked')
                .removeAttr('selected');

            Modal.customer.find("input")
                .not(':button, :submit, :reset, :hidden')
                .val('')
                .removeAttr('checked')
                .removeAttr('selected');

            Modal.modal.empty();
            Modal.modal.hide();
        }

        function ElementsInit() {
            Element.tr = $(".Realization .Elements tbody tr").clone();
            Element.tbody = $(".Realization .Elements tbody");
            var lenght = Element.tbody.children().length,
                val = Element.tr.find(".Good").val();
            if (lenght === 1 && val === "") Element.tbody.empty();

            $.ajax({
                url: "http://"+server_name+"/index.php?option=com_gm_ceiling&task=stock.getProject",
                async: false,
                data: {id: <?=(empty($numberProject))?0:$numberProject;?>},
                type: "POST",
                success: function (data) {
                    //data = JSON.parse(data);
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

        function ShowModal(e, data = null) {
            var e = $(e);
            Modal.modal.show();
            if (data === null) {
                var modal = null;
                if (e.hasClass("Canvas")) modal = Modal.canvas;
                else if (e.hasClass("Component")) modal = Modal.components;
                else if (e.hasClass("Customer")) modal = Modal.customer;
                else return;

                if (modal !== null) {
                    modal.find(".Add").text("Добавить");
                    Modal.modal.append(modal);
                }
                else Modal.modal.hide();
            }
            else {
                data = JSON.parse(data);
                var modal = null;
                if (data.page === "Canvas") modal = Modal.canvas;
                else if (data.page === "Component") modal = Modal.components;
                else if (data.page === "Customer") modal = Modal.customer;
                else return;

                if (modal === null) Modal.modal.hide();

                modal.find(".Add").text("Изменить");
                $.each(data, function (i, v) {
                    if (modal.find("[name = '" + i + "']"))
                        modal.find("[name = '" + i + "']").val(v);
                });
                Modal.modal.append(modal);
                if (data.page !== "Customer") Element.tr = e;
            }

            var top = ($(window).height() - Modal.modal.find(".Form").height()) / 4,
                left = ($(window).width() - Modal.modal.find(".Form").width()) / 2;

            Modal.modal.find(".Form").css({"top": top + "px", "left": left + "px"});
        }

        function RemoveLine(e) {
            e = $(e);
            e.closest("tr").remove();

            Calculate();
            ResizeHead();
        }

        function CloneLine(e) {
            e = $(e);
            var line = e.closest("tr").clone();
            Element.tbody.append(line);

            Calculate();
            ResizeHead();
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

        function CheckInput(e, id) {
            e = $(e);
            var round = Float($("." + id).val()),
                number = Float(e.val()),
                itog = number;
                if(round){
                    if (number % round !== 0) itog = (Math.floor(number / round) + 1) * round;
                }
            e.val(itog);
        }

        function SelectItem(e) {
            e = $(e);
            var parent = e.closest("form"),
                elements = parent.find(".Input");

            if (typeof e.attr('error') !== 'undefined' && e.attr('error') !== false)
            {
                var error = JSON.parse(e.attr('error'));
                $.each(error, function (i, v) {
                    noty({
                        theme: 'relax',
                        layout: 'center',
                        timeout: 1500,
                        type: "error",
                        text: v
                    });
                });
            }
            else if (e.hasClass("Add")) e.closest(".Area").find(".Input").attr({"clear": "false", "add": "true"});
            else {
                $.each(elements, function (i, v) {
                    v = $(v);
                    var id = v.attr('id');
                    if (typeof id !== 'undefined' && id !== false) {
                        var attr = e.attr(id);
                        if (typeof attr !== 'undefined' && attr !== false) {
                            v.val(attr);
                            v.attr({"clear": "false", "add": "false"});
                        }
                    }
                });
            }
        }

        function ClearSelect(e) {
            setTimeout(function () {
                e = $(e);
                if (e.attr("clear") != 'false') e.val("");
                e.siblings(".Selects").empty();
            }, 200);
        }

        function OpenModal(e) {
            e = $(e).closest("tr");
            var input = e.find(".Good");
            ShowModal(e, input.val());
        }

        function OpenModalCustomer(e) {
            e = $(e).closest(".Customer");
            var input = e.find(".InputButInp"),
                data = (input.val() === "") ? null : input.val();
            console.log(data);
            console.log(input.val());
            ShowModal(e, data);
        }

        function ScrollInit() {
            Scroll.EHead = $(".Realization .Elements .ElementsHead");
            Scroll.EHeadTr = Scroll.EHead.find(".ElementsHeadTr");
            Scroll.EHeadTrClone = Scroll.EHeadTr.clone();

            Scroll.EHeadTrClone.removeClass("ElementsHeadTr").addClass("CloneElementsHeadTr");
            Scroll.EHead.append(Scroll.EHeadTrClone);

            $(".Realization").scroll(ResizeHead);
        }

        function ResizeHead() {
            Scroll.EHeadTrClone.css("left", (Scroll.EHeadTr.offset().left));

            for (var i = 0; i < Scroll.EHeadTr.children().length; i++)
                $(Scroll.EHeadTrClone.children()[i]).width($(Scroll.EHeadTr.children()[i]).width() - ((i === 0) ? 1 : 0));
        }

        function Scroll() {
            var scrollTop = $(window).scrollTop(),
                offset = Scroll.EHeadTr.offset(),
                has = Scroll.EHeadTrClone.hasClass("Show");
            if (scrollTop >= offset.top) {
                if (!has) Scroll.EHeadTrClone.addClass("Show");
            }
            else {
                if (has) Scroll.EHeadTrClone.removeClass("Show");
            }
        }

        function Margin(x, y) {
            return (x * 100) / (100 - y);
        }

        function Float(x, y = 2) {
            return Math.ceil(parseFloat(("" + x).replace(',', '.')) * Math.pow(10, y)) / Math.pow(10, y);
        }

        function Realization() {
            $(".PRELOADER_GM").show();
            var data = $("form.Realization").serialize();
            if (Calc) {
                jQuery.ajax({
                    type: 'POST',
                    url: "/index.php?option=com_gm_ceiling&task=stock.realization",
                    data: data,
                    success: function (data) {
                        data = JSON.parse(data);

                        if (data.status === "error")
                        {
                            noty({
                                theme: 'relax',
                                layout: 'center',
                                timeout: 1500,
                                type: "error",
                                text: data.error
                            });
                        }
                        else if (data.status === "ok")
                        {
                            if (data.message != null)
                                noty({
                                    theme: 'relax',
                                    layout: 'center',
                                    timeout: 1500,
                                    type: "success",
                                    text: data.message
                                });

                            if (data.href != null)
                            {
                                $.each(data.href, function (i, t) { $("#"+i).val(t); $("#"+i).attr("checked",true); });
                                $(".ModalDoc .Document .iFrame").attr("src", data.href.MergeFiles);
                                $(".ModalDoc").show();
                                Calc = false;
                            }
                            else $("#BackPage").click();
                        }

                        $(".PRELOADER_GM").hide();
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

                        $(".PRELOADER_GM").hide();
                    }
                });
            } else {
                $(".ModalDoc").show();
                $(".PRELOADER_GM").hide();
            }
        }

        function LoadPDF() {
            var checkbox = $(".ModalDoc .Document .Actions .CheckBox input[type=\"checkbox\"]:checked"),
                values = [];

            $.each(checkbox, function (i, t) { values.push($(t).val());});

            if (values.length > 0) jQuery.ajax({
                type: 'POST',
                url: "http://"+server_name+"/index.php?option=com_gm_ceiling&task=stock.MergeFiles",
                data: {files: values},
                success: function (data) {
                    $(".ModalDoc .Document .iFrame").attr("src", data);
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

        function print_frame() { ($(".iFrame")[0].contentWindow || $('.iFrame')[0]).print(); }

        function save_frame() {
            var now = new Date();
            var link = document.createElement('a');
            link.setAttribute('href',$(".iFrame").attr("src"));
            link.setAttribute('download',"Реализация " + now.getDay() + "/" + now.getMonth() + "/" + now.getFullYear() + " " + now.getHours() + ":" + now.getMinutes() + ".pdf");
            onload=link.click();
        }

        function close_frame() { $("#BackPage").click(); }
    </script>

<?if(false):?>
<?= parent::getPreloaderNotJS(); ?>


<style>
    .receipt, .receipt div {
        width: 100%;
        display: inline-block;
    }

    .receipt .titleAll {
        width: 100%;
        height: 30px;
        line-height: 30px;
        margin: 5px 0;
    }

    .receipt .titleAll .title {
        width: auto;
        padding: 0 10px 0 0;
        float: left;
    }

    .receipt input {
        padding: 0 0 0 5px;
    }

    .receipt button {
        width: 30px;
        height: 30px;
        margin: 5px 0;
        float: left;
        background-color: #414099;
        color: #ffffff;
        border-color: #5653c9;
        border-radius: 5px;
        cursor: pointer;
    }

    .receipt button[type='submit'] {
        margin-right: 0;
    }

    .receipt .inputCanvas,
    .receipt .inputComponent {
        width: calc(100% + 10px);
        margin: 5px -5px;
    }

    .receipt .inputCanvasList .inputRollersList,
    .receipt .inputComponentList .inputOptionsList {
        width: calc(100% + 10px);
        margin: 0 -5px;
        padding: 5px 0 0 0;
    }

    .receipt .inputCanvas .input {
        width: calc((100% - 30px) / 5 - 10px);
        margin: 0 5px;
        float: left;
    }

    .receipt .input input,
    .receipt .input select {
        width: 100%;
        height: 30px;
        line-height: 30px;
        margin: 5px 0;
        float: left;
    }

    .receipt .inputCanvasList .inputRollersList .input {
        width: calc((100% - 30px) / 2 - 10px);
        margin: 0 5px;
        float: left;
    }

    .receipt .inputComponent .input {
        width: calc((100% - 30px) / 2 - 10px);
        margin: 0 5px;
        float: left;
    }

    .receipt .inputComponentList .inputOptionsList .input {
        width: calc((100% - 30px) / 2 - 10px);
        margin: 0 5px;
        float: left;
    }

    .receipt .inputCanvasList, .receipt .inputComponentList {
        width: 100%;
        padding: 0 15px 0 10px;
        margin: 5px 0;
        background-color: rgb(214, 210, 248);
        box-shadow: 0 0 0 .5px rgba(54, 53, 127, 1);
        border-radius: 3px;
    }

    .receipt .fa-plus {
        position: relative;
        top: -2px;
    }

    .receipt .button {
        width: auto;
        height: auto;
        float: right;
        margin: 0;
        margin-left: 10px;
    }

    .receipt .other {
        display: none;
    }

    .receipt .lockSelect {
        display: none;
        z-index: 1;
        position: relative;
        width: 100%;
        height: 0;
        top: -10px;
        float: left;
    }

    .receipt .select {
        position: absolute;
        top: 0;
        left: 0;
        width: calc(100% - 2px);
        margin-left: 1px;
        max-height: 70px;
        overflow-y: auto;
        overflow-x: hidden;
        box-shadow: 0 0 0 1px rgba(0, 0, 0, .3);
    }

    .receipt .input .select div {
        width: 100%;
        padding-left: 5px;
        height: 20px;
        line-height: 20px;
        font-size: 14px;
        margin: 0;
        background: rgb(240, 240, 240);
        float: left;
        box-shadow: inset 0 -1px 0 0 rgba(0, 0, 0, .3);
        cursor: pointer;
        overflow: hidden;
    }

    .receipt .input .select div:hover {
        background: rgb(220, 220, 220);
    }

    .receipt input {
        border: none;
    }

    .receipt input:invalid {
        box-shadow: inset 0 0 0 2px rgba(255, 0, 0, .5);
    }

    .receipt input:valid {
        box-shadow: none;
    }

    .receipt .infoCustomer, .receipt .Result {
        width: 100%;
        height: auto;
        background-color: rgb(214, 210, 248);
        box-shadow: 0 0 0 .5px rgba(54, 53, 127, 1);
        border-radius: 3px;
    }

    .receipt .infoCustomer .inputCustomer,
    .receipt .infoCustomer .outInfoCustomer {
        padding: 0 20px 20px;
        height: auto;
        float: left;
    }

    .receipt .infoCustomer .inputCustomer {
        width: calc(20% - 40px);
        margin: 20px;
        box-shadow: 0 0 0 .5px rgba(54, 53, 127, 1);
        border-radius: 3px;
        padding: 5px 5px 0 5px;
    }

    .receipt .infoCustomer .inputCustomer #inputCustomerType,
    .receipt .infoCustomer .inputCustomer #inputCustomer {
        width: 100%;
        height: 32px;
        line-height: 30px;
        padding: 0 10px;
        margin-bottom: 10px;
        float: left;
    }

    .receipt .infoCustomer .inputCustomer #inputCustomer {
        height: 30px;
    }

    .receipt .infoCustomer .outInfoCustomer {
        width: 80%;
    }

    .receipt .infoCustomer .outInfoCustomer .infoClient,
    .receipt .infoCustomer .outInfoCustomer .infoDealer {
        width: 100%;
        margin-top: 20px;
        min-height: 40px;
        box-shadow: 0 0 0 .5px rgba(54, 53, 127, 1);
        border-radius: 3px;
        padding: 5px;
        float: left;
    }

    .receipt .infoCustomer .title {
        width: auto;
        height: 16px;
        line-height: 16px;
        font-size: 16px;
        padding: 0 5px;
        margin-left: 5px;
        background-color: rgb(214, 210, 248);
        position: relative;
        top: -15px;
        margin-bottom: -10px;
        float: left;
    }

    .receipt .Result {
        margin: 20px 0;
    }

    .receipt .Result .Line {
        height: 40px;
        padding: 5px;
        float: left;
    }

    .receipt .Result .Line .Course,
    .receipt .Result .Line .Price,
    .receipt .Result .Line .Radio {
        float: left;
        width: auto;
        height: 30px;
        line-height: 30px;
        padding: 0 10px;
    }

    .receipt .Result .Line .Price,
    .receipt .Result .Line .Radio {
        float: right;
    }

    .receipt .Result .Line .Price span {
        font-weight: bold;
    }

    .receipt .Result .Line .Price i {
        color: #373a3c !important;
        font-size: 14px;
    }

    .receipt .Result .Line .Radio {
        width: 30px;
        height: 40px;
        line-height: normal;
        padding: 0;
        margin: -5px 0;
        margin-left: 5px;
    }

    .receipt .Result .Line .Radio input[name='valute'] {
        width: 30px;
        height: 30px;
        opacity: 0;
        padding: 0;
        margin: 5px -30px;
        position: relative;
        z-index: -1;
    }

    .receipt .infoCustomer .Customer {
        width: calc(50% - 20px);
        float: left;
        margin: 10px;
        margin-top: 15px;
        box-shadow: 0 0 0 .5px rgba(54, 53, 127, 1);
        border-radius: 3px;
        padding: 10px;
    }

    .receipt .infoCustomer .Customer .line {
        width: 100%;
        float: left;
    }

    .receipt .infoCustomer .Customer .title {
        height: 10px;
        line-height: 10px;
        padding: 0 5px;
        position: relative;
        top: -17px;
        margin-bottom: -18px;
        width: auto;
        background-color: rgb(214, 210, 248);
    }

    .receipt .infoCustomer .Customer table {
        width: 100%;
        font-size: 16px;
    }

    .receipt .infoCustomer .Customer table tr td:first-child {
        width: 20%;
    }

    .receipt .infoCustomer .Customer table tr td input {
        height: 100%;
        width: 100%;
        font-size: 16px;
    }
    .receipt .input
    {
        position: relative;
    }
    .receipt .inputMessage:hover + .Message,
    .receipt .inputMessage:focus + .Message
    {
        display: inline-block;
    }
    .receipt .Message
    {
        position: absolute;
        top: -21px;
        left: 0;
        width: 100%;
        height: 20px;
        line-height: 20px;
        font-size: 14px;
        font-weight: bold;
        text-align: center;
        background-color: #36357f;
        color: #ffffff;
        border-radius: 3px;
        box-shadow: 0 0 0 1px #000000;
        display: none;
    }
    .receipt .Message:before
    {
        content: '▼';
        position: absolute;
        left: calc(50% - 3px);
        top: 12px;
        color: #36357f;
        text-shadow: 0 1px 0 #000000;
        font-size: 8px;
    }
    .receipt .Stock
    {
        width: 100%;
        padding: 10px;
        background-color: rgb(214, 210, 248);
        box-shadow: 0 0 0 0.5px rgba(54, 53, 127, 1);
        border-radius: 3px;
        margin: 10px 0;
    }

    .receipt .Stock .title
    {
        text-align: right;
        width: 50%;
        float: left;
        height: 30px;
        line-height: 30px;
        padding-right: 10px;
    }

    .receipt .Stock .stockSelect
    {
        width: 50%;
        float: left;
        height: 30px;
        line-height: 30px;
    }
</style>

<h1>Реализация полотен и компонентов по проекту №<?= $numberProject; ?></h1>
<form class="receipt" target="_blank"
      action="<?php echo JRoute::_('index.php?option=com_gm_ceiling&task=stock.realization'); ?>"
      method="post" class="form-validate form-horizontal" enctype="multipart/form-data" >
    <input type="hidden" name="status" value="2">
    <input type="number" name="project" value="<?=$numberProject;?>" hidden>
    <div class="infoCustomer">
        <input name="customer[type]" value="1" type="number" hidden>
        <div class="Customer Client">
            <input name="customer[client_id]" value="<?= $customer->client->id ?>" type="number" hidden required>
            <div class="line title">Информация о клиенте</div>
            <table>
                <tr>
                    <td>Имя:</td>
                    <td><?= $customer->client->name ?></td>
                </tr>
                <tr>
                    <td>Телефон:</td>
                    <td><?= $customer->client->phone ?></td>
                </tr>
                <tr>
                    <td>Почта:</td>
                    <td><input type="email" name="customer[client_email]" class="ClientEmail" required></td>
                </tr>
            </table>
        </div>
        <div class="Customer Dealer">
            <input name="customer[dealer_id]" value="<?= $customer->dealer->id ?>" type="number" hidden required>

            <div class="line title">Информация о дилере</div>
            <table>
                <tr>
                    <td>Имя:</td>
                    <td><?= $customer->dealer->name ?></td>
                </tr>
                <tr>
                    <td>Телефон:</td>
                    <td><?= $customer->dealer->phone ?></td>
                </tr>
                <tr>
                    <td>Почта:</td>
                    <td><?= $customer->dealer->email ?></td>
                </tr>
            </table>
        </div>
    </div>
    <!---- Полотна ------------------------------------------------------------------------------------------------------------>
    <div class="Canvases">
        <div class="titleCanvases titleAll">
            <div class="title">Полотна</div>
            <button type="button" class="buttonCanvasAdd" onclick="add('Canvases','List', this)"><i class="fa fa-plus"
                                                                                                    aria-hidden="true"></i>
            </button>
        </div>
        <? foreach ($canvases as $canvas): ?>
            <div class="List inputCanvasList <?= (!empty($canvas->count)) ? 'show' : ''; ?>">
                <input type="number" name="canvases[count][]" id="inputCount"
                       value="<?= (!empty($canvas->count)) ? $canvas->count : '1'; ?>" hidden>
                <div class="inputCanvas">
                    <div class="input canvasCountry" top="List" parent="List">
                        <input type="text" name="canvases[country][]" id="inputCanvasCountry" tname="country"
                               parent="canvases" title = "Страна" class="inputMessage"
                               onkeyup="getList(this, ['inputCanvasName', 'inputCanvasWidth', 'inputCanvasTexture', 'inputCanvasColor']);"
                               onfocus="getList(this, ['inputCanvasName', 'inputCanvasWidth', 'inputCanvasTexture', 'inputCanvasColor']);"
                               onblur="hideItems(this);"
                               placeholder="Введите страну:"
                               value="<?= (!empty($canvas->count)) ? $canvas->country : ''; ?>" autocomplete="off"
                               required>
                        <div class="Message">Страна</div>
                        <div class="lockSelect">
                            <div class="select country"></div>
                        </div>
                    </div>
                    <div class="input canvasName" top="List" parent="List">
                        <input type="text" name="canvases[name][]" id="inputCanvasName" tname="name" parent="canvases"
                               onkeyup="getList(this, ['inputCanvasCountry', 'inputCanvasWidth', 'inputCanvasTexture', 'inputCanvasColor'])"
                               onfocus="getList(this, ['inputCanvasCountry', 'inputCanvasWidth', 'inputCanvasTexture', 'inputCanvasColor'])"
                               onblur="hideItems(this);" title = "Название" class="inputMessage"
                               placeholder="Введите название:"
                               value="<?= (!empty($canvas->count)) ? $canvas->name : ''; ?>" autocomplete="off"
                               required>
                        <div class="Message">Название</div>
                        <div class="lockSelect">
                            <div class="select name"></div>
                        </div>
                    </div>
                    <div class="input canvasWidth" top="List" parent="List">
                        <input type="text" name="canvases[width][]" id="inputCanvasWidth" tname="width"
                               parent="canvases" title = "Ширина" class="inputMessage"
                               onkeyup="getList(this, ['inputCanvasCountry', 'inputCanvasName', 'inputCanvasTexture', 'inputCanvasColor'])"
                               onfocus="getList(this, ['inputCanvasCountry', 'inputCanvasName', 'inputCanvasTexture', 'inputCanvasColor'])"
                               onblur="hideItems(this);" pattern="\d+|\d+[,\.]\d+"
                               placeholder="Введите ширину:"
                               value="<?= (!empty($canvas->count)) ? $canvas->width : ''; ?>" autocomplete="off"
                               required>
                        <div class="Message">Ширина</div>
                        <div class="lockSelect">
                            <div class="select width"></div>
                        </div>
                    </div>
                    <div class="input canvasTexture" top="List" parent="List">
                        <input type="text" name="canvases[texture][]" id="inputCanvasTexture" tname="texture_title"
                               parent="textures" title = "Фактура" class="inputMessage"
                               onkeyup="getList(this, ['inputCanvasCountry', 'inputCanvasName', 'inputCanvasWidth', 'inputCanvasColor'])"
                               onfocus="getList(this, ['inputCanvasCountry', 'inputCanvasName', 'inputCanvasWidth', 'inputCanvasColor'])"
                               onblur="hideItems(this);"
                               placeholder="Введите фактуру:"
                               value="<?= (!empty($canvas->count)) ? $canvas->texture : ''; ?>" autocomplete="off"
                               required>
                        <div class="Message">Фактура</div>
                        <div class="lockSelect">
                            <div class="select texture_title"></div>
                        </div>
                    </div>
                    <div class="input canvasColor" top="List" parent="List">
                        <input type="text" name="canvases[color][]" id="inputCanvasColor" tname="title"
                               parent="colors" title = "Цвет" class="inputMessage"
                               onkeyup="getList(this, ['inputCanvasCountry', 'inputCanvasName', 'inputCanvasWidth', 'inputCanvasTexture'])"
                               onfocus="getList(this, ['inputCanvasCountry', 'inputCanvasName', 'inputCanvasWidth', 'inputCanvasTexture'])"
                               onblur="hideItems(this);"
                               placeholder="Введите цвет:"
                               value="<?= (!empty($canvas->count)) ? $canvas->color : ''; ?>" autocomplete="off"
                               required>
                        <div class="Message">Цвет</div>
                        <div class="lockSelect">
                            <div class="select title"></div>
                        </div>
                    </div>
                    <button type="button" class="buttonCanvasDelete" onclick="remove('Canvases','List', this)"><i
                                class="fa fa-trash-o" aria-hidden="true"></i>
                </div>
                <div class="titleRollers titleAll">
                    <div class="title">Ролики данного полотна</div>
                    <button type="button" class="buttonRollerAdd" onclick="add('List','subList', this)"><i
                                class="fa fa-plus" aria-hidden="true"></i></button>
                </div>
                <? foreach ($canvas->rollers as $roller): ?>
                    <div class="subList inputRollersList">
                        <div class="inputRoller">
                            <input type="number" name="canvases[rollers][discount][]"
                                   value="<?= (!empty($roller)) ? $roller->discount : ''; ?>" hidden>
                            <div class="input rollersQuadrature" top="List" parent="subList">
                                <input type="text" min="0" name="canvases[rollers][quad][]" id="inputRollerQuadrature"
                                       tname="lenght" parent="canvases_all" title = "Квадратура" class="inputMessage"
                                       placeholder="Введите квадратуру:" pattern="\d+|\d+[,\.]\d+"
                                       value="<?= (!empty($roller)) ? $roller->quad : ''; ?>" autocomplete="off"
                                       required>
                                <div class="Message">Квадратура</div>
                                <div class="lockSelect">
                                    <div class="select lenght"></div>
                                </div>
                            </div>
                            <div class="input canvasTexture" top="subList">
                                <input type="number" min="0" name="canvases[rollers][count][]" id="inputRollerCount"
                                       placeholder="Введите количество:" title = "Количество" class="inputMessage"
                                       value="<?= (!empty($roller)) ? $roller->count : ''; ?>" autocomplete="off"
                                       required>
                                <div class="Message">Количество</div>
                            </div>
                            <button type="button" class="buttonRollerDelete" onclick="remove('List','subList', this)"><i
                                        class="fa fa-trash-o" aria-hidden="true"></i></button>
                        </div>
                    </div>
                <? endforeach; ?>
            </div>
        <? endforeach; ?>
    </div>

    <!---- Компоненты ------------------------------------------------------------------------------------------------------------>
    <div class="Components">
        <div class="titleComponents titleAll">
            <div class="title">Компоненты:</div>
            <button type="button" class="buttonCanvasAdd" onclick="add('Components', 'List', this)"><i
                        class="fa fa-plus" aria-hidden="true"></i></button>
        </div>
        <? foreach ($components as $component): ?>
            <div class="List inputComponentList <?= (!empty($component->count)) ? 'show' : ''; ?>">
                <input type="number" name="components[count][]" id="inputCount"
                       value="<?= (!empty($component->count)) ? $component->count : 1; ?>" hidden>
                <div class="inputComponent">
                    <div class="input componentsTitle" top="List" parent="List">
                        <input type="text" name="components[title][]" id="inputComponentTitle" tname="title"
                               parent="components" class="inputMessage" title="Тип"
                               onkeyup="getList(this, ['inputComponentUnit','inputOptionTitle'])"
                               onfocus="getList(this, ['inputComponentUnit','inputOptionTitle'])"
                               onblur="hideItems(this);"
                               placeholder="Введите тип:"
                               value="<?= (!empty($component->count)) ? $component->title : ''; ?>" autocomplete="off"
                               required>
                        <div class="Message">Тип</div>
                        <div class="lockSelect">
                            <div class="select title"></div>
                        </div>
                    </div>
                    <div class="input componentsUnit" top="List" parent="List">
                        <input type="text" name="components[unit][]" id="inputComponentUnit" tname="unit"
                               parent="components" class="inputMessage" title="Тип"
                               onkeyup="getList(this, ['inputComponentTitle','inputOptionTitle'])"
                               onfocus="getList(this, ['inputComponentTitle','inputOptionTitle'])"
                               onblur="hideItems(this);"
                               placeholder="Введите размерность:"
                               value="<?= (!empty($component->count)) ? $component->unit : ''; ?>" autocomplete="off"
                               required>
                        <div class="Message">Размерность</div>
                        <div class="lockSelect">
                            <div class="select unit"></div>
                        </div>
                    </div>
                    <button type="button" class="buttonComponentDelete" onclick="remove('Components', 'List', this)"><i
                                class="fa fa-trash-o" aria-hidden="true"></i>
                </div>
                <div class="titleOptions titleAll">
                    <div class="title">Компонент:</div>
                    <button type="button" class="buttonOptionAdd" onclick="add('List', 'subList', this)"><i
                                class="fa fa-plus" aria-hidden="true"></i></button>
                </div>
                <? foreach ($component->options as $option): ?>
                    <div class="subList inputOptionsList">
                        <div class="inputOption">
                            <input type="number" name="components[options][discount][]"
                                   value="<?= (!empty($option)) ? $option->discount : ''; ?>" hidden>
                            <div class="input optionTitle" top="List" parent="subList">
                                <input type="text" name="components[options][title][]" id="inputOptionTitle"
                                       tname="title" parent="options" class="inputMessage" title="Название"
                                       onkeyup="getList(this, ['inputComponentTitle','inputComponentUnit','inputComponentTitle'])"
                                       onfocus="getList(this, ['inputComponentTitle','inputComponentUnit','inputComponentTitle'])"
                                       onblur="hideItems(this);"
                                       placeholder="Введите название:"
                                       value="<?= (!empty($option)) ? $option->title : ''; ?>" autocomplete="off"
                                       required>
                                <div class="Message">Название</div>
                                <div class="lockSelect">
                                    <div class="select title"></div>
                                </div>
                            </div>
                            <div class="input optionCount" top="subList">
                                <input type="number" min="0" name="components[options][count][]"
                                       class="inputOptionCount inputMessage" title="Количество"
                                       placeholder="Введите количество:"
                                       value="<?= (!empty($option)) ? $option->count : ''; ?>" autocomplete="off"
                                       required>
                                <div class="Message">Количество</div>
                            </div>
                            <button type="button" class="buttonOptionDelete" onclick="remove('List', 'subList', this)">
                                <i
                                        class="fa fa-trash-o" aria-hidden="true"></i></button>
                        </div>
                    </div>
                <? endforeach; ?>
            </div>
        <? endforeach; ?>
    </div>

    <div class="Result" id="Result">
        <div class="Line RUB ResultRUB" id="ResultRUB">
            <div class="Course RUB">
                RUB:
            </div>
            <div class="Price RUB">
                Итого: <span>?</span> <i class="fa fa-rub" aria-hidden="true"></i>
            </div>
        </div>
    </div>
    <div class="Stock">
        <div class="title">Выберете склад для сохранения:</div>
        <select class="stockSelect" name="stock">
            <?foreach ($stocks as $k => $s):?>
                <option value="<?=$s->id;?>" <?=($k == 0)?"selected":"";?>><?=$s->name;?></option>
            <?endforeach;?>
        </select>
    </div>
    <div class="Complite">
        <?if($customer->status == 7):?>
        <button type="submit" onclick="calculation();" class="button buttonComplite btn btn-primary"><i
                    class="fa fa-shopping-cart"
                    aria-hidden="true"> Выдан</i></button>
        <?endif;?>
        <?$s = $customer->status; if($s == 5 || $s == 6):?>
        <button type="button" onclick="Status(<?=($s == 5)?19:7;?>)" class="button buttonComplete btn btn-primary"><i
                    class="fa fa-check-square-o"
                    aria-hidden="true"> Укомплектован</i></button>
        <?endif;?>
        <button type="button" onclick="calculation()" class="button buttonCalculation btn btn-primary"><i
                    class="fa fa-calculator"
                    aria-hidden="true"> Рассчитать</i>
        </button>
        <div class="button buttonCancel"><?= parent::getButtonBack("Отмена", false); ?></div>
    </div>
</form>

<script>
    var objectList = {};
    var RootBlock = {};
    var ResultBlocks = {};

    jQuery(document).ready(function () {
        jQuery('.chosen-container').remove();
        jQuery('select').attr('style', null);
        objectList.Canvases = jQuery('.inputCanvasList:first').clone().removeClass('show');
        jQuery('.inputCanvasList').filter(':not(.show)').remove();

        objectList.Components = jQuery('.inputComponentList:first').clone().removeClass('show');
        jQuery('.inputComponentList').filter(':not(.show)').remove();

        RootBlock.Canvases = jQuery('.Canvases').html();
        RootBlock.Components = jQuery('.Components').html();
        jQuery(".infoCustomer #inputCustomerType").change();
        calculation();
    });

    function add(List, subList, item) {
        item = jQuery(item);

        var nameList = List;
        List = item.closest('.' + List);
        var subLists = List.find('.' + subList);

        var subListClone = null;
        if (objectList[nameList]) subListClone = objectList[nameList].clone();
        else {
            subList = List.find('.' + subList + ':last');
            var subListClone = subList.clone();
        }

        jQuery.each(subListClone.find('input:not(#inputCount)'), function (key, value) {
            jQuery(value).val('');
        });

        List.append(subListClone);

        if (nameList == 'List') {
            var count = List.find('#inputCount');
            count.val(subLists.size() + 1);
        }
        jQuery(".Result .Price span").html("?");
    }

    function remove(List, subList, item) {
        item = jQuery(item);

        var nameList = List;
        List = item.closest('.' + List);
        var subLists = List.find('.' + subList);

        if (subLists.size() > 1 || objectList[nameList]) {
            subList = item.closest('.' + subList);

            subList.remove();

            if (nameList == 'List') {
                var count = List.find('#inputCount');
                count.val(subLists.size() - 1);
            }
        }
        else {
            noty({
                theme: 'relax',
                layout: 'center',
                timeout: 1500,
                type: "error",
                text: "Нельзя удалить!"
            });
        }
        jQuery(".Result .Price span").html("?");
    }

    function filtres(id, values) {
        var pages = {
            canvas: '<?= JRoute::_('index.php?option=com_gm_ceiling&task=canvasform.getCanvases');?>',
            component: '<?= JRoute::_('index.php?option=com_gm_ceiling&task=componentform.getComponents');?>'
        };
        var like = function () {
            var where = {};
            jQuery.each(values, function (id, value) {
                where[value.name] = '\'%' + value.value + '%\'';
            });
            return where;
        };
        switch (id) {
            case 'inputCanvasCountry':
                var filter = {
                    filter: {
                        select: {title: values[0].name},
                        where: {
                            like: [],
                            '>': {'canvases.count': 0}
                        },
                        group: [values[0].name],
                        order: [values[0].name],
                        objectsId: {title: id},
                        page: pages.canvas
                    }
                };
                filter.filter.where.like = like();
                return filter;
                break;
                filter.filter.where.like = like();
                return filter;
                break;
            case 'inputCanvasName':
                var filter = {
                    filter: {
                        select: {title: values[0].name},
                        where: {
                            like: [],
                            '>': {'canvases.count': 0}
                        },
                        group: [values[0].name],
                        order: [values[0].name],
                        objectsId: {title: id},
                        page: pages.canvas
                    }
                };
                filter.filter.where.like = like();
                return filter;
                break;
            case 'inputCanvasWidth':
                var filter = {
                    filter: {
                        select: {title: values[0].name},
                        where: {
                            like: [],
                            '>': {'canvases.count': 0}
                        },
                        group: [values[0].name],
                        order: [values[0].name],
                        objectsId: {title: id},
                        page: pages.canvas
                    }
                };
                filter.filter.where.like = like();
                return filter;
                break;
            case 'inputCanvasTexture':
                var filter = {
                    filter: {
                        select: {title: values[0].name},
                        where: {
                            like: [],
                            '>': {'canvases.count': 0}
                        },
                        group: [values[0].name],
                        order: [values[0].name],
                        objectsId: {title: id},
                        page: pages.canvas
                    }
                };
                filter.filter.where.like = like();
                return filter;
                break;
            case 'inputCanvasColor':
                var filter = {
                    filter: {
                        select: {title: values[0].name},
                        where: {
                            like: [],
                            '>': {'canvases.count': 0}
                        },
                        group: [values[0].name],
                        order: [values[0].name],
                        objectsId: {title: id},
                        page: pages.canvas
                    }
                };
                filter.filter.where.like = like();
                return filter;
                break;
            case 'inputRollerQuadrature':
                var filter = {
                    filter: {
                        select: {title: values[0].name},
                        where: {
                            like: [],
                            '>': {'canvases.count': 0}
                        },
                        group: [values[0].name],
                        order: [values[0].name],
                        objectsId: {title: id},
                        page: pages.canvas
                    }
                };
                filter.filter.where.like = like();
                return filter;
                break;
            case 'inputComponentTitle':
                var filter = {
                    filter: {
                        select: {title: values[0].name, unit: "components.unit"},
                        where: {
                            like: [],
                            '>': {'options.count': 0}
                        },
                        group: [values[0].name],
                        order: [values[0].name],
                        objectsId: {title: id, unit: "inputComponentUnit"},
                        page: pages.component
                    }
                };
                filter.filter.where.like = like();
                return filter;
                break;
            case 'inputComponentUnit':
                var filter = {
                    filter: {
                        select: {title: values[0].name},
                        where: {
                            like: [],
                            '>': {'options.count': 0}
                        },
                        group: [values[0].name],
                        order: [values[0].name],
                        objectsId: {title: id},
                        page: pages.component
                    }
                };
                filter.filter.where.like = like();
                return filter;
                break;
            case 'inputOptionTitle':
                var filter = {
                    filter: {
                        select: {title: values[0].name},
                        where: {
                            like: [],
                            '>': {'options.count': 0}
                        },
                        group: [values[0].name],
                        order: [values[0].name],
                        objectsId: {title: id},
                        page: pages.component
                    }
                };
                filter.filter.where.like = like();
                return filter;
                break;
        }
    }

    function getList(thisObject, Objects = null) {
        var input = jQuery(thisObject);
        var root = input.closest('.List');
        input.attr('check', 0);

        var id = input.attr('id');

        var values = [];
        values.push({name: input.attr('parent') + '.' + input.attr('tname'), value: input.val(), id: id});
        jQuery.each(Objects, function (key, idObject) {
            var itemTemp = root.find('#' + idObject);
            values.push({
                name: itemTemp.attr('parent') + '.' + itemTemp.attr('tname'),
                value: itemTemp.val(),
                id: idObject
            });
        });

        var filter = filtres(id, values);

        var items = input.parent().find('.select').filter('.' + input.attr('tname'));
        var lockSelect = items.parent();
        var option = jQuery('<div class="add" onclick="selectItem(this)" parent="' + id + '">+ Добавить</div>');

        if (input.is(":focus")) {
            jQuery.ajax({
                type: 'POST',
                url: filter.filter.page,
                data: filter,
                success: function (data) {
                    data = JSON.parse(data);
                    items.empty();
                    jQuery.each(data, function (index, item) {
                        if (item.title != null) {
                            var itemObj = option.clone().html(item.title).attr({'class': 'option'});
                            jQuery.each(item, function (index, value) {
                                itemObj.attr(index, value);
                            });

                            items.append(itemObj);
                        }
                    });
                    if (id == 'inputCanvasColor') {
                        var optionNo = option.clone().html("Нет").attr('class', 'empty');
                        items.append(optionNo);
                    }
                    lockSelect.show();
                },
                dataType: "text",
                timeout: 10000,
                error: function () {
                    var n = noty({
                        theme: 'relax',
                        layout: 'center',
                        maxVisible: 5,
                        type: "error",
                        text: "Сервер не отвечает!"
                    });
                }
            });
        }
    }

    function hideItems(thisObject) {
        var input = jQuery(thisObject);
        var select = input.parent().find('.select');
        var lockSelect = select.parent();

        setTimeout(function () {
            if (!input.attr('check') || input.attr('check') == 0) {
                input.val('');
                if (input.attr('id') == 'inputCustomer') {
                    var root = jQuery(".infoCustomer");
                    root.find(".ClientName").find("span").remove();
                    root.find(".ClientPhones").find("span").remove();
                    root.find(".DealerName").find("span").remove();
                    root.find(".DealerPhones").find("span").remove();
                }
            }
            select.empty();
            lockSelect.hide();

        }, 200);
    }

    function selectItem(thisObject) {
        item = jQuery(thisObject);
        var select = item.parent();
        var lockSelect = select.parent();

        var inputDiv = item.closest('.input');
        var root = inputDiv.closest('.' + inputDiv.attr('top'));
        var subroot = inputDiv.closest('.' + inputDiv.attr('parent'));
        var other = inputDiv.find(".other");
        if (other.attr('class') != root.find('#' + item.attr("parent")).attr('class')) other.hide().val('');

        var iclass = item.attr('class');
        var id = item.attr('parent');

        if (iclass == 'option') {

            var filter = filtres(item.attr("parent"), [{name: item.attr("parent"), value: ''}]);
            var objects = filter.filter.objectsId;

            jQuery.each(objects, function (key, val) {
                subroot.find('#' + val).val(item.attr(key));
            });
        }
        else if (iclass == 'empty') {
            subroot.find('#' + id).val(item.html());
        }
        else if (iclass == 'add') {
            inputDiv.find(".other").show();
        }

        subroot.find('#' + id).attr('check', '1');

        select.empty();
        lockSelect.hide();
    }

    function calculation() {
        jQuery('.PRELOADER_GM').show();
        var url = jQuery(".receipt").attr('action'),
            data = {
                data: jQuery(".receipt").serialize(),
                status: 1
            };

        jQuery.ajax({
            type: 'POST',
            url: url,
            data: data,
            success: function (data) {
                jQuery('.PRELOADER_GM').hide();
                data = JSON.parse(data);
                if (data.error != null) {
                    noty({
                        theme: 'relax',
                        layout: 'center',
                        timeout: 5000,
                        type: "error",
                        text: data.error
                    });
                }
                else {
                    var RUB = jQuery(".Result .Line.RUB"),
                        USD = jQuery(".Result .Line.USD");

                    RUB.find(".Price span").text(data.RUB);

                    USD.find(".Course span").text(data.valute.USD);
                    USD.find(".Price span").text(data.USD);
                }
            },
            dataType: "text",
            timeout: 10000,
            error: function () {
                jQuery('.PRELOADER_GM').hide();
                var n = noty({
                    theme: 'relax',
                    layout: 'center',
                    timeout: 2000,
                    type: "error",
                    text: "Сервер не отвечает!"
                });
            }
        });
    }

    function Status(s) {
        jQuery.ajax({
            type: 'POST',
            url: '<?= JRoute::_('index.php?option=com_gm_ceiling&task=stock.Status');?>',
            data: {status: s, id: <?=$numberProject;?>},
            success: function () {window.history.back();},
            dataType: "text",
            timeout: 10000,
            error: function () {
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
</script>
<?endif;?>
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
$app = JFactory::getApplication();
$model = $this->getModel();
$user = JFactory::getUser();
$userId = $user->get('id');
$groups = $user->get('groups');

$canCreate = (in_array(18, $groups) || in_array(19, $groups) || in_array(14, $groups));
$canEdit = (in_array(18, $groups) || in_array(19, $groups) || in_array(14, $groups));
$canDelete = (in_array(18, $groups) || in_array(19, $groups) || in_array(14, $groups));

$jcookie = $app->input->cookie;
$stocks = $model->getStocks();
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
        width: calc(40% - 5px);
        height: 30px;
        overflow: visible;
        margin-right: 10px;
        position: relative;
        cursor: pointer;
        background-color: rgb(54, 53, 127);
        color: #ffffff;
        border: none;
    }

    .Modal .Form .Action .Button.Reset {
        width: calc(30% - 10px);
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

<h1>Реализация</h1>
<form class="Realization" action="javascript:Realization();">
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
        <button type="submit" class="Action btn btn-primary Submit">
            <i class="fa fa-shopping-cart" aria-hidden="true"></i> Реализовать
        </button>
    </div>
    <div class="CustomerInfo">
        <table class="CustomerInfoTable">
            <tbody>
            <tr class="Pay">
                <td class="Name">Оплата:</td>
                <td class="Value">
                    <div class="Radio">
                        <div class="RUB" onclick="$(this).find('input').attr('checked', true);">
                            <input class="radio" type="radio" name="valute" value="RUB" checked><label></label> <span class="RV">0</span> <i class="fa fa-rub"></i>
                        </div>
                        <div class="USD" onclick="$(this).find('input').attr('checked', true);">
                            <input class="radio" type="radio" name="valute" value="USD"><label></label> <span class="RV">0</span> <i class="fa fa-usd"></i>
                        </div>
                    </div>
                    <div class="CBR">
                        Курс: <span class="RUB">0</span> <i class="fa fa-rub"></i> = <span class="USD">0</span> <i class="fa fa-usd"></i>
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
        <div class="Title">Введите данные покупателя:</div>
        <div class="Area Type">
            <input type="text" class="Input Type" name="Type" id="Type" placeholder="Введите тип:"
                   autocomplete="off"
                   NameDB="customer.type"
                   onclick="GetList(this, ['Type'], ['Type','Name','Email','Phone']);"
                   onkeyup="GetList(this, ['Type'], ['Type','Name','Email','Phone']);"
                   onblur="ClearSelect(this)"
                   required>
            <div class="Message Type">Тип покупателя</div>
            <div class="Selects Type"></div>
        </div>
        <div class="Area Name">
            <input type="text" class="Input Name" name="Name" id="Name" placeholder="Введите имя:"
                   autocomplete="off"
                   NameDB="customer.name"
                   onclick="GetList(this, ['Name','Email','Phone','JSON','Margin'], ['Type','Name','Email','Phone']);"
                   onkeyup="GetList(this, ['Name','Email','Phone','JSON','Margin'], ['Type','Name','Email','Phone']);"
                   onblur="ClearSelect(this)"
                   required>
            <div class="Message Name">Имя покупателя</div>
            <div class="Selects Name"></div>
        </div>
        <div class="Area Email">
            <input type="text" class="Input Email" name="Email" id="Email" placeholder="Введите эл. почту:"
                   autocomplete="off"
                   NameDB="customer.email"
                   onclick="GetList(this, ['Name','Email','Phone','JSON','Margin'], ['Type','Name','Email','Phone']);"
                   onkeyup="GetList(this, ['Name','Email','Phone','JSON','Margin'], ['Type','Name','Email','Phone']);"
                   onblur="ClearSelect(this)"
                   required>
            <div class="Message Email">Электронная почта</div>
            <div class="Selects Email"></div>
        </div>
        <div class="Area Phone">
            <input type="text" class="Input Phone" name="Phone" id="Phone" placeholder="Введите номер телефона:"
                   autocomplete="off"
                   NameDB="customer.phone"
                   onclick="GetList(this, ['Name','Email','Phone','JSON','Margin'], ['Type','Name','Email','Phone']);"
                   onkeyup="GetList(this, ['Name','Email','Phone','JSON','Margin'], ['Type','Name','Email','Phone']);"
                   onblur="ClearSelect(this)"
                   required>
            <div class="Message Phone">Номер телефона</div>
            <div class="Selects Phone"></div>
        </div>
        <input type="text" class="Input JSON" name="JSON" id="JSON" NameDB="customer.JSON" hidden>
        <input type="text" class="Input Margin" name="Margin" id="Margin" NameDB="customer.margin" hidden>
        <div class="Action">
            <button type="submit" class="Button Add Component">
                Добавить
            </button>
            <button type="reset" class="Button Reset Component">
                Очистить
            </button>
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
    var server_name = '<?php echo $server_name;?>';
    $(document).ready(Init);
    $(document).scroll(Scroll);
    $(window).resize(Resize);

    var Modal = {},
        Element = {},
        Scroll = {},
        Data = {margin: {canvas: 0, component: 0}},
        Calc = false,
        Customer = null;

    function Init() {
        $(".Actions .Customer").width($(".Actions .Customer .ButtonButInp").outerWidth(true));
        $('.chosen-container').remove();
        $('select').removeAttr("style");
        $(".ModalDoc .Document .Actions .CheckBox input[type=\"checkbox\"]").change(LoadPDF);
        ModalInit();
        ElementsInit();
        ScrollInit();
        Resize();

        $(".PRELOADER_GM").hide();

        $.ajax({
            url: "https://www.cbr-xml-daily.ru/daily_json.js",
            success: function (data) {
                Data.CB = JSON.parse(data).Valute.USD;
                Data.CBR = $(".CustomerInfoTable .Pay .CBR");
                Data.CBR.find(".RUB").html(Float(Data.CB.Value));
                Data.CBR.find(".USD").html(Float(Data.CB.Nominal));
            },
            dataType: "text",
            timeout: 10000,
            error: function () {
                noty({
                    theme: 'relax',
                    layout: 'center',
                    timeout: 1500,
                    type: "error",
                    text: "Не удалось загрузить курс доллара!"
                });
            }
        });
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
            TopSum = $(".CustomerInfoTable .Pay")
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

    function AddCustomer(e) {
        var form = $(".Modal ." + e),
            type = form.find(".Add").text(),
            element = $(".Realization .Actions .Customer"),
            elements = form.find(".Input"),
            cusromer = $(".CustomerInfo .CustomerInfoTable"),
            s = {};

        $.each(elements, function (i, v) {
            v = $(v);
            s[v.attr('name')] = v.val();
        });

        var Temp = JSON.parse(s.JSON);
        Temp.Name = s.Name;
        Temp.Email = s.Email;
        Temp.Phone = s.Phone;
        Temp.Type = s.Type;
        Temp.page = e;
        element.find(".InputButInp").val(JSON.stringify(Temp));

        if (Temp.client !== null)
        {
            var client = cusromer.find('.Client');
            client.show();
            client.find('.Value').text(Temp.client.name);

            if (s.Margin !== "Нет") Data.margin = JSON.parse(s.Margin);
            else Data.margin = {canvas: 0, component: 0};
        }
        else {
            cusromer.find('.Client').hide();
            Data.margin = {canvas: 0, component: 0};
        }

        Customer = Temp;
        var dealer = cusromer.find('.Dealer');
        dealer.show();
        dealer.find('.Value').text(Temp.dealer.name);

        //$(".Elements tbody").empty();
        CheckElements();
        Calculate();
        CancelElement();
    }

    function CheckElements() {
        var element = $(".Realization .Actions .Customer"),
            customer = JSON.parse(element.find(".InputButInp").val()),
            linePay = $(".CustomerInfo .Pay .Value"),
            table = $(".Table tbody"),
            actions = $(".Actions"),
            quad = Modal.canvas.find("#Quad");

        if (customer.type === "2")
        {
            linePay.find(".RUB label").show();
            linePay.find(".USD").show();
            linePay.find(".CBR").show();
            quad.attr("onclick", "GetList(this, ['Quad'], ['Quad','Color','Texture','Country','Name','Width']);");
            quad.attr("onkeyup", "GetList(this, ['Quad'], ['Quad','Color','Texture','Country','Name','Width']);");
            quad.attr("onblur","ClearSelect(this);");
        }
        else
        {
            linePay.find(".RUB input").attr("checked", true);
            linePay.find(".RUB label").hide();
            linePay.find(".USD").hide();
            linePay.find(".CBR").hide();
            quad.removeAttr("onclick");
            quad.removeAttr("onkeyup");
            quad.attr("onblur","$(this).val(Float($(this).val(),2));");
        }

        if (customer.type === "1")
        {
            actions.find(".Canvas").hide();
            actions.find(".Component").show();
        }
        else if (customer.type === "2")
        {
            actions.find(".Canvas").show();
            actions.find(".Component").hide();
        }
        else if (customer.type === "4" || customer.type === "3")
        {
            actions.find(".Canvas").show();
            actions.find(".Component").show();
        }
    }

    function Calculate() {
        var Elements = $("[name='goods[]']"),
            TopSum = $(".CustomerInfoTable .Pay"),
            Sum = 0.0,
            customer = JSON.parse($(".Realization .Actions .Customer .InputButInp").val());

        $.each(Elements, function (i, e) {
            e = $(e);
            var s = JSON.parse(e.val()),
                tr = e.closest("tr");

            if (s.page === "Canvas") {
                if (customer.type === "1") tr.remove();
                else {
                    s.PriceM = Float(s.Price);//Margin(Float(s.Price), Float(Data.margin.canvas));
                    //s.PriceM = (customer.type === "2") ? Margin(Float(0.75 * Data.CB.Value / Data.CB.Nominal), Float(Data.margin.canvas)) : s.PriceM;
                    s.Itog = Float(s.PriceM * s.Quad);
                }
            } else {
                if (customer.type === "2") tr.remove();
                else {
                    s.PriceM = Float(s.Price);//Margin(Float(s.Price), Float(Data.margin.component));
                    s.Itog = Float(s.PriceM * s.Count);
                }
            }

            e.val(JSON.stringify(s));

            tr.find(".Price").html(Float(s.PriceM));
            tr.find(".Itog").html(Float(s.Itog));

            Sum += Float(s.Itog);
        });

        TopSum.find(".RUB .RV").html(Float(Sum));
        TopSum.find(".USD .RV").html(Float((Sum * Data.CB.Nominal) / Data.CB.Value));

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
            console.log(data);
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
        filter.user = Customer;

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
                        timeout: 5000,
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

        if (number % round !== 0) itog = (Math.floor(number / round + round)) * round;

        e.val(itog);
    }

    function SelectItem(e) {
        e = $(e);
        let options = jQuery('.Select').children();
        let dealer_id = '<?php echo $user->dealer_id?>';
        var parent = e.closest("form"),
            elements = parent.find(".Input");
        if (typeof e.attr('error') !== 'undefined' && e.attr('error') !== false)
        {
            let json = JSON.parse(e.attr('JSON')),
                dealer = json.dealer;
            var error = JSON.parse(e.attr('error'));
            $.each(error, function (i, v) {
                noty({
                    theme: 'relax',
                    layout: 'center',
                    timeout: false,
                    type: "info",
                    text: v,
                    buttons:[
                        {
                            addClass: 'btn btn-primary', text: 'Добавить', onClick: function($noty) {
                                jQuery.ajax({
                                    type: 'POST',
                                    url: "/index.php?option=com_gm_ceiling&task=stock.addCounterparty",
                                    data: {
                                        user_id: dealer.id,
                                        name: dealer.name,
                                        phone: dealer.phone,
                                        email: dealer.email
                                    },
                                    success: function (data) {
                                        $noty.close();
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

                            }
                        },
                        {addClass: 'btn btn-primary', text: 'Выбрать ГМ', onClick: function($noty) {
                                $.each(options,function(i,v){
                                    dealer = JSON.parse($(v).attr('JSON'));
                                    if(dealer.dealer.id == dealer_id){
                                        e = $(v);
                                        return false;
                                    }
                                });
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
                                $noty.close();
                            }
                        }
                    ]
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
    function addCounterPartyForDealer(dealer_id){

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
                            timeout: 5000,
                            type: "error",
                            text: data.error
                        });
                    }
                    else if (data.status === "ok")
                    {
                        $.each(data.href, function (i, t) { $("#"+i).val(t); $("#"+i).attr("checked",true); });
                        $(".ModalDoc .Document .iFrame").attr("src", data.href.MergeFiles);
                        $(".ModalDoc").show();
                        Calc = false;
                    }

                    $(".PRELOADER_GM").hide();
                },
                dataType: "text",
                timeout: 10000,
                error: function () {
                    noty({
                        theme: 'relax',
                        layout: 'center',
                        timeout: 5000,
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
                    timeout: 5000,
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

    function close_frame() { $(".ModalDoc").hide(); }
</script>
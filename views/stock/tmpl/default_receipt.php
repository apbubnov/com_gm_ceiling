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

$user = JFactory::getUser();
$userId = $user->get('id');
$groups = $user->get('groups');

$canCreate = (in_array(18, $groups) || in_array(19, $groups) || in_array(14, $groups));
$canEdit = (in_array(18, $groups) || in_array(19, $groups) || in_array(14, $groups));
$canDelete = (in_array(18, $groups) || in_array(19, $groups) || in_array(14, $groups));
//$canCheckin =
//$canChange =

$app = JFactory::getApplication();
$jcookie = $app->input->cookie;

$stocks = $model->getStocks();

$goods = null;
$stock = 0;
$errors = null;

if (isset($_COOKIE['receipt']) && !empty($_COOKIE['receipt'])) {
    $data = json_decode($_COOKIE['receipt']);
    $goods = array();
    foreach ($data->goods as $v) $goods[] = json_decode($v);
    $stock = $data->stock;
    $errors = $data->errors;
}
$server_name = $_SERVER['SERVER_NAME'];
?>
<?= parent::getPreloader(); ?>


<style>
    body {
        background-color: #E6E6FA;
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

    .Receipt .Stock {
        width: 200px;
        height: 38px;
        position: relative;
        display: inline-block;
        border: 1px solid transparent;
        background-color: #414099;
        color: #ffffff;
        border-radius: 4px;
        vertical-align: middle;
        margin-top: -1px;
        cursor: pointer;
    }

    .Receipt .Stock .iStock {
        position: absolute;
        left: 0;
        top: 0;
        border: none;
        width: 100%;
        height: 100%;
    }

    .Receipt .Stock .ButtonStock {
        background-color: #414099;
        color: #ffffff;
        vertical-align: middle;
        cursor: pointer;
    }

    .Receipt {
        width: 100%;
        height: auto;
    }

    .Receipt .Elements {
        min-width: 100%;
        position: relative;
        border-collapse: collapse;
        margin-top: 20px;
    }

    .Receipt .Elements tr {
        border: 1px solid #414099;
        background-color: #E6E6FA;
        color: #000000;
    }

    .Receipt .Elements tr td {
        border: 0;
        border-right: 1px solid #414099;
        width: auto;
        height: 30px;
        line-height: 20px;
        padding: 0 5px;
    }

    .Receipt .Elements tr td button {
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

    .Receipt .Elements thead {
        position: relative;
        top: 0;
        left: 0;
    }

    .Receipt .Elements thead tr td {
        background-color: #414099;
        color: #ffffff;
        border-color: #ffffff;
        padding: 5px 10px;
        text-align: center;
        min-width: 102px;
    }

    .Receipt .Elements tbody tr {
        cursor: pointer;
    }

    .Receipt .Elements tbody tr:hover {
        background-color: #97d8ee;
    }

    .Receipt .Elements thead tr .ButtonTD {
        min-width: 0;
    }

    .Receipt .Elements tbody tr .ButtonTD {
        width: 30px;
    }

    .Receipt .Elements tr td:last-child {
        border-right: 0;
    }

    .Receipt .Elements .CloneElementsHead {
        position: fixed;
        top: 0;
        left: 0;
    }

    .Receipt .Elements .CloneElementsHeadTr {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        z-index: 1;
    }

    .Receipt .Show {
        display: inline-block !important;
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

<div class="Modal" style="display: none;">
    <form class="Form Component" action="javascript:AddElement('Component');"
          Page="index.php?option=com_gm_ceiling&task=componentform.getComponents">
        <div class="Title">Введите данные по компоненту:</div>
        <div class="Area Type">
            <input type="text" class="Input Type" name="Type" id="Type" placeholder="Введите тип:"
                   autocomplete="off"
                   NameDB="components.title"
                   onclick="GetList(this, ['Type','Unit'], ['Type',/*'Name','Unit','CountUnit',*/'Barcode','Article']);"
                   onkeyup="GetList(this, ['Type','Unit'], ['Type',/*'Name','Unit','CountUnit',*/'Barcode','Article']);"
                   onblur="ClearSelect(this)"
                   required>
            <div class="Message Type">Тип</div>
            <div class="Selects Type"></div>
        </div>
        <div class="Area Name">
            <input type="text" class="Input Name" name="Name" id="Name" placeholder="Введите название:"
                   autocomplete="off"
                   NameDB="options.title"
                   onclick="GetList(this, ['Type','Name','CountUnit','Unit'], [/*'Type',*/'Name',/*'Unit','CountUnit',*/'Barcode','Article']);"
                   onkeyup="GetList(this, ['Type','Name','CountUnit','Unit'], [/*'Type',*/'Name',/*'Unit','CountUnit',*/'Barcode','Article']);"
                   onblur="ClearSelect(this)"
                   required>
            <div class="Message Name">Название</div>
            <div class="Selects Name"></div>
        </div>
        <div class="Area Unit">
            <input type="text" class="Input Unit" name="Unit" id="Unit" placeholder="Введите название размерности:"
                   NameDB="components.unit"
                   onclick="GetList(this, ['Unit'], [/*'Type','Name',*/'Unit',/*'CountUnit',*/'Barcode','Article']);"
                   onkeyup="GetList(this, ['Unit'], [/*'Type','Name',*/'Unit',/*'CountUnit',*/'Barcode','Article']);"
                   onblur="ClearSelect(this)"
                   autocomplete="off" required>
            <div class="Message Unit">Название размерности</div>
            <div class="Selects Unit">
            </div>
        </div>
        <div class="Area CountUnit">
            <input type="text" class="Input CountUnit" name="CountUnit" id="CountUnit"
                   placeholder="Введите размерность:"
                   NameDB="options.count_sale"
                   onclick="GetList(this, ['CountUnit'], [/*'Type','Name','Unit',*/'CountUnit','Barcode','Article']);"
                   onkeyup="GetList(this, ['CountUnit'], [/*'Type','Name','Unit',*/'CountUnit','Barcode','Article']);"
                   onblur="ClearSelect(this)"
                   autocomplete="off">
            <div class="Message CountUnit">Размерность</div>
            <div class="Selects CountUnit"></div>
        </div>
        <div class="Area Barcode">
            <input type="text" class="Input Barcode" name="Barcode" id="Barcode"
                   placeholder="Введите штриховой код:"
                   autocomplete="off"
                   NameDB="goods.barcode"
                   onclick="GetList(this, ['Type','Unit','Name','Barcode','Article','CountUnit'], ['Type','Name','Unit','CountUnit','Barcode','Article']);"
                   onkeyup="GetList(this, ['Type','Unit','Name','Barcode','Article','CountUnit'], ['Type','Name','Unit','CountUnit','Barcode','Article']);"
                   onblur="ClearSelect(this)" required>
            <div class="Message Barcode">Штриховой код</div>
            <div class="Selects Barcode"></div>
        </div>
        <div class="Area Article">
            <input type="text" class="Input Article" name="Article" id="Article" placeholder="Введите артикул:"
                   NameDB="goods.article"
                   onclick="GetList(this, ['Type','Unit','Name','Barcode','Article','CountUnit'], ['Type','Name','Unit','CountUnit','Barcode','Article']);"
                   onkeyup="GetList(this, ['Type','Unit','Name','Barcode','Article','CountUnit'], ['Type','Name','Unit','CountUnit','Barcode','Article']);"
                   onblur="ClearSelect(this)"
                   autocomplete="off" required>
            <div class="Message Article">Артикул</div>
            <div class="Selects Article"></div>
        </div>
        <div class="Area Count">
            <input type="text" class="Input Count" name="Count" placeholder="Введите количество:" autocomplete="off"
                   pattern="\d+|\d+[,\.]\d+" required>
            <div class="Message Count">Количество</div>
            <div class="Selects Count"></div>
        </div>
        <div class="Area Price">
            <input type="text" class="Input Price" name="Price" placeholder="Введите цену за ед. размерности:"
                   autocomplete="off" pattern="\d+|\d+[,\.]\d+" required>
            <div class="Message Price">Цена за еденицу размерности</div>
            <div class="Selects Price"></div>
        </div>
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
                   onclick="GetList(this, ['Name','Country'], [/*'Color','Texture','Country',*/'Name',/*'Width',*/'Barcode','Article']);"
                   onkeyup="GetList(this, ['Name','Country'], [/*'Color','Texture','Country',*/'Name',/*'Width',*/'Barcode','Article']);"
                   onblur="ClearSelect(this)"
                   required>
            <div class="Message Name">Название</div>
            <div class="Selects Name"></div>
        </div>
        <div class="Area Country">
            <input type="text" class="Input Country" name="Country" id="Country" placeholder="Введите страну:"
                   autocomplete="off"
                   NameDB="canvases.country"
                   onclick="GetList(this, ['Country'], [/*'Color','Texture',*/'Country',/*'Name','Width',*/'Barcode','Article']);"
                   onkeyup="GetList(this, ['Country'], [/*'Color','Texture',*/'Country',/*'Name','Width',*/'Barcode','Article']);"
                   onblur="ClearSelect(this)"
                   required>
            <div class="Message Country">Страна</div>
            <div class="Selects Country"></div>
        </div>
        <div class="Area Width">
            <input type="text" class="Input Width" name="Width" id="Width" placeholder="Введите ширину:"
                   autocomplete="off"
                   NameDB="canvases.width"
                   onclick="GetList(this, ['Width'], [/*'Color','Texture','Country','Name',*/'Width','Barcode','Article']);"
                   onkeyup="GetList(this, ['Width'], [/*'Color','Texture','Country','Name',*/'Width','Barcode','Article']);"
                   onblur="ClearSelect(this)"
                   pattern="\d+|\d+[,\.]\d+" required>
            <div class="Message Width">Ширина</div>
            <div class="Selects Width"></div>
        </div>
        <div class="Area Texture">
            <input type="text" class="Input Texture" name="Texture" id="Texture" placeholder="Введите текстуру:"
                   autocomplete="off"
                   NameDB="textures.texture_title"
                   onclick="GetList(this, ['Texture'], [/*'Color',*/'Texture',/*'Country','Name','Width',*/'Barcode','Article']);"
                   onkeyup="GetList(this, ['Texture'], [/*'Color',*/'Texture',/*'Country','Name','Width',*/'Barcode','Article']);"
                   onblur="ClearSelect(this)" required>
            <div class="Message Width">Текстура</div>
            <div class="Selects Width"></div>
        </div>
        <div class="Area Color">
            <input type="text" class="Input Color" name="Color" id="Color" placeholder="Введите цвет:"
                   autocomplete="off"
                   NameDB="colors.title"
                   onclick="GetList(this, ['Color'], ['Color',/*'Texture','Country','Name','Width',*/'Barcode','Article']);"
                   onkeyup="GetList(this, ['Color'], ['Color',/*'Texture','Country','Name','Width',*/'Barcode','Article']);"
                   onblur="ClearSelect(this)" required>
            <div class="Message Width">Цвет</div>
            <div class="Selects Width"></div>
        </div>
        <div class="Area Barcode">
            <input type="text" class="Input Barcode" name="Barcode" id="Barcode"
                   placeholder="Введите штриховой код:"
                   NameDB="rollers.barcode"
                   onclick="GetList(this, ['Color','Texture','Country','Name','Width','Barcode','Article'], ['Color','Texture','Country','Name','Width','Barcode','Article']);"
                   onkeyup="GetList(this, ['Color','Texture','Country','Name','Width','Barcode','Article'], ['Color','Texture','Country','Name','Width','Barcode','Article']);"
                   onblur="ClearSelect(this)"
                   autocomplete="off" pattern="\d+" required>
            <div class="Message Barcode">Штриховой код</div>
            <div class="Selects Barcode"></div>
        </div>
        <div class="Area Article">
            <input type="text" class="Input Article" name="Article" id="Article" placeholder="Введите артикул:"
                   NameDB="rollers.article"
                   onclick="GetList(this, ['Color','Texture','Country','Name','Width','Barcode','Article'], ['Color','Texture','Country','Name','Width','Barcode','Article']);"
                   onkeyup="GetList(this, ['Color','Texture','Country','Name','Width','Barcode','Article'], ['Color','Texture','Country','Name','Width','Barcode','Article']);"
                   onblur="ClearSelect(this)"
                   autocomplete="off" required>
            <div class="Message Article">Артикул</div>
            <div class="Selects Article"></div>
        </div>
        <div class="Area Quad">
            <input type="text" class="Input Quad" name="Quad" id="Quad" placeholder="Введите квадратуру:"
                   autocomplete="off"
                   NameDB="rollers.quad"
                   onclick="GetList(this, ['Quad'], ['Quad']);"
                   onkeyup="GetList(this, ['Quad'], ['Quad']);"
                   onblur="ClearSelect(this)"
                   pattern="\d+|\d+[,\.]\d+" required>
            <div class="Message Quad">Квадратура</div>
            <div class="Selects Quad"></div>
        </div>
        <div class="Area Price">
            <input type="text" class="Input Price" name="Price" placeholder="Введите цену за ед. размерности:"
                   autocomplete="off" pattern="\d+|\d+[,\.]\d+" required>
            <div class="Message Price">Цена за еденицу размерности</div>
            <div class="Selects Price"></div>
        </div>
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
    <form class="Form Provider" action="javascript:AddProvider();"
          Page="index.php?option=com_gm_ceiling&task=stock.getCounterparty">
        <div class="Title">Введите данные поставщика:</div>
        <div class="Area Name">
            <input type="text" class="Input Name" name="Name" id="Name" placeholder="Введите название:"
                   NameDB="counterparty.name"
                   onclick="GetList(this, ['Name', 'FullName', 'TIN', 'CPR', 'OGRN', 'LegalAddress', 'MailingAddress', 'CEO', 'BankName', 'PayAccount', 'CorAccount', 'BIC', 'ContactsPhone', 'ContactsEmail', 'CloseContract'], ['Name', 'FullName', 'TIN', 'CPR', 'OGRN', 'LegalAddress', 'MailingAddress', 'CEO', 'BankName', 'PayAccount', 'CorAccount', 'BIC', 'Stock', 'CloseContract']);"
                   onkeyup="GetList(this, ['Name', 'FullName', 'TIN', 'CPR', 'OGRN', 'LegalAddress', 'MailingAddress', 'CEO', 'BankName', 'PayAccount', 'CorAccount', 'BIC', 'ContactsPhone', 'ContactsEmail', 'CloseContract'], ['Name', 'FullName', 'TIN', 'CPR', 'OGRN', 'LegalAddress', 'MailingAddress', 'CEO', 'BankName', 'PayAccount', 'CorAccount', 'BIC', 'Stock', 'CloseContract']);"
                   onblur="ClearSelect(this)"
                   autocomplete="off" required>
            <div class="Message Name">Название</div>
            <div class="Selects Name"></div>
        </div>
        <div class="Area FullName">
            <input type="text" class="Input FullName" name="FullName" id="FullName"
                   placeholder="Введите полное название:"
                   NameDB="counterparty.full_name"
                   onclick="GetList(this, ['Name', 'FullName', 'TIN', 'CPR', 'OGRN', 'LegalAddress', 'MailingAddress', 'CEO', 'BankName', 'PayAccount', 'CorAccount', 'BIC', 'ContactsPhone', 'ContactsEmail', 'CloseContract'], ['Name', 'FullName', 'TIN', 'CPR', 'OGRN', 'LegalAddress', 'MailingAddress', 'CEO', 'BankName', 'PayAccount', 'CorAccount', 'BIC', 'Stock', 'CloseContract']);"
                   onkeyup="GetList(this, ['Name', 'FullName', 'TIN', 'CPR', 'OGRN', 'LegalAddress', 'MailingAddress', 'CEO', 'BankName', 'PayAccount', 'CorAccount', 'BIC', 'ContactsPhone', 'ContactsEmail', 'CloseContract'], ['Name', 'FullName', 'TIN', 'CPR', 'OGRN', 'LegalAddress', 'MailingAddress', 'CEO', 'BankName', 'PayAccount', 'CorAccount', 'BIC', 'Stock', 'CloseContract']);"
                   onblur="ClearSelect(this)"
                   autocomplete="off" required>
            <div class="Message FullName">Полное название</div>
            <div class="Selects FullName"></div>
        </div>
        <div class="Area TIN">
            <input type="text" class="Input TIN" name="TIN" id="TIN" placeholder="Введите ИНН:"
                   NameDB="counterparty.tin"
                   onclick="GetList(this, ['TIN'], ['Name', 'FullName', 'TIN', 'CPR', 'OGRN', 'LegalAddress', 'MailingAddress', 'CEO', 'BankName', 'PayAccount', 'CorAccount', 'BIC', 'Stock', 'CloseContract']);"
                   onkeyup="GetList(this, ['TIN'], ['Name', 'FullName', 'TIN', 'CPR', 'OGRN', 'LegalAddress', 'MailingAddress', 'CEO', 'BankName', 'PayAccount', 'CorAccount', 'BIC', 'Stock', 'CloseContract']);"
                   onblur="ClearSelect(this)"
                   autocomplete="off">
            <div class="Message TIN">ИНН</div>
            <div class="Selects TIN"></div>
        </div>
        <div class="Area CPR">
            <input type="text" class="Input CPR" name="CPR" id="CPR" placeholder="Введите КПП:"
                   NameDB="counterparty.cpr"
                   onclick="GetList(this, ['CPR'], ['Name', 'FullName', 'TIN', 'CPR', 'OGRN', 'LegalAddress', 'MailingAddress', 'CEO', 'BankName', 'PayAccount', 'CorAccount', 'BIC', 'Stock', 'CloseContract']);"
                   onkeyup="GetList(this, ['CPR'], ['Name', 'FullName', 'TIN', 'CPR', 'OGRN', 'LegalAddress', 'MailingAddress', 'CEO', 'BankName', 'PayAccount', 'CorAccount', 'BIC', 'Stock', 'CloseContract']);"
                   onblur="ClearSelect(this)"
                   autocomplete="off" >
            <div class="Message CPR">КПП</div>
            <div class="Selects CPR"></div>
        </div>
        <div class="Area OGRN">
            <input type="text" class="Input OGRN" name="OGRN" id="OGRN" placeholder="Введите ОГРН:"
                   NameDB="counterparty.ogrn"
                   onclick="GetList(this, ['OGRN'], ['Name', 'FullName', 'TIN', 'CPR', 'OGRN', 'LegalAddress', 'MailingAddress', 'CEO', 'BankName', 'PayAccount', 'CorAccount', 'BIC', 'Stock', 'CloseContract']);"
                   onkeyup="GetList(this, ['OGRN'], ['Name', 'FullName', 'TIN', 'CPR', 'OGRN', 'LegalAddress', 'MailingAddress', 'CEO', 'BankName', 'PayAccount', 'CorAccount', 'BIC', 'Stock', 'CloseContract']);"
                   onblur="ClearSelect(this)"
                   autocomplete="off">
            <div class="Message OGRN">Основной государственный регистрационный номер</div>
            <div class="Selects OGRN"></div>
        </div>
        <div class="Area LegalAddress">
            <input type="text" class="Input LegalAddress" name="LegalAddress" id="LegalAddress"
                   placeholder="Введите юр. адрес:"
                   NameDB="counterparty.legal_address"
                   onclick="GetList(this, ['LegalAddress'], ['Name', 'FullName', 'TIN', 'CPR', 'OGRN', 'LegalAddress', 'MailingAddress', 'CEO', 'BankName', 'PayAccount', 'CorAccount', 'BIC', 'Stock', 'CloseContract']);"
                   onkeyup="GetList(this, ['LegalAddress'], ['Name', 'FullName', 'TIN', 'CPR', 'OGRN', 'LegalAddress', 'MailingAddress', 'CEO', 'BankName', 'PayAccount', 'CorAccount', 'BIC', 'Stock', 'CloseContract']);"
                   onblur="ClearSelect(this)"
                   autocomplete="off" required>
            <div class="Message LegalAddress">Юридический адрес</div>
            <div class="Selects LegalAddress"></div>
        </div>
        <div class="Area MailingAddress">
            <input type="text" class="Input MailingAddress" name="MailingAddress" id="MailingAddress"
                   placeholder="Введите поч. адрес:"
                   NameDB="counterparty.mailing_address"
                   onclick="GetList(this, ['MailingAddress'], ['Name', 'FullName', 'TIN', 'CPR', 'OGRN', 'LegalAddress', 'MailingAddress', 'CEO', 'BankName', 'PayAccount', 'CorAccount', 'BIC', 'Stock', 'CloseContract']);"
                   onkeyup="GetList(this, ['MailingAddress'], ['Name', 'FullName', 'TIN', 'CPR', 'OGRN', 'LegalAddress', 'MailingAddress', 'CEO', 'BankName', 'PayAccount', 'CorAccount', 'BIC', 'Stock', 'CloseContract']);"
                   onblur="ClearSelect(this)"
                   autocomplete="off" required>
            <div class="Message MailingAddress">Почтовый адрес</div>
            <div class="Selects MailingAddress"></div>
        </div>
        <div class="Area CEO">
            <input type="text" class="Input CEO" name="CEO" id="CEO" placeholder="Введите ген. директор:"
                   NameDB="counterparty.ceo"
                   onclick="GetList(this, ['CEO'], ['Name', 'FullName', 'TIN', 'CPR', 'OGRN', 'LegalAddress', 'MailingAddress', 'CEO', 'BankName', 'PayAccount', 'CorAccount', 'BIC', 'Stock', 'CloseContract']);"
                   onkeyup="GetList(this, ['CEO'], ['Name', 'FullName', 'TIN', 'CPR', 'OGRN', 'LegalAddress', 'MailingAddress', 'CEO', 'BankName', 'PayAccount', 'CorAccount', 'BIC', 'Stock', 'CloseContract']);"
                   onblur="ClearSelect(this)"
                   autocomplete="off" required>
            <div class="Message CEO">Генеральный директор</div>
            <div class="Selects CEO"></div>
        </div>
        <div class="Area BankName">
            <input type="text" class="Input BankName" name="BankName" id="BankName" placeholder="Введите наим. банка:"
                   NameDB="counterparty.bank_name"
                   onclick="GetList(this, ['BankName'], ['Name', 'FullName', 'TIN', 'CPR', 'OGRN', 'LegalAddress', 'MailingAddress', 'CEO', 'BankName', 'PayAccount', 'CorAccount', 'BIC', 'Stock', 'CloseContract']);"
                   onkeyup="GetList(this, ['BankName'], ['Name', 'FullName', 'TIN', 'CPR', 'OGRN', 'LegalAddress', 'MailingAddress', 'CEO', 'BankName', 'PayAccount', 'CorAccount', 'BIC', 'Stock', 'CloseContract']);"
                   onblur="ClearSelect(this)"
                   autocomplete="off" required>
            <div class="Message BankName">Наименование банка</div>
            <div class="Selects BankName"></div>
        </div>
        <div class="Area PayAccount">
            <input type="text" class="Input PayAccount" name="PayAccount" id="PayAccount"
                   placeholder="Введите расчетный счет:"
                   NameDB="counterparty.pay_account"
                   onclick="GetList(this, ['PayAccount'], ['Name', 'FullName', 'TIN', 'CPR', 'OGRN', 'LegalAddress', 'MailingAddress', 'CEO', 'BankName', 'PayAccount', 'CorAccount', 'BIC', 'Stock', 'CloseContract']);"
                   onkeyup="GetList(this, ['PayAccount'], ['Name', 'FullName', 'TIN', 'CPR', 'OGRN', 'LegalAddress', 'MailingAddress', 'CEO', 'BankName', 'PayAccount', 'CorAccount', 'BIC', 'Stock', 'CloseContract']);"
                   onblur="ClearSelect(this)"
                   autocomplete="off" required>
            <div class="Message PayAccount">Расчетный счет</div>
            <div class="Selects PayAccount"></div>
        </div>
        <div class="Area CorAccount">
            <input type="text" class="Input CorAccount" name="CorAccount" id="CorAccount"
                   placeholder="Введите кор. счет:"
                   NameDB="counterparty.cor_account"
                   onclick="GetList(this, ['CorAccount'], ['Name', 'FullName', 'TIN', 'CPR', 'OGRN', 'LegalAddress', 'MailingAddress', 'CEO', 'BankName', 'PayAccount', 'CorAccount', 'BIC', 'Stock', 'CloseContract']);"
                   onkeyup="GetList(this, ['CorAccount'], ['Name', 'FullName', 'TIN', 'CPR', 'OGRN', 'LegalAddress', 'MailingAddress', 'CEO', 'BankName', 'PayAccount', 'CorAccount', 'BIC', 'Stock', 'CloseContract']);"
                   onblur="ClearSelect(this)"
                   autocomplete="off">
            <div class="Message CorAccount">Корреспондентский счёт</div>
            <div class="Selects CorAccount"></div>
        </div>
        <div class="Area BIC">
            <input type="text" class="Input BIC" name="BIC" id="BIC" placeholder="Введите БИК:"
                   NameDB="counterparty.bic"
                   onclick="GetList(this, ['BIC'], ['Name', 'FullName', 'TIN', 'CPR', 'OGRN', 'LegalAddress', 'MailingAddress', 'CEO', 'BankName', 'PayAccount', 'CorAccount', 'BIC', 'Stock', 'CloseContract']);"
                   onkeyup="GetList(this, ['BIC'], ['Name', 'FullName', 'TIN', 'CPR', 'OGRN', 'LegalAddress', 'MailingAddress', 'CEO', 'BankName', 'PayAccount', 'CorAccount', 'BIC', 'Stock', 'CloseContract']);"
                   onblur="ClearSelect(this)"
                   autocomplete="off"  required>
            <div class="Message BIC">БИК</div>
            <div class="Selects BIC"></div>
        </div>
        <div class="Area ContactsPhone">
            <input type="text" class="Input ContactsPhone" name="ContactsPhone" id="ContactsPhone"
                   placeholder="Введите номер телефона:"
                   NameDB="counterparty.contacts_phone"
                   onclick="GetList(this, ['Name', 'FullName', 'TIN', 'CPR', 'OGRN', 'LegalAddress', 'MailingAddress', 'CEO', 'BankName', 'PayAccount', 'CorAccount', 'BIC', 'ContactsPhone', 'ContactsEmail', 'CloseContract'], ['Name', 'FullName', 'TIN', 'CPR', 'OGRN', 'LegalAddress', 'MailingAddress', 'CEO', 'BankName', 'PayAccount', 'CorAccount', 'BIC', 'Stock', 'CloseContract']);"
                   onkeyup="GetList(this, ['Name', 'FullName', 'TIN', 'CPR', 'OGRN', 'LegalAddress', 'MailingAddress', 'CEO', 'BankName', 'PayAccount', 'CorAccount', 'BIC', 'ContactsPhone', 'ContactsEmail', 'CloseContract'], ['Name', 'FullName', 'TIN', 'CPR', 'OGRN', 'LegalAddress', 'MailingAddress', 'CEO', 'BankName', 'PayAccount', 'CorAccount', 'BIC', 'Stock', 'CloseContract']);"
                   onblur="ClearSelect(this)"
                   autocomplete="off" required>
            <div class="Message ContactsPhone">Номера телефона</div>
            <div class="Selects ContactsPhone"></div>
        </div>
        <div class="Area ContactsEmail">
            <input type="email" class="Input ContactsEmail" name="ContactsEmail" id="ContactsEmail"
                   placeholder="Введите эл. почту:"
                   NameDB="counterparty.contacts_email"
                   onclick="GetList(this, ['Name', 'FullName', 'TIN', 'CPR', 'OGRN', 'LegalAddress', 'MailingAddress', 'CEO', 'BankName', 'PayAccount', 'CorAccount', 'BIC', 'ContactsPhone', 'ContactsEmail', 'CloseContract'], ['Name', 'FullName', 'TIN', 'CPR', 'OGRN', 'LegalAddress', 'MailingAddress', 'CEO', 'BankName', 'PayAccount', 'CorAccount', 'BIC', 'Stock', 'CloseContract']);"
                   onkeyup="GetList(this, ['Name', 'FullName', 'TIN', 'CPR', 'OGRN', 'LegalAddress', 'MailingAddress', 'CEO', 'BankName', 'PayAccount', 'CorAccount', 'BIC', 'ContactsPhone', 'ContactsEmail', 'CloseContract'], ['Name', 'FullName', 'TIN', 'CPR', 'OGRN', 'LegalAddress', 'MailingAddress', 'CEO', 'BankName', 'PayAccount', 'CorAccount', 'BIC', 'Stock', 'CloseContract']);"
                   onblur="ClearSelect(this)"
                   autocomplete="off" required>
            <div class="Message ContactsEmail">Электронная почта</div>
            <div class="Selects ContactsEmail"></div>
        </div>
        <div class="Area CloseContract">
            <input type="date" class="Input CloseContract" name="CloseContract" id="CloseContract"
                   placeholder="Введите дату:"
                   NameDB="counterparty.close_contract" autocomplete="off" required>
            <div class="Message CloseContract">Дата окончания договора</div>
            <div class="Selects CloseContract"></div>
        </div>
        <div class="Area Stock">
            <input type="text" class="Input Stock" name="Stock" id="Stock" placeholder="Введите склад:"
                   NameDB="stock.name"
                   onclick="GetList(this, ['Stock'], ['Stock']);"
                   onkeyup="GetList(this, ['Stock'], ['Stock']);"
                   onblur="ClearSelect(this)"
                   autocomplete="off" required>
            <div class="Message Stock">Склад</div>
            <div class="Selects Stock"></div>
        </div>
        <div class="Action">
            <button type="submit" class="Button Add Provider">
                Добавить
            </button>
            <button type="button" class="Button Cancel" onclick="CancelElement(this)">
                Закрыть
            </button>
        </div>
        <div class="Line"></div>
    </form>
</div>

<h1>Прием полотен и компонентов</h1>
<form class="Receipt"
      action="javascript:Receipt();"
      method="post" class="form-validate form-horizontal" enctype="multipart/form-data">

    <?= parent::getButtonBack(); ?>
    <button type="button" class="btn btn-primary Add Canvas" onclick="ShowModal(this)">
        <i class="fa fa-plus" aria-hidden="true"></i> Полотно
    </button>
    <button type="button" class="btn btn-primary Add Component" onclick="ShowModal(this)">
        <i class="fa fa-plus" aria-hidden="true"></i> Компонент
    </button>
    <div class="Stock Provider">
        <input type="text" name="stock" class="InputStock iStock" required>
        <button type="button" class="ButtonStock iStock" onclick="OpenModalProvider(this)">
            <i class="fa fa-user" aria-hidden="true"></i> Поставщик
        </button>
    </div>
    <button type="submit" class="btn btn-primary Submit">
        <i class="fa fa-paper-plane" aria-hidden="true"></i> Отправить
    </button>

    <table class="Elements">
        <thead class="ElementsHead">
        <tr class="ElementsHeadTr">
            <td>Название</td>
            <td>Штрих-код</td>
            <td>Артикул</td>
            <td>Значение</td>
            <td>Цена</td>
            <td colspan="2" class="ButtonTD">Функции</td>
        </tr>
        </thead>
        <tbody>
        <? if (empty($goods)): ?>
            <tr>
                <td hidden><input class="Good" name="goods[]" type="text" value="" hidden></td>
                <td class="Text" onclick="OpenModal(this)"></td>
                <td class="Barcode" onclick="OpenModal(this)"></td>
                <td class="Article" onclick="OpenModal(this)"></td>
                <td class="Value" onclick="OpenModal(this)"></td>
                <td class="Price" onclick="OpenModal(this)"></td>
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
        <? else: foreach ($goods as $v): ?>
            <tr>
                <td hidden><input class="Good" name="goods[]" type="text" value="<?= json_encode($v); ?>" hidden></td>
                <td class="Text" onclick="OpenModal(this)">
                    <?
                    if ($v->page == "Canvas") echo "Полотно: " . $v->Country . " " . $v->Name . " " . $v->Width;
                    else echo "Компонент: " . $v->Type . " " . $v->Name;
                    ?>
                </td>
                <td class="Barcode" onclick="OpenModal(this)"><?= $v->Barcode; ?></td>
                <td class="Article" onclick="OpenModal(this)"><?= $v->Article; ?></td>
                <td class="Value" onclick="OpenModal(this)">
                    <?
                    if ($v->page == "Canvas") echo $v->Quad . " m² * " . $v->Count . " шт.";
                    else echo $v->Count . " " . $v->Unit;
                    ?>
                </td>
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
        <? endforeach; endif; ?>
        </tbody>
    </table>
</form>
<div class="ModalDoc" style="display: none;">
    <div class="Document">
        <iframe class="iFrame" >

        </iframe>
        <div class="Actions">
            <div class="CheckBox">
                <div class="Name">Оприходование товаров</div>
                <input type="checkbox" id="InventoryOfGoods" name="page">
            </div>
            <div class="CheckBox">
                <div class="Name">Приходный кассовый ордер</div>
                <input type="checkbox" id="RetailCashOrder" name="page">
            </div>
            <div class="Right">
                <button type="button" onclick="print_frame();"><i class="fa fa-print" aria-hidden="true"></i></button>
                <button type="button" onclick="save_frame();"><i class="fa fa-floppy-o" aria-hidden="true"></i></button>
                <button type="button" onclick="close_frame();"><i class="fa fa-times" aria-hidden="true"></i></button>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    var $ = jQuery;
    var server_name = '<?php echo $server_name;?>';
    $(document).ready(Init);
    $(document).scroll(Scroll);
    $(window).resize(Resize);

    var Modal = {},
        Element = {},
        Scroll = {},
        Errors = <?=json_encode($errors);?>,
        Calc = false;

    function Init() {
        ShowErrors();
        $('.chosen-container').remove();
        $('select').removeAttr("style");
        ModalInit();
        ElementsInit();
        ScrollInit();
        Resize();
        $(".ModalDoc .Document .Actions .CheckBox input[type=\"checkbox\"]").change(LoadPDF);
    }

    function Resize() {
        ResizeHead();
    }

    function ShowErrors() {
        if (Errors !== null)
            $.each(Errors, function (i, v) {
                noty({
                    theme: 'relax',
                    layout: 'center',
                    timeout: 5000,
                    type: "error",
                    text: v
                });
            });
    }

    function ModalInit() {
        Modal.canvas = $(".Modal form.Canvas").clone();
        Modal.components = $(".Modal form.Component").clone();
        Modal.provider = $(".Modal form.Provider").clone();
        Modal.modal = $(".Modal");
        Modal.modal.empty();
    }

    function AddElement(e) {
        var form = $(".Modal ." + e),
            type = form.find(".Add").text(),
            element = (type === "Изменить") ? Element.tr : Element.tr.clone(),
            elements = form.find(".Input"),
            s = {};

        $.each(elements, function (i, v) {
            v = $(v);
            s[v.attr('name')] = v.val();
        });
        s.page = e;
        element.find(".Good").val(JSON.stringify(s));

        var line = (e === "Canvas") ? "Полотно: " : "Компонент: ";
        var value = "";
        if (form.hasClass("Canvas")) {
            line += s.Country + " " + s.Name + " " + s.Width;
            value += s.Quad + " m²";
        }
        else {
            line += s.Type + " " + s.Name;
            value += s.Count + " " + s.Unit;
        }
        element.find(".Text").html(line);
        element.find(".Barcode").html(s.Barcode);
        element.find(".Article").html(s.Article);
        element.find(".Value").html(value);
        element.find(".Price").html(s.Price);

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
        Calc = true;
    }

    function AddProvider() {
        var name = 'Provider',
            form = $(".Modal ." + name),
            elements = form.find(".Input"),
            element = $(".Receipt .Stock .InputStock"),
            button = $(".Receipt .Stock .ButtonStock"),
            s = {};

        $.each(elements, function (i, v) {
            v = $(v);
            s[v.attr('name')] = v.val();
        });
        s.page = name;

        element.val(JSON.stringify(s));
        button.text(s.Name);

        Modal.provider.find("input")
            .not(':button, :submit, :reset, :hidden')
            .val('')
            .removeAttr('checked')
            .removeAttr('selected');

        CancelElement();
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

        Modal.provider.find("input")
            .not(':button, :submit, :reset, :hidden')
            .val('')
            .removeAttr('checked')
            .removeAttr('selected');

        Modal.modal.empty();
        Modal.modal.hide();
    }

    function ElementsInit() {
        Element.tr = $(".Receipt .Elements tbody tr").clone();
        Element.tbody = $(".Receipt .Elements tbody");
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
            else if (e.hasClass("Provider")) modal = Modal.provider;

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
            else if (data.page === "Provider") modal = Modal.provider;

            if (modal === null) Modal.modal.hide();

            modal.find(".Add").text("Изменить");
            $.each(data, function (i, v) {
                modal.find("[name = '" + i + "']").val(v);
            });
            Modal.modal.append(modal);
            Element.tr = e;
        }

        var top = ($(window).height() - Modal.modal.find(".Form").height()) / 4,
            left = ($(window).width() - Modal.modal.find(".Form").width()) / 2;

        Modal.modal.find(".Form").css({"top": top + "px", "left": left + "px"});
    }

    function RemoveLine(e) {
        e = $(e);
        e.closest("tr").remove();

        ResizeHead();
    }

    function CloneLine(e) {
        e = $(e);
        var line = e.closest("tr").clone();
        Element.tbody.append(line);

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


        console.log(JSON.stringify(filter));
        if (input.is(":focus")) {
            jQuery.ajax({
                type: 'POST',
                url: filter.page,
                data: {filter: filter},
                success: function (data) {
                    data = JSON.parse(data);

                    $.each(data, function (i, v) {
                        console.log(i + " " + v);
                        var I = Item.clone();
                        $.each(v, function (id, s) {
                            if (s === null) s = "Нет";
                            I.attr(id, s);
                            if (id == ID) I.html(s);
                        });
                        Select.append(I);
                    });

                    if (ID == "Color") {
                        var I = Item.clone().attr({[ID]: "Нет"}).html("Нет");
                        Select.append(I);
                    }

                    var I = Item.clone().addClass("Add").html("Добавить");
                    Select.append(I);
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

    function SelectItem(e) {
        e = $(e);
        var parent = e.closest("form"),
            elements = parent.find(".Input");

        if (e.hasClass("Add")) e.closest(".Area").find(".Input").attr({"clear": "false", "add": "true"});
        else {
            $.each(elements, function (i, v) {
                v = $(v);
                var id = v.attr('id');
                if (typeof id !== 'undefined' && id !== false) {
                    var attr = e.attr(id);
                    if (typeof attr !== 'undefined' && attr !== false) {
                        console.log(id + " " + v);
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

    function OpenModalProvider(e) {
        e = $(e).closest(".Stock");
        var input = e.find(".InputStock"),
            data = (input.val() == "") ? null : input.val();
        ShowModal(e, data);
    }

    function ScrollInit() {
        Scroll.EHead = $(".Receipt .Elements .ElementsHead");
        Scroll.EHeadTr = Scroll.EHead.find(".ElementsHeadTr");
        Scroll.EHeadTrClone = Scroll.EHeadTr.clone();

        Scroll.EHeadTrClone.removeClass("ElementsHeadTr").addClass("CloneElementsHeadTr");
        Scroll.EHead.append(Scroll.EHeadTrClone);

        $(".Receipt").scroll(ResizeHead);
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

    function Receipt() {
        $(".PRELOADER_GM").show();
        var data = $("form.Receipt").serialize();

        if (Calc) {
            jQuery.ajax({
                type: 'POST',
                url: "/index.php?option=com_gm_ceiling&task=stock.Receipt",
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
                error: function (data) {
                    console.log(data);
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
        link.setAttribute('download',"Прием " + now.getDay() + "/" + now.getMonth() + "/" + now.getFullYear() + " " + now.getHours() + ":" + now.getMinutes() + ".pdf");
        onload=link.click();
    }

    function close_frame() { $("#BackPage").click(); }
</script>
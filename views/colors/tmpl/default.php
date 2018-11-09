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
JHtml::_('behavior.colorpicker');


$user       = JFactory::getUser();
$userId     = $user->get('id');
$userGroups = $user->group;
$canEdit = (in_array('16',$userGroups)) ? true : false;
?>
<style>
    fieldset {
        margin: 10px;
        border: 2px solid #414099;
        padding: 4px;
        border-radius: 4px;
    }
    legend{
        width: auto;
    }
</style>
<?=parent::getButtonBack();?>
<div class="container">
    <div class="row" style="margin: 10px 0 10px 0">
        <div class="col-md-4">
            <button class="btn btn-primary" id="addColor" ><i class="fa fa-plus-square" aria-hidden="true"></i> Добавить</button>
        </div>
        <div class="col-md-8">
            <div class="col-md-4">
                <label><b>Фильтр</b></label>
            </div>
            <div class="col-md-4">
                <select id="textureSelect" class="input-gm">
                    <option value = "0">Выберите текстуру</option>
                    <option value = "mat">Мат</option>
                    <option value = "sat">Сатин</option>
                    <option value = "glan">Глянец</option>
                    <option value = "desk">Ткань</option>
                </select>
            </div>
            <div class="col-md-4">
                <button class="btn btn-primary" id = "resetFilter"><i class="fa fa-times" aria-hidden="true"></i> Сбросить</button>
            </div>
        </div>
    </div>
    <div class="row">
        <table id="tableColors" class="table table_cashbox">
            <thead>
                <th class="center">Название</th>
                <th class="center">Фактура</th>
                <th class="center">Картинка</th>
                <th class="center"><i class="fa fa-pencil-square" aria-hidden="true"></i></th>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>
    <div class="modal_window_container" id="mw_container">
        <button type="button" class="close_btn" id="close""><i class="fa fa-times fa-times-tar" aria-hidden="true"></i></button>
        <div class="modal_window" id="mwCreateColor">
            <form>
                <div class="row">
                    <div class="col-md-12">
                        <input id="colorId" value="" type="hidden">
                        <label for="colorTitle">Введите назавние цвета:</label>
                        <input id="colorTitle" class="input-gm">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <fieldset>
                            <legend align="left"><label style="padding-left: auto">Фактура</label></legend>
                            <div align="left">
                                <input type="checkbox" name="texture" id="mat" value="mat" class="inp-cbx" style="display: none">
                                <label for="mat" class="cbx">
                                      <span>
                                        <svg width="12px" height="10px" viewBox="0 0 12 10">
                                          <polyline points="1.5 6 4.5 9 10.5 1"></polyline>
                                        </svg>
                                      </span>
                                    <span>Мат</span>
                                </label><br>
                                <input type="checkbox" name="texture" id="sat" value="sat" class="inp-cbx" style="display: none">
                                <label for="sat" class="cbx">
                                      <span>
                                        <svg width="12px" height="10px" viewBox="0 0 12 10">
                                          <polyline points="1.5 6 4.5 9 10.5 1"></polyline>
                                        </svg>
                                      </span>
                                    <span>Сатин</span>
                                </label><br>
                                <input type="checkbox" name="texture" id="glan" value="glan" class="inp-cbx" style="display: none">
                                <label for="glan" class="cbx">
                                      <span>
                                        <svg width="12px" height="10px" viewBox="0 0 12 10">
                                          <polyline points="1.5 6 4.5 9 10.5 1"></polyline>
                                        </svg>
                                      </span>
                                    <span>Глянец</span>
                                </label><br>
                                <input type="checkbox" name="texture" id="desk" value="desk" class="inp-cbx" style="display: none">
                                <label for="desk" class="cbx">
                                      <span>
                                        <svg width="12px" height="10px" viewBox="0 0 12 10">
                                          <polyline points="1.5 6 4.5 9 10.5 1"></polyline>
                                        </svg>
                                      </span>
                                    <span>Ткань</span>
                                </label><br>
                            </div>
                        </fieldset>
                    </div>
                    <div class="col-md-6">
                        <fieldset>
                            <legend align="left"><label style="padding-left: auto">Цвет</label></legend>
                            <div class="row">
                                <div class="col-md-6" id="colorPicker">
                                    <label for="colorHex">Выберите цвет</label>
                                    <input id = "hexColor" class="jscolor {hash:true}" >
                                </div>
                                <div class="col-md-6" id="images">

                                </div>
                            </div>
                            <div class="row center">
                                <div class="col-md-12">
                                    <button type="button" class="btn btn-primary" id="createImg">Создать изображение</button>
                                </div>
                            </div>
                        </fieldset>
                    </div>
                </div>
                <div class="row center">
                    <div class="col-md-12">
                        <button class="btn btn-primary" id="saveColorBtn">Сохранить</button>
                    </div>
                </div>
            </form>


        </div>
        <div class="modal_window" id="mwEditColor">

        </div>
    </div>
</div>
<script src="/components/com_gm_ceiling/views/colors/jscolor/jscolor.js"></script>
<script type="text/javascript">
    var textures ={mat:"Мат",sat:"Сатин",glan:"Глянец",desk:"Дескор"},
        colors = [],
        newColorFiles = [];
    
    function getData(){
        jQuery.ajax({
            url: "index.php?option=com_gm_ceiling&task=colors.getColors",
            data: {
            },
            dataType: "json",
            async: false,
            success: function (data) {
                colors = data;
                fillTable(colors);
            },
            error: function (data) {
                console.log(data);
                var n = noty({
                    timeout: 2000,
                    theme: 'relax',
                    layout: 'center',
                    maxVisible: 5,
                    type: "error",
                    text: "Ошибка получения данных!"
                });
            }
        });
    }

    function defineTexture(filename) {
        for(var i = 0; i < Object.keys(textures).length;i++){
            if(filename.indexOf(Object.keys(textures)[i]) >=0){
                return textures[Object.keys(textures)[i]];
            }
        }
    }

    function fillTable(data){
        var EDIT_BUTTON = '<button class="btn btn-primary" name ="editBtn"><i class="fa fa-pencil-square" aria-hidden="true"></i></button>';
        jQuery.each(data,function (index,element) {
            jQuery("#tableColors > tbody").append("<tr/>");
            var tr = jQuery("#tableColors > tbody > tr:last").append('<td>'+element.title+'</td><td>'+defineTexture(element.file)+'</td><td><img style="max-height: 50px" src="'+element.file+'"></td><td>'+EDIT_BUTTON+"")
        });
    }

    function getColorsByTexture(texture){
        var result = [];
        jQuery.each(colors, function(index,element){
            if(element.file.indexOf(texture)>=0){
                result.push(element);
            }
            else{
                if(texture == 0) {
                    result.push(element);
                }
            }
        });
        return result;
    }
    jQuery(document).mouseup(function (e) {
        var div = jQuery("#mwEditColor"),
            div1 = jQuery("#mwCreateColor");
        if (!div.is(e.target)
            && div.has(e.target).length === 0 &&
            !div1.is(e.target)
            && div1.has(e.target).length === 0) {
            jQuery("#close").hide();
            jQuery("#mw_container").hide();
            div.hide();
            div1.hide();
        }
    });

    jQuery(document).ready(function () {
        getData();

        jQuery("#textureSelect").change(function(){
            jQuery("#tableColors > tbody").empty();
            console.log(this.value);
            fillTable(getColorsByTexture(this.value));

        });

        jQuery("#resetFilter").click(function () {
            fillTable(colors);
            jQuery("#textureSelect").val(0);
        });

        jQuery("[name = editBtn]").click(function () {
            jQuery("#mw_container").show();
            jQuery("#mwEditColor").show('slow');
            jQuery("#close").show();
        });

        jQuery("#addColor").click(function () {
            jQuery("#mw_container").show();
            jQuery("#mwCreateColor").show('slow');
            jQuery("#close").show();
        });

        jQuery("#createImg").click(function () {
            var selectedCheckboxes = jQuery('input[name="texture"]:checked'),
                selectedTextures = [],
                hexColor = jQuery("#hexColor").val(),
                nameColor = jQuery("#colorTitle").val();
                //idColor = jQuery("#colorId").val();
            jQuery.each(selectedCheckboxes,function (index,elem) {
                selectedTextures.push(elem.value);
            });
            console.log(hexColor);
            if(selectedTextures.length){
                jQuery.ajax({
                    url: "index.php?option=com_gm_ceiling&task=colors.createColorImage",
                    data: {
                        hexCode: hexColor,
                        name: nameColor,
                        textures: selectedTextures
                    },
                    dataType: "json",
                    async: false,
                    success: function (data) {
                        newColorFiles = data;
                        jQuery("#images").empty();
                        for (var i = 0;i<data.length;i++){
                            jQuery("#images").append('<img style="max-height: 50px" src="'+data[i]+'"><br>');
                        }

                    },
                    error: function (data) {
                        console.log(data);
                        var n = noty({
                            timeout: 2000,
                            theme: 'relax',
                            layout: 'center',
                            maxVisible: 5,
                            type: "error",
                            text: "Ошибка получения данных!"
                        });
                    }
                });
            }
            else {
                noty({
                    timeout: 2000,
                    theme: 'relax',
                    layout: 'center',
                    maxVisible: 5,
                    type: "error",
                    text: "Не выбрана ни одна текстура!"
                });
            }
        });

        jQuery("#saveColorBtn").click(function () {
            var hexColor = jQuery("#hexColor").val(),
                nameColor = jQuery("#colorTitle").val(),
                idColor = jQuery("#colorId").val();
            jQuery.ajax({
                url: "index.php?option=com_gm_ceiling&task=colors.save",
                data: {
                    hexCode: hexColor,
                    name: nameColor,
                    files: newColorFiles
                },
                dataType: "json",
                async: false,
                success: function (data) {
                    noty({
                        timeout: 2000,
                        theme: 'relax',
                        layout: 'center',
                        maxVisible: 5,
                        type: "success",
                        text: "Успешно сохранено!"
                    });
                    setTimeout(function () {
                        location.reload(true);
                    },1000);
                },
                error: function (data) {
                    console.log(data);
                    noty({
                        timeout: 2000,
                        theme: 'relax',
                        layout: 'center',
                        maxVisible: 5,
                        type: "error",
                        text: "Ошибка получения данных!"
                    });
                }
            });
        });
    });
</script>

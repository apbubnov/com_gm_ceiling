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
        <div class="col-md-3">
            <button class="btn btn-primary" id="addColor" ><i class="fa fa-plus-square" aria-hidden="true"></i> Добавить цвет</button>
        </div>
        <div class="col-md-3">
            <button class="btn btn-primary" id="addTexture" ><i class="fa fa-plus-square" aria-hidden="true"></i> Добавить фактуру</button>
        </div>
        <div class="col-md-6">
            <div class="col-md-3">
                <label><b>Фильтр</b></label>
            </div>
            <div class="col-md-5">
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
                <th class="center">Полотна</th>
                <th class="center">Добавить полотно</th>
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
                                <div class="col-md-6 colorPicker" id="colorPicker">
                                    <label for="hexColor">Выберите цвет</label>
                                    <input id = "hexColor">
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
            <div class="row center">
                <div class="col-md-12">
                    <input id="colorId" value="" type="hidden">
                    <input id="colorTexture" value="" type="hidden">
                    <label for="colorTitleEdit">Назавние цвета:</label>
                    <input id="colorTitleEdit" class="input-gm">
                </div>
            </div>
            <div class="row center">
                <div class="row center" style="margin-bottom: 10px">
                    <div class="col-md-12">
                        <label for="hexColorEdit">Цвет</label>
                        <input id = "hexColorEdit">
                    </div>
                    <div class="col-md-12" id="imagesEdit">

                    </div>
                </div>
                <div class="row center" >
                    <div class="col-md-12">
                        <button class="btn btn-primary" id="saveColorChangesBtn">Сохранить</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal_window" id="mwEditCanvases">
            <input id = "selectedColor" type="hidden">
            <div class="row center">
                <div class="col-md-3 center">
                    <select id="canvasesTextureSelect" class="inputactive">
                        <option>Выберите текстуру</option>
                    </select>
                </div>
                <div class="col-md-3 center">
                    <select id="canvasesManufacturerSelect" class="inputactive">
                        <option>Выберите производителя</option>
                    </select>
                </div>
                <div class="col-md-3 center">
                    <input type="text" class="inputactive" id="width" placeholder="Ширина">
                </div>
                <div class="col-md-3 center">
                    <input type="text" class="inputactive" id="price" placeholder="Цена">
                </div>

            </div>
            <div class="row center">
                <div class="col-md-12">
                    <button class="btn btn-primary" id="saveNewCanvas">Сохранить</button>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <table id="canvases" class="table table_cashbox">
                        <thead>
                        <th class="center">Полотно</th>
                        <th class="center"><i class="fa fa-trash" aria-hidden="true"></i></th>
                        </thead>
                        <tbody>

                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="modal_window" id="mw_addTexture">
            <div class="row" style="margin-bottom: 15px">
                <label for="textureName">Название фактуры</label><br>
                <input id = "textureName" class="input-gm">

                <input type="checkbox" id="colored"  class="inp-cbx" style="display: none">
                <label for="colored" class="cbx">
                                      <span>
                                        <svg width="12px" height="10px" viewBox="0 0 12 10">
                                          <polyline points="1.5 6 4.5 9 10.5 1"></polyline>
                                        </svg>
                                      </span>
                    <span>Цветная</span>
                </label>
            </div>
            <div class="row center">
                <div class="col-md-12">
                    <button id="saveTexture" type="button" class="btn btn-primary">Сохранить</button>
                </div>
            </div>
        </div>
    </div>
</div>
<link rel="stylesheet" media="screen" type="text/css" href="/components/com_gm_ceiling/views/colors/colorPicker/css/colorpicker.css" />
<script type="text/javascript" src="/components/com_gm_ceiling/views/colors/colorPicker/js/colorpicker.js"></script>
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
                return {key:Object.keys(textures)[i],value:textures[Object.keys(textures)[i]]};
            }
        }
    }

    function fillTable(data){
        var EDIT_BUTTON = '<button class="btn btn-primary" name ="editBtn"><i class="fa fa-pencil-square" aria-hidden="true"></i></button>',
            ADD_CANVAS_BUTTON = '<button class="btn btn-primary" name ="addCanvasBtn"><i class="fa fa-plus-square" aria-hidden="true"></i></button>',
            canvases = [],
            canvasesTitles="";

        jQuery.each(data,function (index,element) {
            canvases = (element.canvases) ? JSON.parse(element.canvases) :[] ;
            canvasesTitles = "";
            element.canvases = canvases;
            for(var j = 0;j<canvases.length;j++){
                canvasesTitles += canvases[j].name+"; ";
            }
            jQuery("#tableColors > tbody").append("<tr/>");
            jQuery("#tableColors > tbody > tr:last").attr("data-color_id",element.id);
            var tr = jQuery("#tableColors > tbody > tr:last").append('<td>'+element.title+'</td><td>'+
                defineTexture(element.file).value+'</td><td><img style="max-height: 50px" src="'+element.file+'"></td><td>'+
                EDIT_BUTTON+'<td>'+ canvasesTitles +'</td><td>'+ ADD_CANVAS_BUTTON+'</td>')
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

    function fillTexturesSelect(){
        jQuery.ajax({
            url: "index.php?option=com_gm_ceiling&task=textures.getFilteredData",
            data: {
                filter: "a.texture_colored = 1"
            },
            dataType: "json",
            async: false,
            success: function (data) {
                jQuery.each(data,function (index,element) {
                    jQuery("#canvasesTextureSelect").append('<option value='+element.id+'>'+element.texture_title+'</option>');
                });
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

    function fillManufacturersSelect(){
        jQuery.ajax({
            url: "index.php?option=com_gm_ceiling&task=Manufacturers.getData",
            data: {
            },
            dataType: "json",
            async: false,
            success: function (data) {
                jQuery.each(data,function (index,element) {
                    jQuery("#canvasesManufacturerSelect").append('<option value='+element.id+'>'+element.name+'</option>');
                });
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
    jQuery(document).mouseup(function (e) {
        var div = jQuery("#mwEditColor"),
            div1 = jQuery("#mwCreateColor"),
            div2 = jQuery(".colorpicker"),
            div3 = jQuery("#mwEditCanvases"),
            div4 = jQuery("#mw_addTexture");
        if (!div.is(e.target)
            && div.has(e.target).length === 0 &&
            !div1.is(e.target)
            && div1.has(e.target).length === 0 &&
            !div2.is(e.target)
            && div2.has(e.target).length === 0 &&
            !div3.is(e.target)
            && div3.has(e.target).length === 0 &&
            !div4.is(e.target)
            && div4.has(e.target).length === 0) {
            jQuery("#close").hide();
            jQuery("#mw_container").hide();
            div.hide();
            div1.hide();
            div2.hide();
            div3.hide();
            div4.hide();
            jQuery("#imagesEdit").empty();
        }
    });

    jQuery(document).ready(function () {
        getData();

        jQuery("#textureSelect").change(function(){
            jQuery("#tableColors > tbody").empty();
            console.log(this.value);
            fillTable(getColorsByTexture(this.value));

        });

        jQuery('#hexColor').ColorPicker({

            color: "#ffffff",
            onShow: function (colpkr) {
                jQuery(colpkr).fadeIn(500);
                return false;
            },
            onHide: function (colpkr) {
                jQuery(colpkr).fadeOut(500);
                return false;
            },
            onChange: function (hsb, hex, rgb) {
                jQuery('#hexColor').val('#' + hex);
            }
        });
        jQuery("#resetFilter").click(function () {
            fillTable(colors);
            jQuery("#textureSelect").val(0);
        });

        jQuery("[name = editBtn]").click(function () {
            var color_id = jQuery(this.closest('tr')).data("color_id"),
                color = colors[color_id];

            jQuery("#mw_container").show();
            jQuery("#mwEditColor").show('slow');
            jQuery("#close").show();
            jQuery("#hexColorEdit").val(color.hex);
            jQuery("#colorTitleEdit").val(color.title);
            jQuery("#colorTexture").val(defineTexture(color.file).key);
            jQuery("#colorId").val(color_id);
            jQuery("#imagesEdit").append('<img style="max-height: 50px" src="'+color.file+'"><br>');
            jQuery('#hexColorEdit').ColorPicker({

                color: '#'+color.hex,
                onShow: function (colpkr) {
                    jQuery(colpkr).fadeIn(500);
                    return false;
                },
                onHide: function (colpkr) {
                    jQuery(colpkr).fadeOut(500);
                    return false;
                },
                onChange: function (hsb, hex, rgb) {
                    jQuery('#hexColorEdit').val('#' + hex);
                }
            });
        });

        jQuery("[name='addCanvasBtn']").click(function () {
            jQuery("#mw_container").show();
            jQuery("#mwEditCanvases").show('slow');
            jQuery("#close").show();

            var DELETE_BUTTON = '<button class="btn btn-danger delete_canvas"><i class="fa fa-trash" aria-hidden="true"></i></button>',
                color_id = jQuery(this.closest('tr')).data('color_id'),
                canvases = colors[color_id].canvases;
            jQuery("#selectedColor").val(color_id);
            jQuery("#canvases > tbody").empty();
            jQuery.each(canvases,function (index,element) {
                jQuery("#canvases > tbody").append('<tr/>');
                jQuery("#canvases >tbody > tr:last").attr("data-canvas_id",element.id);
                jQuery("#canvases >tbody > tr:last").append('<td>'+element.name+'</td><td>'+DELETE_BUTTON+'</td>');
            });
            jQuery("#width").mask("9.9");
            fillTexturesSelect();
            fillManufacturersSelect();
            jQuery(".delete_canvas").click(function () {
                var canvas_id = jQuery(this.closest('tr')).data('canvas_id');
                jQuery.ajax({
                    url: "index.php?option=com_gm_ceiling&task=canvasForm.remove",
                    data: {
                        id: canvas_id
                    },
                    dataType: "json",
                    async: false,
                    success: function (data) {
                        noty({
                            timeout: 2000,
                            theme: 'relax',
                            layout: 'center',
                            maxVisible: 5,
                            type: "error",
                            text: "Удалено!"
                        });
                        setTimeout(function () {
                            location.reload();
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

        jQuery("#addColor").click(function () {
            jQuery("#mw_container").show();
            jQuery("#mwCreateColor").show('slow');
            jQuery("#close").show();
        });

        jQuery("#addTexture").click(function(){
            jQuery("#mw_container").show();
            jQuery("#mw_addTexture").show('slow');
            jQuery("#close").show();
        });

        jQuery("#saveTexture").click(function () {
            var is_colored = (jQuery("#colored").attr('checked'))? 1 : 0;
            jQuery.ajax({
                url: "index.php?option=com_gm_ceiling&task=textures.save",
                data: {
                    title: jQuery("#textureName").val(),
                    is_colored:is_colored
                },
                dataType: "json",
                async: false,
                success: function (data) {
                    noty({
                        timeout: 2000,
                        theme: 'relax',
                        layout: 'center',
                        maxVisible: 5,
                        type: "error",
                        text: "Фактура добавлена!"
                    });
                    setTimeout(function () {
                        location.reload();
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
        jQuery("#createImg").click(function () {
            var selectedCheckboxes = jQuery('input[name="texture"]:checked'),
                selectedTextures = [],
                hexColor = jQuery("#hexColor").val(),
                nameColor = jQuery("#colorTitle").val();
            jQuery.each(selectedCheckboxes,function (index,elem) {
                selectedTextures.push(elem.value);
            });

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

        jQuery("#saveNewCanvas").click(function () {
            var texture = jQuery("#canvasesTextureSelect").val(),
                manufacturer = jQuery("#canvasesManufacturerSelect").val(),
                width = jQuery("#width").val(),
                price = jQuery("#price").val(),
                color_id = jQuery("#selectedColor").val();
            jQuery.ajax({
                url: "index.php?option=com_gm_ceiling&task=canvases.save",
                data: {
                    texture: texture,
                    manufacturer: manufacturer,
                    width: width,
                    price: price,
                    color_id: color_id
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
                   /* setTimeout(function () {
                        location.reload(true);
                    },1000);*/
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

        jQuery("#saveColorChangesBtn").click(function () {
            jQuery.ajax({
                url: "index.php?option=com_gm_ceiling&task=colors.update",
                data: {
                    hexCode: jQuery("#hexColorEdit").val(),
                    name: jQuery("#colorTitleEdit").val(),
                    textures:[jQuery("#colorTexture").val()],
                    idColor:jQuery("#colorId").val()

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

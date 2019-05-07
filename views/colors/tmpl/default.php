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

$texturesModel = Gm_ceilingHelpersGm_ceiling::getModel('textures');
$textures = $texturesModel->getFilteredData('a.texture_colored = 1');
$all_textures = $texturesModel->getFilteredData();
$short_names = [];
foreach($textures as $key=>$texture){
    $short_name = strtolower(substr(Gm_ceilingHelpersGm_ceiling::rus2translit($texture->texture_title),0,3));
    if(!in_array($short_name,$short_names)){
        array_push($short_names,$short_name);
        $textures[$key]->short_name = $short_name;
    }
    else{
        $i=3;
        while(in_array($short_name,$short_names)){
            $short_name = strtolower(substr(Gm_ceilingHelpersGm_ceiling::rus2translit($texture->texture_title),0,$i++));
        }
        array_push($short_names,$short_name);
        $textures[$key]->short_name = $short_name;
    }


}
$jsonTextures = json_encode($textures);
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
    .field{
        clear:both;
        text-align:right;
        line-height:25px;
    }
    .field-label{
        float:left;
        padding-right:10px;
    }
    .main {
        float:left
    }

    .action-btn{
        width:200px;
    }
</style>
<?=parent::getButtonBack();?>
<div class="container">
    <div class="row" style="margin: 10px -15px 10px -15px">
        <div class="col-md-3" style="padding-left: 0;margin-left: 0;">
            <button class="btn btn-primary action-btn" id="addColor" ><i class="fa fa-plus-square" aria-hidden="true"></i> Добавить цвет</button>
        </div>
        <div class="col-md-3" style="padding-left: 0;margin-left: 0;">
            <button class="btn btn-primary action-btn" id="addTexture" ><i class="fa fa-plus-square" aria-hidden="true"></i> Добавить фактуру</button>
        </div>
        <div class="col-md-3" style="padding-left: 0;margin-left: 0;">
            <button class="btn btn-primary action-btn" id="editTextureBtn" ><i class="fa fa-trash-o" aria-hidden="true"></i> Фактуры </button>
        </div>
        <div class="col-md-3">

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
                                <?php foreach ($textures as $texture){?>
                                    <input type="checkbox" name="texture" id="<?php echo $texture->short_name;?>" value="<?php echo $texture->short_name;?>" class="inp-cbx" style="display: none">
                                    <label for="<?php echo $texture->short_name;?>" class="cbx">
                                      <span>
                                        <svg width="12px" height="10px" viewBox="0 0 12 10">
                                          <polyline points="1.5 6 4.5 9 10.5 1"></polyline>
                                        </svg>
                                      </span>
                                        <span><?php echo $texture->texture_title;?></span>
                                    </label><br>
                                <?php } ?>
                            </div>
                        </fieldset>
                    </div>
                    <div class="col-md-6">
                        <fieldset>
                            <legend align="left"><label style="padding-left: auto">Цвет</label></legend>
                            <div class="row">
                                <div class="col-md-6 colorPicker" id="colorPicker">
                                    <label for = "hexColor">Выберите цвет</label>
                                    <input id = "hexColor" class="input-gm">
                                    <div id = "color_selector">

                                    </div>
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
            <input id="colorId" value="" type="hidden">
            <input id="colorTexture" value="" type="hidden">
            <input id="idTexture" value="" type="hidden">
            <div class="row center" style="margin-bottom: 10px">
                <div class="col-md-4"></div>
                <div class="col-md-4 main">
                    <div class="field">
                        <label class="field-label" for="textureTitleEdit">Название текстуры:</label>
                        <input id="textureTitleEdit" class="input-gm">
                    </div>
                    <br>
                    <div class="field">
                        <label class="field-label" for="colorTitleEdit">Название цвета:</label>
                        <input id="colorTitleEdit" class="input-gm">
                    </div>
                </div>
                <div class="col-md-4"></div>

            </div>
            <div class="row center" style="margin-bottom: 10px">
                <div class="col-md-4"></div>
                <div class="col-md-4 main">
                    <div class="field">
                        <label class="field-label" for="hexColorEdit">Цвет</label>
                        <input id = "hexColorEdit" class="input-gm">
                    </div>
                    <div id = "colorpickerHolder"></div>
                </div>
                <div class="col-md-4"></div>
                <div class="col-md-12" id="imagesEdit">

                </div>
            </div>
            <div class="row center" >
                <div class="col-md-12">
                    <button class="btn btn-primary" id="saveColorChangesBtn">Сохранить</button>
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
                            <th class="center">Кол-во</th>
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
        <div class="modal_window" id="mw_editTexture">
            <div class="col-md-3"></div>
            <div class="col-md-6">
                <table class="table table_cashbox">
                    <thead>
                        <th class="center">Фактура</th>
                        <th class="center">Цветная</th>
                        <th class="center">Удалить</th>
                    </thead>
                    <tbody>
                        <?php foreach ($all_textures as $texture){?>

                            <tr data-id = <?php echo $texture->id;?>>
                                <td>
                                    <span><?php echo $texture->texture_title;?></span>

                                </td>
                                <td>
                                    <input type="checkbox" name="texture_edit" id="<?php echo "id".$texture->id;?>" <?php if($texture->texture_colored) echo "checked";?> class="inp-cbx" style="display: none">
                                    <label for="<?php echo "id".$texture->id;?>" class="cbx">
                                              <span>
                                                <svg width="12px" height="10px" viewBox="0 0 12 10">
                                                  <polyline points="1.5 6 4.5 9 10.5 1"></polyline>
                                                </svg>
                                              </span>
                                        <span></span>
                                </td>
                                <td>
                                    <button class="btn btn-danger del_texture"><i class="fa fa-trash" aria-hidden="true"></i></button>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
            <div class="col-md-3"></div>

        </div>
    </div>
</div>
<link rel="stylesheet" media="screen" type="text/css" href="/components/com_gm_ceiling/views/colors/colorPicker/css/colorpicker.css" />
<script type="text/javascript" src="/components/com_gm_ceiling/views/colors/colorPicker/js/colorpicker.js"></script>
<script type="text/javascript">
    var textures = JSON.parse('<?php echo $jsonTextures;?>'),
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
        for(var i = 0; i <textures.length;i++){
            if(filename.indexOf(textures[i].short_name) >=0){
                return {key:textures[i].short_name,value:textures[i].texture_title,id:textures[i].id};
            }
        }
    }

    function fillTable(data){
        var EDIT_BUTTON = '<button class="btn btn-primary" name ="editBtn"><i class="fa fa-pencil-square" aria-hidden="true"></i></button>',
            ADD_CANVAS_BUTTON = '<button class="btn btn-primary" name ="addCanvasBtn"><i class="fa fa-plus-square" aria-hidden="true"></i></button>',
            canvases = [],
            canvasesTitles="";

        jQuery.each(data,function (index,element) {
            console.log(element);
            canvases = (element.canvases) ? JSON.parse(element.canvases) :[] ;
            canvasesTitles = "";
            element.canvases = canvases;
            for(var j = 0;j<canvases.length;j++){
                canvasesTitles += canvases[j].name+"; ";
            }
            jQuery("#tableColors > tbody").append("<tr/>");
            jQuery("#tableColors > tbody > tr:last").attr("data-color_id",element.id);
            var tr = jQuery("#tableColors > tbody > tr:last").append('<td>'+element.title+'</td><td>'+
                defineTexture(element.file).value+'</td><td><img style="max-height: 50px" src="'+element.file+"<?= '?t='.time(); ?>"+'"></td><td>'+
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
            div4 = jQuery("#mw_addTexture"),
            div5 = jQuery("#mw_editTexture");
        if (!div.is(e.target)
            && div.has(e.target).length === 0 &&
            !div1.is(e.target)
            && div1.has(e.target).length === 0 &&
            !div2.is(e.target)
            && div2.has(e.target).length === 0 &&
            !div3.is(e.target)
            && div3.has(e.target).length === 0 &&
            !div4.is(e.target)
            && div4.has(e.target).length === 0 &&
            !div5.is(e.target)
            && div5.has(e.target).length === 0) {
            jQuery("#close").hide();
            jQuery("#mw_container").hide();
            div.hide();
            div1.hide();
            div2.hide();
            div3.hide();
            div4.hide();
            div5.hide();
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


        jQuery("#resetFilter").click(function () {
            fillTable(colors);
            jQuery("#textureSelect").val(0);
        });

        jQuery("#editTextureBtn").click(function () {
            jQuery("#mw_container").show();
            jQuery("#mw_editTexture").show('slow');
            jQuery("#close").show();
            jQuery('[name = "texture_edit"]').click(function(){
                var textureId = jQuery(this).closest('tr').data('id');
                var isColored;
                if(this.checked){
                    isColored = 1;
                }
                else{
                    isColored = 0;
                }

                jQuery.ajax({
                    url: "index.php?option=com_gm_ceiling&task=textures.updateColored",
                    data: {
                        id: textureId,
                        isColored: isColored
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
                            text: "Обновлено!"
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

            })
        });

        jQuery('.del_texture').click(function () {
            var textureId = jQuery(this).closest('tr').data('id');
            jQuery.ajax({
                url: "index.php?option=com_gm_ceiling&task=textures.delete",
                data: {
                    id: textureId
                },
                dataType: "json",
                async: false,
                success: function (data) {
                    noty({
                        timeout: 2000,
                        theme: 'relax',
                        layout: 'center',
                        maxVisible: 5,
                        type: "message",
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

        jQuery("[name = editBtn]").click(function () {
            var color_id = jQuery(this.closest('tr')).data("color_id"),
                color = colors[color_id];

            var color_hex = color.hex.replace("#",'');
            jQuery("#mw_container").show();
            jQuery("#mwEditColor").show('slow');
            jQuery("#close").show();
            while(color_hex.length<6){
                color_hex+=0;
            }
            var colorpicker_id = jQuery("#colorpickerHolder").data('colorpickerId');
            if(!empty(colorpicker_id)){
                jQuery('#colorpickerHolder').ColorPickerSetColor(color_hex);
                jQuery("#"+colorpicker_id).show();
            }
            else {
                jQuery('#colorpickerHolder').ColorPicker({
                    flat: true,
                    color: color_hex,
                    onChange: function (hsb, hex, rgb) {
                        jQuery('#hexColorEdit').val(hex);
                    },
                    onBeforeShow: function () {
                        jQuery(this).ColorPickerSetColor(color_hex);
                    }
                });
            }

            jQuery("#hexColorEdit").val(color.hex);
            jQuery("#colorTitleEdit").val(color.title);

            var definedTexture = defineTexture(color.file);

            jQuery("#textureTitleEdit").val(definedTexture.value);
            jQuery("#colorTexture").val(definedTexture.key);
            jQuery("#idTexture").val(definedTexture.id);
            jQuery("#colorId").val(color_id);
            jQuery("#imagesEdit").append('<img style="max-height: 50px" src="'+color.file+"<?= '?t='.time(); ?>"+'"><br>');


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
                jQuery("#canvases >tbody > tr:last").append('<td>'+element.name+'</td>' +
                    '<td><input class="input-gm" name="count" value="'+element.count+'" style="vertical-align: middle;">' +
                    '<button class="btn btn-primary btn-sm" name="update_count" style="vertical-align: middle;"><i class="fa fa-check" aria-hidden="true"></i></button></td>' +
                    '<td>'+DELETE_BUTTON+'</td>');
            });
            jQuery("#width").mask("9.9");
            fillTexturesSelect();
            fillManufacturersSelect();
            jQuery("[name = 'update_count']").click(function () {
                var canvas_id = jQuery(this.closest('tr')).data('canvas_id'),
                    new_count = jQuery(this.closest('tr')).find('input[name="count"]').val();
                jQuery.ajax({
                    url: "index.php?option=com_gm_ceiling&task=canvasForm.updateCount",
                    data: {
                        id: canvas_id,
                        count:new_count
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
                            text: "Обновлено!"
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
            jQuery('#color_selector').ColorPicker({
                flat: true,
                color: "ffffff",
                onShow: function (colpkr) {
                    jQuery(colpkr).fadeIn(500);
                    return false;
                },
                onHide: function (colpkr) {
                    jQuery(colpkr).fadeOut(500);
                    return false;
                },
                onChange: function (hsb, hex, rgb) {
                    jQuery('#hexColor').val(hex);
                }
            });
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
                        type: "success",
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
                            jQuery("#images").append('<img style="max-height: 50px" src="'+data[i]+"<?= '?t='.time(); ?>"+'"><br>');
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

        jQuery("#saveColorChangesBtn").click(function() {
            jQuery.ajax({
                url: "index.php?option=com_gm_ceiling&task=colors.update",
                data: {
                    hexCode: jQuery("#hexColorEdit").val(),
                    name: jQuery("#colorTitleEdit").val(),
                    textures:[jQuery("#colorTexture").val()],
                    idColor:jQuery("#colorId").val(),
                    textureId: jQuery("#idTexture").val(),
                    textureNewName:jQuery("#textureTitleEdit").val()

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

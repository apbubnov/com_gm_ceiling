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

$stockModel = Gm_ceilingHelpersGm_ceiling::getModel('stock');
$textures = $stockModel->getPropTextures();
$colors = $stockModel->getPropColors();
$manufacturers = $stockModel->getPropManufacturers();


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
<div class="row">
    <div class="col-md-3">
        <?=parent::getButtonBack();?>
    </div>
    <div class="col-md-3">
        <a href="/index.php?option=com_gm_ceiling&view=stock&type=goods" class="btn btn-primary"> <i class="fa fa-plus-square" aria-hidden="true"></i> Добавить полотна</a>
    </div>
</div>

<div class="container">
    <div class="row">
        <div class="col-md-4">
            <h4>Фактуры</h4>
            <div class="row" style="margin-bottom: 10px">
                <label style="margin-left: 15px">Добавить фактуру</label>
                <div class="col-md-10">
                    <input class="form-control" id="new_texture">
                </div>
                <div class="col-md-20">
                    <button class="btn btn-primary" id="save_texture"><i class="far fa-save"></i></button>
                </div>
            </div>
            <table class="table table_cashbox" id="textures_table">
                <thead>
                    <tr>
                        <td class="center">#</td>
                        <td  class="center">Название</td>
                        <td  class="center"><i class="fas fa-edit_texture"></i></td>
                        <td  class="center"><i class="far fa-trash-alt"></i></td>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($textures as $texture){?>
                    <tr data-id="<?=$texture->id?>">
                        <td><?php echo $texture->id;?></td>
                        <td class="name"><?php echo $texture->value;?></td>
                        <td><button class="btn btn-primary btn-sm edit_texture"><i class="fas fa-edit"></i></button></td>
                        <td><button class="btn btn-danger btn-sm delete_texture"><i class="far fa-trash-alt"></i></button></td>

                    </tr>
                <?php }?>
                </tbody>
            </table>
        </div>
        <div class="col-md-4">
            <h4 align="center">Цвета</h4>
            <div class="row" style="margin-bottom: 10px">
                <label style="margin-left: 15px">Добавить новый цвет</label>
                <div class="col-md-12">
                    <button class="btn btn-primary action-btn" id="addColor" ><i class="fa fa-plus-square" aria-hidden="true"></i> Добавить</button>
                </div>
            </div>
            <table class="table table_cashbox">
                <thead>
                <tr >
                    <td  class="center">Название</td>
                    <td  class="center">Цвет</td>
                    <td  class="center"><i class="fas fa-edit"></i></td>
                    <td  class="center"><i class="far fa-trash-alt"></i></td>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($colors as $color){?>
                    <tr data-id="<?= $color->id?>" data-hex="<?= $color->hex?>">
                        <td><?php echo $color->id;?></td>
                        <td><img style="background-color: <?php echo "#".$color->hex?>;width:100%;height:30px"></td>
                        <td><button class="btn btn-primary btn-sm edit_color"><i class="fas fa-edit"></i></button></td>
                        <td><button class="btn btn-danger btn-sm delete_color"><i class="far fa-trash-alt"></i></button></td>
                    </tr>
                <?php }?>
                </tbody>
            </table>
        </div>
        <div class="col-md-4">
            <h4>Производители</h4>
            <div class="row" style="margin-bottom: 10px;">
                <label style="margin-left: 15px">Добавить производителя</label>
                <div class="col-md-10">
                    <input class="form-control" id="new_manufacturer">
                </div>
                <div class="col-md-20">
                    <button class="btn btn-primary" id="save_manufacturer"><i class="far fa-save"></i></button>
                </div>
            </div>
            <table class="table table_cashbox" id="manufacturers_table">
                <thead>
                <tr>
                    <td  class="center">#</td>
                    <td  class="center">Название</td>
                    <td  class="center"><i class="fas fa-edit"></i></td>
                    <td  class="center"><i class="far fa-trash-alt"></i></td>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($manufacturers as $manufacturer){?>
                    <tr data-id="<?=$manufacturer->id?>">
                        <td><?php echo $manufacturer->id;?></td>
                        <td class="name"><?php echo $manufacturer->value;?></td>
                        <td><button class="btn btn-primary btn-sm edit_manufacturer"><i class="fas fa-edit"></i></button></td>
                        <td><button class="btn btn-danger btn-sm delete_manufacturer"><i class="far fa-trash-alt"></i></button></td>
                    </tr>
                <?php }?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="modal_window_container" id="mw_container">
        <button type="button" class="close_btn" id="close""><i class="fa fa-times fa-times-tar" aria-hidden="true"></i></button>
        <div class="modal_window" id="mwCreateColor">
            <form>
                <div class="row">
                    <div class="col-md-12">
                        <label for="colorTitle">Введите назавние цвета:</label>
                        <input id="colorTitle" class="input-gm">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <label for = "hexColor">Выберите цвет</label>
                        <input id = "hexColor" class="input-gm">
                        <div id = "color_selector">
                        </div>
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
            <input id="color_id" value="" type="hidden">
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
            </div>
            <div class="row center" >
                <div class="col-md-12">
                    <button class="btn btn-primary" id="saveColorChangesBtn">Сохранить</button>
                </div>
            </div>
        </div>

    </div>
</div>
<link rel="stylesheet" media="screen" type="text/css" href="/components/com_gm_ceiling/views/colors/colorPicker/css/colorpicker.css" />
<script type="text/javascript" src="/components/com_gm_ceiling/views/colors/colorPicker/js/colorpicker.js"></script>
<script type="text/javascript">
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
        jQuery(".edit_texture").click(function(){
            addEditFields("texture",this);
        });

        jQuery(".edit_color").click(function() {
            var color_id = jQuery(this.closest('tr')).data("id"),
                color_hex = jQuery(this.closest('tr')).data('hex');
            jQuery("#color_id").val(color_id);
            jQuery("#mw_container").show();
            jQuery("#mwEditColor").show('slow');
            jQuery("#close").show();

            var colorpicker_id = jQuery("#colorpickerHolder").data('colorpickerId');
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
        });

        jQuery("#saveColorChangesBtn").click(function () {
            var id = jQuery("#color_id").val(),
                hex = jQuery("#hexColorEdit").val();
            jQuery.ajax({
                type: 'POST',
                url: '/index.php?option=com_gm_ceiling&task=stock.editPropColor',
                data: {
                    id:id,
                    value: hex
                },
                success: function(data){
                    location.reload();
                    //jQuery("#"+tableId +"> tbody").append('<tr data-id="'+data+'"><td>'+data+'</td><td>'+name+'</td></tr>');
                },
                dataType: "text",
                timeout: 10000,
                error: function(data){
                    console.log(data);
                    var n = noty({
                        theme: 'relax',
                        timeout: 2000,
                        layout: 'topCenter',
                        maxVisible: 5,
                        type: "error",
                        text: "Ошибка!"
                    });
                }
            });
        });
        jQuery(".edit_manufacturer").click(function(){
            addEditFields("manufacturer",this);
        });

        jQuery(".delete_texture").click(function(){
            var id = jQuery(this).closest('tr').data('id');
            deleteItem(id,'delPropTexture');
        });

        jQuery(".delete_manufacturer").click(function(){
            var id = jQuery(this).closest('tr').data('id');
            deleteItem(id,'delPropManufacturer');
        });

        jQuery(".delete_color").click(function(){
            var id = jQuery(this).closest('tr').data('id');
            deleteItem(id,'delPropColor');
        });

        jQuery('.table_cashbox').on('click', '.update', function () {
            var type = jQuery(this).data('type'),
                id = jQuery(this).closest('tr').data('id'),
                new_name = jQuery(this).closest('.row').find('.new_name').val(),
                url = '/index.php?option=com_gm_ceiling&task=stock.';
            if(type == 'texture'){
                url+='editPropTexture';
            }
            if(type == 'manufacturer'){
                url+='editPropManufacturer';
            }

            jQuery.ajax({
                type: 'POST',
                url: url,
                data: {
                    id:id,
                    value: new_name
                },
                success: function(data){
                    location.reload();
                    //jQuery("#"+tableId +"> tbody").append('<tr data-id="'+data+'"><td>'+data+'</td><td>'+name+'</td></tr>');
                },
                dataType: "text",
                timeout: 10000,
                error: function(data){
                    console.log(data);
                    var n = noty({
                        theme: 'relax',
                        timeout: 2000,
                        layout: 'topCenter',
                        maxVisible: 5,
                        type: "error",
                        text: "Ошибка!"
                    });
                }
            });

        });

        jQuery("#save_texture").click(function(){
            var name = jQuery("#new_texture").val();
            addNew(name,'addPropTexture',"textures_table");
        });

        jQuery("#save_manufacturer").click(function(){
            var name = jQuery("#new_manufacturer").val();
            addNew(name,'addPropManufacturer',"manufacturers_table");
        });

        jQuery("#saveColorBtn").click(function () {
            var hex = jQuery("#hexColor").val(),
                name = jQuery("#colorTitle").val();
            if(!empty(name) && !empty(hex)){
                jQuery.ajax({
                    type: 'POST',
                    url: 'index.php?option=com_gm_ceiling&task=stock.addPropColor',
                    data: {
                        id: name,
                        value: hex
                    },
                    success: function(data){
                        location.reload();
                        //jQuery("#"+tableId +"> tbody").append('<tr data-id="'+data+'"><td>'+data+'</td><td>'+name+'</td></tr>');
                    },
                    dataType: "text",
                    timeout: 10000,
                    error: function(data){
                        console.log(data);
                        var n = noty({
                            theme: 'relax',
                            timeout: 2000,
                            layout: 'topCenter',
                            maxVisible: 5,
                            type: "error",
                            text: "Ошибка!"
                        });
                    }
                });
            }
            else{
                var n = noty({
                    theme: 'relax',
                    timeout: 2000,
                    layout: 'topCenter',
                    maxVisible: 5,
                    type: "error",
                    text: "Пустое название или цвет!"
                });
            }
        });

        jQuery("#addColor").click(function(){
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

    });

    function addEditFields(type,elem) {
        var save_btn_attr = 'data-type="'+type+'"',
            edit_fields = '<div class="row"><div class="col-md-6"><input class="from_control new_name"></div><div class="col-md-2"><button class="btn btn-primary btn-sm update" '+save_btn_attr+'><i class="far fa-save"></i></button></div></div>',
            td = jQuery(elem).closest('tr').find('.name');
        console.log(td);
        td.empty();
        td.append(edit_fields);
    }

    function addNew(name,functionName,tableId){
        if(!empty(name)){
            jQuery.ajax({
                type: 'POST',
                url: 'index.php?option=com_gm_ceiling&task=stock.'+functionName,
                data: {
                    value: name
                },
                success: function(data){
                    location.reload();
                    //jQuery("#"+tableId +"> tbody").append('<tr data-id="'+data+'"><td>'+data+'</td><td>'+name+'</td></tr>');
                },
                dataType: "text",
                timeout: 10000,
                error: function(data){
                    console.log(data);
                    var n = noty({
                        theme: 'relax',
                        timeout: 2000,
                        layout: 'topCenter',
                        maxVisible: 5,
                        type: "error",
                        text: "Ошибка!"
                    });
                }
            });
        }
        else{
            var n = noty({
                theme: 'relax',
                timeout: 2000,
                layout: 'topCenter',
                maxVisible: 5,
                type: "error",
                text: "Пустое название!"
            });
        }
    }

    function deleteItem(id,functionName) {
        console.log(id,functionName);
        jQuery.ajax({
            type: 'POST',
            url: 'index.php?option=com_gm_ceiling&task=stock.'+functionName,
            data: {
                id: id
            },
            success: function(data){
                location.reload();
                //jQuery("#"+tableId +"> tbody").append('<tr data-id="'+data+'"><td>'+data+'</td><td>'+name+'</td></tr>');
            },
            dataType: "text",
            timeout: 10000,
            error: function(data){
                console.log(data);
                var n = noty({
                    theme: 'relax',
                    timeout: 2000,
                    layout: 'topCenter',
                    maxVisible: 5,
                    type: "error",
                    text: "Ошибка!"
                });
            }
        });
    }


</script>

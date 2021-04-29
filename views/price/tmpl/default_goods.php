<?php
/**
 * Created by PhpStorm.
 * User: bubnov
 * Date: 03.07.2019
 * Time: 14:52
 */
$jinput = JFactory::getApplication()->input;
$dealerId = $jinput->get('dealer_id',null,'INT');

$user = JFactory::getUser();
$user->groups = $user->get('groups');

$managerGM = in_array(16, $user->groups) || in_array(15, $userDealer->groups);
$pricesModel = Gm_ceilingHelpersGm_ceiling::getModel('prices');
/*$stockModel = Gm_ceilingHelpersGm_ceiling::getModel('stock');
$goodsInCategories = $stockModel->getGoodsInCategories();*/
$categoryModel = Gm_ceilingHelpersGm_ceiling::getModel('Goods_category');
$categories = $categoryModel->get();
?>
<style>
    .row{
        margin-bottom: 1em !important;
    }
    .category_title{
        border: #414099 2px solid;
        background: #d3d3f9;
        height: 3em;
        line-height: 2em;
        cursor: pointer;
    }

    .category_title span,i{
        vertical-align: middle;
    }

    .goods{
        display: none;
    }

    .goods_row{
        border: 1px solid #414099;
        height: 8em;
        line-height: 4em;
        margin: 0 0 5px 0 !important;
        padding: 0 !important;

    }

    .div_save{
        margin-top: -0.75em;
    }

    .image_preview{
        max-height: 125px;
        vertical-align: middle;
        padding-top: 2px;
        padding-bottom: 2px;
    }
    .add_image_href_btn{
        width:120px;
        height: 120px;
        background-color: #FFFFFF;
        border: 2px solid grey;
        vertical-align: middle;
        margin-top: 3px;
    }
    .add_image_href_btn i{
        padding-top: 5px;
        font-size: 36pt;
        color: grey;

    }
    .add_image_href_btn:hover{
        color: #414099;
        border-color: #414099;
    }
    .add_image_href_btn:hover > i{
        color: #414099;
    }
    .more_imgs{
        height:125px;
        background-color: #d3d3f9 !important;
    }

</style>
<div id="preloader" class="PRELOADER_GM PRELOADER_GM_OPACITY">
    <div class="PRELOADER_BLOCK"></div>
    <img src="/images/GM_R_HD.png"  alt = 'preloader' class="PRELOADER_IMG">
</div>
<div class="row right">
    <div class="col-md-6"></div>
    <div class="col-md-5">
        <div class="col-md-3">
            <span> Поиск </span>
        </div>
        <div class="col-md-9">
            <input class="form-control" id="search" placeholder="Введите данные для поиска">
        </div>
    </div>
    <div class="col-md-1">
        <button class="btn btn-primary" id="btn_find" >Найти</button>
    </div>
</div>
<div id="goods_list"></div>
<div class="modal_window_container" id="mw_container">
    <button type="button" id="mw_close" class="close_btn">
        <i class="fa fa-times fa-times-tar" aria-hidden="true"></i>
    </button>
    <div class="modal_window" id="mw_images" >
        <div class="row">
            <h4>Изображения для <span id="goods_title"></span></h4>
        </div>
        <div class="row" id="imgs_container">

        </div>
        <div class="row center">
            <div class="col-md-12">
                <button class="btn btn-primary" id="add_imgs">
                    <i class="far fa-plus-square"></i> Добавить новые изображения
                </button>
            </div>
        </div>
    </div>
    <div class="modal_window" id="mw_add_img">
        <input type="hidden" id="selected_goods">
        <div class="row">
            <h4>Добавьте ссылку на изображение</h4>
        </div>
        <div class="row">
            <div class="col-md-3"></div>
            <div id="href_container" class="col-md-6">
                <div class="row href_row">
                    <div class="col-md-8">
                        <input class="form-control href_val">
                    </div>
                    <div class="col-md-2">
                        <input type="checkbox" id="main_img" name="main_img" class="inp-cbx" style="display: none">
                        <label for="main_img" class="cbx">
                        <span>
                            <svg width="12px" height="10px" viewBox="0 0 12 10">
                                <polyline points="1.5 6 4.5 9 10.5 1"></polyline>
                            </svg>
                        </span>
                            <span>Основная</span>
                        </label>
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-primary" id="duplicate_field"><i class="far fa-clone"></i></button>
                    </div>
                </div>
            </div>
            <div class="col-md-3"></div>
        </div>
        <div class="row center">
            <div class="col-md-12">
                <button class="btn btn-primary" id="save_hrefs">Сохранить</button>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    jQuery(document).mouseup(function (e){
        var div = jQuery("#mw_images"),
            div1 = jQuery('#mw_add_img');
        if (!div.is(e.target)
            && div.has(e.target).length === 0
            &&!div1.is(e.target)
            && div1.has(e.target).length === 0) {
            jQuery(".close_btn").hide();
            jQuery("#mw_container").hide();
            jQuery(".modal_window").hide();
        }
    });
    jQuery(document).ready(function(){
        var goods,
            categories = JSON.parse('<?=json_encode($categories);?>');
        getGoods();

        jQuery('body').on('click','.save_btn', function(){
            var row = jQuery(this).closest('.goods_row'),
                goods_id = row.data('id'),
                new_name = row.find('.new_name').val(),
                new_price = row.find('.new_price').val();
            jQuery.ajax({
                type: 'POST',
                url: '/index.php?option=com_gm_ceiling&task=stock.updateGoods',
                data: {
                    goodsId: goods_id,
                    name: new_name,
                    price: new_price,
                },
                dataType: "json",
                timeout: 5000,
                success: function (data) {
                   if(!empty(new_name)){
                       row.find('.old_name').html('<b>'+new_name+'</b>');
                   }
                    if(!empty(new_price)){
                        row.find('.old_price').text(new_price);
                    }
                    row.find('.new_name').val('');
                    row.find('.new_price').val('');
                },
                error: function (error) {
                    noty({
                        timeout: 2000,
                        theme: 'relax',
                        layout: 'center',
                        maxVisible: 5,
                        type: "error",
                        text: 'Ошибка!'
                    });
                }
            });

        });

        jQuery('body').on('click','.category_title',function(){
            var goodsDiv = jQuery(this).closest('.category').find('.goods'),
                angle = jQuery(this).find('i');
            goodsDiv.toggle()
            if(goodsDiv.is(':visible')){
                angle.removeClass('fa-angle-down');
                angle.addClass('fa-angle-up');
            }
            else{
                angle.removeClass('fa-angle-up ');
                angle.addClass('fa-angle-down');
            }
        });

        jQuery('body').on('click','.save_btn_category',function(){
            var row = jQuery(this).closest('.goods_row'),
                category = row.find('.select_category').val(),
                goodsId = row.data('id');
            console.log(row,category,goodsId);
            jQuery.ajax({
                type: 'POST',
                url: '/index.php?option=com_gm_ceiling&task=goods.changeCategory',
                data: {
                    id: goodsId,
                    category: category
                },
                dataType: "json",
                timeout: 5000,
                success: function (data) {
                    console.log(data);
                    if(data == 1){
                        location.reload();
                    }
                    else{
                        noty({
                            timeout: 2000,
                            theme: 'relax',
                            layout: 'center',
                            maxVisible: 5,
                            type: "error",
                            text: 'Перенос в другую категорию не выполнен!'
                        });
                    }
                },
                error: function (error) {
                    noty({
                        timeout: 2000,
                        theme: 'relax',
                        layout: 'center',
                        maxVisible: 5,
                        type: "error",
                        text: 'Ошибка!'
                    });
                }
            });
        });

        jQuery('#btn_find').click(function(){
            var search = jQuery('#search').val();
            getGoods(search);
        });

        jQuery('body').on('click','.more_imgs',function(){
            var category_id = jQuery(this).data('category_id'),
                id = jQuery(this).data('id'),
                currentGoodsInCategory = JSON.parse(goods.find(function (el){ return el.id==category_id}).goods),
                currentGoods = currentGoodsInCategory.find(function (g) { return g.id == id}),
                images = JSON.parse(atob(currentGoods.images));
            jQuery('#selected_goods').val(id);
            jQuery('#imgs_container').empty();
            jQuery.each(images,function (n,img) {
                jQuery('#imgs_container').append('<div class="col-md-3"><img src="'+img.link+'" style="max-width: -webkit-fill-available;"></div>');
            })
            jQuery('#goods_title').text(currentGoods.name);
            jQuery('#mw_container').show();
            jQuery('#mw_close').show();
            jQuery('#mw_images').show();
        });

        jQuery('body').on('click','.add_image_href_btn',function () {
            jQuery('#selected_goods').val(jQuery(this).data('id'));
            jQuery('.href_row.added').remove();
            jQuery('.href_val')[0].value="";
            jQuery('#main_img').removeAttr('checked');
            jQuery('#mw_container').show();
            jQuery('#mw_close').show();
            jQuery('#mw_add_img').show();
        });

        jQuery('#add_imgs').click(function(){
            jQuery('.modal_window').hide();
            jQuery('.href_row.added').remove();
            jQuery('.href_val')[0].value="";
            jQuery('#main_img').removeAttr('checked');
            jQuery('#mw_container').show();
            jQuery('#mw_close').show();
            jQuery('#mw_add_img').show();
        });

        jQuery('#duplicate_field').click(function(){
            var checkBoxId = +/\d+/.exec(jQuery('[name="main_img"]').last().attr('id'))+1,
                checkBox = "<input name = \"main_img\" type=\"checkbox\" id=\"main_img_" + checkBoxId + "\" class=\"inp-cbx\" style=\"display: none\">\n" +
                    "<label for=\"main_img_" + checkBoxId + "\" class=\"cbx\">" +
                    "<span>\n" +
                    "<svg width=\"12px\" height=\"10px\" viewBox=\"0 0 12 10\">" +
                    "<polyline points=\"1.5 6 4.5 9 10.5 1\"></polyline>" +
                    "</svg>" +
                    "</span>" +
                    "<span>Основная</span>\n" +
                    "</label>";
           jQuery('#href_container').append(
               '<div class="row href_row added">' +
                   '<div class="col-md-8"><input class="form-control href_val"></div>' +
                   '<div class="col-md-2">'+checkBox+'</div>' +
                   '<div class="col-md-2"><button class="btn btn-danger remove_href"><i class="far fa-trash-alt"></i></button></div>' +
               '</div>');
        });

        jQuery('#mw_add_img').on('click','.remove_href',function(){
            jQuery(this).closest('.row').remove();
        });

        jQuery('#save_hrefs').click(function(){
            var goodsId = jQuery('#selected_goods').val(),
                hrefsRows = jQuery('.href_row'),
                hrefs = [];
            jQuery.each(hrefsRows,function(i,e){
                var is_main = jQuery(e).find('[name="main_img"]').prop('checked') ? 1 : 0,
                href = jQuery(e).find('.href_val').val();
                if(!empty(href)) {
                    hrefs.push(goodsId+",'"+href+"',"+is_main);
                }
            });
            if(!empty(hrefs)){
                jQuery.ajax({
                    type: 'POST',
                    url: '/index.php?option=com_gm_ceiling&task=goods.addImages',
                    data: {
                        hrefs: hrefs
                    },
                    dataType: "json",
                    timeout: 5000,
                    success: function (data) {
                       location.reload();
                    },
                    error: function (error) {
                    }
                });
            }

        });

        jQuery('#mw_add_img').on('click','[name="main_img"]',function () {
            jQuery.each(jQuery('[name="main_img"]:checked'),function(i,e){jQuery(e).removeAttr('checked')});
            if(!jQuery(this).is('checked')){
                jQuery(this).attr('checked',"true");
            }
        });

        function getGoods(filter) {
            jQuery.ajax({
                type: 'POST',
                url: '/index.php?option=com_gm_ceiling&task=goods.getGoodsInCategoriesByFilter',
                data: {
                    search: filter
                },
                dataType: "json",
                timeout: 5000,
                success: function (data) {
                    jQuery('#preloader').hide();
                    goods = data;
                    showGoods(goods);
                },
                error: function (error) {
                    jQuery('#preloader').hide();
                    console.log(error);
                }
            });
        }

        function showGoods(data){
            var goodsList = jQuery('#goods_list'),
                categoryDiv,
                goodsDiv,
                categorySelect = createSelect(categories),
                goods;
            goodsList.empty();
            console.log(data);
            jQuery.each(data,function (n,category) {
                categoryDiv = jQuery('<div/>');
                categoryDiv.append(
                '<div class="row center category_title" >' +
                    '<div class="col-md-11 col-xs-10">' +
                        '<span style="font-size: 14pt;">' +
                            category.category +
                        '</span>' +
                    '</div>' +
                    '<div class="col-md-1 col-xs-2" style="text-align: right;">' +
                        '<i class="fas fa-angle-down"></i>' +
                    '</div>' +
                '</div>'
                );
                categoryDiv.addClass('category');
                goods = JSON.parse(category.goods);
                goodsDiv = jQuery('<div/>');
                jQuery.each(goods,function(i,goods){
                    var images = (!empty(goods.images)) ? JSON.parse(atob(goods.images)) : '',
                        link = !empty(images[0]) ? images[0].link : '',
                        imgDiv = (images.length > 1)
                            ? '<div class="col-md-9 col-xs-9" style="padding-right: 0;text-align:center;"><img class="image_preview" src="'+link+'"/></div>' +
                            '<div class="col-md-3 col-xs-3"><button class="btn  more_imgs" data-category_id="'+category.id+'" data-id="'+goods.id+'"><i class="fas fa-ellipsis-v" style="font-size: 25pt;color: #414099"></i></button></div>'
                            : '<div class="col-md-12 col-xs-12" style="text-align: center"><label class="add_image_href_btn" data-id="'+goods.id+'"><i class="fas fa-plus"></br></i></br>Добавить</label></div>';
                    goodsDiv.append(
                        '<div class="row goods_row" data-id="'+goods.id+'" data-category_id="'+category.id+'">'+
                        '<div class="col-md-3 col-xs-12">' +
                        imgDiv +
                        '</div>'+
                        '<div class="col-md-9 col-xs-12">'+
                        '<div class="col-md-9 col-xs-10 old_name">'+
                                '<b>'+goods.name+'</b>'+
                            '</div>'+
                            '<div class="col-md-3 col-xs-2 center old_price">'+
                                '<b>Цена:'+goods.price + '</b><i class="fas fa-ruble-sign"></i>'+
                            '</div>'+
                            '<div class="col-md-6 col-xs-6">'+
                                '<div class="col-md-10">'+
                                    categorySelect +
                                '</div>'+
                                '<div class="col-md-2 div_save">'+
                                    '<button class="btn btn-primary save_btn_category">'+
                                        '<i class="far fa-save"></i>'+
                                    '</button>'+
                                '</div>'+
                            '</div>'+
                            '<div class="col-md-6 col-xs-4" style="text-align: right">'+
                                '<div class="col-md-5 col-xs-5">'+
                                    '<input class="form-control new_name" placeholder="Новое название">'+
                                '</div>'+
                                '<div class="col-md-5 col-xs-5">'+
                                    '<input class="form-control new_price" placeholder="Новая цена">'+
                                '</div>'+
                                '<div class="col-md-2 col-xs-2 div_save">'+
                                    '<button class="btn btn-primary save_btn">'+
                                        '<i class="far fa-save"></i>'+
                                    '</button>'+
                                '</div>'+
                            '</div>'+
                        '</div>'+
                        '</div>'
                    );

                });
                goodsDiv.addClass('goods');
                categoryDiv.append(goodsDiv);

                goodsList.append(categoryDiv);
            });

        }

        function createSelect(data){
            var select = '<select class="form-control select_category">';
            select +='<option>Перенести в категорию</option>';
            jQuery.each(data,function(n,c){
                select +='<option value="'+c.id+'">-'+c.category+'</option>';
            });
            select += '</select>';
            return select;
        }
    });
</script>
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
$clientId = $user->associated_client;
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

        margin: 0 0 5px 0 !important;
        padding: 10px !important;

    }

    .left{
        text-align: left;
    }
    .right{
        text-align: right;
    }
    .no_padding{
        padding-left: 0 !important;
        padding-right: 0 !important;
    }
    .image_preview{
        max-height: 125px;
        vertical-align: middle;
        padding-top: 2px;
        padding-bottom: 2px;
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
    <div class="col-md-12">
        <button class="btn btn-primary" id="show_cart">
            <div style="position: relative">
                <i class="fas fa-shopping-cart" style="font-size: 16pt;"></i>
                <div class="circl-digits" style="right: -20px !important;display: none;" id="count"></div>
            </div>
            <div class="col-md-12">
                <label style="font-size: 14pt;"><b><span id="total_sum">0</span></b></label>
                <i class="fas fa-ruble-sign" style="font-size: 11pt;"></i>
            </div>
        </button>
    </div>

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
    <button type="button" id="mw_close" class="close_btn"><i class="fa fa-times fa-times-tar" aria-hidden="true"></i></button>
    <div class="modal_window" id="mw_cart">
        <h4 id="empty_title">Ваша корзина пуста!</h4>
        <div id="added_goods">

        </div>
        <div class="row goods_row">
            <div class="col-md-8" style="text-align:right;"><b>Итого</b></div>
            <div class="col-md-2"><b><span id="cart_total">0</span></b><i class="fas fa-ruble-sign" style="font-size: 11pt;"></i></div>
        </div>
        <div class="row center">
            <button class="btn btn-primary" id="make_order" disabled>К оформлению</button>
        </div>
        <div id="order_info" style="display: none;">
            <div class="row">
                <div class="col-md-6">
                    <div class="row center">
                        <div class="col-md-12">
                            <label><strong>Адрес</strong></label>
                            <div class="row">
                                <div class="col-md-4 col-xs-4">
                                    <label><b>Улица</b></label>
                                </div>
                                <div class="col-md-8 col-xs-8">
                                    <input  class="form-control new_address_cl" placeholder="Улица" type="text" value="<?=$address->street;?>">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4 col-xs-4">
                                    <label><b>Дом \ Корпус</b></label>
                                </div>
                                <div class="col-md-4 col-xs-4">
                                    <input class="form-control new_house_cl" placeholder="Дом" aria-required="true" type="text" value="<?=$address->house;?>">
                                </div>
                                <div class="col-md-4 col-xs-4">
                                    <input class="form-control new_bdq_cl" placeholder="Корпус" aria-required="true"
                                           type="text" value="<?=$address->bdq;?>">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4 col-xs-4">
                                    <label><b>Квартира \ Подъезд</b></label>
                                </div>
                                <div class="col-md-4 col-xs-4">
                                    <input class="form-control new_apartment_cl" placeholder="Квартира" aria-required="true"
                                           type="text" value="<?=$address->apartment;?>">
                                </div>
                                <div class="col-md-4 col-xs-4">
                                    <input class="form-control new_porch_cl" placeholder="Подъезд" aria-required="true"
                                           type="text" value="<?=$address->porch;?>">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4 col-xs-4">
                                    <label><b>Этаж \ Код домофона</b></label>
                                </div>
                                <div class="col-md-4 col-xs-4">
                                    <input class="form-control new_floor_cl" placeholder="Этаж" aria-required="true"
                                           type="text" value="<?=$address->floor;?>">
                                </div>
                                <div class="col-md-4 col-xs-4">
                                    <input class="form-control new_code_cl" placeholder="Код" aria-required="true"
                                           type="text" value="<?=$address->code;?>">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <input name="transport" class="radio" id = "self" value="1" type="radio" checked>
                    <label for = "self">Самовывоз, г.Воронеж пр-т Труда,48</label>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <button class="btn btn-primary" id="order"> Заказать</button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal_window" id="mw_images" >
        <div class="row">
            <h4>Изображения для <span id="goods_title"></span></h4>
        </div>
        <div class="row" id="imgs_container">

        </div>
    </div>
</div>

<script type="text/javascript">
    jQuery(document).mouseup(function (e){
        var div = jQuery("#mw_cart"),
            div1 = jQuery('#mw_images');
        if (!div.is(e.target)
            && div.has(e.target).length === 0) {
            jQuery(".close_btn").hide();
            jQuery("#mw_container").hide();
            jQuery(".modal_window").hide();
        }
    });
    var cart = {
            sum: 0,
            goodsList: []
        },
        allGoods = [];
    jQuery(document).ready(function(){
        var goods;
        getGoods();

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

        jQuery('#btn_find').click(function(){
            var search = jQuery('#search').val();
            getGoods(search);
        });
        
        jQuery('body').on('click','.to_cart',function(){
            var row = jQuery(this).closest('.goods_row'),
                categoryId = row.data('category_id'),
                goodsId = row.data('id'),
                goodsObj = Object.assign({},allGoods.find(function(g){if(g.id == goodsId){ return g; }})),
                count = row.find('.count').val();
            goodsObj.count = round(count,goodsObj.mult);
            row.find('.count').val(goodsObj.count);
            goodsObj.total_sum = goodsObj.count*goodsObj.price;
            addToCart(goodsObj);
            calculateCart();
            printCartData();
        });

        jQuery('#show_cart').click(function () {
            jQuery("#mw_close").show();
            jQuery("#mw_container").show();
            jQuery("#mw_cart").show();
            var row;
            if(!empty(cart.goodsList)){
                jQuery('#empty_title').hide();
                jQuery('#make_order').removeAttr('disabled');
            }
            jQuery('#added_goods').empty();
            jQuery.each(cart.goodsList,function(n,g){
                row = '<div class="row goods_row" data-id="'+g.id+'">' +
                    '<div class="col-md-4"><b>'+g.name+'</b></div>' +
                    '<div class="col-md-4 count_div">' +
                    '<div class="col-md-10">'+g.count+' '+g.unit+'</div>' +
                    '<div class="col-md-2">' +
                    '<button class="btn btn-primary chng_count"><i class="far fa-edit"></i></button>'+
                    '</div>'+
                    '</div>' +
                    '<div class="col-md-2"><span class="total_sum">'+g.total_sum+'</span> <i class="fas fa-ruble-sign" style="font-size: 11pt;"></i></div>' +
                    '<div class="col-md-2"><button class="btn btn-danger remove_from_cart"><i class="far fa-trash-alt"></i></button></div>' +
                    '</div>';
                jQuery('#added_goods').append(row);
            });
        });

        jQuery('body').on('click','.change_count',function(){
            var thisBtn = jQuery(this),
                row = thisBtn.closest('.goods_row'),
                goodsId = row.data('id') ,
                inputCount = row.find('.count'),
                currentGoods = allGoods.find(function (g) {if(g.id == goodsId){ return g;}}),
                value = empty(inputCount.val()) ? 0 : inputCount.val();
            if(thisBtn.hasClass('plus')){
                value = round(+value + +currentGoods.mult,currentGoods.mult);
            }
            if(thisBtn.hasClass('minus')) {
                if(value>0){
                    value = round(value - currentGoods.mult,currentGoods.mult);
                }
            }
            inputCount.val(value);
        });

        jQuery('#make_order').click(function () {
            jQuery('#order_info').toggle();

        });

        jQuery('#order').click(function () {
            var address = makeAddress(),
                clientId = <?=$clientId?>;
            if(!empty(cart)){
                jQuery.ajax({
                    type: 'POST',
                    async: false,
                    url: '/index.php?option=com_gm_ceiling&task=project.create',
                    data: {
                        client_id: clientId,
                        project_info: address
                    },
                    dataType: "json",
                    timeout: 5000,
                    success: function (data) {
                        createCalculation(data);
                    },
                    error: function (error) {

                    }
                });
            }
            else{
                noty({
                    timeout: 2000,
                    theme: 'relax',
                    layout: 'center',
                    maxVisible: 5,
                    type: "error",
                    text: "Корзина пуста!"
                });
            }
        });

        jQuery('#added_goods').on('click','.remove_from_cart',function () {
            var row = jQuery(this).closest('.goods_row'),
                goodsId = row.data('id'),
                index = cart.goodsList.findIndex(function (g,n){return g.id == goodsId});
            if (index > -1) {
                cart.goodsList.splice(index, 1);
                row.remove();
                calculateCart();
                printCartData();
            }
        });

        jQuery('#mw_cart').on('click','.chng_count',function(){
            var row = jQuery(this).closest('.goods_row'),
                div = row.find('.count_div'),
                goodsId = row.data('id'),
                cGoods = cart.goodsList.find(function(g){if(g.id==goodsId){return g;}});
            div.empty();
            div.append(
                '<div class=col-md-12>' +
                '<div class="col-md-2 col-xs-2 no_padding">'+
                '<button class="btn btn-primary change_count minus"><i class="far fa-minus-square"></i></button>'+
                '</div>'+
                '<div class="col-md-8 col-xs-8" style="text-align: center;"><input class="form-control count" placeholder="Кол-во" value="'+cGoods.count+'"></div>'+
                '<div class="col-md-2 col-xs-2 no_padding">'+
                '<button class="btn btn-primary change_count plus"><i class="far fa-plus-square"></i></button>'+
                '</div>'+
                '</div>'+
                '<div class="col-md-12"><button data-g_id = "'+goodsId+'"class="btn btn-primary save_new_count">Ок</button></div>'
            );

        });

        jQuery('#mw_cart').on('click','.save_new_count',function(){
            var row = jQuery(this).closest('.goods_row'),
                div = row.find('.count_div'),
                goodsId = jQuery(this).data('g_id'),
                count = row.find('.count').val(),
                g = cart.goodsList.find(function(g){if(g.id==goodsId){g.count = round(count,g.mult);g.total_sum = g.price*g.count;return g;}});

            div.empty();
            div.append(
                '<div class="col-md-10">'+g.count+' '+g.unit+'</div>' +
                '<div class="col-md-2">' +
                '<button class="btn btn-primary chng_count"><i class="far fa-edit"></i></button>'+
                '</div>'
            );
            row.find('.total_sum').empty();
            row.find('.total_sum').text(g.total_sum);
            calculateCart();
            printCartData();
        });

        jQuery('body').on('click','.more_imgs',function(){
            var category_id = jQuery(this).data('category_id'),
                id = jQuery(this).data('id'),
                currentGoodsInCategory = JSON.parse(goods.find(function (el){ return el.id==category_id}).goods),
                currentGoods = currentGoodsInCategory.find(function (g) { return g.id == id}),
                images = JSON.parse(atob(currentGoods.images));
            jQuery('#imgs_container').empty();
            jQuery.each(images,function (n,img) {
                jQuery('#imgs_container').append('<div class="col-md-3"><img src="'+img.link+'" style="max-width: -webkit-fill-available;"></div>');
            })
            jQuery('#goods_title').text(currentGoods.name);
            jQuery('#mw_container').show();
            jQuery('#mw_close').show();
            jQuery('#mw_images').show();
        });

        function getGoods(filter) {
            jQuery.ajax({
                type: 'POST',
                url: '/index.php?option=com_gm_ceiling&task=goods.getGoodsForStore',
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
                goods;
            goodsList.empty();
            console.log(data);
            jQuery.each(data,function (n,category) {
                categoryDiv = jQuery('<div/>');
                if(category.id != 1){
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
                    allGoods = allGoods.concat(goods);
                    goodsDiv = jQuery('<div/>');
                    jQuery.each(goods, function (i, goods) {
                        var images = (!empty(goods.images)) ? JSON.parse(atob(goods.images)) : '',
                            link = !empty(images[0]) ? images[0].link : '',
                            imgDiv = (images.length >= 1)
                                ? '<div class="col-md-9 col-xs-9" style="padding-right: 0;text-align:center;"><img class="image_preview" src="'+link+'"/></div>' +
                                '<div class="col-md-3 col-xs-3"><button class="btn more_imgs" data-category_id="'+category.id+'" data-id="'+goods.id+'"><i class="fas fa-ellipsis-v" style="font-size: 25pt;color: #414099"></i></button></div>'
                                : 'Изображение отсутсвует';

                        goodsDiv.append(
                            '<div class="row goods_row" data-id="' + goods.id + '" data-category_id="' + category.id + '">' +
                            '<div class="col-md-3 col-xs-12">' +
                                imgDiv+
                            '</div>'+
                            '<div class="col-md-4 col-xs-12">' +
                                '<b>' + goods.name + ', '+goods.unit +'</b>' +
                            '</div>' +
                            '<div class="col-md-1 col-xs-12  price">' +
                            '<b>Цена:</b> '+goods.price +
                            '</div>' +
                            '<div class="col-md-3 col-xs-12 no_padding">'+
                            '<div class="col-md-2 col-xs-2 no_padding">'+
                            '<button class="btn btn-primary change_count minus"><i class="far fa-minus-square"></i></button>'+
                            '</div>'+
                            '<div class="col-md-8 col-xs-8" style="text-align: center;"><input class="form-control count" placeholder="Кол-во"></div>'+
                            '<div class="col-md-2 col-xs-2 no_padding">'+
                            '<button class="btn btn-primary change_count plus"><i class="far fa-plus-square"></i></button>'+
                            '</div>'+
                            '</div>'+
                            '<div class="col-md-1 col-xs-12 no_padding right">'+
                            '<button class="btn btn-primary to_cart"><i class="fas fa-cart-arrow-down"></i></button>'+
                            '</div>'+
                            '</div>'
                        );

                    });
                    goodsDiv.addClass('goods');
                    categoryDiv.append(goodsDiv);

                    goodsList.append(categoryDiv);
                }
            });
        }
        function round(count,multiplicity) {
            return Math.ceil(count/multiplicity)*multiplicity;
        }

        function addToCart(gObj){
            var addedGoods = cart.goodsList.find(function(a){if(a.id == gObj.id) return a;});
            if(empty(addedGoods)){
                cart.goodsList.push(gObj)
            }
            else{
                alert(gObj.count);
                cart.goodsList.find(function(g){
                                        if(g.id == gObj.id){
                                            console.log(g);
                                            g.count += +gObj.count;
                                            g.total_sum += +gObj.total_sum;
                                        }
                                    });
            }
        }

        function calculateCart(){
            cart.sum = 0;
            jQuery.each(cart.goodsList,function (i,g){
               cart.sum += +g.total_sum;
            });
        }
        function printCartData(){
            jQuery('#total_sum').text(cart.sum);
            jQuery('#cart_total').text(cart.sum);
            if(cart.goodsList.length >0){
                jQuery('#count').show();
                jQuery('#count').text(cart.goodsList.length);

            }
            else{
                jQuery('#empty_title').show();
                jQuery('#count').text('');
                jQuery('#count').hide();
                jQuery('#cart_total').text(0);
                jQuery('#make_order').attr('disabled','');
                jQuery('#order_info').hide();
            }
        }

        function makeAddress() {
            var address = "",
                street = jQuery(".new_address_cl").val(),
                house = jQuery(".new_house_cl").val(),
                bdq = jQuery(".new_bdq_cl").val(),
                apartment = jQuery(".new_apartment_cl").val(),
                porch = jQuery(".new_porch_cl").val(),
                floor =jQuery(".new_floor_cl").val(),
                code = jQuery(".new_code_cl").val();
            if(house) address = street + ", дом: " + house;
            if(bdq) address += ", корпус: " + bdq;
            if(apartment) address += ", квартира: "+ apartment;
            if(porch) address += ", подъезд: " + porch;
            if(floor) address += ", этаж: " + floor;
            if(code) address += ", код: " + code;

           return address;
        }
        function createCalculation(projectId) {
            jQuery.ajax({
                type: 'POST',
                async: false,
                url: "/index.php?option=com_gm_ceiling&task=calculation.create_calculation",
                data: {
                    proj_id: projectId
                },
                success: function (data) {
                    addGoodsInCaluclation(data,projectId);
                },
                error: function (data) {
                    var n = noty({
                        timeout: 2000,
                        theme: 'relax',
                        layout: 'center',
                        maxVisible: 5,
                        type: "error",
                        text: "Ошибка сервера."
                    });
                }

            });
        }

        function addGoodsInCaluclation(calulationId,projectId){
            jQuery.ajax({
                type: 'POST',
                async: false,
                url: "/index.php?option=com_gm_ceiling&task=calculation.addGoodsFromCart",
                data: {
                    calc_id: calulationId,
                    project_id: projectId,
                    cart: JSON.stringify(cart)
                },
                success: function (data) {
                    noty({
                        timeout: 10000,
                        theme: 'relax',
                        layout: 'center',
                        maxVisible: 5,
                        type: "success",
                        text: "Заказ №"+projectId+" сформирован. В ближайшее время с Вами свяжется менеджер для подтверждения заказа"
                    });
                },
                error: function (data) {
                    noty({
                        timeout: 2000,
                        theme: 'relax',
                        layout: 'center',
                        maxVisible: 5,
                        type: "error",
                        text: "Ошибка сервера."
                    });
                }

            });
        }
    });
</script>
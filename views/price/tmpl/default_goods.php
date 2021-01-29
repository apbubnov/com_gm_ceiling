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

    .old_price,.div_save{
        margin-top: -0.75em;
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

<script type="text/javascript">
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
                    goodsDiv.append(
                        '<div class="row goods_row" data-id="'+goods.id+'" data-category_id="'+category.id+'">'+
                            '<div class="col-md-12 col-xs-12 old_name">'+
                                '<b>'+goods.name+'</b>'+
                            '</div>'+
                            '<div class="col-md-2 col-xs-2 center old_price">'+
                                goods.price +
                            '</div>'+
                            '<div class="col-md-4 col-xs-6">'+
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
                                '<div class="col-md-6 col-xs-5">'+
                                    '<input class="form-control new_name" placeholder="Новое название">'+
                                '</div>'+
                                '<div class="col-md-4 col-xs-5">'+
                                    '<input class="form-control new_price" placeholder="Новая цена">'+
                                '</div>'+
                                '<div class="col-md-2 col-xs-2 div_save">'+
                                    '<button class="btn btn-primary save_btn">'+
                                        '<i class="far fa-save"></i>'+
                                    '</button>'+
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
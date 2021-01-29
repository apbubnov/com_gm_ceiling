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

</style>
<div id="preloader" class="PRELOADER_GM PRELOADER_GM_OPACITY">
    <div class="PRELOADER_BLOCK"></div>
    <img src="/images/GM_R_HD.png"  alt = 'preloader' class="PRELOADER_IMG">
</div>
<div class="row right">
    <div class="col-md-12">
        <b> <i class="fas fa-shopping-cart"></i> Корзина</b>
    </div>
    <div class="col-md-12">
        <label><b>Количество:</b> <span id="added_count"></span></label>
    </div>
    <div class="col-md-12">
        <label><b>Сумма:</b> <span id="total_sum">0</span> <i class="fas fa-ruble-sign"></i></label>
    </div>
    <div class="col-md-12">
        <button class="btn btn-sm btn-primary">Посмотреть</button>
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

<script type="text/javascript">
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
                    console.log('g',goods);
                    goodsDiv = jQuery('<div/>');
                    jQuery.each(goods, function (i, goods) {
                        goodsDiv.append(
                            '<div class="row goods_row" data-id="' + goods.id + '" data-category_id="' + category.id + '">' +
                            '<div class="col-md-5 col-xs-12">' +
                            '<b>' + goods.name + ', '+goods.unit +'</b>' +
                            '</div>' +
                            '<div class="col-md-1 col-xs-12  price">' +
                            '<b>Цена:</b> '+goods.price +
                            '</div>' +
                            '<div class="col-md-4 col-xs-12 no_padding">'+
                            '<div class="col-md-2 col-xs-2 no_padding left">'+
                            '<button class="btn btn-primary minus"><i class="far fa-minus-square"></i></button>'+
                            '</div>'+
                            '<div class="col-md-8 col-xs-8 no_padding"><input class="form-control count" placeholder="Кол-во"></div>'+
                            '<div class="col-md-2 col-xs-2 no_padding right">'+
                            '<button class="btn btn-primary plus"><i class="far fa-plus-square"></i></button>'+
                            '</div>'+
                            '</div>'+
                            '<div class="col-md-2 no_padding right">'+
                            '<button class="btn btn-primary">В корзину</button>'+
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
        
    });
</script>
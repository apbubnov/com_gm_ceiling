<?php
/**
 * Created by PhpStorm.
 * User: bubnov
 * Date: 29.11.2019
 * Time: 14:09
 */
$user = JFactory::getUser();
$client_id = $user->associated_client;
?>
<style type="text/css">
    .row{
        margin-bottom: 15px !important;
    }
</style>
<div class="start_page">
    <div class="row center">
        <div class="col-md-12">
            <button class="btn btn-large btn-primary" id="precalc_btn" ><i class="fa fa-calculator" aria-hidden="true"></i> Рассчитать</button>
        </div>
    </div>
    <div class="row center">
        <div class="col-md-12">
            <button class="btn btn-large btn-primary" id="my_orders" ><i class="fa fa-list" aria-hidden="true"></i> Мои заказы</button>
        </div>
    </div>
    <div class="row center">
        <div class="col-md-12">
            <button class="btn btn-large btn-primary" id="show_instruct_video" ><i class="fa fa-youtube-play" aria-hidden="true"></i> Обучающие видео</button>
        </div>
    </div>
    <div class="modal_window_container" id="mw_container">
        <button type="button" class="close_btn" id="btn_close"><i class="fa fa-times fa-times-tar" aria-hidden="true"></i></button>
        <div class="modal_window" id="mw_videos">
            <div class="row center">
                <h6>Краткий обзор программы по построению и заказу натяжных потолков</h6>
                <div class="col-xs-12 col-md-12">
                    <iframe  src="https://www.youtube.com/embed/QADSjJMys8U" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                </div>
            </div>
        </div>
    </div>

</div>

<script type="text/javascript">
    jQuery(document).mouseup(function (e){ // событие клика по веб-документу
        var div = jQuery("#mw_videos"); // тут указываем ID элемента
        if (!div.is(e.target) // если клик был не по нашему блоку
            && div.has(e.target).length === 0) { // и не по его дочерним элементам
            div.hide();
            jQuery("#btn_close").hide();
            jQuery("#mw_container").hide();
            jQuery('iframe').attr('src', jQuery('iframe').attr('src'));
        }
    });
    jQuery(document).ready(function () {
        var client_id = '<?= $client_id;?>'
        jQuery("#precalc_btn").click(function(){
            create_project(client_id);
        });

        jQuery("#my_orders").click(function(){
            location.href = '/index.php?option=com_gm_ceiling&view=projects&type=client';
        });

        jQuery('#show_instruct_video').click(function(){
            jQuery('#mw_container').show();
            jQuery('#btn_close').show();
            jQuery('#mw_videos').show('slow');
        });


    });
    function create_precalculation(proj_id)
    {
        jQuery.ajax({
            type: 'POST',
            url: "/index.php?option=com_gm_ceiling&task=calculation.create_calculation",
            data: {
                proj_id: proj_id
            },
            success: function(data){
                console.log(data);
                location.href = '/index.php?option=com_gm_ceiling&view=calculationform2&type=client&calc_id='+data+'&precalculation=1';
            },
            error: function(data){
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

    function create_project(client_id){
        jQuery.ajax({
            type: 'POST',
            url: "index.php?option=com_gm_ceiling&task=create_empty_project",
            data: {
                client_id: client_id
            },
            success: function (data) {
                create_precalculation(data);
            },
            dataType: "text",
            timeout: 10000,
            error: function (data) {
                console.log(data);
                var n = noty({
                    theme: 'relax',
                    layout: 'center',
                    maxVisible: 5,
                    type: "error",
                    text: "Ошибка при создании заказа. Сервер не отвечает"
                });
            }
        });
    }
</script>
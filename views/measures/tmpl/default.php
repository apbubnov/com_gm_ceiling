<?php
/**
 * Created by PhpStorm.
 * User: bubnov
 * Date: 20.06.2019
 * Time: 16:39
 */
?>
<h3 align="center">График замеров и монтажей</h3>
<div class="row" style="margin-bottom:15px;">
    <div class="col-xs-6 col-md-6">
        <h4 align="center">Замеры</h4>
        <div id="measures_calendar" align="center"></div>
    </div>
    <div class="col-xs-6 col-md-6">
        <h4 align="center">Монтажи</h4>
        <div id="calendar_mount" align="center"></div>
    </div>
</div>
<div id="mw_container" class="modal_window_container">
    <button type="button" class="close_btn" id="close_mw"><i class="fa fa-times fa-times-tar" aria-hidden="true"></i></button>
    <div class="modal_window" id="mw_measures_calendar"></div>
    <div id="mw_mounts_calendar" class="modal_window"></div>
</div>

<script type="text/javascript" src="/components/com_gm_ceiling/date_picker/measures_calendar.js"></script>
<script type="text/javascript" src="/components/com_gm_ceiling/date_picker/mounts_calendar.js"></script>
<script type="text/javascript">
    init_measure_calendar('measures_calendar','jform_project_new_calc_date','jform_project_gauger','mw_measures_calendar',['close_mw','mw_container'], 'measure_info');
    init_mount_calendar('calendar_mount','mount','mw_mounts_calendar',['close_mw','mw_container']);

    jQuery(document).mouseup(function (e){// событие клика по веб-документу
        var div1 = jQuery("#mw_mounts_calendar");
        var div2 = jQuery("#mw_measures_calendar");
        if (!div1.is(e.target) // если клик был не по нашему блоку
            && div1.has(e.target).length === 0
            && !div2.is(e.target)
            && div2.has(e.target).length === 0) { // и не по его дочерним элементам
            jQuery("#close_mw").hide();
            jQuery("#mw_container").hide();
            div1.hide();
            div2.hide();
        }
    });
</script>

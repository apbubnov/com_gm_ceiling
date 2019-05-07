<?php
/**
 * Created by PhpStorm.
 * User: bubnov
 * Date: 25.04.2019
 * Time: 9:32
 */
?>

<div class = "start_page">
    <p class="center">
        <a class="btn btn-large btn-primary" href="<?php echo JRoute::_('/index.php?option=com_gm_ceiling&view=clients', false); ?>"><i class="fa fa-user" aria-hidden="true"></i> Клиенты</a>
    </p>
    <div style="margin-left: calc(50% - 100px); padding-bottom: 1em;">
        <div class="container-for-circl">
            <a class="btn btn-large btn-primary" href="<?php echo JRoute::_('/index.php?option=com_gm_ceiling&view=callback', false); ?>"><i class="fa fa-phone-square" aria-hidden="true"></i> Перезвоны</a>
            <div class="circl-digits" id="ZvonkiDiv" style="display: none;"></div>
        </div>
    </div>
    <p class="center">
        <a class="btn btn-large btn-primary" href="<?php echo JRoute::_('/index.php?option=com_gm_ceiling&view=analytics', false); ?>"><i class="fa fa-bar-chart" aria-hidden="true"></i> Аналитика</a>
    </p>
</div>


<script>

    jQuery(document).ready(function () {

        jQuery.ajax({
            type: 'POST',
            url: "index.php?option=com_gm_ceiling&task=printZvonkiOnGmMainPage",
            async: true,
            success: function (data) {
                if (data != null) {
                    if (data[0].count != 0) {
                        document.getElementById('ZvonkiDiv').innerHTML = data[0].count;
                        document.getElementById('ZvonkiDiv').style.display = 'block';
                    }
                }
            },
            dataType: "json",
            timeout: 30000,
            error: function (data) {
                console.log(data);
                var n = noty({
                    timeout: 2000,
                    theme: 'relax',
                    layout: 'center',
                    maxVisible: 5,
                    type: "error",
                    text: "Сервер не отвечает."
                });
            }
        });
    });
</script>
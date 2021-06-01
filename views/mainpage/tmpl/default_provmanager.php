<?php
/**
 * Created by PhpStorm.
 * User: bubnov
 * Date: 25.04.2019
 * Time: 9:32
 */
?>
<style>
    .row{
        margin-bottom: 1em !important;
    }
</style>
<div class = "start_page">
    <div class="row center">
        <a class="btn btn-large btn-primary" href="<?php echo JRoute::_('/index.php?option=com_gm_ceiling&view=providers', false); ?>"><i class="fa fa-user" aria-hidden="true"></i> Производители</a>
    </div>
    <div class="row center">
        <a class="btn btn-large btn-primary" href="<?php echo JRoute::_('/index.php?option=com_gm_ceiling&view=callback', false); ?>">
            <div style="position:relative;">
                <div>
                    <i class="fa fa-phone-square" aria-hidden="true"></i> Перезвоны
                </div>
                <div class="circl-digits" id="ZvonkiDiv" style="display: none;"></div>
            </div>
        </a>
    </div>
    <div class="row center">
        <a href="<?php echo JRoute::_('index.php?option=com_gm_ceiling&view=clients&type=labels', false, 2); ?>" class="btn btn-large btn-primary"><i class="fa fa-tags"></i> Ярлыки</a>
    </div>
</div>


<script>

    jQuery(document).ready(function () {

        jQuery.ajax({
            type: 'POST',
            url: "index.php?option=com_gm_ceiling&task=callback.getCallsCount",
            async: true,
            success: function (data) {
                if (!empty(data)) {
                    document.getElementById('ZvonkiDiv').innerHTML = data;
                    document.getElementById('ZvonkiDiv').style.display = 'block';
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
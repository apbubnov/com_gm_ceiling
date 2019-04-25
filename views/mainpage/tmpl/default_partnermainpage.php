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
</div>

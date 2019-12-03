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
<div class="start_page">
    <p class="center">
        <button class="btn btn-large btn-primary" id="precalc_btn" ><i class="fas fa-calculator"></i> Рассчитать</button>
    </p>
    <p class="center">
        <button class="btn btn-large btn-primary" id="my_orders" ><i class="fas fa-list"></i> Мои заказы</button>
    </p>
</div>

<script type="text/javascript">
    jQuery(document).ready(function () {
        var client_id = '<?= $client_id;?>'
        jQuery("#precalc_btn").click(function(){
            create_project(client_id);
        });

        jQuery("#my_orders").click(function(){
           location.href = '/index.php?option=com_gm_ceiling&view=projects&type=client';
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
                location.href = '/index.php?option=com_gm_ceiling&view=calculationform&type=client&calc_id='+data+'&precalculation=1';
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
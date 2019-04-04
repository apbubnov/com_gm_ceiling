<?php
/**
 * Created by PhpStorm.
 * User: bubnov
 * Date: 18.03.2019
 * Time: 14:56
 */
$clients_model = Gm_ceilingHelpersGm_ceiling::getModel('clients');
$result_clients = $clients_model->getDesignersByClientName('', 7);
?>
<h4 class="center">Объекты</h4>
<table class="table table-striped one-touch-view" id="callbacksList">
    <thead>
    <tr>
        <th class="center">
            Название
        </th>
    </tr>
    </thead>
    <tbody id="tbody_builders">
    <?php
    foreach ($result_clients as $key => $value)
    {
        if ($value->refused_to_cooperate == 0)
        {
            ?>
            <tr class="row center" data-href="<?php echo JRoute::_('index.php?option=com_gm_ceiling&view=clientcard&type=builder&subtype=mounter&id='.(int) $value->id); ?>">
                <td class="center">
                    <?php echo $value->client_name; ?>
                </td>
            </tr>
            <?php
        }
    }
    ?>
    </tbody>
</table>
<script>
    jQuery(document).ready(function() {
        jQuery('body').on('click', 'tr', function (e) {
            if (jQuery(this).data('href') != "") {
                document.location.href = jQuery(this).data('href');
            }
        });
    });
</script>
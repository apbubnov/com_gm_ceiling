<?php
?>
<?=parent::getButtonBack();
?>

<table class="table table-striped one-touch-view table_cashbox " id="projectList">
    <thead>
    <tr>
        <th class='center' width=5%>
            №
        </th>
        <th class='center' width=20%>
            Желаемая дата монтажа
        </th>
        <th class='center' width=40%>
            Адрес
        </th>
        <th class='center' width=10%>
            Телефон
        </th>
        <th class='center' width=14%>
            Дилер
        </th>
        <th class='center' width=14%>
            Статус
        </th>
        <th>
            <i class="fa fa-trash-o" aria-hidden="true"></i>
        </th>
    </tr>
    </thead>
    <tbody>
    <?php foreach($this->items as $item){
        $flag = false;
        $dealer = JFactory::getUser($item->dealer_id);
        $mounters = explode(',',$item->project_mounter);
        foreach($mounters as $mounter_id){
            $mounter = JFactory::getUser($mounter_id);
            if(in_array('26',$mounter->groups)){
                $flag = true;
                break;
            }
        }
        if($flag){
            if($item->project_status == 30){
                $style = 'style = "background: linear-gradient( to right, white 50%, red 100%);"';
            }
            else{
                $style = "";
            }
            ?>
            <tr data-id = "<?php echo $item->id;?>" style = "cursor: pointer;" data-href="<?= JRoute::_('index.php?option=com_gm_ceiling&view=project&type=gmchief&subtype=service&id=' . $item->id); ?>">
                <td class='center one-touch'>
                    <?php echo $item->id;?>
                </td>
                <td class='center one-touch'>
                    <?php echo str_replace(',', ',<br>',$item->project_mounting_date);?>
                </td>
                <td class="one-touch">
                    <?php echo $item->project_info;?>
                </td>

                <td class='center one-touch'>
                    <?php echo $dealer->username;?>
                </td>
                <td class='center one-touch'>
                    <?php echo $dealer->name; ?>
                </td>
                <td class='center one-touch'  <?= $style; ?>>
                    <?php echo $item->status; ?>
                </td>
                <td>
                    <button class="btn btn-danger delete"><i class="fa fa-trash-o" aria-hidden="true"></i></button>
                </td>
            </tr>
        <?php }
    }?>
    </tbody>
</table>
<script type="text/javascript">
    jQuery(document).ready(function(){
        jQuery(".delete").click(function(){
            var row = jQuery(this).closest('td').parent();
            var id = row.data('id');
            jQuery.ajax({
                type: 'POST',
                url: "index.php?option=com_gm_ceiling&task=project.removeService",
                data: {
                    project_id: id
                },
                dataType: "json",
                success: function (data) {
                    row.remove();
                    noty({
                        timeout: 2000,
                        theme: 'relax',
                        layout: 'center',
                        maxVisible: 5,
                        type: "success",
                        text: "Удалено!"
                    });
                },
                timeout: 10000,
                error: function (data) {
                    noty({
                        timeout: 2000,
                        theme: 'relax',
                        layout: 'center',
                        maxVisible: 5,
                        type: "error",
                        text: "Ошибка!"
                    });
                }
            });
            return false;
        });
    });
</script>
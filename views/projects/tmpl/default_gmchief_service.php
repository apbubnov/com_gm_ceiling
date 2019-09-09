<?php
?>
<?=parent::getButtonBack();
$service_projects = [];
foreach($this->items as $item) {
    $mount_data = json_decode($item->mount_data);
    $date_times = [];
    foreach($mount_data as $mount_date){
        $mounter_groups = JFactory::getUser($mount_date->mounter_id)->groups;
        if(in_array('26',$mounter_groups) && !in_array($mount_date->date_time,$date_times)){
            $date_times[] = $mount_date->date_time;
        }
    }
    if(!empty($date_times)){
        $item->mount_dates = implode(',',$date_times);
        $service_projects[] = $item;
    }
}
//throw new Exception(print_r($service_projects,true));
usort($service_projects,function ($project1,$project2){
    $date1 = new DateTime(explode(',',$project1->mount_dates)[0]);
    $date2 = new DateTime(explode(',',$project2->mount_dates)[0]);
    if($date1 == $date2){
        return 0;
    }
    return $date1 > $date2 ? -1 : 1;
});
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
            <i class="fas fa-trash-alt" aria-hidden="true"></i>
        </th>
    </tr>
    </thead>
    <tbody>
    <?php foreach($service_projects as $item){
        //$flag = false;
        $dealer = JFactory::getUser($item->dealer_id);
       /* $mounters = explode(',',$item->project_mounter);
        foreach($mounters as $mounter_id){
            $mounter = JFactory::getUser($mounter_id);
            if(in_array('26',$mounter->groups)){
                $flag = true;
                break;
            }
        }
        if($flag){*/
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
                    <?php echo str_replace(',', ',<br>',$item->mount_dates);?>
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
                    <button class="btn btn-danger delete"><i class="fas fa-trash-alt" aria-hidden="true"></i></button>
                </td>
            </tr>
        <?php //}
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
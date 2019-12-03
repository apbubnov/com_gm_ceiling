<?php
/**
 * Created by PhpStorm.
 * User: bubnov
 * Date: 26.11.2019
 * Time: 15:59
 */
	$user = JFactory::getUser();

?>
<table id = "report_table" class="table table-striped table_cashbox one-touch-view">
	<tbody>

    </tbody>
</table>
<script type="text/javascript">
	jQuery(document).ready(function(){
		getBuilderCommonData();
        jQuery('.project_id').click(function(){
            var project_id = jQuery(this).data('id');
            location.href = 'index.php?option=com_gm_ceiling&view=project&type=calculator&subtype=project&id='+ +project_id;
        });
	});

	function getBuilderCommonData(){
            jQuery.ajax({
                url: "index.php?option=com_gm_ceiling&task=clients.getBuilderCommonData",
                data: {
                    builderId: '<?php echo $user->id; ?>'
                },
                dataType: "json",
                async: false,
                success: function(data) {
                    console.log("common",data);
                    fillCommonTab(data);
                },
                error: function(data) {
                    var n = noty({
                        timeout: 2000,
                        theme: 'relax',
                        layout: 'center',
                        maxVisible: 5,
                        type: "error",
                        text: "Ошибка получения данных"
                    });
                }
            });
        }
	function fillCommonTab(data){
            jQuery('#report_table > tbody').empty();
            jQuery.each(data,function(index,elem){
                jQuery('#report_table > tbody').append('<tr></tr>');
                jQuery('#report_table > tbody > tr:last').append('<td>' +
                                                                    '<div class="row"><b>'+elem.client_name+'</b></div>' +
                                                                    '<div class="row"><b>S=</b>'+elem.square+'</div>' +
                                                                    '<div class="row"><b>P=</b>'+elem.perimeter+'</div>' +
                                                                 '</td>');
                var projects = JSON.parse(elem.projects);
                jQuery.each(projects,function(ind,project){
                    jQuery('#report_table > tbody > tr:last').append('<td>' +
                                                                        '<div class="row project_id" data-id="'+project.project_id+'"><b>'+project.name+'</b></div>' +
                                                                        '<div class="row"> <b>Статус:</b> '+project.status+'</div>' +
                                                                     '</td>');
                });
            });

        }
</script>
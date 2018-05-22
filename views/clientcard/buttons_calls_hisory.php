<?php if(array_search('16', $user_group)) { ?>
<div>
    <button class="btn btn-primary" id="btn_outcoming_bad" type="button">Исходящий недозвон</button>
    <button class="btn btn-primary" id="btn_outcoming_good" type="button">Исходящий дозвон</button>
    <button class="btn btn-primary" id="btn_incoming" type="button">Входящий звонок</button>
</div>
<?php } ?>
<script type="text/javascript">
	function addCallHistory(status){
		jQuery.ajax({
            type: 'POST',
            url: "index.php?option=com_gm_ceiling&task=addCallHistory",
            data: {	
                client_id: <?php echo $this->item->id; ?>,
                status: status
            },
            success: function(data){
                location.reload();
            },
            dataType: "json",
            timeout: 10000,
            error: function(data){
                console.log(data);
                var n = noty({
                    theme: 'relax',
                    timeout: 2000,
                    layout: 'center',
                    maxVisible: 5,
                    type: "error",
                    text: "Ошибка!"
                });
            }				
        });
	}
	document.getElementById('btn_outcoming_bad').onclick = function() {
		addCallHistory(1);
	};
	document.getElementById('btn_outcoming_good').onclick = function() {
		addCallHistory(2);
	};
	document.getElementById('btn_incoming').onclick = function() {
		addCallHistory(3);
	};
</script>
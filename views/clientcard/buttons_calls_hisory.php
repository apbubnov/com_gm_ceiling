<?php if (array_search('16', $user_group) || array_search('13', $user_group)) { ?>
<div class="row">
    <div class="col-md-2"></div>
	<div class="col-md-5">
        <select id="select_call_status" class="form-control">
            <option value="1">Исходящий недозвон</option>
            <option value="2">Исходящий дозвон</option>
            <option value="3">Входящий звонок</option>
            <option value="4">Презентация</option>
            <option value="5">Лид</option>
        </select>
    </div>
    <div class="col-md-3">
        <button class="btn btn-primary" id="btn_addCallHistory" type="button">Добавить статус</button>
    </div>
    <div class="col-md-2"></div>
</div>

<script type="text/javascript">
	function addCallHistory(status) {
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
            error: function(data) {
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
	document.getElementById('btn_addCallHistory').onclick = function() {
        var status = document.getElementById('select_call_status').value;
		addCallHistory(status);
	};
</script>
<?php } ?>
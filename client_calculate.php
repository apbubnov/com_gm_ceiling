<script src="http://code.jquery.com/jquery-1.8.3.js"></script>
<script type="text/javascript">
		jQuery.ajax({
        type: 'POST',
        url: "/index.php?option=com_gm_ceiling&task=calculation.create_calculation",
        data: {
            proj_id: 1
        },
        success: function(data){
        	data = data.replace(/\"/g,'');
			console.log(data);
			var url_type = '';
			var url_subtype = '';
			if (url_getparams['type'] != undefined)
			{
				url_type = `&type=${url_getparams['type']}`;
			}
			if (url_getparams['subtype'] != undefined)
			{
				url_subtype = `&subtype=${url_getparams['subtype']}`;
			}
			location.href = `/index.php?option=com_gm_ceiling&view=calculationform2&calc_id=${data-0}&api=1`;
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
</script>
var url_getparams = window
		    .location
		    .search
		    .replace('?','')
		    .split('&')
		    .reduce(
		        function(p,e){
		            var a = e.split('=');
		            p[ decodeURIComponent(a[0])] = decodeURIComponent(a[1]);
		            return p;
		        },
		        {}
		    );

function create_calculation(proj_id)
{
	jQuery.ajax({
        type: 'POST',
        url: "/index.php?option=com_gm_ceiling&task=calculation.create_calculation",
        data: {
            proj_id: proj_id
        },
        success: function(data){
        	data = data.replace(/\"/g,'');
			console.log(data);
			var url_type = '';
			var url_subtype = '';
			var url_api = '';
			if (url_getparams['type'] != undefined)
			{
				url_type = `&type=${url_getparams['type']}`;
			}
			if (url_getparams['subtype'] != undefined)
			{
				url_subtype = `&subtype=${url_getparams['subtype']}`;
			}
			if(jQuery("#client").prop('checked')){
            	url_api = '&api=1'
        	}
			location.href = `/index.php?option=com_gm_ceiling&view=calculationform${url_type}${url_subtype}${url_api}&calc_id=${data-0}`;
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
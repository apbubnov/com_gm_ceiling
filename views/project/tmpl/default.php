<?php
/**
 * @version    CVS: 0.1.7
 * @package    Com_Gm_ceiling
 * @author     SpectralEye <Xander@spectraleye.ru>
 * @copyright  2016 SpectralEye
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access
//defined('_JEXEC') or die;

?>

<script>
	jQuery(document).ready(function(){

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

		create_calculation(<?php echo $this->item->id; ?>);

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
					if (url_getparams['type'] != undefined)
					{
						url_type = `&type=${url_getparams['type']}`;
					}
					if (url_getparams['subtype'] != undefined)
					{
						url_subtype = `&subtype=${url_getparams['subtype']}`;
					}
					location.href = `/index.php?option=com_gm_ceiling&view=calculationform${url_type}${url_subtype}&calc_id=${data-0}&api=1`;
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
	});
</script>

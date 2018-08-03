<?php
/**
 * @version    CVS: 0.1.7
 * @package    Com_Gm_ceiling
 * @author     SpectralEye <Xander@spectraleye.ru>
 * @copyright  2016 SpectralEye
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access
defined('_JEXEC') or die;

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');
JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.multiselect');
JHtml::_('formbehavior.chosen', 'select');

$user = JFactory::getUser();
$analytic_model = Gm_ceilingHelpersGm_ceiling::getModel('analytic_new');
$data = json_encode($analytic_model->getData($user->dealer_id));


echo parent::getButtonBack();

?>
<div class="container">
    <div class="row ">
        <div class="col-md-12">
            <table id = "analytic_common" class="analitic-table">
                <thead id = "thead" class = "caption-style-tar">
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>        
    </div>
</div>


<script type="text/javascript">
    jQuery(document).ready(function(){
        var data = JSON.parse('<?php echo $data?>');
        makeTh(jQuery("#analytic_common > thead"),data[0]);
        data.shift();
        fill_table(data);   
        console.log(data);
    });
    
    function makeTh(container, data) {
        var row = jQuery("<tr/>");
        container.empty();
        jQuery.each(data, function(key, value) { 
            row.append(jQuery("<th/ data-value = '"+key+"'>").text(value));
        });
        container.append(row);
    }

    function fill_table(data){
        var ths = jQuery("#analytic_common > thead  th"),key ="",total = [];
        jQuery('#analytic_common tbody').empty();
        for(let i = 0;i<data.length;i++){
            jQuery('#analytic_common').append('<tr></tr>');
            jQuery.each(ths,function(index,item){
                key = jQuery(item).data('value');
                let val = (data[i][key] ? data[i][key] : 0); 
                jQuery('#analytic_common > tbody > tr:last').append('<td>'+ val +'</td>');
                if(key == 'advt_title'){
                    total[key] = '<b>Итого</b>';
                }
                else{
                    total[key] = (total[key]) ? total[key] + val : val;
                }
                
            });
            
        }
        if(Object.keys(total).length){
            jQuery('#analytic_common').append('<tr></tr>');
            jQuery.each(ths,function(index,item){
                key = jQuery(item).data('value');
                jQuery('#analytic_common > tbody > tr:last').append('<td><b>'+  total[key] +'</b></td>');
            });
        }
    }
    
</script>
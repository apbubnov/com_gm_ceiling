<?php
/**
 * @version    CVS: 0.1.7
 * @package    Com_Gm_ceiling
 * @author     bubnov <2017 al.p.bubnov@gmail.com>
 * @copyright  2017 al.p.bubnov@gmail.com
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access
defined('_JEXEC') or die;

$user       = JFactory::getUser();
$userId     = $user->get('id');
$user_group = $user->groups;
?>

<form>
    <?=parent::getButtonBack();?>
    <input type="date" id="calendar" value="<?php echo date('Y-m-d');?>">
    <table class="table table-striped one-touch-view g_table" id="callbacksList">
        <thead>
        <tr>
            <th>
               ФИО клиента
            </th>
            <th>
               Дата
            </th>
            <th>
               Примечание
            </th>
            <th>
            </th>
        </tr>
        </thead>

        <tbody id="table_body">
        </tbody>
    </table>
</form>

<script>
    var del_click_bool = false;

    var arr_calls = [];
    var table_body_elem = document.getElementById('table_body');
    var user_id = <?php echo $userId; ?>;
    function show_calls()
    {
        var str;
        var calendar_elem_value = document.getElementById('calendar').value;
        table_body_elem.innerHTML = "";
        for (var i = 0; i < arr_calls.length; i++)
        {
                str = '';
                if (arr_calls[i].dealer_type == 3)
                {
                    str += '<tr data-href="index.php?option=com_gm_ceiling&view=clientcard&type=designer&id='+(arr_calls[i].client_id-0)+'&call_id='+(arr_calls[i].id-0)+'">';
                }
                else if (arr_calls[i].dealer_type == 5)
                {
                    str += '<tr data-href="index.php?option=com_gm_ceiling&view=clientcard&type=designer2&id='+(arr_calls[i].client_id-0)+'&call_id='+(arr_calls[i].id-0)+'">';
                }
                else if (arr_calls[i].dealer_type == 0 || arr_calls[i].dealer_type == 1)
                {
                    str += '<tr data-href="index.php?option=com_gm_ceiling&view=clientcard&type=dealer&id='+(arr_calls[i].client_id-0)+'&call_id='+(arr_calls[i].id-0)+'">';
                }
                else
                {
                    str += '<tr data-href="index.php?option=com_gm_ceiling&view=clientcard&id='+(arr_calls[i].client_id-0)+'&call_id='+(arr_calls[i].id-0)+'">';
                }
                str += '<td>'+arr_calls[i].client_name+'</td>';
                str += '<td>'+arr_calls[i].date_time+'</td>';
                str += '<td>'+arr_calls[i].comment+'</td>';
                str += '<td><button class="btn btn-danger btn-sm" type="button" onclick="del_call('+arr_calls[i].id+')"><i class="fa fa-trash" aria-hidden="true"></i></button></td>';
                str += '</tr>';
                table_body_elem.innerHTML += str;
        }
    }

    function getData(){
        jQuery.ajax({
            type: 'POST',
            url: "index.php?option=com_gm_ceiling&task=callback.getData",
            data: {
                userId: user_id,
                date: jQuery('#calendar').val()
            },
            success: function(data){
                console.log(12312);
                arr_calls = data;
                show_calls();
            },
            dataType:"json",
            timeout: 10000,
            error: function(data){
                var n = noty({
                    timeout: 2000,
                    theme: 'relax',
                    layout: 'center',
                    maxVisible: 5,
                    type: "error",
                    text: "Ошибка!"
                });
            }
        });
    }
    jQuery(document).ready(function()
    {

        getData();

        document.getElementById('calendar').onchange = function(){
            getData();
        };

        jQuery('body').on('click', 'tr', function(e)
        {
            if (jQuery(this).data('href') !== undefined && !del_click_bool)
            {
                document.location.href = jQuery(this).data('href');
            }
            del_click_bool = false;
        });
    });

    function del_call(id)
    {
        del_click_bool = true;

        jQuery.ajax({
            type: 'POST',
            url: "index.php?option=com_gm_ceiling&task=delCall",
            data: {
                call_id: id
            },
            success: function(data){
                for (var i = arr_calls.length; i--;)
                {
                    if (arr_calls[i].id == id)
                    {
                        arr_calls.splice(i, 1);
                    }
                }
                show_calls();
            },
            timeout: 10000,
            error: function(data){
                var n = noty({
                    timeout: 2000,
                    theme: 'relax',
                    layout: 'center',
                    maxVisible: 5,
                    type: "error",
                    text: "Ошибка!"
                });
            }
        });
    }
</script>
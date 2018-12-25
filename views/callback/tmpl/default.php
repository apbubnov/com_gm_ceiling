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
            <th>ФИО клиента</th>
            <th>Дата</th>
            <th>Примечание</th>
            <th></th>
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
    function show_calls() {
        var calendar_elem_value = document.getElementById('calendar').value;
        table_body_elem.innerHTML = '';
        for (var i = 0; i < arr_calls.length; i++) {
            setTimeout(printTr, 0, arr_calls[i]);
        }

        function printTr(arr_calls_i) {
            var str, tr, td;
            if (arr_calls_i.dealer_type !== null) {
                arr_calls_i.dealer_type = arr_calls_i.dealer_type-0;
            }
            tr = table_body_elem.insertRow();
            tr.setAttribute('data-callId', arr_calls_i.id-0);
            tr.setAttribute('data-dealerType', arr_calls_i.dealer_type);
            tr.setAttribute('data-clientId', arr_calls_i.client_id-0);
            td = tr.insertCell();
            td.innerHTML = arr_calls_i.client_name;
            td = tr.insertCell();
            td.innerHTML = arr_calls_i.date_time;
            td = tr.insertCell();
            td.innerHTML = arr_calls_i.comment;
            td = tr.insertCell();
            td.innerHTML = '<button class="btn btn-danger btn-sm" type="button" data-id="'+arr_calls_i.id+'"><i class="fa fa-trash" aria-hidden="true"></i></button>';
            td.getElementsByTagName('button')[0].onclick = del_call;
            tr.onclick = clickTr;
        }
    }

    function getData() {
        jQuery.ajax({
            type: 'POST',
            url: "index.php?option=com_gm_ceiling&task=callback.getData",
            data: {
                userId: user_id,
                date: jQuery('#calendar').val()
            },
            success: function(data){
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

    function clickTr(e) {
        if (this.getAttribute('data-clientId') && !del_click_bool) {
            var type = this.getAttribute('data-dealerType')-0;
            var url;
            if (type === 3) {
                url = 'index.php?option=com_gm_ceiling&view=clientcard&type=designer&id=';
            } else if (type === 5) {
                url = 'index.php?option=com_gm_ceiling&view=clientcard&type=designer2&id=';
            } else if (type === 0 || type === 1) {
                url = 'index.php?option=com_gm_ceiling&view=clientcard&type=dealer&id=';
            } else {
                url = 'index.php?option=com_gm_ceiling&view=clientcard&id=';
            }
            document.location.href = url+(this.getAttribute('data-clientId')-0)+'&call_id='+(this.getAttribute('data-callId')-0);
        }
        del_click_bool = false;
    }

    jQuery(document).ready(function() {
        getData();

        document.getElementById('calendar').onchange = function(){
            getData();
        };
    });

    function del_call() {
        var id = this.getAttribute('data-id')-0;
        del_click_bool = true;

        jQuery.ajax({
            type: 'POST',
            url: "index.php?option=com_gm_ceiling&task=delCall",
            data: {
                call_id: id
            },
            success: function(data){
                for (var i = arr_calls.length; i--;) {
                    if (arr_calls[i].id == id) {
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
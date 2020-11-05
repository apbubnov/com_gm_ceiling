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

$clients_model = Gm_ceilingHelpersGm_ceiling::getModel('clients');
$labels = $clients_model->getClientsLabels($user->dealer_id);
$isGauger = in_array('21',$user_group);
$checked = $isGauger ? 'checked' : '';
?>

<style type="text/css">
    .table {
        border-collapse: separate;
        border-spacing: 0 0.5em;
    }
    th {
        text-align: center;
    }
</style>

<form>
    <div class="row">
        <div class="col-md-2 col-xs-12" style="margin-bottom: 10px;">
            <?=parent::getButtonBack();?>
        </div>
    </div>
    <div class="row">
        <div class="col-md-3 col-xs-6">
            <input class="form-control" type="date" id="calendar" value="<?php echo date('Y-m-d');?>">
        </div>
        <div class="col-md-3 col-xs-6">
            <select class="wide cust-select" id="select_label">
                <option value="" selected>Ярлыки</option>
                <?php foreach($labels as $label): ?>
                    <option value="<?= $label->id; ?>"><?= $label->title; ?></option>
                <?php endforeach;?>
            </select>
            <div class="nice-select wide" tabindex="0">
                <span class="current">Ярлыки</span>
                <ul class="list">
                    <li class="option" data-value="" data-color="#ffffff" style="--rcolor:#ffffff" data-display="Ярлыки">Ярлыки</li>
                    <?php foreach($labels as $label): ?>
                        <li class="option" data-value="<?= $label->id; ?>" data-color="#<?= $label->color_code; ?>" style="--rcolor:#<?= $label->color_code; ?>"><?= $label->title; ?></li>
                    <?php endforeach;?>
                </ul>
            </div>
        </div>
        <div class="col-md-3 col-xs-6">
            <select class="form-control" id="select_type">
                <option value="0">Все типы</option>
                <option value="1">Новые</option>
                <option value="2">Отказы от договора</option>
            </select>
        </div>
        <div class="col-md-3 col-xs-6">
            <div class="form-control">
                <input type="checkbox" id="only_self" class="inp-cbx dup" <?=$checked?> style="display: none">
                <label for="only_self" class="cbx">
                    <span>
                        <svg width="12px" height="10px" viewBox="0 0 12 10">
                            <polyline points="1.5 6 4.5 9 10.5 1"></polyline>
                        </svg>
                    </span>
                    <span>Только свои</span>
                </label>
            </div>
        </div>
    </div>
    
    <table class="table one-touch-view g_table" id="callbacksList">
        <thead>
        <tr>
            <th>ФИО клиента</th>
            <th>Дата</th>
            <th>Примечание</th>
            <th>Менеджер</th>
            <th></th>
        </tr>
        </thead>
        <tbody id="table_body">
        </tbody>
    </table>
</form>

<script>
    var del_click_bool = false,
        arr_calls = [],
        table_body_elem = document.getElementById('table_body'),
        user_id = <?php echo $userId; ?>,
        timeouts = [],
        savedData;
    function show_calls() {
        timeouts = [];
        var calendar_elem_value = document.getElementById('calendar').value;
        table_body_elem.innerHTML = '';
        var scrollTrIndex = !empty(savedData) ? savedData.trIndex : 0;
        if (!scrollTrIndex) {
            scrollTrIndex = 0;
        }
        for (var i = 0, j; i < arr_calls.length; i += 50) {
            if (i + 50 > arr_calls.length) {
                j = arr_calls.length;
            } else {
                j = i + 50;
            }
            timeouts.push(setTimeout(printGroupTr, 0, i, j));
        }
        reduceGTable();

        function printGroupTr(i, j) {
            for (var k = i; k < j; k++) {
                printTr(arr_calls[k], k, scrollTrIndex);
            }
        }

        function printTr(arr_calls_i, i, scrollTrIndex) {
            var str, tr, td,client_name,
                important = '<i class="fas fa-exclamation-circle" style="color:red;"></i>';
            if (arr_calls_i.dealer_type !== null) {
                arr_calls_i.dealer_type = arr_calls_i.dealer_type-0;
            }
            tr = table_body_elem.insertRow();
            //tr.setAttribute('id', 'trCall'+arr_calls_i.id);
            tr.setAttribute('data-callId', arr_calls_i.id-0);
            tr.setAttribute('data-dealerType', arr_calls_i.dealer_type);
            tr.setAttribute('data-clientId', arr_calls_i.client_id-0);
            td = tr.insertCell();
            console.log(arr_calls_i);
            client_name = (arr_calls_i.important == 1) ? important + ' ' + arr_calls_i.client_name : arr_calls_i.client_name;
            td.innerHTML = client_name;
            td = tr.insertCell();
            td.innerHTML = arr_calls_i.date_time;
            td = tr.insertCell();
            td.innerHTML = arr_calls_i.comment;
            td = tr.insertCell();
            td.innerHTML = arr_calls_i.manager_name;
            td = tr.insertCell();
            td.innerHTML = '<button class="btn btn-danger btn-sm" type="button" data-id="'+arr_calls_i.id+'"><i class="fa fa-trash" aria-hidden="true"></i></button>';
            td.getElementsByTagName('button')[0].onclick = del_call;
            if (arr_calls_i.label_color !== null) {
                jQuery(tr).css('outline', '#'+arr_calls_i.label_color+' solid 2px');
            }
            jQuery(tr).css('margin-top', '10px');
            tr.onclick = clickTr;
            if (scrollTrIndex !== 0 && scrollTrIndex < i) {
                setTimeout(scrollToSavedTrIndex, 100);
            }
        }
    }

    function getData() {
        var onlySelf = jQuery('#only_self').is(':checked') ? 1 : 0;
        jQuery.ajax({
            type: 'POST',
            url: "index.php?option=com_gm_ceiling&task=callback.getData",
            data: {
                date: jQuery('#calendar').val(),
                label_id: jQuery('#select_label').val(),
                type: jQuery('#select_type').val(),
                only: onlySelf
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
            var prevTrIndex = jQuery(this)[0].rowIndex - 2;
            console.log(prevTrIndex);
            if(prevTrIndex<0){
                prevTrIndex = 0;
            }
           // if (prevTrIndex > 0) {
            localStorage.setItem('savedData',JSON.stringify({
                trIndex: prevTrIndex,
                selectedDate: jQuery('#calendar').val(),
                label: jQuery('#select_label').val(),
                type: jQuery('#select_type').val(),
                self: jQuery('#only_self').is(':checked')
            }));
          //  }
            
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

    function scrollToSavedTrIndex() {
        if(!empty(savedData)) {
            var scrollTrIndex = savedData.trIndex;
            if (scrollTrIndex) {
                var need_row = jQuery(table_body_elem).find('tr').eq(scrollTrIndex);
                jQuery('html, body').animate({
                    scrollTop: jQuery(need_row).offset().top
                }, 500);
            }
        }
        savedData = [];
    }

    jQuery(document).ready(function() {
        savedData = JSON.parse(localStorage.getItem('savedData'));
        localStorage.removeItem('savedData');
        if(!empty(savedData)) {
            if(!empty(savedData.selectedDate)){
                jQuery('#calendar').val(savedData.selectedDate);
            }
            if(!empty(savedData.label)) {
                jQuery('#select_label').val(savedData.label);
                jQuery.each(jQuery('li.option'),function(index,option){
                    if(jQuery(option).data('value') == savedData.label){
                        jQuery(option).addClass('selected');
                        var color = (jQuery(".option.selected").data("color"));
                        jQuery('.nice-select.wide')[0].style.setProperty('--rcolor', color);
                        jQuery('.current').text(option.innerText);
                        return;
                    }
                });
            }
            if(!empty(savedData.type)){
                jQuery('#select_type').val(savedData.type);
            }
            if(!empty(savedData.self)){
                jQuery('#only_self').attr('checked','checked');
            }
            else{
                jQuery('#only_self').attr('checked',false);
            }
        }
        getData();
        document.getElementById('calendar').onchange = function(){
            getData();
        };

        jQuery('#select_label').niceSelect();
        jQuery("#select_label").change(function() {
            var color = (jQuery(".option.selected").data("color"));
            jQuery('.nice-select.wide')[0].style.setProperty('--rcolor', color);
            getData();
        });

        jQuery("#select_type").change(function() {
            getData();
        });

        jQuery('#only_self').change(function (){
            getData();
        });
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
                jQuery('tr[data-callid="'+id+'"]').remove();
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
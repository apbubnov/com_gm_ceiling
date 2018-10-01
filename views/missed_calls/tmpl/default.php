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

$api_phone_model = Gm_ceilingHelpersGm_ceiling::getModel('api_phones');
$phones = $api_phone_model->getArrayNumbers();
$items = json_encode($this->item);
?>
<form>
    <div id="preloader" class="PRELOADER_GM PRELOADER_GM_OPACITY">
        <div class="PRELOADER_BLOCK"></div>
        <img src="images/GM_R_HD.png" class="PRELOADER_IMG">
    </div>
    <a class="btn btn-large btn-primary"
       href="/index.php?option=com_gm_ceiling&view=mainpage&type=gmmanagermainpage"
       id="back"><i class="fa fa-arrow-left" aria-hidden="true"></i> Назад</a>
    <input type="date" id="calendar" value="<?php echo date('Y-m-d');?>">
    <br>
    <div id = "empty" style="display:none;">
        <label>Нет пропущенных звонков!</label>
    </div>
    <table class="table table-striped one-touch-view" id="callbacksList">
        <thead>
        <tr>
            <th>
               Номер/Клиент
            </th>
            <th>
                Реклама
            </th>
            <th>
               Вызываемый
            </th>
            <th>
               Дата-Время
            </th>
            <th>
            </th>
        </tr>
        </thead>
        <tbody>
        </tbody>
    </table>
</form>

<script>
    var obr_calls = JSON.parse('<?php echo $items; ?>');
    console.log(obr_calls);
    var table_body_elem = document.getElementById('table_body');
    var user_id = <?php echo $userId; ?>;
    var phones = <?php echo json_encode($phones);?>;
    var api_phone_id;
    console.log(phones);
    var client;
    function getClient(phone){
        jQuery.ajax({
            type: 'POST',
            url: "index.php?option=com_gm_ceiling&task=getClientByPhone",
            async: false,
            data: {
                phone: phone
            },
            success: function(data){
                client = data;
            },
            dataType: "json",
            timeout: 20000,
            error: function(data){
                console.log(data);
                var n = noty({
                    timeout: 2000,
                    theme: 'relax',
                    layout: 'center',
                    maxVisible: 5,
                    type: "error",
                    text: "Сервер не отвечает."
                });
            }                   
        });
    }
    function show_calls(data)
    {
        var call_bool = false;
        jQuery("#empty").hide();
        jQuery('#callbacksList').show();
        jQuery('#callbacksList tbody').empty();
         for(var i=0;i<data.length;i++) {
            call_bool = false;
            for(var key in obr_calls)
            {
                if(data[i].id == obr_calls[key].call_id)
                {
                    call_bool = true;
                    break;
                } 
            }
            if (!call_bool)
            {
                jQuery('#callbacksList').append('<tr data-href=""></tr>');
                for(var j=0;j<Object.keys(data[i]).length;j++){
                    if(Object.keys(data[i])[j]=='businessNumber'){
                        data[i][Object.keys(data[i])[j]] = phones[data[i][Object.keys(data[i])[j]].replace('+','')];
                    }
                    if(Object.keys(data[i])[j]=='from'){
                        data[i][Object.keys(data[i])[j]] = data[i][Object.keys(data[i])[j]].replace('+','');
                        getClient(data[i][Object.keys(data[i])[j]]);
                        if(client)
                        {
                            data[i][Object.keys(data[i])[j]] = client['client_name']+"/"+data[i][Object.keys(data[i])[j]];
                            if(client['dealer_type'] == 3)
                            {
                                jQuery('#callbacksList > tbody > tr:last').attr('data-href','index.php?option=com_gm_ceiling&view=clientcard&type=designer&id='+client['id']);
                            }
                            else if(client['dealer_type'] == 0 || client['dealer_type'] == 1)
                            {
                                jQuery('#callbacksList > tbody > tr:last').attr('data-href','index.php?option=com_gm_ceiling&view=clientcard&type=dealer&id='+client['id']);
                            }
                            else
                            {
                                jQuery('#callbacksList > tbody > tr:last').attr('data-href','index.php?option=com_gm_ceiling&view=clientcard&id='+client['id']);
                            }
                        }
                    }
                    if(Object.keys(data[i])[j]=='dateTimeUtc'){
                        date_t = new Date(data[i][Object.keys(data[i])[j]]);
                        data[i][Object.keys(data[i])[j]] = formatDate(date_t);
                    }
                    if(Object.keys(data[i])[j]=='businessNumber' || Object.keys(data[i])[j]=='dateTimeUtc'||Object.keys(data[i])[j]=='from'||Object.keys(data[i])[j]=='participants'){ 
                        jQuery('#callbacksList > tbody > tr:last').append('<td>'+data[i][Object.keys(data[i])[j]] +'</td>');
                    }
                    if(Object.keys(data[i])[j]=='id')
                    {
                        jQuery('#callbacksList > tbody > tr:last').attr('id',data[i][Object.keys(data[i])[j]]);
                    }
                }
            } 
        } 
    }
    function formatDate(date)
    {

      var dd = date.getDate();
      if (dd < 10) dd = '0' + dd;

      var mm = date.getMonth() + 1;
      if (mm < 10) mm = '0' + mm;

      var yy = date.getFullYear();
      if (yy < 10) yy = '0' + yy;

      var hh = date.getHours();
      if (hh < 10) hh = '0' + hh;

      var ii = date.getMinutes();
      if (ii < 10) ii = '0' + ii;

      var ss = date.getSeconds();
      if (ss < 10) ss = '0' + ss;

      return dd + '.' + mm + '.' + yy + ' ' + hh + ':' + ii + ':' + ss;
    }
    function sendDateOnMissedCalls()
    {
        var calendar_elem_value = document.getElementById('calendar').value;
        jQuery.ajax({
            type: 'POST',
            url: "index.php?option=com_gm_ceiling&task=missedCalls",
            async: true,
            data: {
                date: calendar_elem_value,
                filter : "missed"
            },
            success: function(data){
                console.log(data);
                if(data.isSuccess&&data.data.calls.length>0){
                    show_calls(data.data.calls);
                }
                else{
                    jQuery("#callbacksList").hide();
                    jQuery("#empty").show();
                }
               jQuery("#preloader").hide();
            },
            dataType: "json",
            timeout: 30000,
            error: function(data){
                console.log(data);
                var n = noty({
                    timeout: 2000,
                    theme: 'relax',
                    layout: 'center',
                    maxVisible: 5,
                    type: "error",
                    text: "Сервер не отвечает."
                });
            }                   
        });
    }
    function get_number(name){
        for (var k in phones ) {
            if (phones[k] == name) {
                return k;
            }
        }
    }

    function ajaxCreateNewClient(pf,api_id) {
        jQuery.ajax({
            type: 'POST',
            url: "index.php?option=com_gm_ceiling&task=client.create", 
            data: {
                phone:pf
            },
            success: function(data) {
                ajaxCreateNewProject(api_id,data-0);
            },
            dataType: "json",
            async: false,
            timeout: 10000,
            error: function(data) {
                var n = noty({
                    timeout: 2000,
                    theme: 'relax',
                    layout: 'center',
                    maxVisible: 5,
                    type: "error",
                    text: "Ошибка при создании клиента. Сервер не отвечает."
                });
            }                   
        });
    }

    function getAdvtByPhone(phone){
        console.log(phone,'func');
        jQuery.ajax({
                type: 'POST',
                url: "index.php?option=com_gm_ceiling&task=Api_phones.getAdvtByPhone", //ищем проекты по номеру
                data: {
                    phone: phone
                },
                success: function(data) {
                    console.log(data);
                    if(Object.keys(data).length){
                        api_phone_id = data.id;
                    }
                    else{
                       var n = noty({
                        timeout: 2000,
                        theme: 'relax',
                        layout: 'center',
                        maxVisible: 5,
                        type: "error",
                        text: data
                    }); 
                    }
                },
                dataType: "json",
                async: false,
                timeout: 10000,
                error: function(data) {
                    console.log(data);
                    var n = noty({
                        timeout: 2000,
                        theme: 'relax',
                        layout: 'center',
                        maxVisible: 5,
                        type: "error",
                        text: "Сервер не отвечает."
                    });
                }                   
            });
    }

    function ajaxCreateNewProject(api_phone_id,client_id){
        jQuery.ajax({
            type: 'POST',
            url: "index.php?option=com_gm_ceiling&task=create_empty_project",
            data: {
                api_id: api_phone_id,
                client_id: client_id
            },
            success: function(data){
                
                data = JSON.parse(data);
                url = '/index.php?option=com_gm_ceiling&view=project&type=gmmanager&subtype=calendar&id=' + data;
                location.href =url;
            },
            dataType: "text",
            async: false,
            timeout: 20000,
            error: function(data){
                var n = noty({
                    timeout: 2000,
                    theme: 'relax',
                    layout: 'center',
                    maxVisible: 5,
                    type: "error",
                    text: "Ошибка. Сервер не отвечает"
                });
            }                   
        });
    }
    jQuery(document).ready(function()
    {
        sendDateOnMissedCalls();

        document.getElementById('calendar').onchange = function()
        {
            sendDateOnMissedCalls();
        };

        jQuery('body').on('click', 'tr', function(e)
        {
            var suc = false;
            var call_id = this.id;
            jQuery.ajax({
                type: 'POST',
                url: "index.php?option=com_gm_ceiling&task=missed_calls.addObrCall",
                data: {
                    call_id: call_id
                },
                success: function(data){
                    suc = true;
                },
                dataType: "text",
                async: false,
                timeout: 20000,
                error: function(data){
                    var n = noty({
                        timeout: 2000,
                        theme: 'relax',
                        layout: 'center',
                        maxVisible: 5,
                        type: "error",
                        text: "Ошибка. Сервер не отвечает"
                    });
                }                   
            });
            if (suc)
            {
                if(jQuery(this).data('href')!=""){
                    document.location.href = jQuery(this).data('href');
                }
                else{
                    console.log(jQuery(this)[0].childNodes[1].innerText);
                    var pt = get_number(jQuery(this)[0].childNodes[1].innerText),
                        pf = jQuery(this)[0].childNodes[0].innerText;
                        getAdvtByPhone(pt);
                        ajaxCreateNewClient(pf,api_phone_id);  
                }
            }
        });
    });
</script>
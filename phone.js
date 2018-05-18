var flag_hangUp = false;
function phone(login, pass)
{
    //window_phone = window.open('', 'mightycall_webphone_expand', 'width=345,height=500,location=false,status=false');
    //window.name = 'mightycall_webphone_expand';
    MightyCallWebPhone.ApplyConfig({login: login, password: pass});
    MightyCallWebPhone.Phone.Init();
    MightyCallWebPhone.Phone.Focus();

    function webPhoneOnCallIncoming(callInfo) {
        //console.log('Звонок от:' + callInfo.From);
        //console.log('Звонок к:' + callInfo.To);
        var pt = callInfo.To.replace('+','');
        var pf = callInfo.From.replace('+','');

        var regexp_u1 = /subtype=calendar/;
        var regexp_u2 = /view=calculationform/;
        var regexp_u3 = /subtype=designer/;
        var regexp_u4 = /subtype=production/;
        if (regexp_u1.test(window.location.href) || regexp_u2.test(window.location.href)
            || regexp_u3.test(window.location.href) || regexp_u4.test(window.location.href))
        {
            var reg_phone_from = new RegExp('\&phonefrom=' + pf,'i');

            if (reg_phone_from.test(location.href))
            {
                add_history_ph(1, "Входящий звонок с " + pf, pt, pf, undefined);
                return;
            }

            jQuery.ajax({
                type: 'POST',
                url: "index.php?option=com_gm_ceiling&task=getProjectsByPhone",
                data :{
                    phone : callInfo.From
                },
                success: function(data){
                    console.log(data);
                    if (data !== null)
                    {
                        var reg_proj_id;
                        var find_proj_bool = false;
                        for (var i = data.length; i--;)
                        {
                            reg_proj_id = new RegExp('\&id=' + data[i].id,'i');
                            if (reg_proj_id.test(location.href))
                            {
                                add_history_ph(data[i].client_id, "Входящий звонок с " + pf, pt, pf, undefined);
                                find_proj_bool = true;
                                break;
                            }
                        }
                        if (!find_proj_bool)
                        {
                            flag_hangUp = true;
                            MightyCallWebPhone.Phone.HangUp();
                        }
                    }
                    else
                    {
                        flag_hangUp = true;
                        MightyCallWebPhone.Phone.HangUp();
                    }
                },
                dataType: "json",
                async: false,
                timeout: 10000,
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
                    flag_hangUp = true;
                    MightyCallWebPhone.Phone.HangUp();
                }                   
            });
            
            return;
        }

        MightyCallWebPhone.Phone.Focus();

        jQuery.ajax({
            type: 'POST',
            url: "index.php?option=com_gm_ceiling&task=getClientByPhone",
            data :{
                phone : callInfo.From
            },
            success: function(data){
                console.log(data);
                if (data === null)
                {
                    create_empty_project(pt, pf);
                }
                else
                {
                    var loc;
                    if (data.dealer_type == 3)
                    {
                        loc = '/index.php?option=com_gm_ceiling&view=clientcard&type=designer&id=';
                    }
                    else if (data.dealer_type == 0 || data.dealer_type == 1)
                    {
                        loc = '/index.php?option=com_gm_ceiling&view=clientcard&type=dealer&id=';
                    }
                    else
                    {
                        loc = '/index.php?option=com_gm_ceiling&view=clientcard&id=';
                    }
                    add_history_ph(data.id, "Входящий звонок с " + pf, pt, pf, loc);
                }
            },
            dataType: "json",
            timeout: 10000,
            async: false,
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

    function create_empty_project(pt, pf)
    {
        jQuery.ajax({
            type: 'POST',
            url: "index.php?option=com_gm_ceiling&task=create_empty_project",
            data: {
                client_id: 1
            },
            success: function(data){
                data = JSON.parse(data);
                url = '/index.php?option=com_gm_ceiling&view=project&type=gmmanager&subtype=calendar&id=' + data + '&phoneto=' + pt + '&phonefrom=' + pf;
                location.href =url;
            },
            dataType: "text",
            async: false,
            timeout: 10000,
            error: function(data){
                var n = noty({
                    timeout: 2000,
                    theme: 'relax',
                    layout: 'center',
                    maxVisible: 5,
                    type: "error",
                    text: "Ошибка при создании заказа. Сервер не отвечает"
                });
            }                   
        });
    }

    function add_history_ph(id_client, comment, pt, pf, part_url)
    {
        jQuery.ajax({
            url: "index.php?option=com_gm_ceiling&task=addComment",
            data: {
                comment: comment,
                id_client: id_client
            },
            dataType: "json",
            async: false,
            success: function (data) {
                var n = noty({
                    timeout: 2000,
                    theme: 'relax',
                    layout: 'center',
                    maxVisible: 5,
                    type: "success",
                    text: "Добавленна запись в историю клиента"
                });
                if (document.getElementById('comments_id') && document.getElementById('comments'))
                {
                    document.getElementById('comments_id').value += data + ';';
                    show_comments_ph(id_client);
                }
                if (part_url !== undefined)
                {
                    if (pt === "" || pf === "")
                    {
                        url = part_url + id_client;
                    }
                    else
                    {
                        url = part_url + id_client + '&phoneto=' + pt + '&phonefrom=' + pf;
                    }
                    setTimeout(function(){location.href = url;}, 1000);
                }
            },
            error: function (data) {
                console.log(data);
                var n = noty({
                    timeout: 2000,
                    theme: 'relax',
                    layout: 'center',
                    maxVisible: 5,
                    type: "error",
                    text: "Ошибка отправки"
                });
            }
        });
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

    function show_comments_ph(id_client)
    {
        jQuery.ajax({
            url: "index.php?option=com_gm_ceiling&task=selectComments",
            data: {
                id_client: id_client
            },
            dataType: "json",
            async: false,
            success: function (data) {
                var comments_area = document.getElementById('comments');
                comments_area.innerHTML = "";
                var date_t;
                for (var i = 0; i < data.length; i++)
                {
                    date_t = new Date(data[i].date_time);
                    comments_area.innerHTML += formatDate(date_t) + "\n" + data[i].text + "\n----------\n";
                }
                comments_area.scrollTop = comments_area.scrollHeight;
            },
            error: function (data) {
                console.log(data);
                var n = noty({
                    timeout: 2000,
                    theme: 'relax',
                    layout: 'center',
                    maxVisible: 5,
                    type: "error",
                    text: "Ошибка вывода примечаний"
                });
            }
        });
    }

    function webPhoneOnCallCompleted(callInfo)
    {
        if (flag_hangUp)
        {
            return;
        }
        var pt = callInfo.To.replace('+','');
        var pf = callInfo.From.replace('+','');

        jQuery.ajax({
            type: 'POST',
            url: "index.php?option=com_gm_ceiling&task=getClientByPhone",
            data :{
                phone : pf
            },
            success: function(data){
                console.log(data);
                if (data === null)
                {
                    jQuery.ajax({
                        type: 'POST',
                        url: "index.php?option=com_gm_ceiling&task=getClientByPhone",
                        data :{
                            phone : pt
                        },
                        success: function(data){
                            console.log(data);
                            if (data === null)
                            {
                                add_history_ph(1, "Звонок завершен", pt, pf, undefined);
                            }
                            else
                            {
                                add_history_ph(data.id, "Звонок " + pt + " завершен", pt, pf, undefined);
                            }
                        },
                        dataType: "json",
                        timeout: 10000,
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
                else
                {
                    add_history_ph(data.id, "Звонок " + pf + " завершен", pt, pf, undefined);
                }
            },
            dataType: "json",
            timeout: 10000,
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

    MightyCallWebPhone.Phone.OnCallIncoming.subscribe(webPhoneOnCallIncoming);
    MightyCallWebPhone.Phone.OnCallCompleted.subscribe(webPhoneOnCallCompleted);
}

function call(num)
{
    flag_hangUp = false;
    MightyCallWebPhone.Phone.Call(num);
    MightyCallWebPhone.Phone.Focus();
}

var timer_n_c;

function nearest_callback()
{
    jQuery.ajax({
        type: 'POST',
        url: "index.php?option=com_gm_ceiling&task=nearestCallback",
        success: function(data){
            console.log(data);
            if (data !== null)
            {
                data_callback = data;
                for (var i = data_callback.length; i--;)
                {
                    if (data_callback[i].notify == 0)
                    {
                        jQuery.ajax({
                            type: 'POST',
                            url: "index.php?option=com_gm_ceiling&task=notify_new",
                            async: false,
                            data: {
                                id: data_callback[i].id,
                                client_id: data_callback[i].client_id,
                                date_time: data_callback[i].date_time,
                                comment: data_callback[i].comment,
                                manager_id: data_callback[i].manager_id,
                                type: 5
                            },
                            success: function(data){
                                console.log(data);
                            },
                            dataType: "json",
                            timeout: 10000,
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
                }
            }
        },
        dataType: "json",
        timeout: 10000,
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
    timer_n_c = setInterval(timer_nearest_callback, 600000);
}

function timer_nearest_callback()
{
    jQuery.ajax({
        type: 'POST',
        url: "index.php?option=com_gm_ceiling&task=nearestCallback",
        success: function(data){
            console.log(data);
            if (data !== null)
            {
                data_callback = data;
                for (var i = data_callback.length; i--;)
                {
                    jQuery.ajax({
                        type: 'POST',
                        url: "index.php?option=com_gm_ceiling&task=notify_new",
                        async: false,
                        data: {
                            client_id: data_callback[i].client_id,
                            date_time: data_callback[i].date_time,
                            comment: data_callback[i].comment,
                            manager_id: data_callback[i].manager_id,
                            type: 5
                        },
                        success: function(data){
                            console.log(data);
                        },
                        dataType: "json",
                        timeout: 10000,
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
            }
        },
        dataType: "json",
        timeout: 10000,
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


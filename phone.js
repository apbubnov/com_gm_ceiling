function phone(login, pass)
{
    MightyCallWebPhone.ApplyConfig({login: login, password: pass});
    MightyCallWebPhone.Phone.Init();
    //MightyCallWebPhone.Phone.Focus();
    var api_phone_id;
    var pt,pf;
    function getAdvtByPhone(phone){
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

    function webPhoneOnCallIncoming(callInfo) { //входящий
        pt = callInfo.To.replace('+','');
        pf = callInfo.From.replace('+','');
        
        var regexp_u1 = /view=project/;
        if (regexp_u1.test(window.location.href)) { //если на вьюхе проекта
            jQuery.ajax({
                type: 'POST',
                url: "index.php?option=com_gm_ceiling&task=getProjectsByPhone", //ищем проекты по номеру
                data: {
                    phone: pf
                },
                success: function(data) {
                    console.log(data);
                    if (data !== null) {
                        var reg_proj_id;
                        for (var i = data.length; i--;) {
                            reg_proj_id = new RegExp('\&id=' + data[i].id,'i');
                            if (reg_proj_id.test(location.href)) {  //если находимся в проекте того кто нам звонит
                                ajaxAddNewHistory(data[i].client_id, "Входящий звонок с " + pf);
                                return;
                            }
                        }
                    }
                    MightyCallWebPhone.Phone.HangUp(); //сбрасываем трубку
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
            return;
        }

        MightyCallWebPhone.Phone.Focus();
        console.log(pf);
        jQuery.ajax({
            type: 'POST',
            url: "index.php?option=com_gm_ceiling&task=getClientByPhone", //ищем клиента по телефону
            data: {
                phone: pf
            },
            success: function(data) {
                console.log(data);
                if (data === null) {
                    getAdvtByPhone(pt);
                    if(api_phone_id){
                        ajaxCreateNewClient(); //создаем нового клиента
                    }
                } else {
                    var loc; //на какую карточку будет редирект
                    if (data.dealer_type == 3) {
                        loc = '/index.php?option=com_gm_ceiling&view=clientcard&type=designer&id='+data.id;
                    }
                    else if (data.dealer_type == 0 || data.dealer_type == 1) {
                        loc = '/index.php?option=com_gm_ceiling&view=clientcard&type=dealer&id='+data.id;
                    } else {
                        loc = '/index.php?option=com_gm_ceiling&view=clientcard&id='+data.id;
                    }
                    ajaxAddNewHistory(data.id, "Входящий звонок с " + pf); //добавляем историю
                    location.href = loc; //редирект
                }
            },
            dataType: "json",
            timeout: 10000,
            async: false,
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

    function ajaxCreateNewClient() {
        jQuery.ajax({
            type: 'POST',
            url: "index.php?option=com_gm_ceiling&task=client.create", 
            data: {
                phone:pf
            },
            success: function(data) {
                ajaxCreateNewProject(data-0);
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


    function ajaxCreateNewProject(client_id) {
        jQuery.ajax({
            type: 'POST',
            url: "index.php?option=com_gm_ceiling&task=create_empty_project",
            data: {
                client_id: client_id,
                api_id: api_phone_id
            },
            success: function(data) {
                ajaxAddNewHistory(client_id, "Входящий звонок с " + pf);
                location.href = '/index.php?option=com_gm_ceiling&view=project&type=gmmanager&subtype=calendar&id='+data
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
                    text: "Ошибка при создании проекта. Сервер не отвечает."
                });
            }                   
        });
    }

    function ajaxAddNewHistory(client_id, text) {
        jQuery.ajax({
            type: 'POST',
            url: "index.php?option=com_gm_ceiling&task=addComment",
            data: {
                id_client: client_id,
                comment: text
            },
            success: function(data) {
                var n = noty({
                    timeout: 2000,
                    theme: 'relax',
                    layout: 'center',
                    maxVisible: 5,
                    type: "success",
                    text: "Запись в историю добавлена!"
                });
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
                    text: "Ошибка при добавлении комментария. Сервер не отвечает."
                });
            }                   
        });
    }

    /*function add_history_ph(id_client, comment, pt, pf, part_url)
    {
        jQuery.ajax({
            url: "index.php?option=com_gm_ceiling&task=addComment",
            data: {
                comment: comment,
                id_client: id_client
            },
            dataType: "json",
            async: false,
            success: function(data) {
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
    }*/

    /*function formatDate(date) {
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
    }*/

    /*function show_comments_ph(id_client)
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
    }*/

    MightyCallWebPhone.Phone.OnCallIncoming.subscribe(webPhoneOnCallIncoming);
}

function call(num) {
    flag_hangUp = false;
    MightyCallWebPhone.Phone.Call(num);
    MightyCallWebPhone.Phone.Focus();
}

var timer_n_c;

function nearest_callback() {
    jQuery.ajax({
        type: 'POST',
        url: "index.php?option=com_gm_ceiling&task=nearestCallback",
        success: function(data) {
            console.log(data);
            if (data !== null) {
                data_callback = data;
                for (var i = data_callback.length; i--;) {
                    if (data_callback[i].notify == 0) {
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
                            success: function(data) {
                                console.log(data);
                            },
                            dataType: "json",
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
                }
            }
        },
        dataType: "json",
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
    timer_n_c = setInterval(timer_nearest_callback, 600000);
}

function timer_nearest_callback() {
    jQuery.ajax({
        type: 'POST',
        url: "index.php?option=com_gm_ceiling&task=nearestCallback",
        success: function(data) {
            console.log(data);
            if (data !== null) {
                data_callback = data;
                for (var i = data_callback.length; i--;) {
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
            }
        },
        dataType: "json",
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


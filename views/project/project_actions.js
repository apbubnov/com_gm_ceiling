init_measure_calendar('measures_calendar', 'jform_project_new_calc_date', 'jform_project_gauger', 'mw_measures_calendar', [], 'measure_info');
init_mount_calendar('calendar_mount', 'mount', 'mw_mounts_calendar', ['mw_close', 'container_mw']);

// закрытие окон модальных
jQuery(document).mouseup(function (e) { // событие клика по веб-документу
    var div1 = jQuery("#modal_window_by_email");
    var div2 = jQuery("#mw_measure");
    var div3 = jQuery("#mw_add_call");
    var div4 = jQuery("#mw_advt");
    var div5 = jQuery("#mw_address");
    var div6 = jQuery("#mw_cl_info");
    var div7 = jQuery("#mw_mounts_calendar");
    var div8 = jQuery("#mw_measures_calendar"),
        div9 = jQuery('#mw_call_add');
    if (!div1.is(e.target) // если клик был не по нашему блоку
        && div1.has(e.target).length === 0
        && !div2.is(e.target)
        && div2.has(e.target).length === 0
        && !div3.is(e.target)
        && div3.has(e.target).length === 0
        && !div4.is(e.target)
        && div4.has(e.target).length === 0
        && !div5.is(e.target)
        && div5.has(e.target).length === 0
        && !div6.is(e.target)
        && div6.has(e.target).length === 0
        && !div7.is(e.target)
        && div7.has(e.target).length === 0
        && !div8.is(e.target)
        && div8.has(e.target).length === 0
        && !div9.is(e.target)
        && div9.has(e.target).length === 0) { // и не по его дочерним элементам
        jQuery("#mw_close").hide();
        jQuery("#container_mw").hide();
        div1.hide();
        div2.hide();
        div3.hide();
        div4.hide();
        div5.hide();
        div6.hide();
        div7.hide();
        div8.hide();
        div9.hide();
    }
});


jQuery("#rec_to_mesure").click(function () {
    jQuery("#mw_close").show();
    jQuery("#container_mw").show();
    jQuery("#mw_measure").show();
});

jQuery('#save_btn').click(function () {
    jQuery('#project_status').val(project_status);
    jQuery('#form-client').submit();
});

jQuery("#ref_btn").click(function () {
    jQuery("#refuse_block").toggle();

    if (jQuery("#refuse_block").is(":visible")) {
        jQuery('[name="slider-refuse"][data-status = "2"]').attr('checked',true);
        jQuery('#ref_note_type').val(jQuery('[name = slider-refuse]:checked').data("note_type"));
        jQuery("#project_status").val(jQuery('[name = slider-refuse]:checked').data("status"));

    } else {
        jQuery('[name="slider-refuse"]:checked').removeAttr('checked');
        jQuery('#ref_note_type').val('');
        jQuery("#project_status").val(project_status);
    }
});

jQuery('#accept_project').click(function () {
    jQuery("#project_activation").toggle();
});
jQuery('#return_project').click(function () {
    var project_data = {project_calculation_date: getFormattedDatetime(), project_status: 1}
    jQuery.ajax({
        url: "index.php?option=com_gm_ceiling&task=project.updateProjectData",
        data: {
            project_id: project_id,
            project_data: project_data
        },
        dataType: "json",
        async: true,
        success: function (data) {
            location.href = '/index.php?option=com_gm_ceiling&task=mainpage';
        },
        error: function (data) {

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
});

/*Запуск в производтсвто по email*/
jQuery("#save_email").click(function () {
    jQuery.ajax({
        url: "index.php?option=com_gm_ceiling&task=users.getManufacturerInfo",
        data: {},
        dataType: "json",
        async: true,
        success: function (data) {
            manufacturersData = data;
            var manufacturersHtml = '';
            jQuery("#manufacturer_list").empty();
            for (var i = 0; i < data.length; i++) {
                if (i % 3 == 0) {
                    console.log('1', i);
                    manufacturersHtml += '<div class="row">';
                }
                manufacturersHtml += '<div class="col-md-4 manuf_div" data-email="' + data[i].email + '">';
                manufacturersHtml += '<div class=row><b>' + data[i].name + '</b></div>';
                manufacturersHtml += '<div class=row><b>Адрес выдачи:</b> ' + data[i].address + '</div>';
                manufacturersHtml += '</div>';
                if ((i + 1) % 3 == 0 || i + 1 == data.length) {
                    console.log('2', i);
                    manufacturersHtml += '</div>';
                }
            }
            jQuery("#manufacturer_list").append(manufacturersHtml);

        },
        error: function (data) {
            console.log(data);
            var n = noty({
                timeout: 2000,
                theme: 'relax',
                layout: 'center',
                maxVisible: 5,
                type: "error",
                text: "Ошибка!Попробуйте позднее!"
            });
        }
    });

    jQuery("#mw_close").show();
    jQuery("#container_mw").show();
    jQuery("#modal_window_by_email").show();
});

jQuery('#modal_window_by_email').on('click', '.manuf_div', function () {
    jQuery('.manuf_div').removeClass('selected');
    jQuery(this).addClass('selected');
});

jQuery('#directly_email').focus(function () {
    jQuery('.manuf_div').removeClass('selected');
});

jQuery('.runByEmail').click(function () {
    var div = jQuery('.manuf_div.selected'),
        email = div.length ? div.data('email') : jQuery('#directly_email').val(),
        include_calculations = get_selected_calcs();
    if (!empty(email)) {
        jQuery.ajax({
            url: "index.php?option=com_gm_ceiling&task=project.activateByEmail",
            data: {
                project_id: project_id,
                email: email,
                include_calcs: include_calculations,
                mount_data: jQuery('#mount').val(),
                production_note: jQuery('#jform_production_note').val(),
                mount_note: jQuery('#jform_mount_note').val(),
                ref_note: jQuery('#jform_refuse_note').val()
            },
            dataType: "json",
            async: true,
            success: function (data) {
                noty({
                    timeout: 2000,
                    theme: 'relax',
                    layout: 'center',
                    maxVisible: 5,
                    type: "success",
                    text: "Проект запущен в производство!"
                });
                setTimeout(function () {
                    location.reload();
                }, 3000);
            },
            error: function (data) {
                console.log(data);
                noty({
                    timeout: 2000,
                    theme: 'relax',
                    layout: 'center',
                    maxVisible: 5,
                    type: "error",
                    text: "Ошибка!Попробуйте позднее!"
                });
            }
        });
    } else {
        noty({
            timeout: 2000,
            theme: 'relax',
            layout: 'center',
            maxVisible: 5,
            type: "error",
            text: "Не указан email!"
        });
    }
});
/*--------*/

jQuery('[name = "slider-refuse"]').change(function(){
    jQuery("#project_status").val(jQuery(this).data("status"));
    jQuery("#ref_note_type").val(jQuery(this).data("note_type"));
});

jQuery("#all_calcs").change(function () {
    if(jQuery(this).prop('checked')){
        jQuery('[name="runByCall"]').prop('checked',true);
        jQuery('[name = "date_all_canvas_ready"]').val("");
        jQuery('[name = "date_canvas_ready"]').val("");
    }
    else{
        jQuery('[name="runByCall"]').prop('checked',false);
    }
});
jQuery('[name = "date_all_canvas_ready"]').focus(function () {
    var date = new Date,
        month  = (date.getMonth()<10) ?"0"+(date.getMonth()+1) : (date.getMonth()+1),
        day = (date.getDate()<10) ?"0"+date.getDate() : date.getDate(),
        value = date.getFullYear()+"-"+month+"-"+day+"T09:00";
    this.value = value;
    jQuery('[name = "date_canvas_ready"]').val(value);
    jQuery("#all_calcs").prop('checked',false);
    jQuery('[name = "runByCall"]').prop('checked',false);
});
jQuery('[name = "date_all_canvas_ready"]').change(function () {
    var date_time = this;
    jQuery('[name = "date_canvas_ready"]').val(this.value);
});

jQuery('[name = "runByCall"]').change(function () {
    var checkBox = this;
    if(checkBox.checked){
        jQuery('[name = "date_canvas_ready"]').filter(function () {
            if(jQuery(this).data("calc_id") == jQuery(checkBox).data("calc_id")){
                this.value =  "";
            };
        });
        jQuery('[name = "date_all_canvas_ready"]').val("");
    }
    else{
        jQuery("#all_calcs").prop('checked',false);
    }

});

jQuery('[name = "date_canvas_ready"]').focus(function () {
    var date = new Date,
        month  = (date.getMonth()<10) ?"0"+(date.getMonth()+1) : (date.getMonth()+1),
        day = (date.getDate()<10) ?"0"+date.getDate() : date.getDate();
    this.value = date.getFullYear()+"-"+month+"-"+day+"T09:00";
    jQuery('[name = "date_all_canvas_ready"]').val("");
    jQuery('#all_calcs').prop('checked',false);
});
jQuery('[name = "date_canvas_ready"]').change(function () {
    var date_time = this;
    jQuery('[name = "runByCall"]').filter(function () {
        if(jQuery(this).data("calc_id") == jQuery(date_time).data("calc_id")){
            jQuery(this).attr("checked",false);
        };
    });
});

jQuery("#btn_ready_date_вave").click(function() {
    var readyDates = jQuery('[name = "date_canvas_ready"]').filter(function () {
            if(this.value){
                return this;
            };
        }),
        byCall = jQuery('[name = "runByCall"]:checked'),
        result = [];
    jQuery.each(readyDates,function (index,elem) {
        result.push({calc_id:jQuery(elem).data("calc_id"),ready_time:jQuery(elem).val()});
    });
    jQuery.each(byCall,function (index,elem) {
        result.push({calc_id:jQuery(elem).data("calc_id"),ready_time:"by_call"});
    });
    jQuery.ajax({
        /*index.php?option=com_gm_ceiling&task=project.update_ready_time*/
        url: "index.php?option=com_gm_ceiling&task=calculation.set_ready_time",
        data: {
            data: JSON.stringify(result)
        },
        dataType: "json",
        async: true,
        success: function (data) {
            noty({
                timeout: 2000,
                theme: 'relax',
                layout: 'center',
                maxVisible: 5,
                type: "success",
                text: "Время готовности полотен назначено"
            });
        },
        error: function (data) {
            console.log(data);
            noty({
                timeout: 2000,
                theme: 'relax',
                layout: 'center',
                maxVisible: 5,
                type: "error",
                text: "Ошибка"
            });
        }
    });
});

jQuery("#save_exit").click(function() {
    jQuery("input[name='project_status']").val(4);
    jQuery("input[name='project_verdict']").val(1);
    jQuery('#form-client').submit();
});

jQuery("#save").click(function() {
    var prepayment_taken = jQuery("#prepayment_taken").val(),
        prepayment_sum = jQuery('#prepayment').val();
    if((prepayment_sum != "" && prepayment_sum >= 0) || prepayment_taken != 0){
        if (empty(jQuery("#mount").val())) {
            noty({
                theme: 'relax',
                layout: 'center',
                maxVisible: 5,
                type: "alert",
                text: "Не указана дата монтажа. Продолжить?",
                buttons: [
                    {
                        addClass: 'btn btn-primary', text: 'Да', onClick: function (modal) {
                            jQuery("input[name='project_status']").val(5);
                            jQuery("input[name='project_verdict']").val(1);
                            jQuery('#form-client').submit();
                            modal.close();
                        }
                    },
                    {
                        addClass: 'btn btn-primary', text: 'Нет', onClick: function (modal) {
                            modal.close();
                        }
                    }
                ]
            });
        }
        else {
            jQuery("input[name='project_status']").val(5);
            jQuery("input[name='project_verdict']").val(1);
            jQuery('#form-client').submit();
        }
    }
    else {
        noty({
            timeout: 2000,
            theme: 'relax',
            layout: 'center',
            maxVisible: 5,
            type: "error",
            text: "Не введена предоплата!"
        });
        jQuery('html,body').animate({ scrollTop: jQuery('#prepayment').offset().top }, 1000);

    }

});

jQuery('#move_to_ref_btn').click(function(){
    noty({
        theme: 'relax',
        layout: 'center',
        maxVisible: 5,
        type: "alert",
        text: "Создать перезвон?",
        buttons: [
            {
                addClass: 'btn btn-primary', text: 'Да', onClick: function (modal) {
                    modal.close();
                    jQuery("#mw_close").show();
                    jQuery("#container_mw").show();
                    jQuery('#mw_call_add').show();
                }
            },
            {
                addClass: 'btn btn-primary', text: 'Нет', onClick: function (modal) {
                    jQuery('#form-client').submit();
                    modal.close();
                }
            }
        ]
    });
});

jQuery('#add_ref_call').click(function(){
    if (jQuery("#calldate").val() == '')
    {
        var n = noty({
            timeout: 2000,
            theme: 'relax',
            layout: 'topCenter',
            maxVisible: 5,
            type: "warning",
            text: "Укажите дату перезвона"
        });
        return;
    }
    var date = jQuery("#calldate").val().replace(/(-)([\d]+)/g, function(str,p1,p2) {
            if (p2.length === 1) {
                return '-0'+p2;
            }
            else {
                return str;
            }
        }),
        time = jQuery("#calltime").val(),
        important = jQuery('#important').is(':checked') ? 1 : 0;
    if (time == '') {
        time = '00:00';
    }
    jQuery.ajax({
        url: "index.php?option=com_gm_ceiling&task=addCall",
        data: {
            id_client: jQuery("#client_id").val(),
            date: date+' '+time,
            comment: jQuery("#callcomment").val(),
            important: important
        },
        dataType: "json",
        async: true,
        success: function (data) {
            add_history(client_id, 'Добавлен звонок на ' + date + ' ' + time + ':00');
            jQuery('#form-client').submit();
        },
        error: function (data) {
            var n = noty({
                timeout: 2000,
                theme: 'relax',
                layout: 'center',
                maxVisible: 5,
                type: "error",
                text: "Ошибка сервера"
            });
        }
    });
});

jQuery("#save_rec").click(function(){
    var address = "",
        street = jQuery("#jform_rec_address").val(),
        house = jQuery("#jform_rec_house").val(),
        bdq = jQuery("#jform_rec_bdq").val(),
        apartment = jQuery("#jform_rec_apartment").val(),
        porch = jQuery("#jform_rec_porch").val(),
        floor =jQuery("#jform_rec_floor").val(),
        code = jQuery("#jform_rec_code").val();
    if(house) address = street + ", дом: " + house;
    if(bdq) address += ", корпус: " + bdq;
    if(apartment) address += ", квартира: "+ apartment;
    if(porch) address += ", подъезд: " + porch;
    if(floor) address += ", этаж: " + floor;
    if(code) address += ", код: " + code;

    var data = {id:project_id,project_calculator:jQuery("#jform_project_gauger").val(), project_calculation_date:jQuery("#jform_project_new_calc_date").val(),project_info:address,project_status:1},
        measure_note = jQuery('#measure_note').val();

    if(!empty(measure_note)){
        saveNote(project_id,measure_note,2);
    }
    data = JSON.stringify(data)
    jQuery.ajax({
        url: "index.php?option=com_gm_ceiling&task=project.change_project_data",
        data: {
            new_data: data
        },
        dataType: "json",
        async: true,
        success: function (data) {
            var n = noty({
                timeout: 2000,
                theme: 'relax',
                layout: 'topCenter',
                maxVisible: 5,
                type: "success",
                text: "Данные успешно изменены!"
            });
            location.reload();
        },
        error: function (data) {

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
});

function saveNote(project_id,note,noteType){
    jQuery.ajax({
        url: "index.php?option=com_gm_ceiling&task=project.addNote",
        data: {
            project_id: project_id,
            note: note,
            type: noteType
        },
        dataType: "json",
        async: true,
        success: function (data) {
        },
        error: function (data) {

            var n = noty({
                timeout: 2000,
                theme: 'relax',
                layout: 'center',
                maxVisible: 5,
                type: "error",
                text: "Ошибка сохранения примечания!"
            });
        }
    });
}
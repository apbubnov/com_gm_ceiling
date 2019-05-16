
jQuery("[name = click_transport]").click(function () {
    calculate_transport();
});

if (jQuery("input[name='transport']:checked").val() == '2') {
        jQuery("#transport_dist").show();
}

if (jQuery("input[name='transport']:checked").val() == '1') {
        jQuery("#transport_dist_col").show();
}

jQuery("[name = 'include_calculation[]']").change(function(){
    let canv_data = (self_data[jQuery(this).val()].canv_data).toFixed(0);
    let comp_data = (self_data[jQuery(this).val()].comp_data).toFixed(0);
    let mount_data = (self_data[jQuery(this).val()].mount_data).toFixed(0);
    let calc_sum = (self_data[jQuery(this).val()].sum).toFixed(0);
    let calc_sum_discount = (self_data[jQuery(this).val()].sum_discount).toFixed(0);
    let n4 = self_data[jQuery(this).val()].square;
    let n5 = self_data[jQuery(this).val()].perimeter;
    let old_canv = jQuery("#calcs_self_canvases_total span.sum").text();
    let old_comp = jQuery("#calcs_self_components_total span.sum").text();
    let old_mount = jQuery("#calcs_self_mount_total span.sum" ).text();
    let old_all = jQuery("#calcs_total_border").text();
    let old_total = jQuery("#project_total span.sum").text();
    let old_total_discount = jQuery("#project_total_discount span.sum").text();
    let old_n4 = jQuery("#total_square span.sum").text();
    let old_n5 = jQuery("#total_perimeter span.sum").text();
    if(jQuery(this).prop("checked") == true){
       jQuery("#calcs_self_canvases_total span.sum").text(parseInt(old_canv) + parseInt(canv_data));
       if(jQuery("input[name='smeta']").val()!=1){
           jQuery("#calcs_self_components_total span.sum").text(parseInt(old_comp) + parseInt(comp_data));
       }
       jQuery("#calcs_self_mount_total span.sum").text(parseInt(old_mount) + parseInt(mount_data));
       jQuery("#calcs_total_border").text(parseInt(old_all) + parseInt(canv_data) +  parseInt(comp_data) + parseInt(mount_data));
       jQuery("#project_total span.sum").text(parseInt(old_total)+ parseInt(calc_sum));
       jQuery("#project_total_discount span.sum").text(parseInt(old_total_discount)+ parseInt(calc_sum_discount));
       jQuery("#total_square span.sum").text(parseFloat(old_n4) + parseFloat(n4));
       jQuery("#total_perimeter span.sum").text(parseFloat(old_n5) + parseFloat(n5));
      
    }
    else{
        jQuery("#calcs_self_canvases_total span.sum").text(old_canv-canv_data);
        if(jQuery("input[name='smeta']").val()!=1){
            jQuery("#calcs_self_components_total span.sum").text(old_comp-comp_data);
        }
        jQuery("#calcs_self_mount_total span.sum").text(old_mount-mount_data);
        jQuery("#calcs_total_border").text(old_all - canv_data - comp_data - mount_data);
        jQuery("#project_total span.sum").text(old_total - calc_sum);
        jQuery("#project_total_discount span.sum").text(old_total_discount - calc_sum_discount);
        jQuery("#total_square span.sum").text((old_n4 - n4).toFixed(2));
        jQuery("#total_perimeter span.sum").text((old_n5 - n5).toFixed(2));
        let more_one = check_selected();
        if(!more_one){
            jQuery("#project_total_discount span.sum").text(jQuery("#transport_sum span.sum").text());
        }
       
    }
    if(jQuery("#project_total_discount span.sum").text()){
         jQuery("#project_sum").val(jQuery("#project_total_discount span.sum").text());
    }
    else{
        jQuery("#project_sum").val(jQuery("#project_total span.sum").text());
    }
    jQuery("#calcs_self_components_total span.sum").data('oldval',jQuery("#calcs_self_components_total span.sum").text());
    check_min_sum(jQuery("#calcs_self_canvases_total span.sum").text());
});

jQuery(".delete_calc").click(function(){
        let calc_id = jQuery(this).data('calculation_id');
        jQuery.ajax({
            url: "index.php?option=com_gm_ceiling&task=calculation.delete",
            data:{
                calc_id: calc_id
            },
            type: "POST",
            dataType: 'json',
            async: false,
            success: function (data) {
                location.reload();
            },
            error: function (data) {
                var n = noty({
                    theme: 'relax',
                    layout: 'center',
                    maxVisible: 5,
                    type: "error",
                    text: "Ошибка при попытке удалить!"
                });
            }
        });
});

function check_min_sum(canv_sum){
    let min_sum = 0;
    if(canv_sum == 0) {
        if(min_components_sum>0){
            min_sum = min_components_sum;
        }
    }
    else{
        if(min_project_sum>0){
            min_sum = min_project_sum;
        }
    }            
    let project_total = jQuery("#project_total span.sum").text();
    if(jQuery("#project_total_discount span.dop").length == 0){
        jQuery("#project_total_discount").append('<span class = \"dop\" style = \"font-size: 9px\";></span>');
    }
    if(project_total < min_sum){
        jQuery("#project_total_discount span.dop").html(` * минимальная сумма заказа ${min_sum} р.`);
        jQuery("#project_total_discount span.sum").text(min_sum);

    }
    else{
        jQuery("#project_total_discount span.dop").html(" ");
    }
    if(jQuery("#project_total_discount span.sum").text()){
         jQuery("#project_sum").val(jQuery("#project_total_discount span.sum").text());
    }
    else{
        jQuery("#project_sum").val(jQuery("#project_total span.sum").text());
   }
}
function check_selected(){
    let result = false;
    jQuery("[name = 'include_calculation[]']").each(function(){
        if(jQuery(this).prop("checked") == true ){
            result = true;
        }
    });
    return result;
}

jQuery("#show").click(function(){
    //перегенерить смету по выбранным
    regenerate_common_estimate();
});
function get_selected_calcs(){
    let ids = [];
    jQuery.each(jQuery("[name = 'include_calculation[]']:checked"),function(){
        ids.push(jQuery(this).val());
    });
    return ids;
}
function regenerate_common_estimate(){
    let calc_ids = get_selected_calcs();
    console.log(project_id, calc_ids);
     jQuery.ajax({
        url: "index.php?option=com_gm_ceiling&task=regenerate_common_estimate",
        data:{
            proj_id: project_id,
            calc_ids: calc_ids
        },
        type: "POST",
        dataType: 'json',
        async: false,
        success: function (data) {
            //console.log(data);
        },
        error: function (data) {
            var n = noty({
                theme: 'relax',
                layout: 'center',
                maxVisible: 5,
                type: "error",
                text: "Ошибка при генерации общей сметы по выбранным потолкам"
            });
        }
    }); 
}
jQuery("#send_all_to_email").click(function () {
    regenerate_common_estimate();
    var email = jQuery("#all-email").val();
    if ((/^[A-Za-z\d\-\_\.]+\@{1}[A-Za-z\d\-\_]+\.[A-Za-z\d]+$/).test(email))
    {
        var client_id = jQuery("#client_id").val();
        var filenames = jQuery("[name='include_pdf[]']:checked").map(function(){return {name: this.value, title: jQuery(this).data('name')};}).get();
        console.log(filenames);
        var formData = new FormData();
        jQuery.each(jQuery('#dopfile')[0].files, function (i, file) {
            formData.append('dopfile', file)
        });
        formData.append('filenames', JSON.stringify(filenames));
        formData.append('email', email);
        formData.append('client_id', client_id);
        jQuery.ajax({
            url: "index.php?option=com_gm_ceiling&task=send_estimate",
            data: formData,
            type: "POST",
            dataType: 'json',
            processData: false,
            contentType: false,
            cache: false,
            async: false,
            success: function(data) {
                //console.log(data);
                var n = noty({
                    theme: 'relax',
                    timeout: 2000,
                    layout: 'center',
                    maxVisible: 5,
                    type: "success",
                    text: "Общая смета отправлена!"
                });
            },
            error: function(data) {
                var n = noty({
                    theme: 'relax',
                    timeout: 2000,
                    layout: 'center',
                    maxVisible: 5,
                    type: "error",
                    text: "Ошибка отправки"
                });
            }
        });
    }
    else
    {
        var n = noty({
            theme: 'relax',
            timeout: 2000,
            layout: 'center',
            maxVisible: 5,
            type: "warning",
            text: "Проверьте email"
        });
    }
});


jQuery("input[name='transport']").click(function () {
    var transport = jQuery("input[name='transport']:checked").val();
    if (transport == '2') {
        jQuery("#transport_dist").show();
        jQuery("#transport_dist_col").hide();
        jQuery("#distance").val('');
        jQuery("#distance_col_1").val('');
    }
    else if(transport == '1') {
        jQuery("#transport_dist").hide();
        jQuery("#transport_dist_col").show();
        jQuery("#distance_col").val('');
        jQuery("#distance").val('');
    }
    else {
        jQuery("#transport_dist").hide();
        jQuery("#transport_dist_col").hide();
        jQuery("#distance").val('');
        jQuery("#distance_col").val('');
    }
    if(transport == 0){
        calculate_transport();
    }
});
function change_transport(sum){
    let old_transport = jQuery("#transport_sum span.sum").text();
    let new_transport = sum.client_sum;
    let new_self_transport = sum.mounter_sum;
    let old_self_transport = jQuery("#transport_sum span.sum").data('selfval');
    jQuery("#project_sum_transport").val(new_transport);
    jQuery("#transport_sum span.sum").text(new_transport);
    let old_self_mount = jQuery("#calcs_self_mount_total span.sum").text();
    let old_self_total = jQuery("#calcs_total_border").text();
    let old_total = jQuery("#project_total span.sum").text();
    let old_total_discount = jQuery("#project_total_discount span.sum").text();
    jQuery("#project_total span.sum").text(parseInt(old_total) - old_transport + parseInt(new_transport));
    jQuery("#project_total_discount span.sum").text(old_total_discount - old_transport + new_transport);
    jQuery("#calcs_self_mount_total span.sum").text(old_self_mount - old_self_transport + new_self_transport);
    jQuery("#calcs_total_border").text(old_self_total - old_self_transport + new_self_transport);
    jQuery("#transport_sum span.sum").data('selfval',new_self_transport);
    if(jQuery("#project_total_discount span.sum").text()){
         jQuery("#project_sum").val(jQuery("#project_total_discount span.sum").text());
    }
    else{
        jQuery("#project_sum").val(jQuery("#project_total span.sum").text());
   }
}

function update_transport(id,transport,distance,distance_col){
    jQuery.ajax({
        type: 'POST',
        url: "index.php?option=com_gm_ceiling&task=project.update_transport",
        data:{
            id : id,
            transport : transport,
            distance : distance,
            distance_col : distance_col
        },
        success: function(data){
            change_transport(data);
        },
        dataType: "json",
        timeout: 10000,
        error: function(data){
            var n = noty({
                theme: 'relax',
                timeout: 2000,
                layout: 'center',
                maxVisible: 5,
                type: "error",
                text: "Ошибка при попытке рассчитать транспорт. Сервер не отвечает"
            });
        }
    }); 
}

function calculate_transport(){
    var id = project_id;
    var transport = jQuery("input[name='transport']:checked").val();
    var distance = jQuery("#distance").val();
    var distance_col = jQuery("#distance_col").val();
    var distance_col_1 = jQuery("#distance_col_1").val();
    console.log(distance,distance_col,distance_col_1);
    switch(transport){
        case "0" :
            update_transport(id,0,0,0);
            break;
        case "1":
           
            update_transport(id,transport,distance,distance_col_1);
            break;
        case "2" :
                               
            update_transport(id,transport,distance,distance_col);
            break;
    }
}

var flag = 1;
jQuery("#sh_ceilings").click(function () {
    if (flag) {
        jQuery(".section_ceilings").hide();
        flag = 0;
    }
    else {
        jQuery(".section_ceilings").show();
        flag = 1;
    }
});

var flag1 = 0;
jQuery("#sh_estimate").click(function () {
    if (flag1) {
        jQuery(".section_estimate").hide();
        flag1 = 0;
    }
    else {
        jQuery(".section_estimate").show();
        flag1 = 1;
    }
    jQuery(".section_estimate").each(function () {
        var el = jQuery(this);
        if (el.attr("vis") == "hide") el.hide();
    })
});

jQuery('#create_pdfs').click(function(){
    jQuery.ajax({
        type: 'POST',
        url: "/index.php?option=com_gm_ceiling&task=createPdfs",
        data: {
            id:project_id
        },
        success: function(data) {
            location.reload();
        },
        error: function(data) {
            console.log(data);
        }
    });
});

function save_data_to_session(action_type,id=null,obj=null){
    var phones = [];
        var s = window.location.href;
        var classname = jQuery("input[name='new_client_contacts[]']");
        Array.from(classname).forEach(function (element) {
            phones.push(element.value);
        });
    //console.log(phones);
    var data = {
            fio: jQuery("#jform_client_name").val(),
            address: jQuery("#jform_address").val(),
            house: jQuery("#jform_house").val(),
            bdq: jQuery("#jform_bdq").val(),
            apartment: jQuery("#jform_apartment").val(),
            porch: jQuery("#jform_porch").val(),
            floor: jQuery("#jform_floor").val(),
            code: jQuery("#jform_code").val(),
            date: jQuery("#jform_project_new_calc_date").val(),
            time: jQuery("#jform_new_project_calculation_daypart").val(),
            manager_comment: jQuery("#gmmanager_note").val(),
            phones: phones,
            comments: jQuery("#comments_id").val(),
            gauger: jQuery("#jform_project_gauger").val(),
            sex: jQuery('[name = "slider-sex"]:checked').val(),
            type : jQuery('[name = "slider-radio"]:checked').val(),
            recool: jQuery("#recoil_choose").val(),
            advt: jQuery("#advt_choose").val()
        };
    var object = {proj_id : jQuery("#project_id").val(), data:JSON.stringify(data)};
    jQuery.ajax({
        type: 'POST',
        url: "index.php?option=com_gm_ceiling&task=save_data_to_session",
        data: object,
        success: function (data) {
            console.log(data);
            if(action_type == 1){
                create_calculation(project_id);
            }
            if(action_type == 2){
                console.log(jQuery(this));
                if(obj.attr('href')){
                    window.location = obj.attr('href');
                }
                else{
                    window.location = "index.php?option=com_gm_ceiling&view=calculationform2&type=gmmanager&subtype=calendar&calc_id=" + id;
                }
            }
            if(action_type == 3){
                jQuery("#form-client").submit();
            }
        },
        dataType: "text",
        timeout: 10000,
        error: function () {
            var n = noty({
                timeout: 2000,
                theme: 'relax',
                layout: 'center',
                maxVisible: 5,
                type: "error",
                text: "Ошибка cервер не отвечает"
            });
        }
    });
}

var btn_add_note = document.getElementById('btn_add_note');
if (btn_add_note) {
    btn_add_note.onclick = addNote;
}

function addNote() {
    jQuery.ajax({
        type: 'POST',
        url: "index.php?option=com_gm_ceiling&task=project.addNote",
        data: {
            project_id: project_id,
            note: document.getElementById('textarea_note').value
        },
        success: function(data) {
            location.reload();
        },
        dataType: "json",
        timeout: 10000,
        error: function(data) {
            var n = noty({
                theme: 'relax',
                timeout: 2000,
                layout: 'center',
                maxVisible: 5,
                type: "error",
                text: "Ошибка при добавлении примечания"
            });
        }
    }); 
}

jQuery("#duplicate_calcs").click(function () {
    jQuery("#duplicate_tr").toggle();
});

jQuery(".btn_duplicate").click(function(){
    if(!jQuery(this).data("need_new")){
        duplicate(0);
    }
    else{
        duplicate(1);
    }
});

function duplicate(need_new){
    var checkboxes = jQuery(".inp-cbx.dup:checked"),
        selected_calcs = [];
    jQuery.each(checkboxes,function(index,elem){
        selected_calcs.push(jQuery(elem).data("calc_id"));
    });
    if(selected_calcs.length){
        jQuery.ajax({
            type: 'POST',
            url: "index.php?option=com_gm_ceiling&task=calculations.duplicate",
            data: {
                project_id: project_id,
                calcs: selected_calcs,
                need_new: need_new
            },
            success: function(data) {
                if(!need_new){
                    location.reload();
                }
                else{
                    console.log(data);
                    noty({
                        theme: 'relax',
                        layout: 'center',
                        timeout: false,
                        type: "info",
                        text: "Перейти в новый проект?",
                        buttons:[
                            {
                                addClass: 'btn btn-primary', text: 'Перейти', onClick: function ($noty) {
                                    var href = window.location.href.split('?')[1];
                                    var params = new URLSearchParams(href);
                                    params.set('id',data);
                                    window.location = '/index.php?'+params;
                                }
                            },
                            {
                                addClass: 'btn btn-primary', text: 'Отмена', onClick: function($noty) {
                                    $noty.close();
                                }
                            }
                        ]
                    })
                }
            },
            dataType: "json",
            timeout: 10000,
            error: function(data) {
                var n = noty({
                    theme: 'relax',
                    timeout: 2000,
                    layout: 'center',
                    maxVisible: 5,
                    type: "error",
                    text: "Ошибка при дублтровании!"
                });
            }
        });
    }

}

jQuery('.btn_img_file').click(function() {
    var elem_file = jQuery(this).siblings('.img_file')[0];
    var n = noty({
        theme: 'relax',
        type: 'alert',
        layout: 'topCenter',
        text: '<input type="radio" value="before" name="img_type" style="cursor: pointer;" checked> До<br>'+
            '<input type="radio" value="after" name="img_type" style="margin-top: 10px; cursor: pointer;"> После<br>'+
            '<input type="radio" value="defect" name="img_type" style="margin-top: 10px; cursor: pointer;"> Дефект<br>',
        modal: true,
        buttons:[
            {
                addClass: 'btn btn-primary', text: 'Ок', onClick: function($noty) {
                    elem_file.setAttribute('data-img-type', jQuery('[name="img_type"]:checked').val());
                    elem_file.click();
                }
            },
            {
                addClass: 'btn btn-primary', text: 'Отмена', onClick: function($noty) {
                    $noty.close();
                }
            }
        ]
    }).show();
    document.getElementsByClassName('noty_message')[0].style.textAlign = 'left';
    document.getElementsByClassName('noty_message')[0].style.paddingLeft = '30%';
    document.getElementsByClassName('noty_message')[0].style.fontSize = '14pt';
    document.getElementsByClassName('noty_buttons')[0].style.textAlign = 'center';
});

jQuery('.img_file').change(function() {
    var this_elem = jQuery(this)[0];
    var calc_id = jQuery(this).data('calc-id');
    var elem_div = jQuery(this).siblings('.div_imgs')[0];
    var formdata = new FormData();
    if (this_elem.files.length < 1) {
        return;
    }

    formdata.append('calc_id', calc_id);
    jQuery.each(this_elem.files, function(key, value) {
        formdata.append(key, value);
    });
    console.log(jQuery(this).data('img-type'));
    formdata.append('type', jQuery(this).data('img-type'));
    
    jQuery.ajax({
        type: 'POST',
        url: "index.php?option=com_gm_ceiling&task=calculation.upload_img",
        cache: false,
        processData: false, // Не обрабатываем файлы (Don't process the files)
        contentType: false, // Так jQuery скажет серверу что это строковой запрос
        data: formdata,
        success: function(data) {
            console.log(data);
            for (var i = data.length, img; i--;) {
                img = document.createElement("IMG");
                img.setAttribute('src', data[i]);
                img.setAttribute('class', 'uploaded_calc_img');
                elem_div.appendChild(img);
                img.onclick = clickUploadedCalcImg;
                //elem_div.innerHTML += '<img src="'+data[i]+'" class="uploaded_calc_img">';
            }
        },
        dataType: "json",
        timeout: 20000,
        error: function(data) {
            console.log(data);
            var n = noty({
                theme: 'relax',
                timeout: 2000,
                layout: 'center',
                maxVisible: 5,
                type: "error",
                text: "Ошибка!"
            });
        }
    });
});

function clickUploadedCalcImg() {
    jQuery("#modal_window_img")[0].innerHTML = '<img src="'+this.src+'" class="big_uploaded_img">';
    jQuery("#input_delete_uploaded_calc_img").val(this.getAttribute('data-path'));
    jQuery("#btn_close_img").show();
    jQuery("#btn_del_img").show();
    jQuery("#img_modal_container").show();
    jQuery("#modal_window_img").show();
}

jQuery("#btn_del_img").click(function() {
    noty({
        theme: 'relax',
        layout: 'center',
        timeout: false,
        type: "info",
        text: "Удалить изображение?",
        buttons:[
            {
                addClass: 'btn btn-primary', text: 'Да', onClick: function ($noty) {
                    jQuery.ajax({
                        type: 'POST',
                        url: "index.php?option=com_gm_ceiling&task=calculation.delete_img",
                        data: {
                            path: jQuery("#input_delete_uploaded_calc_img").val()
                        },
                        success: function(data) {
                            if (data) {
                                location.reload();
                            } else {
                                noty({
                                    theme: 'relax',
                                    timeout: 2000,
                                    layout: 'center',
                                    maxVisible: 5,
                                    type: "warning",
                                    text: "Изображение не удалено!"
                                });
                            }
                        },
                        dataType: "json",
                        timeout: 20000,
                        error: function(data) {
                            console.log(data);
                            var n = noty({
                                theme: 'relax',
                                timeout: 2000,
                                layout: 'center',
                                maxVisible: 5,
                                type: "error",
                                text: "Ошибка!"
                            });
                        }
                    });
                }
            },
            {
                addClass: 'btn btn-primary', text: 'Отмена', onClick: function($noty) {
                    $noty.close();
                }
            }
        ]
    });
});

jQuery('.uploaded_calc_img').click(clickUploadedCalcImg);

jQuery("#btn_close_img").click(function(){
    jQuery("#btn_close_img").hide();
    jQuery("#btn_del_img").hide();
    jQuery("#img_modal_container").hide();
    jQuery("#modal_window_img").hide();
});

jQuery('[name="add_calc_comment"]').click(function(){
    var button = jQuery(this),
        calc_id = button.data('calc_id'),
        textarea = button.closest('div').find('[name="calc_comment"]'),
        comment = textarea.val();
    if(!empty(comment)) {
        jQuery.ajax({
            type: 'POST',
            url: "index.php?option=com_gm_ceiling&task=calculation.saveComment",
            data: {
                calc_id: calc_id,
                comment: comment
            },
            success: function (data) {
                var n = noty({
                    theme: 'relax',
                    timeout: 2000,
                    layout: 'center',
                    maxVisible: 5,
                    type: "success",
                    text: "Cохранено!"
                });
            },
            dataType: "json",
            timeout: 20000,
            error: function (data) {
                console.log(data);
                var n = noty({
                    theme: 'relax',
                    timeout: 2000,
                    layout: 'center',
                    maxVisible: 5,
                    type: "error",
                    text: "Ошибка сохранения!"
                });
            }
        });
    }
    else{
        var n = noty({
            theme: 'relax',
            timeout: 2000,
            layout: 'center',
            maxVisible: 5,
            type: "error",
            text: "Пустой комментарий!"
        });
    }
});
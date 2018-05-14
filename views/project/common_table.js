var project_id;

function accept_global_variables(pr_id)
{
    project_id = pr_id;
}

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
    
    jQuery("#calcs_self_components_total span.sum").data('oldval',jQuery("#calcs_self_components_total span.sum").text());
    check_min_sum(jQuery("#calcs_self_canvases_total span.sum").text());
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
    jQuery("#project_sum").val(jQuery("#project_total_discount span.sum").text());
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
    let project_id = project_id.value;
    let calc_ids = get_selected_calcs();
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
jQuery("#send_all_to_email3").click(function () {
    regenerate_common_estimate();
    var email = jQuery("#all-email3").val();
    var id  = jQuery("#project_id").val();
    var client_id = jQuery("#client_id").val();
    var filenames = [];
    var formData = new FormData();
    jQuery.each(jQuery('#dopfile2')[0].files, function (i, file) {
        formData.append('dopfile2', file)
    });
    formData.append('filenames', JSON.stringify(filenames));
    formData.append('email', email);
    formData.append('id', id);
    formData.append('type', 2);
    formData.append('client_id', client_id);
    jQuery.ajax({
        url: "index.php?option=com_gm_ceiling&task=send_estimate",
        data: formData,
        type: "POST",
        dataType: 'json',
        processData: false,
        contentType: false,
        cache: false,
        async:false,
        success: function (data) {
            var n = noty({
                theme: 'relax',
                layout: 'center',
                maxVisible: 5,
                type: "success",
                text: "Общая смета отправлена!"
            });

        },
        error: function (data) {
            var n = noty({
                theme: 'relax',
                layout: 'center',
                maxVisible: 5,
                type: "error",
                text: "ошибка отправки"
            });
        }
    });

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
    jQuery("#project_sum").val(jQuery("#project_total_discount span.sum").text());
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
    var id = project_id.value;
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
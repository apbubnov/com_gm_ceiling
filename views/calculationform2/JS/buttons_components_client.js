const help_block_n6 = '<span class="airhelp"><img src="/images/vstavka.jpg" width="280"/><br>Между стеной и натяжным потолком после монтажа остается технологический зазор 5мм, который закрывается декоративной вставкой.<br>В расчет входит вставка по периметру + монтажная работа по установке вставки</span>';

const help_block_n12 = '<span class="airhelp">В расчет входит:<ul style="text-align: left;"><li>3 самореза (ГДК 3,5*51)</li><li>3 дюбеля (красн. 6*51)</li><li>8 саморезов (п/сф 305*9,5 цинк)</li><li>1 шуруп кольцо (6*40)</li><li>2 клеммные пары</li><li>1 круглое кольцо (50)</li><li>1 платформа под люстру (тарелка)</li><li>4 подвеса прямых (П 60 (0,8))</li><li>0,5м провода (ПВС 2*0,75)</li></ul>+ монтажная работа по установке люстр</span>';

const help_block_n13 = '<span class="airhelp">В расчет входит:<ul style="text-align: left;"><li>4 самореза (ГДК 3,5*51)</li><li>2 дюбеля (красн. 6*51)</li><li>4 саморезов (п/сф 305*9,5 цинк)</li><li>термоквадрат или круглое кольцо</li><li>1 клеммная пара</li><li>1 платформа под светильник (квадратная или круглая)</li><li>2 подвеса прямых (П 60 (0,8))</li></ul>+ монтажная работа по установке светильников</span>';

const help_block_n14 = '<span class="airhelp">В расчет на 1 трубу входит 1 пластина</br>+ монтажная работа по обводу трубы</span>';

const help_block_n16 = '<span class="airhelp">Шторный карниз можно крепить на потолок двумя способами.<br> Видимый:<br><img src="/images/karniz.jpg" width="280"/><br>В расчет на 1м карниза входит:<br><ul style="text-align: left;"><li>1м бруса (40*50)</li><li>6 саморезов (ГДК 3,5*51)</li><li>6 дюбелей (красн. 6*51)</li><li>9 саморезов (ГДК 3,5*41)</li><li>3 подвеса прямых (П 60 (0,8))</li></ul>Скрытый:<br><img src="/images/karniz2.jpg" width="280"/><br>В расчет на 1м скрытого карниза входит:<br><ul style="text-align: left;"><li>1м бруса (40*50)</li><li>6 саморезов (ГДК 3,5*51)</li><li>6 дюбелей (красн. 6*51)</li><li>13 саморезов (ГДК 3,5*41)</li><li>3 подвеса прямых (П 60 (0,8))</li><li>2 белых кронштейна (15*12,5)</li></ul>+ монтжаная работа по установке шторного карниза</span>';

const help_block_n21 = '<span class="airhelp">В расчет на 1 пожарную сигнализацию входит:<ul style="text-align: left;"><li>3 дюбеля (красн. 6*51)</li><li>1 клеммная пара</li><li>1 круглое кольцо (50)</li><li>1 платформа для карнизов (70*100)</li><li>3 подвеса прямых (П 60 (0,8))</li><li>3 самореза (ГКД 3,5*51)</li><li>6 саморезов (п/сф 3,5*9,5 цинк)</li></ul>+ монтжаная работа по установке пожарной сигнализации</span>';

const help_block_n22 = '<span class="airhelp">В расчет на 1 вентиляцию входит:<br><ul style="text-align: left;"><li>2 дюбеля (красн. 6*51)</li><li>1 квадратная или круглая платформа</li><li>4 самореза (ГКД 3,5*51)</li><li>4 самореза (п/сф 3,5*9,5 цинк)</li><li>1 термоквадрат или круглое кольцо</li></ul>В расчет на 1 электровытяжку входит:<br><ul style="text-align: left;"><li>2 дюбеля (красн. 6*51)</li><li>1 клеммная пара</li><li>1 круглая или квадратная платформа</li><li>1 круглое кольцо или термоквадрат</li><li>2 подвеса прямых (П 60 (0,8))</li><li>0,5м провода (ПВС 2*0,75)</li><li>4 самореза (ГКД 3,5*51)</li><li>4 самореза (п/сф 3,5*9,5 цинк)</li></ul>+ монтжаная работа по установке вытяжки</span>';

const help_block_n29 = '<span class="airhelp">Для перехода без нишей в расчет входит 343 р. + маржа на комплектующие</br>Для перехода с нишей в расчет входит 532 о. + маржа на комплектующие</br>+ монтажная работа "переход уровня с нишей или без"</span>';

const help_block_height = '<span class="airhelp">В расчет входит добавочная стоимость на высоту помещения выше 3х метров</span>';

const help_block_need_mount = null;
const help_block_light_cptn = null;
const help_block_oter_mount_cptn = null;
const help_block_basic_work = null;

let discount_el = create_single_input(1,"new_discount","jform[discount]","","","hidden","0","100");
let n28_el = create_single_input(1,"jform_n28","jform[n28]","","","hidden");
let arr_blocks = [
    {block_id:"block_basic_work",btn_cont_id:"basic_work",prev_id:"add_mount_and_components",btn_id:"btn_basic_work",btn_text:"Основные работы",need_ajax : 0,kind_btn:"1", parent: "btn_add_components",
        children: [
            {block_id:"block_n6",btn_cont_id:"btn_cont_n6",prev_id:"block_basic_work",btn_id:"btn_n6",btn_text:"Декоративная вставка",need_ajax : 0,kind_btn:"0", img: "insert.png", parent: "basic_work"},
            {block_id:"block_n14",btn_cont_id:"btn_cont_n14",prev_id:"block_n6",btn_id:"btn_n14",btn_text:"Добавить трубы входящие в потолок",need_ajax : 1,kind_btn:"0", img: "pipes.png", parent: "basic_work"},
            {block_id:"block_n16",btn_cont_id:"btn_cont_n16",prev_id:"block_n14",btn_id:"btn_n16",btn_text:"Добавить шторный карниз",need_ajax : 1,kind_btn:"0", img: "cornice.png", parent: "basic_work"}
        ]
    },
    {block_id:"block_light_cptn",btn_cont_id:"head_lighting",prev_id:"block_basic_work",btn_id:"btn_light_cptn",btn_text:"Освещение",need_ajax : 0,kind_btn:"1", parent: "btn_add_components",
        children: [
            {block_id:"block_n12",btn_cont_id:"btn_cont_n12",prev_id:"block_light_cptn",btn_id:"btn_n12",btn_text:"Добавить люстры",need_ajax : 0,kind_btn:"0", img: "lamp.png", parent: "light_cptn"},
            {block_id:"block_n13",btn_cont_id:"btn_cont_n13",prev_id:"block_n12",btn_id:"btn_n13",btn_text:"Добавить светильники",need_ajax : 1,kind_btn:"0", img: "lamps.png", parent: "light_cptn"},
        ]
    },
    {block_id:"block_oter_mount_cptn",btn_cont_id:"head_other_mount",prev_id:"block_light_cptn",btn_id:"btn_oter_mount_cptn",btn_text:"Прочие работы",need_ajax : 0,kind_btn:"1", parent: "btn_add_components",
        children: [
            {block_id:"block_height",btn_cont_id:"btn_cont_height",prev_id:"block_oter_mount_cptn",btn_id:"btn_height",btn_text:"Высота помещения",need_ajax : 0,kind_btn:"0", img: "height.png", parent: "oter_mount_cptn"},
            {block_id:"block_n21",btn_cont_id:"btn_cont_n21",prev_id:"block_height",btn_id:"btn_n21",btn_text:"Пожарная сигнализация",need_ajax : 0,kind_btn:"0", img: "firealarm.png", parent: "oter_mount_cptn"},
            {block_id:"block_n22",btn_cont_id:"btn_cont_n22",prev_id:"block_n21",btn_id:"btn_n22",btn_text:"Вентиляция",need_ajax : 1,kind_btn:"0", img: "hood.png", parent: "oter_mount_cptn"},
            {block_id:"block_n29",btn_cont_id:"btn_cont_n29",prev_id:"block_n22",btn_id:"btn_n29",btn_text:"Переход уровня",need_ajax : 1,kind_btn:"0", img: "perehod.png", parent: "oter_mount_cptn"},
        ]
    },
    {block_id:"block_need_mount",btn_cont_id:"btn_cont_need_mount",prev_id:"block_oter_mount_cptn",btn_id:"btn_need_mount",btn_text:"Отменить монтаж",need_ajax : 0,kind_btn:"1", img: "nomounting.png", parent: "btn_add_components"},
];

arr_blocks.forEach(function(item){
    generate_block(item);
});
jQuery("#add_mount_and_components").append(n28_el);
jQuery("#add_mount_and_components").append(discount_el);

jQuery("#jform_n28").val(0);
jQuery("#new_discount").val(50);

let n_data = {};
let n6_src = {
    name : 'jform[n6]',
    values : [
        {id:'jform_n6_2',value:0,text:"Вставка не нужна",selected:true},
        {id:'jform_n6_1',value:"color",text:"Цветная вставка"},
        {id:'jform_n6',value:314,text:"Белая вставка"},
      
    ]
};
let n6 =  create_radios_group(n6_src);
let n12 = create_single_input(1,"jform_n12","jform[n12]","Введите кол-во люстр:","Кол-во,шт.","tel");
let n21 = create_single_input(1,"jform_n21","jform[n21]","","Кол-во,шт.","tel");
let height_src = {
    name : 'jform[height]',
    values : [
        {id:'max_height',value:1,text:"больше 3х метров"},
        {id:'min_height',value:0,text:"меньше 3х метров",selected:true}
    ]
    
}
let height = create_radios_group(height_src);

let cornice_src = {
    name : 'jform[n16]',
    values : [
        {id:'jform_n16',value:0,text:"Обычный карниз",selected:true},
        {id:'jform_n16_1',value:1,text:"Скрытый карниз"}
    ]
}
let n16 = create_single_input(1,"jform_n27","jform[n27]","Введите длину шторного карниза в МЕТРАХ","м.","tel")
n16 += create_radios_group(cornice_src);

let need_mount_src = {
    name : 'need_mount',
    values : [
        {id:'with_mount',value:1,text:"Нужен",selected:true},
        {id:'without',value:0,text:"Не нужен"}
    ]
}
let need_mount =  create_radios_group(need_mount_src);

let n13_src = {
    id : 'jform_n13',
    name : 'jform[n13]',
    columns:[
        {div_class:'advanced_col1',text:'Кол-во',input_name:"n13_count[]",input_id:"n13_count",input_type:1},
        {div_class:'advanced_col2',text:'Вид',input_name:"n13_type[]",input_id:"n13",input_type:2},
        {div_class:'advanced_col3',text:'Диаметр',input_name:"n13_ring[]",input_id:"n13_1",input_type:2},
        {div_class:'advanced_col4 center',text:'<label><i class="fa fa-trash" aria-hidden="true"></i></label>'}
    ]
}
let n13 = create_block_with_divs(n13_src);

let ecola_src = {
    id : 'jform_ecola',
    name : 'jform[ecola]',
    columns:[
        {div_class:'advanced_col1',text:'Кол-во',input_name:"ecola_count[]",input_id:"ecola_count",input_type:1},
        {div_class:'advanced_col2',text:'Цвет',input_name:"light_color[]",input_id:"",input_type:2},
        {div_class:'advanced_col3',text:'Лампа',input_name:"light_lamp_color[]",input_id:"",input_type:2},
        {div_class:'advanced_col4 center',text:'<label><i class="fa fa-trash" aria-hidden="true"></i></label>'}
    ]
}

n13 +='<h4>Вы можете приобрести светильники у нас:</h4>';
n13 += create_block_with_divs(ecola_src);

let n14_src = {
    id : 'jform_n14',
    name : 'jform[n14]',
    columns:[
        {div_class:'advanced_col1',text:'Кол-во',input_name:"n14_count[]",input_id:"n14_count",input_type:1},
        {div_class:'advanced_col5',text:'Диаметр',input_name:"n14_type[]",input_id:"",input_type:2},
        {div_class:'advanced_col4 center',text:'<label><i class="fa fa-trash" aria-hidden="true"></i></label>'}
    ]
}
let n14 = create_block_with_divs(n14_src);

let n15_src = {
    id : 'jform_n15',
    name : 'jform[n15]',
    columns:[
        {div_class:'advanced_col1',text:'Кол-во',input_name:"n15_count[]",input_id:"n15_count",input_type:1},
        {div_class:'advanced_col2',text:'Тип',input_name:"n15_type[]",input_id:"n15",input_type:2},
        {div_class:'advanced_col3',text:'Длина',input_name:"n15_size[]",input_id:"n15_1",input_type:2},
        {div_class:'advanced_col4 center',text:'<label><i class="fa fa-trash" aria-hidden="true"></i></label>'}
    ]
}
n16 += '<h4>Вы можете приобрести карнизы у нас:</h4>';
n16 += create_block_with_divs(n15_src);

let n22_src = {
    id : 'jform_n22',
    name : 'jform[n22]',
    columns:[
        {div_class:'advanced_col1',text:'Кол-во',input_name:"n22_count[]",input_id:"n22_count",input_type:1},
        {div_class:'advanced_col2',text:'Тип',input_name:"n22_type[]",input_id:"n22",input_type:2},
        {div_class:'advanced_col3',text:'Размер',input_name:"n22_diam[]",input_id:"n22_1",input_type:2},
        {div_class:'advanced_col4 center',text:'<label><i class="fa fa-trash" aria-hidden="true"></i></label>'}
    ]
}
let n22 =  create_block_with_divs(n22_src);

let n29_src = {
    id : 'jform_n29',
    name : 'jform[n29]',
    columns:[
        {div_class:'advanced_col1',text:'Кол-во',input_name:"n29_count[]",input_id:"n29_count",input_type:1},
        {div_class:'advanced_col5',text:'Тип',input_name:"n29_type[]",input_id:"n29",input_type:2},
        {div_class:'advanced_col4 center',text:'<label><i class="fa fa-trash" aria-hidden="true"></i></label>'}
    ]
}
let n29 =  create_block_with_divs(n29_src);

/* контейнер и колонки */
function create_container(cnt_id,col_id, parent){
    return `<div class = "container" id = "${cnt_id}" data-parent="${parent}">
                        <div class = "row">
                            <div class = "col-sm-4"></div>
                                <div class = "col-sm-4" id = "${col_id}"></div>
                            <div class = "col-sm-4"></div>
                        </div>
                    </div>`;
}

function create_block_btn(class_name,style,btn_id,btn_text,help,cont_id,need_ajax,img,style_btn){
    if (eval(help) == null) {
        return `<button type="button" id="${btn_id}" data-cont_id = "${cont_id}" data-need_ajax = "${need_ajax}"  class="${style_btn}">
                    <table style="width: 100%;">
                        <tr>
                            <td style="width: calc(100% - 25px);">
                                <label class="no_margin">${btn_text}</label>
                            </td>
                            <td style="width: 25px;">
                                <i class="fa fa-angle-down" style="color: #414099;"></i>
                            </td>
                        </tr>
                    </table>  
                </button>
        `;
    } else {
        return `<table class="${class_name}" style="${style}">
                    <tr>
                        <td class="td_calcform1">
                            <button type="button" id="${btn_id}" data-cont_id = "${cont_id}" data-need_ajax = "${need_ajax}"  class="${style_btn}">
                                <table style="width: 100%;">
                                    <tr>
                                        <td style="width: 25px;">
                                            <img src="../../../../../images/${img}" class="img_calcform">
                                        </td>
                                        <td style="width: calc(100% - 25px);">
                                            <label class="no_margin">${btn_text}</label>
                                        </td>
                                    </tr>
                                </table>
                            </button>
                        </td>
                        <td class="td_calcform2">
                            <div class="btn-primary help" style="padding: 5px 10px; border-radius: 5px; height: 38px; width: 38px; margin-left: 5px;">
                                <div class="help_question">?</div>
                                    ${eval(help)}
                            </div>
                        </td>
                    </tr>
                </table>`;
    }
}
/* ______________ */
/* radio buttons */
function create_single_radio(name,id,value,text,selected){
    
    let checked_attr = (selected) ? 'checked = "checked"': '';
    return `<div style="display: inline-block; width: 100%;">
                <input name="${name}" id="${id}" class="radio" value="${value}" ${checked_attr} type="radio">
                <label for="${id}">${text}</label>
            </div>`;
}

function create_radios_group(object){
    result = '<div class="form-group" style="text-align: left; margin-left: calc(50% - 47px);">';
   
    for(let i=object.values.length;i--;){
        
        result += create_single_radio(object.name,object.values[i].id,object.values[i].value,object.values[i].text,(object.values[i].selected) ? object.values[i].selected : null);
    }
    result += '</div>';
    return result;

}
/* _____________ */
//инпут
function create_single_input(input_type,id,name,text,placeholder,type,min=null,max=null){
    let lbl_str = `<div style="width: 100%; text-align: left;">
                        <label id="${id}-lbl" for="${id}" class="" >${text}</label>
                    </div>`;
    if(input_type == 1){
        return `<div class="form-group">
                    ${(text) ? lbl_str : ""}
                    <input name="${name}" id="${id}"  class="form-control" placeholder="${placeholder}" type="${type}" min = "${(min) ? min : ""}" max = "${(max) ? max : ""}">
                </div>`;
    }
    if(input_type == 2){
        return `<div class="form-group">
                <select name="${name}" id="${id}"  class="form-control" placeholder="${placeholder}"></select>
            </div>`; 
    }
    if(input_type == 3){
        return `<div class="form-group Area Type">
                    <input id="Type" name="components_stock_name[]" autocomplete="off" NameDB="CONCAT(components.title,' ',options.title)" onclick="GetList(this, ['Type'], ['Type']);" onkeyup="GetList(this, ['Type'], ['Type']);" onblur="ClearSelect(this)" class='form-control Input Type' type='text'>
                    <input name="${name}" id="ID"  class="form-control" placeholder="${placeholder}" type="${type}" hidden min = "${(min) ? min : ""}" max = "${(max) ? max : ""}">
                    <div class="Selects Type"></div>
                </div>`;
    }
   
}
//кнопка добавить
function create_add_button(id){
    return `<button id="${id}" class="btn btn-primary add" style="margin-bottom:15px" type="button">Добавить</button>`
}
function create_block_with_divs(object){
    let btn_id = `add_${object.id}`;
    let div_id = `${object.id}_block_html`;
    let div = ` <div id = "${div_id}">
                    ${create_body(object.columns)}
                </div>
                ${create_add_button(btn_id)}`;
    result = `<div id = "${object.id}_block">
                ${create_captions(object.columns)}
                ${(!document.getElementById(div_id))? div : ""}
               
              </div>`;
    return result;
              
}
function create_captions(columns){
    result = `<div class="form-group" style="margin-bottom: 0em;">`;
    for(let i =0;i<columns.length;i++){
        result+= `<div class = "${columns[i].div_class}">${columns[i].text}</div>`;
    }
    result+=`</div>`;
    return result;
}

function  auto_replace (e){
    if(e.keyCode == 44){
        this.value += '.';
        e.preventDefault ? e.preventDefault() : e.returnValue = false;
    }
};

function create_body(columns){
    result = `<div class="form-group" style="margin-bottom: 0em;">`;
    for(let i =0;i<columns.length;i++){
        if(columns[i].input_type){
            result+= `<div class = "${columns[i].div_class}">${create_single_input(columns[i].input_type,columns[i].input_id,columns[i].input_name,"",columns[i].text,"")}</div>`;
        }
        else {
            result+=`<div class = "${columns[i].div_class}"><button class="clear_form_group btn btn-danger" type="button"><i class="fa fa-trash" aria-hidden="true"></i></button></div>`;
        }
    }
    result+=`</div>`;
    return result;
}
jQuery(".component-content").on("click", ".add_fields", function () {
    let cont_id = jQuery(this).data('cont_id');
    let need_ajax = jQuery(this).data('need_ajax');
    let var_name = cont_id.replace('block_','');
    let col_id = `jform_${var_name}_inside`;
    let cont =  create_container("",col_id, var_name);
    let element = eval(var_name);

    if(!document.getElementById(col_id)){
        jQuery(`#${cont_id}`).after(cont);
        jQuery(`#${col_id}`).append(element); 
    }
    else{
        jQuery(`#${col_id}`).toggle();
    }
    toggle_color(jQuery(this));

    if(!n_data[var_name]){
        if(need_ajax){
            if(var_name == 'n13'){
                get_n_data('ecola');
            }
            get_n_data(var_name);
        }
    }
    if(var_name == 'n13' || var_name == 'n22' ){
       jQuery(`[name = '${var_name}_type[]']`).change(change_select_event);
    }
    let radios = jQuery('.radio');
    for(let i = radios.length;i--;){
        radios[i].onclick = change_event_radio;
    }
    let btns_add = document.getElementsByClassName('add');
    for(let i = btns_add.length;i--;){
        btns_add[i].onclick = btn_add_event;
    }
    jQuery("[name = 'jform[n6]'").click(change_radio);
    let inputs = jQuery('input[type=tel]');
    [].forEach.call(inputs,function(el){
        el.addEventListener("keypress",auto_replace);
    });
}); 

jQuery(".component-content").on("click", ".btn_calc", function () {
    let id_block = jQuery(this).closest("button").attr("data-cont_id");
    let parent = id_block.replace("block_", "");
    if (parent == 'need_mount') {
        let col_id = `jform_${parent}_inside`;
        let cont =  create_container("",col_id, parent);
        let element = eval(parent);
        if(!document.getElementById(col_id)){
            jQuery(`#${id_block}`).after(cont);
            jQuery(`#${col_id}`).append(element); 
        }
        else{
            jQuery(`#${col_id}`).toggle();
        }
        jQuery("[name = 'need_mount']").click(function(){
            jQuery("[name = 'need_mount']").removeAttr('fix');
            jQuery(this).attr('fix',true);
        });
        if(jQuery("#without").attr("fix") != "true" ){
            jQuery("#with_mount").attr("checked",true);
        }    
    } else {
        if (jQuery(`[data-parent = "${parent}"]`).length < 1) {
            arr_blocks.forEach(function(item) {
                if (item.block_id == id_block && item.parent) {
                    item.children.forEach(function(item2){
                        generate_block(item2);
                    });
                }
            });
        } else {
            arr_blocks.forEach(function(item) {
                if (item.block_id == id_block && item.parent) {
                    item.children.forEach(function(item2){
                       let id = item2.block_id.replace("block_","");
                        if (jQuery(`#jform_${id}_inside`).closest('.col-sm-4').css("display") != "none") {
                            jQuery(`#jform_${id}_inside`).closest('.col-sm-4').hide();
                            toggle_color(jQuery(`#btn_${id}`));
                        }
                    });
                }
            });
            jQuery(`[data-parent = "${parent}"]`).toggle();
        }    
    }
});

function in_array(array,value){
    let result = false;
    for(let i = array.length; i--;){
        if(array[i] === value){
            result = true;
            break;
        }
    }
    return result;
}

let change_event_radio = function(){
    jQuery(`[name = '${this.name}']`).attr('checked',false);

    jQuery(this).attr("checked",true); 
    
    if(this.name == 'jform[n28]' && this.value !=3){
        if(jQuery('[name = "need_mount"]').length){
            jQuery("#jform_need_mount_inside").show();
        }
        else{
            jQuery("#btn_need_mount").trigger("click");

        } 
        if(jQuery('#without').attr("fix")!="true"){
            jQuery(`[name = 'need_mount']`).attr('checked',false);
            jQuery('#with_mount').attr("checked",true); 
        }

        
    }
   if(this.name == 'jform[n28]' && this.value ==3){
         if( jQuery('#with_mount').attr("fix") !="true"){
            jQuery(`[name = 'need_mount']`).attr('checked',false);
            jQuery('#without').attr("checked",true); 
            jQuery("#jform_need_mount_inside").hide();
        }

   }
};
let change_radio = function(){
    if(this.id == "jform_n6_1"){
        if(jQuery("#n6_color_cnt").length){

            jQuery("#n6_color_cnt").show();
        }
        else{
            jQuery("#jform_n6_inside").append(create_n6_button());
            document.getElementById("btn_select_n6_color").onclick = show_color_switch;
            jQuery("#n6_color_cnt").show();
        }
    }
    else{
            jQuery("#n6_color_img").hide();
            jQuery("#jform_n1").val("");
            jQuery("#n6_color_img").prop("src","");
            jQuery("#n6_color_cnt").hide();
        }
};

let show_color_switch = function(){    
        data = n6_colors;
        var items = "<div class='center'>";
        jQuery.each( data, function( key, val ) {
            items += "<button class='click_color_1' style='width: 70px; height: 80px; display: inline-block; float: left; margin:3px;' type='button' data-color_id_1='"+ val.id + "' data-color_img_1='" + val.file + "'><img style='width: 70px; height: 70px; display: inline-block; float: left; margin:3px;' src='"+ val.file + "' alt='' /><div class='color_title1'>" + val.title + "</div><div class='color_title2'>" + val.title+ "</div></button>";
        
        });
        items += "</div>";
        modal({
            type: 'info',
            title: 'Выберите цвет',
            text: items,
            size: 'large',
            onShow: function() {
                jQuery(".click_color_1").click(function(){ 
                    jQuery("#n6_color_img").prop( "src", jQuery( this ).data("color_img_1"));
                    jQuery("#jform_n6_1").val(jQuery( this ).data("color_id_1"));
                    jQuery("#n6_color_img").show();


                });
            },
            callback: function(result) {
                

            },
            autoclose: false,
            center: true,
            closeClick: true,
            closable: true,
            theme: 'xenon',
            animate: true,
            background: 'rgba(0,0,0,0.35)',
            zIndex: 1050,
            buttonText: {
                ok: 'Позвоните мне',
                cancel: 'Закрыть'
            },
            template: '<div class="modal-box"><div class="modal-inner"><div class="modal-title"><a class="modal-close-btn"></a></div><div class="modal-text"></div><div class="modal-buttons"></div></div></div>',
            _classes: {
                box: '.modal-box',
                boxInner: ".modal-inner",
                title: '.modal-title',
                content: '.modal-text',
                buttons: '.modal-buttons',
                closebtn: '.click_color_1'
            }
        });                     
    

};

function get_color_file(value){
    for(let i= n6_colors.length;i--;){
        if(n6_colors[i].id == value){
            return n6_colors[i].file;
        }
    }
}

function find_radio_element(elements,value){
    for(let j = elements.length;j--;){
        if(elements[j].value == value){
            return elements[j];
        }
    }
}

function check_radio(elements,value){
    jQuery(find_radio_element(elements,value)).attr('checked',true);
}
function create_n6_button(){
    return `<div id = n6_color_cnt>
                <div style="width: 100%; text-align: left;">
                    <label >Выберите цвет:</label>
                </div>
                <button id="btn_select_n6_color" class="btn btn-primary btn-width" type="button" >Цвет <img id="n6_color_img" class="calculation_color_img" style='width: 50px; height: 30px; display:none;'/></button>
            </div>`;
}
let change_select_event = function(){
    let options ;
    let target_name = "";
    let index = getSelectIndex(jQuery(`[name = '${this.id}_type[]']`),this);
    if(this.value == 3 || this.value == 6 || this.value ==8 ){
        options = create_options(n_data[this.id][`${this.id}_square`]);
        if(this.id == 'n13'){
            target_name = `${this.id}_ring[]`;
        }
        if(this.id == 'n22'){
            target_name = `${this.id}_diam[]`;
        }
         
        jQuery('option', jQuery(`[name = '${target_name}']`)[index]).remove();
        for(let i=0;i<options.length;i++){
            jQuery(`[name = '${target_name}']`)[index].append(options[i]);
        }
    }
    if(this.value == 2 || this.value == 5 || this.value == 7 ){
        if(this.id == 'n13'){
            options  = create_options(n_data[this.id][`${this.id}_ring`]);
            target_name = `${this.id}_ring[]`;
        }
        if(this.id == 'n22'){
            options  = create_options(n_data[this.id][`${this.id}_diam`]);
            target_name = `${this.id}_diam[]`;
        }
        jQuery('option', jQuery(`[name = '${target_name}']`)[index]).remove();
        for(let i=0;i<options.length;i++){
            jQuery(`[name = '${target_name}']`)[index].append(options[i]);
        } 
    }
}
function check_select_option(name,index,value){
    let element =  document.getElementsByName(name)[index];
    let options = element.options;
    for(let i = options.length;i--;){
        if(options[i].value == value){
            jQuery(options[i]).attr('selected','selected');
            break;
        }
    }
    element.dispatchEvent(new Event("change"));

}

function getSelectIndex(selects,obj){
    for (key in selects) {
        if(selects[key]==obj){
            
            index = key;
        }
        
    }
    return index;
}

function get_n_data(var_name){
    jQuery.ajax({
        type: 'POST',
        url: 'index.php?option=com_gm_ceiling&task=getComponentsToCalculationForm',
        data: {
            component_code: var_name
        },
        async:false,
        success: function (data) {
            n_data[var_name] = data;
            let select_names = Object.keys(data);
            for(let i = select_names.length;i--;){
                let items = data[select_names[i]];
                let options = create_options(items);
                jQuery(`[name = '${select_names[i]}[]']`).append(options);
            }
        },
        dataType: "json",
        timeout: 10000,
        error: function (data) {
            noty({
                theme: 'relax',
                layout: 'center',
                timeout: 1500,
                type: "error",
                text: "Сервер не отвечает!"
            }); 
        }
    });
}

let btn_add_event = function(){
    
    let id = this.id.replace("add_","");
    let html = create_body(eval(`${id.replace("jform_","")}_src`).columns);
    let select_names = {};
    jQuery(`#${id}_block_html`).append(html);
    let select_data; 
    if(id == 'n15' || id.replace("jform_","") == 'n15' ){
        select_data = n_data['n16'];
    }
    else{
        select_data = n_data[`${id.replace("jform_","")}`];
    }
    /* добавил чтобы не сыпалась ошибка в консоль */
    if(select_data){
        select_names = Object.keys(select_data);
    }
    /* ____ */
    for(let i = select_names.length;i--;){
        let items = select_data[select_names[i]];
        let options = create_options(items);
        jQuery(`[name = '${select_names[i]}[]']`).last().append(options);
    }
    jQuery(".clear_form_group").click(function(){
        jQuery(this).closest(".form-group").remove();
    });
    //событие на изменение селектов
    let wanted_id = `${id.replace("jform_","")}_type[]`;
    if(wanted_id == 'n13_type[]' || wanted_id == 'n22_type[]' || wanted_id == 'n15_type[]'){
        let classname = jQuery(`[name = '${wanted_id}'`);
        Array.from(classname).forEach(function(element) {
            element.addEventListener('change',change_select_event);
        });
    }
};
function create_options(data){
    let options = [];
    let option;
    for(let i = 0;i<data.length;i++){
        option = new Option(data[i].title,data[i].id);
        if(data[i].id == 19 || data[i].id == 66){
            option = new Option(data[i].title,data[i].id,true,true);
        }
        options.push(option);
    }
    return options;
}
function toggle_color(element){
    if (element.css("background-color") == "rgb(65, 64, 153)") {
        element.css("background-color", "#010084");
    } else {
        element.css("background-color", "#414099");
    }
}
function generate_block(object){
    let cnt = create_container(object.block_id,object.btn_cont_id, object.parent);
    jQuery(`#${object.prev_id}`).after(cnt);
    if(object.kind_btn == 2) {
        jQuery(`#${object.btn_cont_id}`).append(object.btn_text);
    } else {
        if (object.kind_btn == 1) {
            style_btn = "btn btn_calc";
        } else if (object.kind_btn == 0) {
            style_btn = "btn add_fields";
        }
        let block =  create_block_btn('table_calcform',"margin-bottom: 15px;",object.btn_id,object.btn_text,`help_${object.block_id}`,object.block_id,object.need_ajax, object.img, style_btn);
        jQuery(`#${object.btn_cont_id}`).append(block);
    }
}
var $ = jQuery;
//для подгрузки компонентов со склада
function GetList(e, select, like) {
    var input = $(e),
        Selects = input.siblings(".Selects"),
        ID = input.attr("id"),
        parent = input.closest(".Form"),
        filter = {
            select: {},
            where: {like: {}},
            group: [],
            order: [],
            page: null
        },
        Select = $('<div/>').addClass("Select"),
        Item = $('<div/>').addClass("Item").attr("onclick", "SelectItem(this);");

    input.attr({"clear": "true", "add": "false"});
    Selects.empty();
    Selects.append(Select);
    var Select = Selects.find(".Select");

    filter.select["Type"] = input.attr("NameDB");
    filter.select["ID"] = "options.id";
    filter.where.like["components.title"] = "'%" + input.val() + "%' || true";
    filter.where.like["options.title"] = "'%" + input.val() + "%'";
    filter.page = "/index.php?option=com_gm_ceiling&task=componentform.getComponents";


    if (input.is(":focus")) {
        jQuery.ajax({
            type: 'POST',
            url: filter.page,
            data: {filter: filter},
            success: function (data) {
                data = JSON.parse(data);
                $.each(data, function (i, v) {
                    var I = Item.clone();
                    $.each(v, function (id, s) {
                        if (s === null) s = "Нет";
                        I.attr(id, s);
                        if (id == ID) I.html(s);
                    });
                    Select.append(I);
                });
            },
            dataType: "text",
            timeout: 10000,
            error: function () {
                noty({
                    theme: 'relax',
                    layout: 'center',
                    timeout: 1500,
                    type: "error",
                    text: "Сервер не отвечает!"
                });
            }
        });
    }
}

function SelectItem(e) {
    e = $(e);
    var parent = e.closest(".Area"),
        elements = parent.find(".Input");

    if (typeof e.attr('error') !== 'undefined' && e.attr('error') !== false)
    {
        var error = JSON.parse(e.attr('error'));
        $.each(error, function (i, v) {
            noty({
                theme: 'relax',
                layout: 'center',
                timeout: 1500,
                type: "error",
                text: v
            });
        });
    }
    else if (e.hasClass("Add")) e.closest(".Area").find(".Input").attr({"clear": "false", "add": "true"});
    else {
        elements.val(e.attr("Type"));
        elements.attr({"clear": "false", "add": "false"});
        parent.find("#ID").val(e.attr("ID"));
    }
}

function empty( mixed_var ) { 

    return ( mixed_var === "" || mixed_var === 0  || mixed_var === "0" || mixed_var === null  || mixed_var === false  ||  ( mixed_var instanceof Array && mixed_var.length === 0 ) ||  mixed_var === "[]" || mixed_var ==="{}" );
}

function ClearSelect(e) {
    setTimeout(function () {
        e = $(e);
        if (e.attr("clear") != 'false') e.val("");
        e.siblings(".Selects").empty();
    }, 200);
}
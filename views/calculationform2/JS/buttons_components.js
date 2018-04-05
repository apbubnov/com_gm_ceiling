
let arr_blocks = [
    {block_id:"block_n28",btn_cont_id:"btn_cont_n28",prev_id:"add_mount_and_components",btn_id:"btn_baguette",btn_text:(calculation.n_28) ? "Изменить багет" : "Добавить багет"},
    {block_id:"block_n6",btn_cont_id:"btn_cont_n6",prev_id:"block_n28",btn_id:"btn_insert",btn_text:"Декоративная вставка"},
    {block_id:"block_light_cptn",btn_cont_id:"head_lighting",prev_id:"block_n6",btn_id:"",btn_text:"<h3>Освещение</h3>"},
    {block_id:"block_n12",btn_cont_id:"btn_cont_n1",prev_id:"block_light_cptn",btn_id:"btn_chandelier",btn_text:"Добавить люстры"},
    {block_id:"block_n13",btn_cont_id:"btn_cont_n13",prev_id:"block_n12",btn_id:"btn_fixtures",btn_text:"Добавить светильники"},
    {block_id:"block_n19",btn_cont_id:"btn_cont_n19",prev_id:"block_n13",btn_id:"btn_wire",btn_text:"Провод"},
    {block_id:"block_oter_mount_cptn",btn_cont_id:"head_other_mount",prev_id:"block_n19",btn_id:"",btn_text:"<h4>Прочий монтаж</h4>"},
    {block_id:"block_n14",btn_cont_id:"btn_cont_n14",prev_id:"block_oter_mount_cptn",btn_id:"btn_pipes",btn_text:"Добавить трубы входящие в потолок"},
    {block_id:"block_n16",btn_cont_id:"btn_cont_n16",prev_id:"block_n14",btn_id:"btn_cornice",btn_text:"Добавить шторный карниз"},
    {block_id:"block_n17",btn_cont_id:"btn_cont_n17",prev_id:"block_n16",btn_id:"btn_bar",btn_text:"Закладная брусом"},
    {block_id:"block_n20",btn_cont_id:"btn_cont_n20",prev_id:"block_n17",btn_id:"btn_delimeter",btn_text:"Разделитель"},
    {block_id:"block_n7",btn_cont_id:"btn_cont_n7",prev_id:"block_n20",btn_id:"btn_bar",btn_text:"Метраж стен с плиткой"},
    {block_id:"block_n8",btn_cont_id:"btn_cont_n8",prev_id:"block_n7",btn_id:"btn_stoneware",btn_text:"Метраж стен с керамогранитом"},
    {block_id:"block_n18",btn_cont_id:"btn_cont_n18",prev_id:"block_n8",btn_id:"btn_stoneware",btn_text:"Усиление стен"},
    {block_id:"block_dop_krepezh",btn_cont_id:"btn_dop_krepezh",prev_id:"block_n18",btn_id:"btn_fixture2",btn_text:"Дополнительный крепеж"},
    {block_id:"block_n21",btn_cont_id:"btn_cont_n21",prev_id:"block_dop_krepezh",btn_id:"btn_firealarm",btn_text:"Пожарная сигнализация"},
    {block_id:"block_n22",btn_cont_id:"btn_cont_n22",prev_id:"block_n21",btn_id:"btn_hoods",btn_text:"Вентиляция"},
    {block_id:"block_n23",btn_cont_id:"btn_cont_n23",prev_id:"block_n22",btn_id:"btn_diffuser",btn_text:"Диффузор"},
    {block_id:"block_n30",btn_cont_id:"btn_cont_n30",prev_id:"block_n23",btn_id:"btn_soaring",btn_text:"Парящий потолок"},
    {block_id:"block_n29",btn_cont_id:"btn_cont_n29",prev_id:"block_n30",btn_id:"btn_level",btn_text:"Переход уровня"},
    {block_id:"block_n31",btn_cont_id:"btn_cont_n31",prev_id:"block_n29",btn_id:"btn_notch2",btn_text:"Внутренний вырез (в цеху)"},
    {block_id:"block_n11",btn_cont_id:"btn_cont_n11",prev_id:"block_n31",btn_id:"btn_notch1",btn_text:"Внутренний вырез (на месте)"},
    {block_id:"block_n32",btn_cont_id:"btn_cont_n32",prev_id:"block_n11",btn_id:"btn_draining",btn_text:"Слив воды"},
    {block_id:"block_height",btn_cont_id:"btn_cont_height",prev_id:"block_n32",btn_id:"btn_height",btn_text:"Высота помещения"},
    {block_id:"block_n24",btn_cont_id:"btn_cont_n24",prev_id:"block_height",btn_id:"btn_access",btn_text:"Сложность доступа"},
    {block_id:"block_extra",btn_cont_id:"btn_cont_extra",prev_id:"block_n24",btn_id:"btn_accessories",btn_text:"Другие комплектующие"},
    {block_id:"block_extra2",btn_cont_id:"btn_cont_extra2",prev_id:"block_extra",btn_id:"btn_accessories2",btn_text:"Другие комплектующие со склада"},
    {block_id:"block_extra_mount",btn_cont_id:"btn_cont_extra_mount",prev_id:"block_extra2",btn_id:"btn_mount",btn_text:"Другие работы по монтажу"},
    {block_id:"block_need_mount",btn_cont_id:"btn_cont_need_mount",prev_id:"block_extra_mount",btn_id:"btn_mount2",btn_text:"Отменить монтаж"},
    {block_id:"block_discount",btn_cont_id:"btn_cont_discount",prev_id:"block_need_mount",btn_id:"",btn_text:"Скидка"}
];
arr_blocks.forEach(function(item){
    generate_block(item);
});

function create_container(cnt_id,col_id){
    return `<div class = "container" id = "${cnt_id}">
                        <div class = "row">
                            <div class = "col-sm-4"></div>
                                <div class = "col-sm-4" id = "${col_id}"></div>
                            <div class = "col-sm-4"></div>
                        </div>
                    </div>`;
    
}
function create_block_btn(class_name,style,btn_id,btn_text,help){
    return `<table class="${class_name}" style="${style}">
                        <tr>
                            <td class="td_calcform1">
                                <button type="button" id="${btn_id}" class="btn add_fields">
                                <label class="no_margin">${btn_text}</label>
                                </button>
                            </td>
                            <td class="td_calcform2">
                                <div class="btn-primary help" style="padding: 5px 10px; border-radius: 5px; height: 38px; width: 38px; margin-left: 5px;">
                                    <div class="help_question">?</div>
                                        ${help}
                                </div>
                            </td>
                        </tr>
                    </table>`;
}
function generate_block(object){
    let cnt = create_container(object.block_id,object.btn_cont_id);
    jQuery(`#${object.prev_id}`).after(cnt);
    if(object.btn_id){
        let block =  create_block_btn('table_calcform',"margin-bottom: 15px;",object.btn_id,object.btn_text,eval(`help_${object.block_id}`));
        jQuery(`#${object.btn_cont_id}`).append(block);
    }
    else{
        jQuery(`#${object.btn_cont_id}`).append(object.btn_text);
    }
}


<?php
/**
 * @version    CVS: 0.1.7
 * @package    Com_Gm_ceiling
 * @author     SpectralEye <Xander@spectraleye.ru>
 * @copyright  2016 SpectralEye
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access
defined('_JEXEC') or die;

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');
JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.multiselect');
JHtml::_('formbehavior.chosen', 'select');

$user       = JFactory::getUser();
$userId     = $user->get('id');
$dealerId   = $user->dealer_id;

$project = $_GET['project'];

$mounters_order_model = Gm_ceilingHelpersGm_ceiling::getModel('mountersorder');
$calc_ids = $mounters_order_model->getData($project);
$mas = [];
foreach ($calc_ids as $value) {
    array_push($mas, $value->calculation_id);
}
$data_of_n_pack1 = $mounters_order_model->GetNPack1($mas);
$data_of_n_pack2 = $mounters_order_model->GetNPack2($mas, $project);
$data_of_n_pack3 = $mounters_order_model->GetNPack3($mas);
$data_of_n_pack4 = $mounters_order_model->GetNPack4($mas);
$data_of_n_pack5 = $mounters_order_model->GetNPack5($mas);
$data_of_n_pack6 = $mounters_order_model->GetNPack6($project);

$data_of_n_pack7 = $mounters_order_model->GetNPack7($mas);

$data_of_mp = $mounters_order_model->GetMp($dealerId);

?>

<?=parent::getButtonBack();?>

<link rel="stylesheet" href="components/com_gm_ceiling/views/mountersorder/tmpl/CSS/style.css" type="text/css" />

<div id="content-tar">
    <h2 class="center tar-color-414099">Просмотр проекта №<?php echo $project; ?></h2>

    <ul class="nav nav-tabs" role="tablist" id="tabs">
        <li class="nav-item">
            <a class="nav-link active" data-toggle="tab" href="#summary" role="tab">Общее</a>
        </li>
    </ul>
    <div class="tab-content" id="content-tabs">
        <div id="summary" class="content-tab tab-pane active" role="tabpanel">
            <div class = "overflow">
                <table id="table-all-orders" cols=4>
                    <tr class="caption">
                        <td>Наименование</td>
                        <td>Цена, ₽</td>
                        <td>Количество</td>
                        <td>Стоимость, ₽</td>
                    </tr>
                    <tr id="before-insert" class="caption">
                        <td colspan=3 style="text-align: right;">Итого, ₽:</td>
                        <td id="sum-all"></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
    <div id="buttons-cantainer">
        <button id="begin" class="btn"><i class="fa fa-play fa-tar" aria-hidden="true"></i> Монтаж начат</button>
        <button id="complited" class="btn modal"><i class="fa fa-check" aria-hidden="true"></i> Монтаж выполнен</button>
        <button id="underfulfilled" class="btn modal"><i class="fa fa-pause fa-tar" aria-hidden="true"></i> Монтаж недовыполнен</button>
    </div>
    <div id="modal-window-container-tar">
        <button id="close-tar" type="button"><i class="fa fa-times fa-times-tar" aria-hidden="true"></i></button>
        <div id="modal-window-1-tar">
            <p>Введите примечание:</p>
            <p>
                <textarea id="note"></textarea>
            </p>
            <div id="warning">
                <p>Введите примечание</p>
            </div>
            <p><button type="button" id="save" class="btn btn-primary">Ок</button></p>
        </div>
    </div>
</div>

<script>
    DealerID = <?php echo $dealerId; ?>;

    arr = [];
    mas = [];

    // создание и заполнение вкладок
    function AddTab(DataOfMount) {
        // вкладки потолков
        // из объекта с объектами создаем массив с объектами
        var array = jQuery.map(DataOfMount, function(value, index) {
            arr[index] = value;
        });
        // бежим по массиву
        var n5 = 0, n6 = 0, n7 = 0, n8 = 0, n9 = 0, n11 = 0, n12 = 0, n13 = 0, n14 = 0, n17 = 0, n18 = 0, n20 = 0, n21 = 0,
            n22_56 = 0, n22_78 = 0, n23 = 0, n24 = 0, n27 = 0, n29_23 = 0, n29_24 = 0, n29_25 = 0, n29_26 = 0, n30 = 0, dop_krepezh = 0;
        var n5price = 0, n6price = 0, n7price = 0, n8price = 0, n9price = 0, n11price = 0, n12price = 0, n13price = 0, n14price = 0,
            n17price = 0, n18price = 0, n20price = 0, n21price = 0, n22_56price = 0, n22_78price = 0, n23price = 0, n24price = 0, 
            n27price = 0, n29price_23 = 0, n29price_24 = 0, n29price_25 = 0, n29price_26 = 0, n30price = 0, dop_krepezhprice = 0;
        var sumAll = 0;
        mas_extra_mounting = [];
        mas_j = 0;
        for (var i = 0; i < arr.length; i++) {
            if (arr[i] != undefined) {
                // создаем вкладки с потолками
                name = arr[i].name;
                jQuery("#tabs").append('<li class="nav-item"><a class="nav-link" data-toggle="tab" href="#ceiling'+i+'" role="tab">'+name+'</a></li>');
                tab = '<div id="ceiling'+i+'" class="content-tab tab-pane" role="tabpanel">';
                // картинка
                image = arr[i].image;
                tab += '<div id="ceiling"><img src="/calculation_images/'+image+'.png" id="image-ceiling"></div>'
                // таблица
                tab += '<div class = "overflow">';
                tab += '<table id="table-order-'+i+'" cols=4 class="table-order">';
                tab += '<tr id="caption" class="caption"><td>Наименование</td><td>Цена, ₽</td><td>Количество</td><td>Стоимость, ₽</td></tr>';
                // расчет и вывод наряда
                var sum = 0;
                if (arr[i].n5_count != undefined) {
                    if (arr[i].n5_count != 0) {
                        tab += '<tr><td class="left">Периметр</td>';
                        tab += '<td>'+arr[i].n5_price+'</td>';
                        tab += '<td>'+arr[i].n5_count+'</td>';
                        tab += '<td>'+arr[i].n5_sum+'</td></tr>';
                        sum += +arr[i].n5_sum;
                        n5 += +arr[i].n5_count;
                        n5price = arr[i].n5_price;
                    }
                }
                if (arr[i].n6_count != undefined) {
                    if (arr[i].n6_count != 0) {
                        tab += '<tr><td class="left">Вставка</td>';
                        tab += '<td>'+arr[i].n6_price+'</td>';
                        tab += '<td>'+arr[i].n5_count+'</td>';
                        tab += '<td>'+arr[i].n5_sum+'</td></tr>';
                        sum += +arr[i].n5_sum;
                        n6 += +arr[i].n5_count;
                        n6price = arr[i].n6_price;
                    }
                }
                if (arr[i].n7_count != undefined) {
                    if (arr[i].n7_count != 0) {
                        tab += '<tr><td class="left">Крепление в плитку</td>';
                        tab += '<td>'+arr[i].n7_price+'</td>';
                        tab += '<td>'+arr[i].n7_count+'</td>';
                        tab += '<td>'+arr[i].n7_sum+'</td></tr>';
                        sum += +arr[i].n7_sum;
                        n7 += +arr[i].n7_count;
                        n7price = arr[i].n7_price;    
                    }
                }
                if (arr[i].n8_count != undefined) {
                    if (arr[i].n8_count != 0) {
                        tab += '<tr><td class="left">Крепление в керамогранит</td>';
                        tab += '<td>'+arr[i].n8_price+'</td>';
                        tab += '<td>'+arr[i].n8_count+'</td>';
                        tab += '<td>'+arr[i].n8_sum+'</td></tr>';
                        sum += +arr[i].n8_sum;
                        n8 += +arr[i].n8_count;
                        n8price = arr[i].n8_price; 
                    }
                }
                if (arr[i].n9_count != undefined) {
                    if (arr[i].n9_count != 0) {
                        tab += '<tr><td class="left">Обработка угла</td>';
                        tab += '<td>'+arr[i].n9_price+'</td>';
                        tab += '<td>'+arr[i].n9_count+'</td>';
                        tab += '<td>'+arr[i].n9_sum+'</td></tr>';
                        sum += +arr[i].n9_sum;
                        n9 += +arr[i].n9_count;
                        n9price = arr[i].n9_price; 
                    }
                }
                if (arr[i].n11_count != undefined) {
                    if (arr[i].n11_count != 0) {
                        tab += '<tr><td class="left">Внутренний вырез</td>';
                        tab += '<td>'+arr[i].n11_price+'</td>';
                        tab += '<td>'+arr[i].n11_count+'</td>';
                        tab += '<td>'+arr[i].n11_sum+'</td></tr>';
                        sum += +arr[i].n11_sum;
                        n11 += +arr[i].n11_count;
                        n11price = arr[i].n11_price; 
                    }
                }
                if (arr[i].n12_count != undefined) {
                    if (arr[i].n12_count != 0) {
                        tab += '<tr><td class="left">Установка люстр</td>';
                        tab += '<td>'+arr[i].n12_price+'</td>';
                        tab += '<td>'+arr[i].n12_count+'</td>';
                        tab += '<td>'+arr[i].n12_sum+'</td></tr>';
                        sum += +arr[i].n12_sum;
                        n12 += +arr[i].n12_count;
                        n12price = arr[i].n12_price;
                    }
                }
                if (arr[i].n13_count != undefined) {
                    if (arr[i].n13_count != 0) {
                        tab += '<tr><td class="left">Установка светильников</td>';
                        tab += '<td>'+arr[i].n13_price+'</td>';
                        tab += '<td>'+arr[i].n13_count+'</td>';
                        tab += '<td>'+arr[i].n13_sum+'</td></tr>';
                        sum += +arr[i].n13_sum;
                        n13 += +arr[i].n13_count;
                        n13price = arr[i].n13_price;  
                    }
                }
                if (arr[i].n14_count != undefined) {
                    if (arr[i].n14_count != 0) {
                        tab += '<tr><td class="left">Обвод труб</td>';
                        tab += '<td>'+arr[i].n14_price+'</td>';
                        tab += '<td>'+arr[i].n14_count+'</td>';
                        tab += '<td>'+arr[i].n14_sum+'</td></tr>';
                        sum += +arr[i].n14_sum;
                        n14 += +arr[i].n14_count;
                        n14price = arr[i].n14_price; 
                    }
                }
                if (arr[i].n17_count != undefined) {
                    if (arr[i].n17_count != 0) {
                        tab += '<tr><td class="left">Закладная брусом</td>';
                        tab += '<td>'+arr[i].n17_price+'</td>';
                        tab += '<td>'+arr[i].n17_count+'</td>';
                        tab += '<td>'+arr[i].n17_sum+'</td></tr>';
                        sum += +arr[i].n17_sum;
                        n17 += +arr[i].n17_count;
                        n17price = arr[i].n17_price;
                    }
                }
                if (arr[i].n18_count != undefined) {
                    if (arr[i].n18_count != 0) {
                        tab += '<tr><td class="left">Укрепление стен</td>';
                        tab += '<td>'+arr[i].n18_price+'</td>';
                        tab += '<td>'+arr[i].n18_count+'</td>';
                        tab += '<td>'+arr[i].n18_sum+'</td></tr>';
                        sum += +arr[i].n18_sum;
                        n18 += +arr[i].n18_count;
                        n18price = arr[i].n18_price; 
                    }
                }
                if (arr[i].n20_count != undefined) {
                    if (arr[i].n20_count != 0) {
                        tab += '<tr><td class="left">Разделитель стен</td>';
                        tab += '<td>'+arr[i].n20_price+'</td>';
                        tab += '<td>'+arr[i].n20_count+'</td>';
                        tab += '<td>'+arr[i].n20_sum+'</td></tr>';
                        sum += +arr[i].n20_sum;
                        n20 += +arr[i].n20_count;
                        n20price = arr[i].n20_price; 
                    }
                }
                if (arr[i].n21_count != undefined) {
                    if (arr[i].n21_count != 0) {
                        tab += '<tr><td class="left">Пожарные сигнализации</td>';
                        tab += '<td>'+arr[i].n21_price+'</td>';
                        tab += '<td>'+arr[i].n21_count+'</td>';
                        tab += '<td>'+arr[i].n21_sum+'</td></tr>'; 
                        sum += +arr[i].n21_sum;
                        n21 += +arr[i].n21_count;
                        n21price = arr[i].n21_price;
                    }
                }
                if (arr[i].n22_56_count != undefined) {
                    if (arr[i].n22_56_count != 0) {
                        tab += '<tr><td class="left">Вентиляции</td>';
                        tab += '<td>'+arr[i].n22_56_price+'</td>';
                        tab += '<td>'+arr[i].n22_56_count+'</td>';
                        tab += '<td>'+arr[i].n22_56_sum+'</td></tr>';
                        sum += +arr[i].n22_56_sum;
                        n22_56 += +arr[i].n22_56_count;
                        n22_56price = arr[i].n22_56_price;
                    }
                }
                if (arr[i].n22_78_count != undefined) {
                    if (arr[i].n22_78_count != 0) {
                        tab += '<tr><td class="left">Электровытяжки</td>';
                        tab += '<td>'+arr[i].n22_78_price+'</td>';
                        tab += '<td>'+arr[i].n22_78_count+'</td>';
                        tab += '<td>'+arr[i].n22_78_sum+'</td></tr>';
                        sum += +arr[i].n22_78_sum;
                        n22_78 += +arr[i].n22_78_count;
                        n22_78price = arr[i].n22_78_price;
                    }
                }
                if (arr[i].n23_count != undefined) {
                    if (arr[i].n23_count != 0) {
                        tab += '<tr><td class="left">Установка диффузоров</td>';
                        tab += '<td>'+arr[i].n23_price+'</td>';
                        tab += '<td>'+arr[i].n23_count+'</td>';
                        tab += '<td>'+arr[i].n23_sum+'</td></tr>';
                        sum += +arr[i].n23_sum;
                        n23 += +arr[i].n23_count;
                        n23price = arr[i].n23_price;    
                    }
                }
                if (arr[i].n24_count != undefined) {
                    if (arr[i].n24_count != 0) {
                        tab += '<tr><td class="left">Сложность доступа</td>';
                        tab += '<td>'+arr[i].n24_price+'</td>';
                        tab += '<td>'+arr[i].n24_count+'</td>';
                        tab += '<td>'+arr[i].n24_sum+'</td></tr>';
                        sum += +arr[i].n24_sum;
                        n24 += +arr[i].n24_count;
                        n24price = arr[i].n24_price;  
                    }
                }
                if (arr[i].n27_count != undefined) {
                    if (arr[i].n27_count != 0) {
                        tab += '<tr><td class="left">Шторных карнизов</td>';
                        tab += '<td>'+arr[i].n27_price+'</td>';
                        tab += '<td>'+arr[i].n27_count+'</td>';
                        tab += '<td>'+arr[i].n27_sum+'</td></tr>';  
                        sum += +arr[i].n27_sum;
                        n27 += +arr[i].n27_count;
                        n27price = arr[i].n27_price; 
                    }
                }
                if (arr[i].n29_count_23 != undefined) {
                    if (arr[i].n29_count_23 != 0) {
                        tab += '<tr><td class="left">Переход уровня по прямой</td>';
                        tab += '<td>'+arr[i].n29_price_23+'</td>';
                        tab += '<td>'+arr[i].n29_count_23+'</td>';
                        tab += '<td>'+arr[i].n29_sum_23+'</td></tr>';  
                        sum += +arr[i].n29_sum_23;
                        n29_23 += +arr[i].n29_count_23;
                        n29price_23 = arr[i].n29_price_23; 
                    }
                }
                if (arr[i].n29_count_24 != undefined) {
                    if (arr[i].n29_count_24 != 0) {
                        tab += '<tr><td class="left">Переход уровня по кривой</td>';
                        tab += '<td>'+arr[i].n29_price_24+'</td>';
                        tab += '<td>'+arr[i].n29_count_24+'</td>';
                        tab += '<td>'+arr[i].n29_sum_24+'</td></tr>';  
                        sum += +arr[i].n29_sum_24;
                        n29_24 += +arr[i].n29_count_24;
                        n29price_24 = arr[i].n29_price_24; 
                    }
                }
                if (arr[i].n29_count_25 != undefined) {
                    if (arr[i].n29_count_25 != 0) {
                        tab += '<tr><td class="left">Переход уровня по прямой с нишей</td>';
                        tab += '<td>'+arr[i].n29_price_25+'</td>';
                        tab += '<td>'+arr[i].n29_count_25+'</td>';
                        tab += '<td>'+arr[i].n29_sum_25+'</td></tr>';  
                        sum += +arr[i].n29_sum_25;
                        n29_25 += +arr[i].n29_count_25;
                        n29price_25 = arr[i].n29_price_25; 
                    }
                }
                if (arr[i].n29_count_26 != undefined) {
                    if (arr[i].n29_count_26 != 0) {
                        tab += '<tr><td class="left">Переход уровня по кривой с нишей</td>';
                        tab += '<td>'+arr[i].n29_price_26+'</td>';
                        tab += '<td>'+arr[i].n29_count_26+'</td>';
                        tab += '<td>'+arr[i].n29_sum_26+'</td></tr>';  
                        sum += +arr[i].n29_sum_26;
                        n29_26 += +arr[i].n29_count_26;
                        n29price_26 = arr[i].n29_price_26; 
                    }
                }
                if (arr[i].n30_count != undefined) {
                    if (arr[i].n30_count != 0) {
                        tab += '<tr><td class="left">Парящий потолок</td>';
                        tab += '<td>'+arr[i].n30_price+'</td>';
                        tab += '<td>'+arr[i].n30_count+'</td>';
                        tab += '<td>'+arr[i].n30_sum+'</td></tr>';  
                        sum += +arr[i].n30_sum;
                        n30 += +arr[i].n30_count;
                        n30price = arr[i].n30_price; 
                    }
                }
                if (arr[i].dop_krepezh_count != undefined) {
                    if (arr[i].dop_krepezh_count != 0) {
                        tab += '<tr><td class="left">Дополнительный крепеж</td>';
                        tab += '<td>'+arr[i].dop_krepezh_price+'</td>';
                        tab += '<td>'+arr[i].dop_krepezh_count+'</td>';
                        tab += '<td>'+arr[i].dop_krepezh_sum+'</td></tr>';  
                        sum += +arr[i].dop_krepezh_sum;
                        dop_krepezh += +arr[i].dop_krepezh_count;
                        dop_krepezhprice = arr[i].dop_krepezh_price;
                    }
                }
                if (arr[i].extra_mounting != undefined) {
                    var massiv = jQuery.map(arr[i].extra_mounting, function(value, index) {
                        mas[index] = value;
                    });
                    for (var j = 0, k=0; j < mas.length; j++, k++) {
                        if (mas[j] != undefined) {
                            tab += '<tr><td class="left">'+mas[j].title+'</td>';
                            tab += '<td>'+mas[j].value+'</td>';
                            tab += '<td>1</td>';
                            tab += '<td>'+mas[j].value+'</td></tr>';
                            sum += +mas[j].value;
                            mas_extra_mounting[mas_j] = {"title" : mas[j].title, "value" : mas[j].value}
                            mas_j++;
                        }
                    }
                }
                tab += '<tr class="caption"><td colspan=3 style="text-align: right;">Итого, ₽:</td>';
                // вывод итоговой стоимости работы
                tab += '<td>'+sum.toFixed(2)+'</td>';
                tab += '</tr></table></div></div>';      
                jQuery("#content-tabs").append(tab);
                sumAll += sum;
            }
        }

        // вкладка общее
        tabAll = '';
        if (n5 !=0) {
            tabAll += '<tr><td>Периметр</td>';
            tabAll += '<td>'+n5price+'</td>';
            tabAll += '<td>'+(+n5).toFixed(2)+'</td>';
            tabAll += '<td>'+(+n5 * +n5price).toFixed(2)+'</td></tr>';
        }
        if (n6 !=0) {
            tabAll += '<tr><td>Вставка</td>';
            tabAll += '<td>'+n6price+'</td>';
            tabAll += '<td>'+(+n6).toFixed(2)+'</td>';
            tabAll += '<td>'+(+n6 * +n6price).toFixed(2)+'</td></tr>';
        }
        if (n7 !=0) {
            tabAll += '<tr><td>Крепление в плитку</td>';
            tabAll += '<td>'+n7price+'</td>';
            tabAll += '<td>'+n7+'</td>';
            tabAll += '<td>'+(+n7 * +n7price).toFixed(2)+'</td></tr>';
        }
        if (n8 !=0) {
            tabAll += '<tr><td>Крепление в керамогранит</td>';
            tabAll += '<td>'+n8price+'</td>';
            tabAll += '<td>'+n8+'</td>';
            tabAll += '<td>'+(+n8 * +n8price).toFixed(2)+'</td></tr>';
        }
        if (n9 !=0) {
            tabAll += '<tr><td>Обработка угла</td>';
            tabAll += '<td>'+n9price+'</td>';
            tabAll += '<td>'+n9+'</td>';
            tabAll += '<td>'+(+n9 * +n9price).toFixed(2)+'</td></tr>';
        }
        if (n11 !=0) {
            tabAll += '<tr><td>Крепление в керамогранит</td>';
            tabAll += '<td>'+n11price+'</td>';
            tabAll += '<td>'+n11+'</td>';
            tabAll += '<td>'+(+n11 * +n11price).toFixed(2)+'</td></tr>';
        }
        if (n12 !=0) {
            tabAll += '<tr><td>Установка люстр</td>';
            tabAll += '<td>'+n12price+'</td>';
            tabAll += '<td>'+n12+'</td>';
            tabAll += '<td>'+(+n12 * +n12price).toFixed(2)+'</td></tr>';
        }
        if (n13 !=0) {
            tabAll += '<tr><td>Установка светильников</td>';
            tabAll += '<td>'+n13price+'</td>';
            tabAll += '<td>'+n13+'</td>';
            tabAll += '<td>'+(+n13 * +n13price).toFixed(2)+'</td></tr>';
        }
        if (n14 !=0) {
            tabAll += '<tr><td>Обвод труб</td>';
            tabAll += '<td>'+n14price+'</td>';
            tabAll += '<td>'+n14+'</td>';
            tabAll += '<td>'+(+n14 * +n14price).toFixed(2)+'</td></tr>';
        }
        if (n17 !=0) {
            tabAll += '<tr><td>Закладная брусом</td>';
            tabAll += '<td>'+n17price+'</td>';
            tabAll += '<td>'+(+n17).toFixed(2)+'</td>';
            tabAll += '<td>'+(+n17 * +n17price).toFixed(2)+'</td></tr>';
        }
        if (n18 !=0) {
            tabAll += '<tr><td>Укрепление стен</td>';
            tabAll += '<td>'+n18price+'</td>';
            tabAll += '<td>'+n18+'</td>';
            tabAll += '<td>'+(+n18 * +n18price).toFixed(2)+'</td></tr>';
        }
        if (n20 !=0) {
            tabAll += '<tr><td>Разделитель стен</td>';
            tabAll += '<td>'+n20price+'</td>';
            tabAll += '<td>'+n20+'</td>';
            tabAll += '<td>'+(+n20 * +n20price).toFixed(2)+'</td></tr>';
        }
        if (n21 !=0) {
            tabAll += '<tr><td>Пожарные сигнализации</td>';
            tabAll += '<td>'+n21price+'</td>';
            tabAll += '<td>'+n21+'</td>';
            tabAll += '<td>'+(+n21 * +n21price).toFixed(2)+'</td></tr>';
        }
        if (n22_56 !=0) {
            tabAll += '<tr><td>Вентиляции</td>';
            tabAll += '<td>'+n22_56price+'</td>';
            tabAll += '<td>'+n22_56+'</td>';
            tabAll += '<td>'+(+n22_56 * +n22_56price).toFixed(2)+'</td></tr>';
        }
        if (n22_78 !=0) {
            tabAll += '<tr><td>Электровытяжки</td>';
            tabAll += '<td>'+n22_78price+'</td>';
            tabAll += '<td>'+n22_78+'</td>';
            tabAll += '<td>'+(+n22_78 * +n22_78price).toFixed(2)+'</td></tr>';
        }
        if (n23 !=0) {
            tabAll += '<tr><td>Установка диффузоров</td>';
            tabAll += '<td>'+n23price+'</td>';
            tabAll += '<td>'+n23+'</td>';
            tabAll += '<td>'+(+n23 * +n23price).toFixed(2)+'</td></tr>';
        }
        if (n24 !=0) {
            tabAll += '<tr><td>Сложность доступа</td>';
            tabAll += '<td>'+n24price+'</td>';
            tabAll += '<td>'+n24+'</td>';
            tabAll += '<td>'+(+n24 * +n24price).toFixed(2)+'</td></tr>';
        }
        if (n27 !=0) {
            tabAll += '<tr><td>Шторных карнизов</td>';
            tabAll += '<td>'+n27price+'</td>';
            tabAll += '<td>'+(+n27).toFixed(2)+'</td>';
            tabAll += '<td>'+(+n27 * +n27price).toFixed(2)+'</td></tr>';
        }
        if (n29_23 !=0) {
            tabAll += '<tr><td>Переход уровня по прямой</td>';
            tabAll += '<td>'+n29price_23+'</td>';
            tabAll += '<td>'+(+n29_23)+'</td>';
            tabAll += '<td>'+(+n29_23 * +n29price_23).toFixed(2)+'</td></tr>';
        }
        if (n29_24 !=0) {
            tabAll += '<tr><td>Переход уровня по кривой</td>';
            tabAll += '<td>'+n29price_24+'</td>';
            tabAll += '<td>'+(+n29_24)+'</td>';
            tabAll += '<td>'+(+n29_24 * +n29price_24).toFixed(2)+'</td></tr>';
        }
        if (n29_25 !=0) {
            tabAll += '<tr><td>Переход уровня по прямой с нишей</td>';
            tabAll += '<td>'+n29price_25+'</td>';
            tabAll += '<td>'+(+n29_25)+'</td>';
            tabAll += '<td>'+(+n29_25 * +n29price_25).toFixed(2)+'</td></tr>';
        }
        if (n29_26 !=0) {
            tabAll += '<tr><td>Переход уровня по кривой с нишей</td>';
            tabAll += '<td>'+n29price_26+'</td>';
            tabAll += '<td>'+(+n29_26)+'</td>';
            tabAll += '<td>'+(+n29_26 * +n29price_26).toFixed(2)+'</td></tr>';
        }
        if (n30 !=0) {
            tabAll += '<tr><td>Парящий потолок</td>';
            tabAll += '<td>'+n30price+'</td>';
            tabAll += '<td>'+(+n30).toFixed(2)+'</td>';
            tabAll += '<td>'+(+n30 * +n30price).toFixed(2)+'</td></tr>';
        }
        if (dop_krepezh !=0) {
            tabAll += '<tr><td>Дополнительный крепеж</td>';
            tabAll += '<td>'+dop_krepezhprice+'</td>';
            tabAll += '<td>'+dop_krepezh+'</td>';
            tabAll += '<td>'+(+dop_krepezh * +dop_krepezhprice).toFixed(2)+'</td></tr>';
        }
        if (mas_extra_mounting != undefined) {
            for (var i=0; i<mas_extra_mounting.length; i++) {
                tabAll += '<tr><td>'+mas_extra_mounting[i].title+'</td>';
                tabAll += '<td>'+mas_extra_mounting[i].value+'</td>';
                tabAll += '<td>1</td>';
                tabAll += '<td>'+mas_extra_mounting[i].value+'</td></tr>';       
            }
        }
        if (arr["transport"] !=0) {
            tabAll += '<tr id="caption" class="caption"><td colspan="4" style="text-align: center;">Транспорт</td></tr>';
            tabAll += '<tr><td></td>';
            tabAll += '<td>'+arr["transport_price"]+'</td>';
            tabAll += '<td>'+arr["transport_count"]+'</td>';
            tabAll += '<td>'+arr["transport_sum"]+'</td></tr>';
            sumAll += arr["transport_sum"];
        }
        jQuery("#before-insert").before(tabAll);
        if (sumAll < 1500) {
            jQuery("#sum-all").text("1500");
        } else {
            jQuery("#sum-all").text(sumAll);
        }
    }

    //получение значений из url с помощью js
    // функция получения значений из url
    function getUrlVars() {
        var vars = {};
        var parts = window.location.href.replace(/[?&]+([^=&]+)=([^&]*)/gi, function(m,key,value) {
            vars[key] = value;
        });
        return vars;
    }
    var url_proj = getUrlVars()["project"];

    // функция получения текущего времени
    var date;
    function CurrentDateTime() {
        var now = new Date();
        var year = String(now.getFullYear());
        var month = String(now.getMonth());
        month++;
        if (month.length == 1) {
            month = "0"+month;
        }
        var day = String(now.getDate());
        if (day.length == 1) {
            day = "0"+day;
        }
        var hour = String(now.getHours());
        if (hour.length == 1) {
            hour = "0"+hour;
        }
        var minute = String(now.getMinutes());
        if (minute.length == 1) {
            minute = "0"+minute;
        }
        var second = String(now.getSeconds());
        if (second.length == 1) {
            second = "0"+second;
        }
        date = year+"-"+month+"-"+day+" "+hour+":"+minute+":"+second;
        return date;
    }

    jQuery(document).ready( function() {

    // передача значений n и mp на контроллер
    // отправка ajax для просчета на контроллер
    jQuery.ajax( {
        type: "POST",
        url: "index.php?option=com_gm_ceiling&task=mountersorder.GetData",
        dataType: 'json',
        data: {
            DataOfNPack1 : <?php echo json_encode($data_of_n_pack1); ?>,
            DataOfNPack2 : <?php echo json_encode($data_of_n_pack2); ?>, 
            DataOfNPack3 : <?php echo json_encode($data_of_n_pack3); ?>, 
            DataOfNPack4 : <?php echo json_encode($data_of_n_pack4); ?>, 
            DataOfNPack5 : <?php echo json_encode($data_of_n_pack5); ?>,
            DataOfNPack6 : <?php echo json_encode($data_of_n_pack6); ?>,
            DataOfNPack7 : <?php echo json_encode($data_of_n_pack7); ?>,
            DataOfMp : <?php echo json_encode($data_of_mp); ?>,
        },
        success: function(msg) {
            DataOfMount = msg;
            AddTab(DataOfMount);
        }
    });

    //отправка ajax для проверки дат начала конца монтажа и статуса
    jQuery.ajax( {
        type: "POST",
        url: "index.php?option=com_gm_ceiling&task=mountersorder.GetDates",
        dataType: 'json',
        data: {
            url_proj : url_proj,
        },
        success: function(msg) {
            start = msg[0].project_mounting_start;
            end = msg[0].project_mounting_end;
            status_mount = msg[0].project_status;
            if (status_mount == 17 ) {
                jQuery("#begin").attr("disabled", "disabled");
                jQuery("#complited").attr("disabled", false);
                jQuery("#underfulfilled").attr("disabled", "disabled");
            } else {
                if (start == "0000-00-00 00:00:00") {
                    jQuery("#complited").attr("disabled", "disabled");
                    jQuery("#underfulfilled").attr("disabled", "disabled");
                } else {
                    jQuery("#begin").attr("disabled", "disabled");
                    jQuery("#complited").attr("disabled", false);
                    jQuery("#underfulfilled").attr("disabled", false);
                }
            }
        }
    });

    //скрыть модальное окно
    jQuery(document).mouseup(function (e) {
		var div = jQuery("#modal-window-1-tar");
		if (!div.is(e.target)
		    && div.has(e.target).length === 0) {
			jQuery("#close-tar").hide();
			jQuery("#modal-window-container-tar").hide();
			jQuery("#modal-window-1-tar").hide();
		}
    });

    //  кнопка "монтаж начат"
    jQuery("#begin").click( function() {
        CurrentDateTime();
        jQuery.ajax( {
            type: "POST",
            url: "index.php?option=com_gm_ceiling&task=mountersorder.MountingStart",
            dataType: 'json',
            data: {
                date : date,
                url_proj : url_proj,
            },
            success: function(msg) {
                if (msg[0].project_status == 16) {
                    window.location.href = "/index.php?option=com_gm_ceiling&&view=mounterscalendar"
                }
            }
        });
    });

    // узнаем какая кнопка, открываем модальное окно
    jQuery("#buttons-cantainer").on("click", ".modal", function() {
        whatBtn = this.id;
        jQuery("#close-tar").show();
        jQuery("#modal-window-container-tar").show();
        jQuery("#modal-window-1-tar").show("slow");
    });

    // получение значений из селектов
    jQuery("#modal-window-container-tar").on("click", "#save", function() {
        var note = jQuery("#note").val();
        if (whatBtn == "complited") {
            // кнопка "монтаж выполнен"
            if (note.length != 0) {
                CurrentDateTime();
                jQuery.ajax( {
                    type: "POST",
                    url: "index.php?option=com_gm_ceiling&task=mountersorder.MountingComplited",
                    dataType: 'json',
                    data: {
                        date : date,
                        url_proj : url_proj,
                        note : note,
                    },
                    success: function(msg) {
                        if (msg[0].project_status == 11) {
                            window.location.href = "/index.php?option=com_gm_ceiling&&view=mounterscalendar"
                        }
                    }
                });
            } else {
                 jQuery("#warning").show();
            }
        } else if (whatBtn == "underfulfilled") {
            // кнопка "монтаж недовыполнен"
            if (note.length != 0) {
                CurrentDateTime();
                jQuery.ajax( {
                    type: "POST",
                    url: "index.php?option=com_gm_ceiling&task=mountersorder.MountingUnderfulfilled",
                    dataType: 'json',
                    data: {
                        date : date,
                        url_proj : url_proj,
                        note : note,
                    },
                    success: function(msg) {
                        if (msg[0].project_status == 17) {
                            window.location.href = "/index.php?option=com_gm_ceiling&&view=mounterscalendar"
                        }
                    }
                });
            } else {
                 jQuery("#warning").show();
            }
        }
    });

    // проверка, если пустое то убирать подсказку
    jQuery("#modal-window-container-tar").on("keydown", "#note", function() {
        jQuery("#warning").hide();
    });

});

</script>

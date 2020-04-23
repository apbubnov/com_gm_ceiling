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

/**
 * Calculation controller class.
 *
 * @since  1.6
 */
class Gm_ceilingControllerSketch extends JControllerLegacy
{
    public function save_data_from_sketch() {
        try {
            $jinput = JFactory::getApplication()->input;
            $calc_img = $jinput->get('calc_img', '', 'string');
            $cut_img = $jinput->get('cut_img', '', 'string');
            $n4 = $jinput->get('n4', '', 'FLOAT'); //площадь
            $n5 = $jinput->get('n5', '', 'FLOAT'); //периметр
            $n9 = $jinput->get('n9', '', 'int'); //углы
            /*_________параметры от старой версии(временно для плавного перехода на новую структуру)_______________*/
            $n5_shrink = $jinput->get('n5_shrink', '', 'string'); //периметр с усадкой
            $n10 = $jinput->get('curvilinear_length', '', 'string'); //криволинейный участок
            $n31 = $jinput->get('inner_cutout_length', '', 'string'); //внутренний вырез
            $texture = $jinput->get('texture', 0, 'int');
            $color = $jinput->get('color', 0, 'int');
            $manufacturer = $jinput->get('manufacturer', 0, 'int');
            $width = $jinput->get('width', 0, 'INT'); //ширина полотна
            /*__________________________*/
            $calc_id = $jinput->get('calc_id', 0, 'int');
            $length_arr = json_decode($jinput->get('arr_length','', 'STRING')); //длины сторон
            $arr_points = $jinput->get('arr_points', null, 'array'); //координаты раскроя
            $goods = $jinput->get('goods', [], 'array'); //полотна
            $jobs = $jinput->get('jobs', [], 'array'); //работы
            $offcut_square = $jinput->get('offcut_square', 0, 'FLOAT');
            if(empty(floatval($offcut_square))){
                $offcut_square = $jinput->get('square_obrezkov', 0, 'FLOAT');
            }
            $cuts = $jinput->get('cuts', '', 'string');
            $canvas_area = $jinput->get('sq_polo', 0, 'FLOAT');
            $p_usadki = $jinput->get('p_usadki', 1, 'FLOAT');
            $drawing_data = $jinput->get('drawing_data', '', 'string');

            $original_sketch = base64_encode(gzcompress($drawing_data, 9));
            $calc_data = '';
            $cut_data = '';

            $walls = $length_arr->walls;
            $diags = $length_arr->diags;
            for ($i = 0; $i < count($arr_points); $i++) {
                $points_polonta = '';
                for ($j = 0; $j < count($arr_points[$i]); $j++) {
                    $points_polonta .= implode($arr_points[$i][$j]).', ';
                }
                $points_polonta = substr($points_polonta, 0, -2);

                $cut_data .= 'Полотно'.($i + 1).': '.$points_polonta.'|';
            }
            $cut_data = substr($cut_data, 0, -1);

            $wallsNames = [];
            $wallsAssoc = [];

            foreach ($walls as $key => $value) {
                $wallsNames[] = $value->name;
                $wallsAssoc[$value->name] = $value;
            }
            $walls = [];
            usort($wallsNames, strnatcasecmp($a,$b));
            foreach ($wallsNames as $value) {
                $walls[] = $wallsAssoc[$value];
            }
            usort($diags,function($a,$b){
                if(strlen($a->name) > strlen($b->name)){
                    return 1;
                }
                elseif(strlen($a->name) < strlen($b->name)){
                    return -1;
                }
                elseif(strlen($a->name) == strlen($b->name)){
                    return strcasecmp($a->name,$b->name);
                }
            });
            for ($i = 0; $i < count($walls); $i++) {
                $calc_data .= $walls[$i]->name.'='.$walls[$i]->length.';';
            }
            $calc_data .= "||";
            for ($i = 0; $i < count($diags); $i++) {
                $calc_data .= $diags[$i]->name.'='.$diags[$i]->length.';';
            }
            /*for ($i = 0; $i < count($length_arr); $i++) {
                $calc_data .= implode('=', $length_arr[$i]);
                $calc_data .= ';';
            }*/

            $client_calc_img = base64_decode($calc_img);
            $client_calc_img = preg_replace('/<text [^\<\>]+>\d+\.{0,1}\d*<\/text>/', '', $client_calc_img);

            $filename_calc = md5('calculation_sketch'.$calc_id);
            $filename_client_calc = md5('calculation_sketch_client'.$calc_id);
            $filename_cut = md5('cut_sketch'.$calc_id);
            file_put_contents($_SERVER['DOCUMENT_ROOT']."/calculation_images/$filename_calc.svg", base64_decode($calc_img));
            file_put_contents($_SERVER['DOCUMENT_ROOT']."/cut_images/$filename_cut.svg", base64_decode($cut_img));
            file_put_contents($_SERVER['DOCUMENT_ROOT']."/calculation_images/$filename_client_calc.svg", $client_calc_img);

            $canv_model = Gm_ceilingHelpersGm_ceiling::getModel('canvases');
            $calculation_model = Gm_ceilingHelpersGm_ceiling::getModel('calculation');
            $model_calcform = Gm_ceilingHelpersGm_ceiling::getModel('CalculationForm', 'Gm_ceilingModel');
            $n3 = '';
            if(!empty($texture) && !empty($manufacturer) && !empty($width)){

                $width = (string)($width / 100);
                if (strpos($width, '.') === false) {
                    $width .= '.0';
                }

                $filter = "`texture_id` = $texture AND `manufacturer_id` = $manufacturer AND `width` = '$width' AND `count` > 0";
                if (!empty($color)) {
                    $filter .= " AND `color_id` = $color";
                }
                $result  = $canv_model->getFilteredItemsCanvas($filter,'old');
                $n3 = $result[0]->id;
                $data['n3'] = $n3;
                if (!empty($n5_shrink)) {
                    $data['n5_shrink'] = $n5_shrink;
                }
                if (isset($n10)) {
                    $data['n10'] = $n10;
                }
                if (isset($n31)) {
                    $data['n31'] = $n31;
                }
            }
            else{
                $model_calcform->addGoodsInCalculation($calc_id, $goods, true);
                $model_calcform->addJobsInCalculation($calc_id, $jobs, true);
            }

            $data['id'] = $calc_id;
            if (!empty($n4)) {
                $data['n4'] = $n4;
            }
            if (!empty($n5)) {
                $data['n5'] = $n5;
            }
            if (!empty($n9)) {
                $data['n9'] = $n9;
            }
            if (!empty($p_usadki)) {
                $data['shrink_percent'] = $p_usadki;
            }
            $data['calc_data'] = $calc_data;
            $data['cut_data'] = $cut_data;
            $data['original_sketch'] = $original_sketch;
            $data['offcut_square'] = $offcut_square;
            $result  = $calculation_model->update_calculation($data);
            $canv_model->saveCuts($calc_id, $cuts, $canvas_area);
            if(!empty($n3)){
                Gm_ceilingHelpersGm_ceiling::calculate(1,$data['id'],2,1,0,1);
            }
            else{
                $calculationController = Gm_ceilingHelpersGm_ceiling::getController('calculationForm');
                $calculationController->reCalculate($calc_id);
            }

            //die(print_r($_POST));
            die(true);
        } catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function getCanvasesByTextureAndManufacturer() {
        try {
            $jinput = JFactory::getApplication()->input;
            $textureId = $jinput->get('textureId', 0, 'int');
            $manufacturerId = $jinput->get('manufacturerId', 0, 'int');
            $old = $jinput->getInt('old');
            $canv_model = Gm_ceilingHelpersGm_ceiling::getModel('canvases');
            if($old){
                $filter = "`texture_id` = $textureId AND `manufacturer_id` = $manufacturerId AND `count` > 0";
                $result  = $canv_model->getFilteredItemsCanvas($filter,'old');
            }
            else{
                $filter = "`texture_id` = $textureId AND `manufacturer_id` = $manufacturerId AND `visibility` = 1";
                $result  = $canv_model->getFilteredItemsCanvas($filter);
            }

            die(json_encode($result));
        } catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

}
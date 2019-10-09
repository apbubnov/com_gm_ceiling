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
            $calc_id = $jinput->get('calc_id', 0, 'int');
            $length_arr = $jinput->get('arr_length', null, 'array'); //длины сторон
            $arr_points = $jinput->get('arr_points', null, 'array'); //координаты раскроя
            $goods = $jinput->get('goods', array(), 'array'); //полотна
            $jobs = $jinput->get('jobs', array(), 'array'); //работы
            $offcut_square = $jinput->get('offcut_square', 0, 'FLOAT');
            $cuts = $jinput->get('cuts', '', 'string');
            $canvas_area = $jinput->get('sq_polo', 0, 'FLOAT');
            $p_usadki = $jinput->get('p_usadki', 1, 'FLOAT');
            $drawing_data = $jinput->get('drawing_data', '', 'string');

            $original_sketch = base64_encode(gzcompress($drawing_data, 9));
            $calc_data = '';
            $cut_data = '';

            for ($i = 0; $i < count($arr_points); $i++) {
                $points_polonta = '';
                for ($j = 0; $j < count($arr_points[$i]); $j++) {
                    $points_polonta .= implode($arr_points[$i][$j]).', ';
                }
                $points_polonta = substr($points_polonta, 0, -2);

                $cut_data .= 'Полотно'.($i + 1).': '.$points_polonta.'|';
            }
            $cut_data = substr($cut_data, 0, -1);

            for ($i = 0; $i < count($length_arr); $i++) {
                $calc_data .= implode('=', $length_arr[$i]);
                $calc_data .= ';';
            }

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

            $model_calcform->addGoodsInCalculation($calc_id, $goods, true);
            $model_calcform->addJobsInCalculation($calc_id, $jobs, true);

            $data['id'] = $calc_id;
            if (!empty($n4)) {
                $data['n4'] = $n4;
            }
            if (!empty($n5)) {
                $data['n5'] = $n5;
            }
            if (isset($n9)) {
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
            $calculationController = Gm_ceilingHelpersGm_ceiling::getController('calculationForm');
            $calculationController->reCalculate($calc_id);
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

            $canv_model = Gm_ceilingHelpersGm_ceiling::getModel('canvases');

            $filter = "`texture_id` = $textureId AND `manufacturer_id` = $manufacturerId AND `visibility` = 1";
            $result  = $canv_model->getFilteredItemsCanvas($filter);
            die(json_encode($result));
        } catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

}
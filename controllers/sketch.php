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
	public function save_data_from_sketch()
    {
        try
        {
            $jinput = JFactory::getApplication()->input;
            $calc_img = $jinput->get('calc_img', '', 'string');
            $cut_img = $jinput->get('cut_img', '', 'string');
            $n4 = $jinput->get('jform_n4', '0', 'string');
            $n5 = $jinput->get('jform_n5', '0', 'string');
            $n9 = $jinput->get('jform_n9', '0', 'string');
            $texture = $jinput->get('texture', 0, 'int');
            $color = $jinput->get('color', 0, 'int');
            $manufacturer = $jinput->get('manufacturer', 0, 'int');
            $width = $jinput->get('width', 0, 'INT');
            $auto = $jinput->get('auto', 0, 'int');
            $user_id = $jinput->get('user_id', 0, 'int');
            $calc_id = $jinput->get('calc_id', 0, 'int');
            $length_arr = $jinput->get('arr_length', null, 'array');
            $arr_points = $jinput->get('arr_points', null, 'array');
            $offcut_square = $jinput->get('square_obrezkov', 0, 'FLOAT');
            $cuts = $jinput->get('cuts', '', 'string');
            $p_usadki = $jinput->get('p_usadki', '1', 'FLOAT');
            $seam = $jinput->get('seam', 0, 'INT');
            $wp = $jinput->get('walls_points', array(), 'ARRAY');
            $dp = $jinput->get('diags_points', array(), 'ARRAY');
            $pp = $jinput->get('pt_points', array(), 'ARRAY');
            $code = $jinput->get('code', 0, 'INT');
            $alphavite = $jinput->get('alfavit', 0, 'INT');

            for ($i = 0; $i < count($wp); $i++) {
                $original_sketch .= implode(';', $wp[$i]);
                $original_sketch .= ';';
            }
            $original_sketch .= '||';
            for ($i = 0; $i < count($dp); $i++) {
                $original_sketch .= implode(';', $dp[$i]);
                $original_sketch .= ';';
            }
            $original_sketch .= '||';
            for ($i = 0; $i < count($pp); $i++) {
                $original_sketch .= implode(';', $pp[$i]);
                $original_sketch .= ';';
            }
            $original_sketch .= '||' . $code . '||' . $alphavite;

            for ($i = 0; $i < count($arr_points); $i++)
            {
                $points_polonta = '';
                for ($j = 0; $j < count($arr_points[$i]); $j++)
                {
                    $points_polonta .= implode($arr_points[$i][$j]).', ';
                }
                $points_polonta = substr($points_polonta, 0, -2);

                $cut_data .= "Полотно" . ($i + 1) . ": " . $points_polonta . "| ";
            }
            $cut_data .= '||'.$p_usadki;


            for ($i = 0; $i < count($length_arr); $i++)
            {
                $calc_data .= implode('=', $length_arr[$i]);
                $calc_data .= ';';
            }

            $filename_calc = md5('calculation_sketch'.$calc_id);
            $filename_cut = md5('cut_sketch'.$calc_id);

            file_put_contents($_SERVER['DOCUMENT_ROOT']."/calculation_images/$filename_calc.svg", base64_decode($calc_img));
            file_put_contents($_SERVER['DOCUMENT_ROOT']."/cut_images/$filename_cut.svg", base64_decode($cut_img));

            $canv_model = Gm_ceilingHelpersGm_ceiling::getModel('canvases');
            $calculation_model = Gm_ceilingHelpersGm_ceiling::getModel('calculation');

            $width = (string)($width / 100);
            if (strpos($width, '.') === false) {
            	$width .= '.0';
            }

            $filter = "`texture_id` = $texture AND `manufacturer_id` = $manufacturer AND `width` = '$width' AND `count` > 0";
            if (!empty($color))
            {
            	$filter .= " AND `color_id` = $color";
            }
            $result  = $canv_model->getFilteredItemsCanvas($filter);
            $n3 = $result[0]->id;
            $data['id'] = $calc_id;
            $data['n3'] = $n3;
            $data['n4'] = $n4;
            $data['n5'] = $n5;
            $data['n9'] = $n9;
            $data['calc_data'] = $calc_data;
            $data['cut_data'] = $cut_data;
            $data['original_sketch'] = $original_sketch;
            $data['offcut_square'] = $offcut_square;
            $result  = $calculation_model->update_calculation($data);

            $canv_model->saveCuts($calc_id, $cuts);
            
            die($result);
        }
        catch(Exception $e)
        {
            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);
        }
    }
}
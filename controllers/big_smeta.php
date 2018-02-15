<?php
/**
 * @version    CVS: 0.1.7
 * @package    Com_Gm_ceiling
 * @author     SpectralEye <Xander@spectraleye.ru>
 * @copyright  2016 SpectralEye
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

/**
 * Stock list controller class.
 *
 * @since  1.6
 */
class Gm_ceilingControllerBig_smeta extends JControllerLegacy
{
    /**
     * Proxy for getModel.
     *
     * @param   string $name The model name. Optional.
     * @param   string $prefix The class prefix. Optional
     * @param   array $config Configuration array for model. Optional
     *
     * @return object    The model
     *
     * @since    1.6
     */
    public function &getModel($name = 'Big_smeta', $prefix = 'Gm_ceilingModel', $config = array())
    {
        return parent::getModel($name, $prefix, array('ignore_request' => true));
    }

    public function transport()
    {
        try
        {
            $POST = $_POST;
            $data = (object)array();
            $sum = 0;
            $data->id = $POST['project_id'];
            $data->transport = $POST['transport'];
            $data->distance = $POST['jform']['distance'];
            if($POST['transport'] == 1 ) $distance_col = $POST['jform']['distance_col_1'];
            elseif($POST['transport'] == 2 ) $distance_col = $POST['jform']['distance_col'];
            else $distance_col = 0;
            $data->distance_col = $distance_col ;
            
            $model_project = $this->getModel('Project', 'Gm_ceilingModel');
            $res = $model_project->transport($data);
            $dealer_info_model = $this->getModel('Dealer_info', 'Gm_ceilingModel');
            if(empty($res->user_id)) $res->user_id = 1;
            $margin = $dealer_info_model->getMargin('dealer_mounting_margin',$res->user_id);
            $sheets_dir = $_SERVER['DOCUMENT_ROOT'] . '/costsheets/';
            if($res) {
                if($data->transport == 1) { $transport_sum = $this->margin1($res->transport * $distance_col, $margin);
                $transport_sum_1 = $res->transport * $distance_col;
                }
                elseif($data->transport == 2) {
                    $transport_sum = ($res->distance  * $data->distance + $res->transport) * $distance_col;
                    $transport_sum_1 = ($res->distance  * $data->distance + $res->transport) * $distance_col;
//                    if($transport_sum < $this->margin1($res->transport, $margin))
//                      {
//                          $transport_sum = $this->margin1($res->transport, $margin);
//                          $transport_sum_1 = $res->transport;
//                      }
                }
                else { $transport_sum = 0; $transport_sum_1 = 0; } 
            }
           if($data->transport == 1) { 
            $discount = $model_project->getDiscount($data->id);
            $min = 100;
            foreach($discount as $d) {
                if($d->discount < $min) $min = $d->discount;
            }
            if   ($min != 100)  { $transport_sum = $transport_sum * ((100 - $min)/100);
            $transport_sum_1 = $transport_sum_1 * ((100 - $min)/100);
            }
            }
           
            $model = $this->getModel();
            if(!empty($POST['include_calculation'])) {

                $calculation = $model->calculation($POST['include_calculation']);
                $html = ' <h1>Номер договора: ' . $POST['project_id'] . '</h1><br>';
                $html .= '<h2>Дата: ' . date("d.m.Y") . '</h2>';
                $html .= '<h2>Краткая информация по выбранным(-ому) потолкам(-у): </h2>';
                $html .= '<table border="0" cellspacing="0" width="100%">
                <tbody><tr><th>Название</th><th class="center">Площадь, м<sup>2</sup>.</th><th class="center">Периметр, м </th><th class="center">Стоимость, руб.</th></tr>';
                //написать модель, которая будет возвращать данные о калькуляции
                foreach ($calculation as $calc) {
                    $html .= '<tr>';
                    $html .= '<td>' . $calc->calculation_title . '</td>';
                    $html .= '<td class="center">' . $calc->n4 . '</td>';
                    $html .= '<td class="center">' . $calc->n5 . '</td>';
                    $html .= '<td class="center">' . round($POST['calculation_total_discount'][ $calc->id], 2) . '</td>';
                    $html .= '</tr>';
                    $sum += $POST['calculation_total_discount'][ $calc->id];
                }
                $html .= '<tr><th colspan="3" class="right">Итого, руб:</th><th class="center">' . $sum . '</th></tr>';
                $html .= '</tbody></table><p>&nbsp;</p><br>';
    
            }
            
            $html .= '<h2>Транспортные расходы: </h2>';
            $html .= '<table border="0" cellspacing="0" width="100%">
			<tbody><tr><th>Вид транспорта</th><th class="center">Кол-во км<sup>2</sup>.</th><th class="center">Кол-во выездов  </th><th class="center">Стоимость, руб.</th></tr>';
                if($POST['transport'] == '2' ) {
                    $html .= '<tr>';
                    $html .= '<td>' . 'Выезд за город' . '</td>';
                    $html .= '<td class="center">' . $POST['jform']['distance'] . '</td>';
                    $html .= '<td class="center">' . $POST['jform']['distance_col'] . '</td>';
                    $html .= '<td class="center">' . $transport_sum . '</td>';
                    $html .= '</tr>';
                }
                elseif($POST['transport'] == '1' ) {
                    $html .= '<tr>';
                    $html .= '<td>' . 'Транспорт по городу' . '</td>';
                    $html .= '<td class="center"> - </td>';
                    $html .= '<td class="center">' . $POST['jform']['distance_col_1'] . '</td>';
                    $html .= '<td class="center">' . $transport_sum . '</td>';
                    $html .= '</tr>';
                }
                elseif($POST['transport'] == '0' ) {
                    $html .= '<tr>';
                    $html .= '<td>' . 'Без транспорта' . '</td>';
                    $html .= '<td class="center"> - </td>';
                    $html .= '<td class="center"> - </td>';
                    $html .= '<td class="center"> 0 </td>';
                    $html .= '</tr>';
                }

            $html .= '</tbody></table><p>&nbsp;</p>';
            $html .= '<div style="text-align: right; font-weight: bold;"> ИТОГО: ' . round($transport_sum + $sum, 2) . ' руб.</div>';

            $array_html = array();
            $array_html[] = $html;
            if(!empty($POST['include_calculation'])) {
                foreach ($POST['include_calculation'] as $calc) {

                    $patch = $_SERVER['DOCUMENT_ROOT'] . "/costsheets/" . md5($calc . "-0-0") . ".pdf";
                    $array_html[] = $patch;
                }
            }


            $filename = md5($POST['project_id'] . "-9") . ".pdf";
            Gm_ceilingHelpersGm_ceiling::save_pdf($array_html, $sheets_dir . $filename, "A4");


             $mount = $model_project->getMount($POST['project_id']);
             if(!empty($mount->id)) $mount_name = $model_project->getMounterBrigade($mount->id);
            //смета по монтажным работам
            $html = ' <h1>Номер договора: ' . $POST['project_id'] . '</h1><br>';
            $html .= '<h2>Дата: ' . date("d.m.Y") . '</h2>';
            if(!empty($mount->name)) $html .= '<h2>Монтажная бригада: ' . $mount->name . '</h2>';
             if (isset($mount_name)) {
                    $html .= "<h2>Состав монтажной бригады: </h2>";
                    foreach ($mount_name AS $k => $value) {
                        $html .= $value->name . (($k < count($mount_name) - 1) ? " , " : " ");
                    }
                    $html .= "<br>";
                   // foreach($mount_name as $value) $html .= $value->name." ,";
                    
                   
                }
            if(!empty($calculation)) {
                $html .= '<h2>Краткая информация по выбранным(-ому) потолкам(-у): </h2>';
                $html .= '<table border="0" cellspacing="0" width="100%">
                <tbody><tr><th>Название</th><th class="center">Площадь, м<sup>2</sup>.</th><th class="center">Периметр, м </th><th class="center">Стоимость, руб.</th></tr>';
                //написать модель, которая будет возвращать данные о калькуляции
                foreach ($calculation as $calc) {
                    $html .= '<tr>';
                    $html .= '<td>' . $calc->calculation_title . '</td>';
                    $html .= '<td class="center">' . $calc->n4 . '</td>';
                    $html .= '<td class="center">' . $calc->n5 . '</td>';
                    $html .= '<td class="center">' . $calc->mounting_sum . '</td>';
                    $html .= '</tr>';
                    $sum_1 += $calc->mounting_sum;
                }
                $html .= '<tr><th colspan="3" class="right">Итого, руб:</th><th class="center">' . $sum_1 . '</th></tr>';
                $html .= '</tbody></table><p>&nbsp;</p><br>';

            }
          
            $html .= '<h2>Транспортные расходы: </h2>';
            $html .= '<table border="0" cellspacing="0" width="100%">
			<tbody><tr><th>Вид транспорта</th><th class="center">Кол-во км<sup>2</sup>.</th><th class="center">Кол-во выездов  </th><th class="center">Стоимость, руб.</th></tr>';
                if($POST['transport'] == '2' ) {
                    $html .= '<tr>';
                    $html .= '<td>' . 'Выезд за город' . '</td>';
                    $html .= '<td class="center">' . $POST['jform']['distance'] . '</td>';
                    $html .= '<td class="center">' . $POST['jform']['distance_col'] . '</td>';
                    $html .= '<td class="center">' . $transport_sum_1 . '</td>';
                    $html .= '</tr>';
                }
                elseif($POST['transport'] == '1' ) {
                    $html .= '<tr>';
                    $html .= '<td>' . 'Транспорт по городу' . '</td>';
                    $html .= '<td class="center"> - </td>';
                    $html .= '<td class="center">' . $POST['jform']['distance_col_1'] . '</td>';
                    $html .= '<td class="center">' . $transport_sum_1 . '</td>';
                    $html .= '</tr>';
                }
                elseif($POST['transport'] == '0' ) {
                    $html .= '<tr>';
                    $html .= '<td>' . 'Без транспорта' . '</td>';
                    $html .= '<td class="center"> - </td>';
                    $html .= '<td class="center"> - </td>';
                    $html .= '<td class="center"> 0 </td>';
                    $html .= '</tr>';
                }

            $html .= '</tbody></table><p>&nbsp;</p>';
            $html .= '<div style="text-align: right; font-weight: bold;"> ИТОГО: ' . round($transport_sum_1 + $sum_1, 2) . ' руб.</div>';

            $array_html = array();
            $array_html[] = $html;

            foreach ($POST['include_calculation'] as $calc) {

               $patch = $_SERVER['DOCUMENT_ROOT'] . "/costsheets/" . md5($calc . "-2") . ".pdf";
               $array_html[] = $patch;
            }
            //print_r($components_data); exit;
            $filename = md5($POST['project_id'] . "-10") . ".pdf";
            Gm_ceilingHelpersGm_ceiling::save_pdf($array_html, $sheets_dir . $filename, "A4");

            $transport_sum = json_encode(round($transport_sum, 2));

            Gm_ceilingHelpersGm_ceiling::create_client_common_estimate($data->id);
            Gm_ceilingHelpersGm_ceiling::create_common_estimate_mounters($data->id);
            Gm_ceilingHelpersGm_ceiling::create_estimate_of_consumables($data->id);

            die($transport_sum);
        }
        catch(Exception $e)
        {
            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);
        }
    }
    function margin1($value, $margin)
    {
        try{
            $return = ($value * 100 / (100 - $margin));
            return $return;
        }
        catch(Exception $e)
        {
            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);
        }
    }

    function commercialOffer()
    {
        try{
            $jinput = JFactory::getApplication()->input;
            $code = $jinput->get('code', null, 'STRING');
            $users_model = Gm_ceilingHelpersGm_ceiling::getModel('users');
            $result  = $users_model->acceptCommercialOfferCode($code);
            $type = JFactory::getUser($result->user_id)->dealer_type;
            if ($type == 3)
            {
                $this->setRedirect(JRoute::_('/files/KP_OTD.pdf', false));
            }
            else
            {
                $this->setRedirect(JRoute::_('/files/KP_DEA.pdf', false));
            }
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
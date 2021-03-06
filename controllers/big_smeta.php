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


 /*   public function costyl(){
        try
        {
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);

            $query->select('id as project_id,project_mounter as mounter_id,project_mounting_date as date_time, 1 as `type`')
                ->from('#__gm_ceiling_projects')
                ->where("project_mounter is not NULL and project_mounting_date is NOT NULL");
            $db->setQuery($query);

            $items = $db->loadObjectList();
            foreach ($items as $value) {
                $columns = [];
                $values = [];
                foreach($value as $key => $val ){
                    array_push($columns, $key);
                    array_push($values, $val);
                }
                $query = $db->getQuery(true);
                $query
                    ->insert('#__gm_ceiling_projects_mounts')
                    ->columns($db->quoteName($columns))
                    ->values(implode(',',$db->quote($values)));
                    $db->setQuery($query);
                    $db->execute();
            }
            
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }*/
   /* public function WTF(){
        $db = JFactory::getDbo();
        for($i=1;$i<100000;$i++){
            try {
                $client = rand(1, 1087);
                $status = rand(1, 10);
                $query = $db->getQuery(true);
                $query->insert('rgzbn_gm_ceiling_clients_statuses_map')
                    ->columns('`client_id`,`status_id`')
                    ->values("$client,$status");
                $db->setQuery($query);
                $db->execute();
            }
            catch(Exception $e){
                $i--;
                continue;
            }
        }

    }*/
    /*public function costyl_original_sketch(){
        try
        {
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);
            $query->select('`id`, `original_sketch`')
                ->from('`#__gm_ceiling_calculations`')
                ->where('`change_time` < \'2018-07-05 00:00:00\'');
            $db->setQuery($query);

            $items = $db->loadObjectList();
            foreach ($items as $value) {
                if (!empty($value->original_sketch))
                {
                    $str = $value->original_sketch;
                    $data = explode('||',$str);
                    $alphavite = $data[4];
                    $code = $data[3];
                    $walls = $data[0];
                    $diags = $data[1];
                    $pt = explode(';',$data[2]);
                    array_pop($pt);
                    $pt_points = '';
                    $iter = 0;
                    for ($i = 0; $i < count($pt) / 2; $i++)
                    {
                        $x = $pt[$iter];
                        $x += 4;
                        $iter++;
                        $y = $pt[$iter];
                        $y += 2;
                        $iter++;
                        $pt_points .= $x.';'.$y.';';
                    }
                    $str_result = $walls.'||'.$diags.'||'.$pt_points.'||'.$code.'||'.$alphavite;
                    $query = $db->getQuery(true);
                    $query->update('`#__gm_ceiling_calculations`')
                        ->set("`original_sketch` = '$str_result'")
                        ->where("`id` = $value->id");
                    $db->setQuery($query);
                    $db->execute();
                }
            }
        }
        catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }*/
   /* function costyl_canvases(){
        try
        {
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);
            $query->select('id,id_canvas,barcode,article,quad')
                ->from('`#__gm_ceiling_canvases_all`')
                ->where('id_canvas between 228 and 297');
            $db->setQuery($query);

            $items = $db->loadObjectList();

            foreach ($items as $value) {
                $query = $db->getQuery(true);
                $query->insert('`#__gm_ceiling_analytics_canvases`')
                    ->columns('canvas_id,roller_id,barcode,article,quad,price,stock,user_id,counterparty_id,status')
                    ->values("$value->id_canvas,$value->id,$value->barcode,$value->article,$value->quad,55,2,71,9,1");
                $db->setQuery($query);
                $db->execute();
            
            }
        }
        catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }*/

   function writeComp(){
       try {
           $db = JFactory::getDbo();
           $query = $db->getQuery(true);
           $query
               ->select('id')
               ->from('`#__gm_ceiling_calculations`')
               ->order('id');
           $db->setQuery($query);
           $ids = $db->loadObjectList();
           foreach ($ids as $id){
               $components = Gm_ceilingHelpersGm_ceiling::calculate_components($id->id,null,0);
               if(!empty($components)){
                   foreach ($components as $component){

                       $query->clear();
                       $query
                           ->insert('`#__gm_ceiling_calcs_components`')
                           ->columns('`calc_id`,`component_id`,`count`,`sum`')
                           ->values("$id->id,".$component['id'].",".$component['quantity'].",".$component['self_dealer_total']);
                       $db->setQuery($query);
                       $db->execute();

                   }
               }
           }
       }
       catch(Exception $e) {
           Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
       }
   }

   function writeWire(){
       try {
           $db = JFactory::getDbo();
           $query = $db->getQuery(true);
           $query
               ->select('id,n19')
               ->from('`#__gm_ceiling_calculations`')
               ->where('n19>0')
               ->order('id');
           $db->setQuery($query);
           $ids = $db->loadObjectList();
           foreach ($ids as $id){
               $query->clear();
               $query
                   ->insert('`#__gm_ceiling_wires`')
                   ->columns('`calc_id`,`wire_id`,`count`')
                   ->values("$id->id,4,$id->n19");
               $db->setQuery($query);
               $db->execute();

           }
       }
       catch(Exception $e) {
           Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
       }
   }
   function costyl_mp(){
       try
       {
           $db = JFactory::getDbo();
           $query = $db->getQuery(true);
           $query->select('user_id,mp11')
               ->from('`rgzbn_gm_ceiling_mount`');
           $db->setQuery($query);

           $items = $db->loadObjectList();

           foreach ($items as $value) {
               $query = $db->getQuery(true);
               $query->update('`rgzbn_gm_ceiling_mount`')
                   ->set("mp11_3 = $value->mp11")
                   ->where("user_id = $value->user_id");
               $db->setQuery($query);
               $db->execute();

           }
       }
       catch(Exception $e) {
           Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
       }
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
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
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
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());

        }
    }

    function commercialOffer()
    {
        try{
            $jinput = JFactory::getApplication()->input;
            $code = $jinput->get('code', null, 'STRING');
            $type_kp = $jinput->get('type', null, 'INT');
            $users_model = Gm_ceilingHelpersGm_ceiling::getModel('users');
            $result  = $users_model->acceptCommercialOfferCode($code, $type_kp);
            $type = JFactory::getUser($result->user_id)->dealer_type;
            $dealer_info_model = Gm_ceilingHelpersGm_ceiling::getModel('dealer_info');
            $dealer_city = $dealer_info_model->getDataById($result->user_id)->city;
            if ($type == 3)
            {
                if($type_kp==1){
                    $this->setRedirect(JRoute::_('/files/KP_OTDMSKVRN.pdf', false));
                }
                else{
                    $this->setRedirect(JRoute::_('/files/KP_OTD.pdf', false));
                }
                
            }
            else if ($type == 6)
            {
                if($type_kp==1){
                    $this->setRedirect(JRoute::_('/files/KP_Proizv.pdf', false));
                }
                else{
                    $this->setRedirect(JRoute::_('/index.php?option=com_gm_ceiling&view=info&type=thanks', false));
                }
                
            }
            else if ($type == 7)
            {
                if($type_kp == 3) {
                    $this->setRedirect(JRoute::_('/files/zastroishiki_moscow.pdf', false));
                }
                else {
                    $this->setRedirect(JRoute::_('/files/KP_DEV.pdf', false));
                }
            }
            else
            {
                if($type_kp==2){
                    $this->setRedirect(JRoute::_('/files/Oshibki_montazha.pdf', false));
                }
                else
                {
                    if($dealer_city == "Москва"){
                        $this->setRedirect(JRoute::_('/files/KP_Moscow.pdf', false));
                    }
                    else{
                        $this->setRedirect(JRoute::_('/files/KP_DEA.pdf', false));
                    }
                }
            }
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());

        } 
    }

    function dealerInstruction()
    {
        try{
            $jinput = JFactory::getApplication()->input;
            $code = $jinput->get('code', null, 'STRING');
            $short = $jinput->get('short', null, 'STRING');
            $users_model = Gm_ceilingHelpersGm_ceiling::getModel('users');
            $result  = $users_model->acceptDealerInstructionCode($code,$short);
            if($short == 1){
                $this->setRedirect('https://youtu.be/4NuudvjMLug');
            }
            if($short == 0){
                $this->setRedirect('https://youtu.be/SliL0bmgTug');
            }
            if($short == 2){
                $this->setRedirect('https://youtu.be/pRLvFBiZuDg');
            }
            
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());

        }
    }
    function changeDealerType(){
        try{
            $jinput = JFactory::getApplication()->input;
            $id = $jinput->get('user_id', null, 'INT');
            $type = $jinput->get('type', null, 'INT');
            $users_model = Gm_ceilingHelpersGm_ceiling::getModel('users');
            $users_model->change_dealer_type($id,$type);
            die(true);
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());

        }
    }
    function add_request(){
        try{
            $jinput = JFactory::getApplication()->input;
            $id = $jinput->get('id', null, 'INT');
            $dealer_id = $jinput->get('dealer_id', null, 'INT');
            $users_model = Gm_ceilingHelpersGm_ceiling::getModel('users');
            $users_model->add_request($id,$dealer_id);
            die(true);
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());

        }
    }
    function dealerRequest(){
        try{
            $jinput = JFactory::getApplication()->input;
            $id = $jinput->get('id', null, 'INT');
            $type = $jinput->get('type', null, 'STRING');
            $user = JFactory::getUser($id);
            $callback_model = Gm_ceilingHelpersGm_ceiling::getModel('callback');
           
            if($type == "info"){
                $callback_model->save(date('Y-m-d H:i:s'),"Дилер $user->name хочет узнать подробнее о быстром заказе",$user->associated_client,1);
            }
            if($type == "access"){
                $callback_model->save(date('Y-m-d H:i:s'),"Дилер $user->name хочет получить доступ к приложению, для быстрого заказа",$user->associated_client,1);
            }
                $this->setRedirect(JRoute::_('/index.php?option=com_gm_ceiling&view=info&type=thanks', false));
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());

        }
    }

    function verify(){
        try{
            $jinput = JFactory::getApplication()->input;
            $Password = $jinput->get('pass', null, 'STRING');
            $user_id = $jinput->get('id', null, 'STRING');
            $user = JFactory::getUser($user_id);
            $verifyPass = JUserHelper::verifyPassword($Password, $user->password, $user->id);

            die(json_encode((object)array("verification" => $verifyPass,"user_id" => $user_id)));
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());

        }
    }

    function moveMount(){
       $model = Gm_ceilingHelpersGm_ceiling::getModel('mount');
       $model->transferAllDealerprices();
    }
}
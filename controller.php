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
define('USERNAME', 'gm_vrn-api');
define('PASSWORD', 'gm_vrn');
define('GATEWAY_URL', 'https://3dsec.sberbank.ru/payment/rest/');
define('RETURN_URL', 'http://test1.gm-vrn.ru/index.php?option=com_gm_ceiling&view=project&type=calculator&subtype=project&id=');

jimport('joomla.application.component.controller');

/**
 * Class Gm_ceilingController
 *
 * @since  1.6
 */
class Gm_ceilingController extends JControllerLegacy
{
    /**
     * Method to display a view.
     *
     * @param   boolean $cachable If true, the view output will be cached
     * @param   mixed $urlparams An array of safe url parameters and their variable types, for valid values see {@link JFilterInput::clean()}.
     *
     * @return  JController   This object to support chaining.
     *
     * @since    1.5
     */
    public function display($cachable = false, $urlparams = false)
    {
        try
        {
            $app = JFactory::getApplication();
            $user = JFactory::getUser();
            $view = $app->input->getCmd('view', 'components');
            $task = $app->input->getCmd('task', 'components');
            $subtype = $app->input->getCmd('subtype', NULL);

            $app->input->set('subtype', $subtype);
            $type = $app->input->getCmd('type', NULL);
            $id = $app->input->getInt('id');
            if ($type == NULL) {
                $groups = $user->get('groups');
                $_SESSION['user_group'] = $groups;
                $_SESSION['dealer_type'] = $user->dealer_type;
                if ($task == "mainpage") {
                    if (!$user->guest) {
                        $dop_num_model = $this->getModel('Dop_numbers_of_users','Gm_ceilingModel');
                        $dop_num = ($dop_num_model->getData($user->id))->dop_number;
                        if(!empty($dop_num)){
                            $dealers_key_model = $this->getModel('Dealers_Key','Gm_ceilingModel');
                            $api_key = $dealers_key_model->getData($user->dealer_id);
                            if(!empty($api_key->key)){
                                $_SESSION['api_key'] = $api_key->key;
                                $_SESSION['dop_num'] = $dop_num;
                            }
                        }
                        if (in_array("13", $groups)) {
                            $type = "managermainpage"; //Менеджер дилера
                        } elseif (in_array("21", $groups)) {
                            $type = "calculatormainpage"; //Замерщик дилера
                        } elseif (in_array("12", $groups)) {
                            $type = "chiefmainpage"; //Начальник МС дилера
                        } elseif (in_array("14", $groups)) {
                            $type = "dealermainpage"; //Дилер
                            if($user->dealer_type == 2){
                                $type = "clientmainpage"; //клиент
                            }
                            if($user->dealer_type == 8){
                                $type = "partnermainpage"; //Дилер
                            }
                            if($user->dealer_type == 7){ //Застройщик
                                $type = "buildermainpage";
                            }

                        } elseif (in_array("16", $groups)) {
                            $type = "gmmanagermainpage"; //Менеджер ГМ
                        } elseif (in_array("17", $groups)) {
                            $type = "gmchiefmainpage"; //Начальник МС ГМ
                        } elseif (in_array("20", $groups)) {
                            $type = "adminmainpage"; //Администратор
                        } elseif (in_array("22", $groups)) {
                            $type = "gmcalculatormainpage"; //Замерщик ГМ
                        } elseif (in_array("19", $groups)) {
                            $type = "gmstock"; //Кладовщик ГМ
                        } elseif (in_array("18", $groups) || in_array("23", $groups)) {
                            $type = "gmguild"; //Цех ГМ
                        } elseif (in_array("11", $groups)) {
                            $type = "mountersmainpage";//монтажная бригада
                        }
                        elseif (in_array("24", $groups)) {
                            $type = "manufacturermainpage";//производитель
                        }
                        elseif (in_array("33", $groups)) {
                            $type = "mastermainpage";//мастер
                        }
                        elseif(in_array('46',$groups)){
                            $type = "buildermountersmainpage";
                        }
                        elseif (
                            in_array('34', $groups) ||
                            in_array('39', $groups) ||
                            in_array('40', $groups) ||
                            in_array('41', $groups) ||
                            in_array('42', $groups) ||
                            in_array('43', $groups) ||
                            in_array('44', $groups)
                        ) {
                            $type = "buildermountersmainpage";
                            //$this->setRedirect(JRoute::_('index.php?option=com_gm_ceiling&view=analytics', false));//аналитик
                        }
                        elseif (in_array('35', $groups)) {
                            $type = "analyst";
                            //$this->setRedirect(JRoute::_('index.php?option=com_gm_ceiling&view=analytics', false));//аналитик
                        }
                        elseif(in_array('45', $groups)){
                            $this->setRedirect(JRoute::_('index.php?option=com_gm_ceiling&view=teams&type=storekeeper', false));//аналитик
                        }
                        elseif(in_array('47',$groups)){
                            /*менеджер по производителям*/
                            $this->setRedirect(JRoute::_('index.php?option=com_gm_ceiling&view=mainpage&type=provmanager', false));
                        }
                        elseif(in_array('48,49,50,51,52,53,54',$groups)){
                            /*производитель*/
                        }
                        if (!empty($type)) {
                            $this->setRedirect(JRoute::_('index.php?option=com_gm_ceiling&view=mainpage&type='.$type, false));
                            $app->input->set('type', $type);
                        }
                    } else {
                        $this->setRedirect(JRoute::_('index.php?option=com_users&view=registration', false));
                    }
                }
            }
            /*для просмотра клиентом страницы с товараами (project_consumables)*/
            $userClient = null;
            if($view == 'project' && $type == 'consumables'){
                $projectModel = self::getModel('Project');
                $project = $projectModel->getNewData($id);
                $usersModel = Gm_ceilingHelpersGm_ceiling::getModel('users');
                $userClient = $usersModel->getUserByAssociatedClient($project->client_id);
                if(!empty($userClient) && $userClient->dealer_type == 2){
                    Gm_ceilingHelpersGm_ceiling::forceLogin((int)$userClient->id);
                }
            }
            /*&& $view != 'prices' && $view != 'canvases' && $view != 'components'*/
            if (empty($userClient) && $user->guest && $view != 'calculationform' && $view != 'info' && $view != 'analiticdealers' )
            {
                header('location: /index.php?option=com_users&view=login');
                die('403 forbidden');
            }

            $app->input->set('view', $view);
            parent::display($cachable, $urlparams);

            return $this;
        }
        catch(Exception $e)
        {
            if ($e->getCode() > 0) {
                throw new Exception($e->getMessage(), $e->getCode());
            }
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

//KM_CHANGED START

    /* Функция для AJAX-изменения комментария бухгалтера. */
    public function change_buh_note()
    {
       /* try
        {
            $jinput = JFactory::getApplication()->input;
        
            $id = $jinput->get('id', '0', 'INT');
            $buh_note = $jinput->get('buh_note', '0', 'STRING');
    
            $user = JFactory::getUser();
            if (!$user->guest) {
                $project_model = Gm_ceilingHelpersGm_ceiling::getModel('project');
                $result = $project_model->change_buh_note($id, $buh_note);
            }
    
            die($result);
        }
       catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }*/
    }

    /* Функция для AJAX-изменения статуса договора. */
    public function change_status()
    {
        try
        {
            $jinput = JFactory::getApplication()->input;
        
            $id = $jinput->get('id', '0', 'INT');
            $project_status = $jinput->get('project_status', '0', 'INT');
    
            $user = JFactory::getUser();
            if (!$user->guest) {
                $project_model = Gm_ceilingHelpersGm_ceiling::getModel('project');
                $result = $project_model->change_status($id, $project_status);
            }
            Gm_ceilingHelpersGm_ceiling::push($id, $project_status);
    
            die($result);
        }
       catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function addNewAdvt()
    {
        try
        {
            $jinput = JFactory::getApplication()->input;
            $name = $jinput->get('name', '', 'STRING');
            $advt_model = Gm_ceilingHelpersGm_ceiling::getModel('api_phones');
            $result = $advt_model->save($name);
            die(json_encode($result));
        }
       catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function back_status($id, $project_status)
    {
        try
        {
            $jinput = JFactory::getApplication()->input;
        
            //$id = $jinput->get('id', '0', 'INT');
            //$project_status = $jinput->get('project_status', '0', 'INT');
            //throw new Exception($id);
            $user = JFactory::getUser();
            if (!$user->guest) {
                $project_model = Gm_ceilingHelpersGm_ceiling::getModel('project');
                $project_model->change_status($id, $project_status);
            }
        }
       catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    /* функция для AJAX-сохранения дополнительных затрат по договору */
    public function add_spend()
    {
        try
        {
           /* $jinput = JFactory::getApplication()->input;

            $id = $jinput->get('id', '0', 'INT');

            $extra_spend_title = $jinput->get('extra_spend_title', '', 'ARRAY');
            $extra_spend_value = $jinput->get('extra_spend_value', '', 'ARRAY');
            $extra_spend = array();
            foreach ($extra_spend_title as $key => $title) {
                if (!empty($title) && $extra_spend_value[$key]) {
                    $extra_spend[] = array(
                        'title' => $title,
                        'value' => $extra_spend_value[$key]
                    );
                }
            }
            $extra_spend = json_encode($extra_spend, JSON_FORCE_OBJECT);

            $project_model = Gm_ceilingHelpersGm_ceiling::getModel('project');
            $result = $project_model->update_spend($id, $extra_spend);

            die($result);*/
        }
       catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function addPhoneToClient()
    {
        $user = JFactory::getUser();
        $user_group = $user->groups;
        try
        {
            $jinput = JFactory::getApplication()->input;
            $id = $jinput->get('id', null, 'INT');
            $old_id = $jinput->get('old_id',null,'INT');
            $project_id = $jinput->get('p_id', null, 'INT');
            $client_model = Gm_ceilingHelpersGm_ceiling::getModel('client');
            $clients_dop_contacts_model =  Gm_ceilingHelpersGm_ceiling::getModel('clients_dop_contacts');
            $clients_dop_contacts_model->updateClientId($old_id,$id);
            $cl_history = Gm_ceilingHelpersGm_ceiling::getModel('client_history');
            $cl_history->updateHistoryByClientId($old_id,$id);
            $client_phones_model = Gm_ceilingHelpersGm_ceiling::getModel('client_phones');
            $client_phones_model->changeClientId($old_id, $id);
            $project_model = Gm_ceilingHelpersGm_ceiling::getModel('project');
            $cl_history->save($id, "Клиент звонил с нового номера");
            $project_model->delete($project_id);
            $client_model->delete($old_id);
            die(true); 
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());

        }           
        
    }

    public function findOldClients()
    {
        try {
            $jinput = JFactory::getApplication()->input;
            $FIO = $jinput->get('fio', '', 'STRING');
            $flag = $jinput->get('flag', 'clients', 'STRING');
            $label_id = $jinput->get('label_id', null, 'INT');
            $manager_id = $jinput->get('manager_id', null, 'INT');
            $city = $jinput->get('city', null, 'STRING');
            $dealer_price_sort = $jinput->get('dealer_price_sort', null, 'STRING');

            $clients_model = Gm_ceilingHelpersGm_ceiling::getModel('clients');
            if ($flag == 'clients')
            {
                $result = $clients_model->getItemsByClientName($FIO);
            }
            elseif ($flag == 'dealers')
            {
                $result = $clients_model->getDealersByClientName($FIO, $manager_id, $city);
            }
            elseif ($flag == 'designers')
            {
                $result = $clients_model->getDesignersByClientName($FIO, 3, $label_id);
            }
            elseif ($flag == 'designers2')
            {
                $result = $clients_model->getDesignersByClientName($FIO, 5);
            }
            elseif ($flag == 'builders')
            {
                $result = $clients_model->getDesignersByClientName($FIO, 7);
            }
            elseif ($flag == 'wininstallers')
            {
                $result = $clients_model->getDesignersByClientName($FIO, 8);
            }

            foreach ($result as $key => $dealer) {
                $user_dealer = JFactory::getUser($dealer->dealer_id);
                $result[$key]->min_canvas_price = $user_dealer->getFunctionCanvasesPrice("MIN");
                $result[$key]->min_component_price = $user_dealer->getFunctionComponentsPrice("MIN");

                /*Dealer history*/
                /*$recoil_map_project_model = Gm_ceilingHelpersGm_ceiling::getModel('recoil_map_project');
                $dealer_history = $recoil_map_project_model->getData($client->dealer_id);
                $dealer_history_sum = 0;
                foreach ($dealer_history as $key => $item) {
                    $dealer_history_sum += $item->sum;*/
            }

            if ($dealer_price_sort != "") {
                $result_temp = $result;
                $result = [];
                $nil = "000000000000000000000000000000000000000000000000000";

                foreach ($result_temp as $key => $dealer) {
                    $keyCanv = $dealer->min_canvas_price;
                    $keyComp = $dealer->min_canvas_price;
                    $key = "";
                    $i = 0;
                    for(;$i < strlen($keyCanv) && $i < strlen($keyComp); $i++)
                        $key .= $keyCanv[$i] . $keyComp[$i];
                    $key .= substr($keyCanv, $i, strlen($keyCanv));
                    $key .= substr($keyComp, $i, strlen($keyComp));
                    $key .= $dealer->dealer_id;

                    $len = strlen($key);
                    $nillen = strlen($nil);
                    $key .= substr($nil, 0, $nillen - $len);

                    $result[$key] = $dealer;
                }

                if ($dealer_price_sort == "asc")
                    ksort($result);
                else
                    krsort($result);

                $result_temp = $result;
                $result = [];

                foreach ($result_temp as $value)
                    $result[] = $value;
            }
            die(json_encode($result));
        } catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function findOldDealers()
    {
        try
        {
            $jinput = JFactory::getApplication()->input;
            $FIO = $jinput->get('fio', '', 'STRING');
            $clients_model = Gm_ceilingHelpersGm_ceiling::getModel('clients');
            $result = $clients_model->getDealersByClientName($FIO);
            die(json_encode($result));
        }
       catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

public function register_mnfctr(){
        try
        {
            $jinput = JFactory::getApplication()->input;
            $phone = $jinput->get('phone', '', 'STRING');
            $phone = preg_replace('/[\(\)\-\+\s]/', '', $phone);
            $FIO = $jinput->get('FIO', '', 'STRING');
            $email = $jinput->get('email', '', 'STRING');
            $city = $jinput->get('city', '', 'STRING');
            $clientform_model =Gm_ceilingHelpersGm_ceiling::getModel('ClientForm', 'Gm_ceilingModel');
            $client_data['client_name'] = $FIO;
            $client_data['client_contacts'] = $phone;
            $client_id = $clientform_model->save($client_data);

            //создание user'а
            $dealer_id = Gm_ceilingHelpersGm_ceiling::registerUser($FIO, $phone, $email, $client_id, 6);
            $client_model = Gm_ceilingHelpersGm_ceiling::getModel('Client', 'Gm_ceilingModel');
            $client_model->updateClient($client_id,null,$dealer_id);
            $dealer_info_model = Gm_ceilingHelpersGm_ceiling::getModel('dealer_info');
            $dealer_info_model->update_city($dealer_id,$city);
            $clients_dop_contacts = Gm_ceilingHelpersGm_ceiling::getModel('clients_dop_contacts');
            $clients_dop_contacts->save($client_id,1,$email);
            die(json_encode(true));
            
       }
      catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function update_mnfctr(){
        try
        {
            $jinput = JFactory::getApplication()->input;
            $field = $jinput->get('field', '', 'STRING');
            $value = $jinput->get('value', '', 'STRING');
            $id = $jinput->get('id', '', 'INT');
            if($field == "city"){
                $dealer_info_model = Gm_ceilingHelpersGm_ceiling::getModel('dealer_info');
                $dealer_info_model->update_city($id,$value);
            }
            else{
                $user_model = Gm_ceilingHelpersGm_ceiling::getModel('users');
                if($field == "phone"){
                    $user_model->updatePhone($id,$value);
                }
                if($field == "name"){
                    $user_model->updateName($id,$value);
                }
                if($field == "email"){
                    $user_model->updateEmail($id,$value);
                }
            }
            
            die(true);
            
       }
      catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }
    
    public function register_user()
    {
        try
        {
            $jinput = JFactory::getApplication()->input;
            $phone = $jinput->get('phone', '', 'STRING');
            $phone = preg_replace('/[\(\)\-\+\s]/', '', $phone);

            $FIO = $jinput->get('FIO', '', 'STRING');
            $email = $jinput->get('email', '', 'STRING');
            $password = $jinput->get('pass', '', 'STRING');
            $password2 = $jinput->get('pass2', '', 'STRING');
            $login = $jinput->get('login', '', 'STRING');
            $client_id = $jinput->get('client_id', '', 'INT');
            $project_id = $jinput->get('project_id', '', 'INT');
            if(empty($login)){
               $login = $phone;
            }
            jimport('joomla.user.helper');
            $data = [
               "name" => $FIO,
               "username" => $login,
               "password" => $password,
               "password2" => $password2,
               "email" => $email,
               "groups" => array(2, 14),
               "phone" => $phone,
               "block" => 0,
               "dealer_type" => 2
            ];
            try {
               $user = new JUser;
               if (!$user->bind($data)) {
                   throw new Exception($user->getError());
               }
               if (!$user->save()) {
                   throw new Exception($user->getError());
               }

               $userID = $user->id;



               $user =& JUser::getInstance((int)$userID);
               $post['dealer_id'] = $userID;
               if (!$user->bind($post)) return false;
               if (!$user->save()) return false;
               $margin_model = $this->getModel('dealer_info', 'Gm_ceilingModel');
               $margin_model->save(50,50,60,0,0,0,$userID,0,0);
               //обновление dealer_id у клиента 
               $this->updateDealerId('clients',$client_id,$userID);
               //обновление dealer_id у проектов
               $this->updateDealerId('projects',$client_id,$userID,$project_id);
               $mailer = JFactory::getMailer();
               $config = JFactory::getConfig();
               $sender = array(
                   $config->get('mailfrom'),
                   $config->get('fromname')
               );
               $mailer->setSender($sender);
               $mailer->addRecipient($email);
               $body = "Здравствуйте. Вы зарегистрировались на сайте Гильдии Мастеров. Данные Вашей  учетной записи \n Логин: " . $login . " \n Пароль: " . $password;
               $mailer->setSubject('Данные регистрации');
               $mailer->setBody($body);
               $send = $mailer->Send();
               $result = json_encode($userID);
               die($result);
            } catch (Exception $e) {
               $result = json_encode([
                   'error' => [
                       'msg' => $e->getMessage(),
                       'code' => $e->getCode(),
                   ],
               ]);
               die($result);
            }
       }
      catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function deleteUser()
    {
        try
        {
            $jinput = JFactory::getApplication()->input;
            $user_id = $jinput->get('user_id', '', 'INT');
            $client_id = $jinput->get('client_id', '', 'INT');
            $this->updateDealerId('clients',$client_id,1);
            $this->updateDealerId('projects',$client_id,1);
            $user =& JUser::getInstance($user_id);
            try{
                if (!$user->delete()) {
                    throw new Exception($user->getError());
                }
                
            }
            catch(Eception $e){
                $result = json_encode(array(
                    'error' => array(
                        'msg' => $e->getMessage(),
                        'code' => $e->getCode(),
                    ),
                ));
                die($result);
            }
        }
       catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }
    public function updateClientFIO(){
        try
        {
            $jinput = JFactory::getApplication()->input;
            $client_id = $jinput->get('client_id', '', 'INT');
            $new_fio = $jinput->get('fio', '', 'STRING');
            $model_client = Gm_ceilingHelpersGm_ceiling::getModel('client');
            $user_model = Gm_ceilingHelpersGm_ceiling::getModel('users');
            $old_name = $model_client->getClientById($client_id)->client_name;
            $model_client->updateClient($client_id,$new_fio);
            $user_model->updateUserNameByAssociatedClient($client_id, $new_fio);
            $history_model = Gm_ceilingHelpersGm_ceiling::getModel('client_history');
            $history_model->save($client_id,"Изменено ФИО с $old_name на $new_fio");
            die($new_fio);
        }
       catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }
    public function getClientDealerId()
    {
        try
        {
            $jinput = JFactory::getApplication()->input;
            $client_id = $jinput->get('client_id', '', 'INT');
            $model_client = Gm_ceilingHelpersGm_ceiling::getModel('client');
            $client_dealer_id = $model_client->getClientById($client_id)->dealer_id;
            die(json_encode($client_dealer_id));
        }
       catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }
    public function updateDealerId($model_name = null,$client_id = null,$dealer_id=null,$project_id=null){
        try
        {
            if(empty($client_id)&&empty($dealer_id)&&empty($model_name))
            {
                $jinput = JFactory::getApplication()->input;
                $client_id = $jinput->get('client_id', '', 'INT');
                $dealer_id = $jinput->get('dealer_id', '', 'INT');
                $model_name = $jinput->get('model_name', '', 'STRING');
                $project_id = $jinput->get('project_id', '', 'INT');
                //throw new Exception();
            }
            $model = Gm_ceilingHelpersGm_ceiling::getModel($model_name);
            $model->updateDealerId($client_id,$dealer_id,$project_id);
        }
       catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }
   
    /* функция для AJAX-сохранения штрафов по договору */
    public function add_penalty()
    {
        try
        {
            $jinput = JFactory::getApplication()->input;
        
            $id = $jinput->get('id', '0', 'INT');
    
            $penalty_title = $jinput->get('penalty_title', '', 'ARRAY');
            $penalty_value = $jinput->get('penalty_value', '', 'ARRAY');
            $penalty = array();
            foreach ($penalty_title as $key => $title) {
                if (!empty($title) && $penalty_value[$key]) {
                    $penalty[] = array(
                        'title' => $title,
                        'value' => $penalty_value[$key]
                    );
                }
            }
            $penalty = json_encode($penalty, JSON_FORCE_OBJECT);
    
            $project_model = Gm_ceilingHelpersGm_ceiling::getModel('project');
            $result = $project_model->update_penalty($id, $penalty);
    
            die($result);
        }
       catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    /* функция для AJAX-сохранения премий по договору */
    public function add_bonus()
    {
        try
        {
            $jinput = JFactory::getApplication()->input;

            $id = $jinput->get('id', '0', 'INT');

            $bonus_title = $jinput->get('bonus_title', '', 'ARRAY');
            $bonus_value = $jinput->get('bonus_value', '', 'ARRAY');
            $bonus = array();
            foreach ($bonus_title as $key => $title) {
                if (!empty($title) && $bonus_value[$key]) {
                    $bonus[] = array(
                        'title' => $title,
                        'value' => $bonus_value[$key]
                    );
                }
            }
            $bonus = json_encode($bonus, JSON_FORCE_OBJECT);

            $project_model = Gm_ceilingHelpersGm_ceiling::getModel('project');
            $result = $project_model->update_bonus($id, $bonus);

            die($result);
        }
       catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    /* функция для AJAX-получения доступного времени для записи на замер в конкретную дату */
    public function get_calculator_times()
    {
        try
        {
            $jinput = JFactory::getApplication()->input;
            $jdate = new JDate($jinput->get('date', '01.01.1970', 'STRING') . " 00:00:00");
            $date = $jdate->format('Y-m-d');


            $times = array(
                0 => "9:00",
                1 => "10:00",
                2 => "11:00",
                3 => "12:00",
                4 => "13:00",
                5 => "14:00",
                6 => "15:00",
                7 => "16:00",
                8 => "17:00",
                9 => "18:00",
                10 => "19:00",
                11 => "20:00",
                12 => "21:00"
            );

            $times2 = array(
                0 => "10:00",
                1 => "11:00",
                2 => "12:00",
                3 => "13:00",
                4 => "14:00",
                5 => "15:00",
                6 => "16:00",
                7 => "17:00",
                8 => "18:00",
                9 => "19:00",
                10 => "20:00",
                11 => "21:00",
                12 => "22:00"
            );


            $db = JFactory::getDbo();
            $query = "SELECT DATE_FORMAT(`project_calculation_date`,'%H:%i') as _time FROM `#__gm_ceiling_projects` WHERE DATE_FORMAT(`project_calculation_date`,'%Y-%m-%d') = '" . $date . "'";

            $db->setQuery($query);

            $busy_times = $db->loadObjectList();

            foreach ($busy_times as $busy_time) {
                if (($key = array_search($busy_time->_time, $times)) !== FALSE) {
                    unset($times[$key]);
                    unset($times2[$key]);
                }
            }

            $return = array();
            foreach ($times as $key => $time) {
                $return[] = $times[$key] . " - " . $times2[$key];
            }

            die(json_encode($return));
        }
       catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function createProject($client_id, $api_phone_id, $project_info, $project_calculation_date)
    {
        try
        {
            $user = JFactory::getUser();
            if(empty($user->dealer_id)){
                $client_model = Gm_ceilingHelpersGm_ceiling::getModel('client');
                $client = $client_model->getClientById($client_id);
                $user = JFactory::getUser($client->dealer_id);
            }
            $info_model = Gm_ceilingHelpersGm_ceiling::getModel('dealer_info');
            $gm_canvases_margin = $info_model->getMargin('gm_canvases_margin', 1);
            $gm_components_margin = $info_model->getMargin('gm_components_margin', 1);
            $gm_mounting_margin = $info_model->getMargin('gm_mounting_margin', 1);
            $dealer_canvases_margin = $info_model->getMargin('dealer_canvases_margin', $user->dealer_id);
            $dealer_components_margin = $info_model->getMargin('dealer_components_margin', $user->dealer_id);
            $dealer_mounting_margin = $info_model->getMargin('dealer_mounting_margin', $user->dealer_id);
            $project_model = Gm_ceilingHelpersGm_ceiling::getModel('projectform');
            $project_data['state'] = 1;
            $project_data['client_id'] = $client_id;
            $project_data['project_info'] = $project_info;
            $project_data['project_status'] = 0;
            $project_data['project_calculation_daypart'] = 0;
            $project_data['project_calculation_date'] = $project_calculation_date;
            $project_data['project_note'] = "";
            $project_data['who_calculate'] = 0;
            $project_data['created'] = date("Y.m.d");
            $project_data['project_discount'] = 0;
            $project_data['gm_canvases_margin'] = $gm_canvases_margin;
            $project_data['gm_components_margin'] = $gm_components_margin;
            $project_data['gm_mounting_margin'] = $gm_mounting_margin;
            $project_data['dealer_canvases_margin'] = $dealer_canvases_margin;
            $project_data['dealer_components_margin'] = $dealer_components_margin;
            $project_data['dealer_mounting_margin'] = $dealer_mounting_margin;
            $project_data['api_phone_id'] = $api_phone_id;
            $result = $project_model->save($project_data);
            return $result;
        }
       catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function getDataFromPromo()
    {
        try
        {
            $fromDomain = 'http://promo.gm-vrn.ru';
            header('Access-Control-Allow-Origin: ' . $fromDomain);
            header('Access-Control-Allow-Credentials: true');
            header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
            header('Access-Control-Allow-Headers: Content-Type');
            header('Access-Control-Max-Age: 1000');
            header('Access-Control-Allow-Headers: Content-Type, Content-Range, Content-Disposition, Content-Description');

            $jinput = JFactory::getApplication()->input;
            $name = $jinput->get('name', 'Клиент с promo', 'STRING');
            $phones[] = $jinput->get('phone', '', 'STRING');
            $phones[0] = mb_ereg_replace('[^\d]', '', $phones[0]);
            $email = $jinput->get('email', '', 'STRING');
            $action = $jinput->get('action', '', 'STRING');
            $api_phone_id = $jinput->get('api_phone_id', 0, 'INT');
            $adress = $jinput->get('adress', '', 'STRING');
            $calc_date = $jinput->get('date', '0000-00-00', 'STRING');
            $calc_time = $jinput->get('time', '00:00', 'STRING');
            $dealer_id = $jinput->get('dealer_id', 1, 'INT');
            $calc_date_time = $calc_date . ' ' . $calc_time;
            $cl_phones_model = Gm_ceilingHelpersGm_ceiling::getModel('client_phones');
            $result = $cl_phones_model->getItemsByPhoneNumber($phones[0], $dealer_id);
            $call_model = Gm_ceilingHelpersGm_ceiling::getModel('callback');
            $dop_contacts_model = Gm_ceilingHelpersGm_ceiling::getModel('clients_dop_contacts');
            /*проверка на существование этого клиента*/
            if (empty($result)) {
                //регистрация клиента
                $client_model = Gm_ceilingHelpersGm_ceiling::getModel('ClientForm');
                $client_data['client_name'] = $name;
                $client_data['type_id'] = 1;
                $client_data['dealer_id'] = $dealer_id;//GM
                $client_data['created'] = date("Y-m-d H:i:s");
                $client_id = $client_model->save($client_data);
                //добавляем номер телефона
                $cl_phones_model->save($client_id, $phones);
                $proj_id = $this->createProject($client_id, $api_phone_id, $adress, $calc_date_time);
            } else {
                $client_id = $result->id;
                $pr_model = Gm_ceilingHelpersGm_ceiling::getModel('project');
                $repeat_model = Gm_ceilingHelpersGm_ceiling::getModel('repeatrequest');
                $projects = $pr_model->getProjectsByClientID($client_id);
                if ($adress != '' && $calc_date_time != '0000-00-00 00:00') {
                    if (count($projects) > 0) {
                        $proj_id = $this->createProject($client_id, 10, $adress, $calc_date_time);
                        $repeat_model->save($proj_id, $api_phone_id);
                    } else {
                        $proj_id = $this->createProject($client_id, $api_phone_id, $adress, $calc_date_time);
                    }
                } else {
                    $find = false;
                    foreach ($projects as $project) {
                        if ($project->api_phone_id == $api_phone_id) {
                            $find = true;
                            $proj_id = $project->id;
                            break;
                        }
                    }
                    if (!$find) {
                        if (count($projects) > 0) {
                            $proj_id = $this->createProject($client_id, 10, $adress, $calc_date_time);
                            $repeat_model->save($proj_id, $api_phone_id);
                        } else {
                            $proj_id = $this->createProject($client_id, $api_phone_id, $adress, $calc_date_time);
                        }
                    }
                }
            }
            if (!empty($email)) {
                $dop_contacts_model->save($client_id, 1, $email);
            }
            $api_phones_model = Gm_ceilingHelpersGm_ceiling::getModel('api_phones');
            $advt_name = $api_phones_model->getDataById($api_phone_id)->name;
            $history_model = Gm_ceilingHelpersGm_ceiling::getModel('client_history');
            $history_model->save($client_id, "Произведено действие на сайте: \"$action\" ($advt_name)");
            $call_model->save(date("Y-m-d H:i:s"), $action, $client_id, $dealer_id);
            die(true);
        }
       catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function addCall()
    {
        try {
            $user = JFactory::getUser();
            $jinput = JFactory::getApplication()->input;
            $jdate = new JDate($jinput->get('date', date('Y-m-d H:i:s'), 'STRING'));
            $id_client = $jinput->get('id_client', null, 'INT');
            $manager_id = $jinput->get('manager_id', 0, 'INT');
            $important = $jinput->get('important',0,'INT');
            if (empty($manager_id)) {
                $manager_id = $user->id;
            }
            $comment = $jinput->get('comment', '', 'STRING');
            $old_call = $jinput->get('old_call', null, 'INT');
            $callback_model = Gm_ceilingHelpersGm_ceiling::getModel('callback');
            if (empty($old_call)) {
                $result = $callback_model->save($jdate, $comment, $id_client, $manager_id,$important);
            } else {
                $result = $callback_model->moveTime($old_call, $jdate, $comment,$id_client,$important);
            }

            die($result);
        } catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function delCall($call_id = null)
    {
        try
        {
            $jinput = JFactory::getApplication()->input;
            if (empty($call_id)) {
                $call_id = $jinput->get('call_id', 0, 'INT');
            }
            $callback_model = Gm_ceilingHelpersGm_ceiling::getModel('callback');
            $result = $callback_model->deleteCall($call_id);

            die(true);
        }
       catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function updateStatusCall()
    {
        try
        {
            $jinput = JFactory::getApplication()->input;
            $id = $jinput->get('id', '', 'INT');
    
            $callback_model = Gm_ceilingHelpersGm_ceiling::getModel('callback');
            $result = $callback_model->updateStatus($id);
        }
       catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
        die(true);
    }

    public function updateCall()
    {
        try
        {
            $jinput = JFactory::getApplication()->input;
            $id = $jinput->get('id', '', 'INT');
            $jdate = new JDate($jinput->get('date', '01.01.1970', 'STRING'));
            $comment = $jinput->get('comment', '', 'STRING');
    
            $callback_model = Gm_ceilingHelpersGm_ceiling::getModel('callback');
            $result = $callback_model->updateCall($id, $jdate, $comment);
    
            die(true);
        }
       catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function addComment()
    {
        try
        {
            $jinput = JFactory::getApplication()->input;
            $id_client = $jinput->get('id_client', '0', 'INT');
            $comment = $jinput->get('comment', '', 'STRING');

            $history_model = Gm_ceilingHelpersGm_ceiling::getModel('client_history');
            $result = $history_model->save($id_client, $comment);

            die(json_encode($result));
        }
       catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function selectComments()
    {
        try
        {
            $jinput = JFactory::getApplication()->input;
            $id_client = $jinput->get('id_client', '0', 'INT');

            $comment_model = Gm_ceilingHelpersGm_ceiling::getModel('client_history');
            $result = $comment_model->getDataByClientId($id_client);

            die(json_encode($result));
        }
       catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function changeCallTime()
    {
        try
        {
            $jinput = JFactory::getApplication()->input;
            $id = $jinput->get('id', '0', 'INT');
            $date = $jinput->get('date', '', 'STRING');
            $comment = $jinput->get('comment', '', 'STRING');
            $callback_model = Gm_ceilingHelpersGm_ceiling::getModel('callback');
            $callback_model->moveTime($id, $date, $comment);
            die(true);
        }
       catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function getColor()
    {
        try
        {
            $model = Gm_ceilingHelpersGm_ceiling::getModel('components');
            $items = $model->getColor();
            $colors = array();
            foreach ($items as $i => $item) {
                if ($item->id != null)
                    $colors[] = array($item->id, $item->title, $item->file);
            }
            //die(json_encode($items));
            die(json_encode($colors));
        }
       catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function getComponentsToCalculationForm()
    {
        try
        {
            $jinput = JFactory::getApplication()->input;
            $component_code = $jinput->get('component_code', '', 'STRING');

            $model = Gm_ceilingHelpersGm_ceiling::getModel('components');
            $items = (object)array();

            $filter = '`count` > 0 AND';
            switch ($component_code)
            {
                case 'n13':
                    $filter = '(`component_id` = 12 OR `component_id` = 21)';
                    $arr = $model->getFilteredItems($filter);
                    foreach ($arr as $key => $value)
                    {
                        if ($value->component_id == 12) {
                            $items->n13_square[] = $value;
                        }
                        if ($value->component_id == 21) {
                            $items->n13_ring[] = $value;
                        }
                    }
                    $items->n13_type[] = array(
                                                'id' => 2,
                                                'title' => 'Круглый',
                                            );
                    $items->n13_type[] = array(
                                                'id' => 3,
                                                'title' => 'Квадратный',
                                            );
                    break;
                case 'ecola':
                    $filter = '(`component_id` = 19 OR `component_id` = 20) and count>0';
                    $arr = $model->getFilteredItems($filter);
                    foreach ($arr as $key => $value)
                    {
                        if ($value->component_id == 19) {
                            $items->light_color[] = $value;
                        }
                        if ($value->component_id == 20) {
                            $items->light_lamp_color[] = $value;
                        }
                    }
                    break;
                case 'n14':
                    $filter = "`component_id` = 24";
                    $items->n14_type = $model->getFilteredItems($filter);
                    break;
                case 'n16':
                    $filter = "`component_id` = 51";
                    $items->n15_size = $model->getFilteredItems($filter);
                    $items->n15_type[] = array(
                                                'id' => 10,
                                                'title' => 'Трехрядный',
                                            );
                    break;
                case 'n19':
                    $filter = '(`component_id` = 4 and count > 0)';
                    $wires = $model->getFilteredItems($filter);
                    foreach($wires as $wire){
                        $items->n19_type[] = $wire;
                    }

                    break;
                case 'n22':
                    $filter = '(`component_id` = 12 OR `component_id` = 21)';
                    $arr = $model->getFilteredItems($filter);
                    foreach ($arr as $key => $value)
                    {
                        if ($value->component_id == 12) {
                            $items->n22_square[] = $value;
                        }
                        if ($value->component_id == 21) {
                            $items->n22_diam[] = $value;
                        }
                    }
                    $items->n22_type[] = array(
                                                'id' => 5,
                                                'title' => 'Круглая электровытяжка',
                                            );
                    $items->n22_type[] = array(
                                                'id' => 6,
                                                'title' => 'Квадратная электровытяжка',
                                            );
                    /*$items->n22_type[] = array(
                                                'id' => 7,
                                                'title' => 'Круглая электровытяжка',
                                            );
                    $items->n22_type[] = array(
                                                'id' => 8,
                                                'title' => 'Квадратная электровытяжка',
                                            );*/
                    break;
                case 'n23':
                    $filter = "`component_id` = 22";
                    $items->n23_size = $model->getFilteredItems($filter);
                    break;
                case 'n29':
                    $items->n29_type[] = array(
                                        'id' => 12,
                                        'title' => 'По прямой',
                                    );
                    $items->n29_type[] = array(
                                        'id' => 13,
                                        'title' => 'По кривой',
                                    );
                    $items->n29_type[] = array(
                                        'id' => 15,
                                        'title' => 'По прямой (с нишей)',
                                    );
                    $items->n29_type[] = array(
                                        'id' => 16,
                                        'title' => 'По кривой (с нишей)',
                                    );
                    $items->n29_type[] = array(
                                        'id' => 17,
                                        'title' => 'Профиль LED',
                                    );
                    $items->n29_type[] = array(
                                        'id' => 18,
                                        'title' => 'Профиль КП',
                                    );
                    break;
            }
            
            die(json_encode($items));
        }
       catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    /*  функция для AJAX-запроса расчета потолка из calculationform
        функция вызывает одноименную функцию в файле /helpers/gm_ceiling.php
    */
    public function calculate()
    {
        try
        {
            $jinput = JFactory::getApplication()->input;
            $from_db = 0;
            $id = $jinput->get('id', '0', 'INT');
            $save = $jinput->get('save', '0', 'INT');
            $pdf = $jinput->get('pdf', '0', 'INT');
            $need_mount = $jinput->get('need_mount', '2', 'INT');
            $gm_mounters = $jinput->get('gm_mounters',"",'STRING');
            $del_flag = $jinput->get('del_flag', '0', 'INT');
            $result = Gm_ceilingHelpersGm_ceiling::calculate($from_db, $id, $save, $pdf, $del_flag, $need_mount,$gm_mounters);
            die($result);
        }
        catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function payComponents() {
        try
        {
            $user = JFactory::getUser();
            $user->getDealerInfo();

            $jinput = JFactory::getApplication()->input;

            $DATA = (object) [];
            $server_name = $_SERVER['SERVER_NAME'];
            $Components = $jinput->get('Сomponents', null, "ARRAY");
            $Count = $jinput->get('Сount', null, "ARRAY");

            $DATA->Date = $jinput->get('Date', null, 'STRING');
            $DATA->Comment = $jinput->get('Comment', null, "STRING");
            $DATA->Components = [];

            foreach ($Components as $key => $value)
                if (!empty($value))
                    $DATA->Components[$key] = (object) ["id" => $value, "count" => $Count[$key]];

            $ID_PROJECT = NULL;
            $USER_ID = $user->id;
            if ("Create project" || true) {
                $client_id = $user->associated_client;

                $project_data = [];
                $project_data['ready_time'] = $DATA->Date;
                $project_data['project_note'] = $DATA->Comment;
                $project_data['project_verdict'] = 1;
                $project_data['state'] = 1;

                $project_data['client_id'] = $client_id;

                $project_data['project_info'] = "";
                $project_data['project_status'] = 5;
                $project_data['project_calculation_daypart'] = 0;

                $project_data['project_calculation_date'] = "0000-00-00 00:00";
                $project_data['who_calculate'] = 0;
                $project_data['created'] = date("Y.m.d");
                $project_data['project_discount'] = 0;

                $project_data['gm_canvases_margin'] = $user->gm_canvases_margin;
                $project_data['gm_components_margin'] = $user->gm_components_margin;
                $project_data['gm_mounting_margin'] = $user->gm_mounting_margin;

                $project_data['dealer_canvases_margin'] = $user->dealer_canvases_margin;
                $project_data['dealer_components_margin'] = $user->dealer_components_margin;
                $project_data['dealer_mounting_margin'] = $user->dealer_mounting_margin;

                $project_model = Gm_ceilingHelpersGm_ceiling::getModel('projectform');
                $ID_PROJECT = $project_model->save($project_data);
            }

            $_POST["id"] = 0;
            $_POST["project_id"] = $ID_PROJECT;
            $_POST["dealer_id"] = $USER_ID;
            $_POST["components_title_stock"] = $Components;
            $_POST["components_value_stock"] = $Count;
            $_POST["n1"] = "NULL";
            $_POST["n2"] = "NULL";
            $_POST["n3"] = "NULL";
            $result = Gm_ceilingHelpersGm_ceiling::calculate(0, null, 1, 1, 0, 0);
            $this->setMessage("Проект успешно отправлен на производство! Цена за работу производителем изменится после одобрения! Итоговую цену можно увидеть в расходке.");
            $this->setRedirect(JRoute::_("http://$server_name/index.php?option=com_gm_ceiling&view=project&type=calculator&subtype=project&id=".$ID_PROJECT, false));
        }
       catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function notify_new()
    {
        try
        {
            $jinput = JFactory::getApplication()->input;
            $id = $jinput->get('id', '0', 'INT');
            $client_id = $jinput->get('client_id', '0', 'INT');
            $date_time = $jinput->get('date_time', '0000-00-00 00:00:00', 'STRING');
            $comment = $jinput->get('comment', '0', 'STRING');
            $manager_id = $jinput->get('manager_id', '0', 'INT');
            $type = $jinput->get('type', '0', 'INT');
            $model = Gm_ceilingHelpersGm_ceiling::getModel('clientform');
            $items = $model->getManager($manager_id);
            $manager_name = $items->manager_name;
            $email = $items->email;
            $data = (object)[
                "client_id" => $client_id,
                "date_time" => $date_time,
                "comment" => $comment,
                "manager_name" => $manager_name,
                "email" => $email
            ];
            /*$data->client_id = $client_id;
            $data->date_time = $date_time;
            $data->comment = $comment;
            $data->manager_name = $manager_name;
            $data->email = $email;*/
            Gm_ceilingHelpersGm_ceiling::notify($data, $type);
            $model_call = Gm_ceilingHelpersGm_ceiling::getModel('callback');
            $model_call->updateNotify($id);
            
            die(true);
        }
       catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    /*  функция AJAX-отметки договора и назначения новой суммы
        используется на странице, которую использует бухгалтер (/view/projects/tmpl/default_dealer)
    */
    public function check_project()
    {
        try
        {
            $jinput = JFactory::getApplication()->input;

            $id = $jinput->get('id', '0', 'INT');
            $type = $jinput->get('type', '0', 'INT');
            $check = $jinput->get('check', '0', 'INT');
            $new_value = $jinput->get('new_value', '0', 'FLOAT');
            $project_model = Gm_ceilingHelpersGm_ceiling::getModel('project');
            $result = $project_model->check($id, $type, $check, $new_value);

            die($result);
        }
       catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    /*  функция AJAX-отображения календаря монтажей
        смотреть дальше функцию draw_calendar в файле /helpers/gm_ceiling.php
    */
/*    public function update_calendar()
    {
        try
        {
            $jinput = JFactory::getApplication()->input;

            $project_id = $jinput->get('project_id', '0', 'INT');
            $project_mounter = $jinput->get('project_mounter', '0', 'INT');
            $month1 = $jinput->get('month', '0', 'INT');
            $year1 = $jinput->get('year', '0', 'INT');
            $current_from = $jinput->get('current_from', '00.00.0000 00:00', 'STRING');
            $current_to = $jinput->get('current_to', '00.00.0000', 'STRING');
    //        throw  new  Exception($current_from);
            if ($month1 == 0)
                $month1 = date('n');
            if ($year1 == 0) {
                $year1 = date('Y');
            }

            if ($month1 == 12) {
                $month2 = 1;
                $year2 = $year1 + 1;
            } else {
                $month2 = $month1 + 1;
                $year2 = $year1;
            }

            $result = Gm_ceilingHelpersGm_ceiling::draw_calendar($project_id, $project_mounter, $month1, $year1, $current_from, $current_to);
            $result .= Gm_ceilingHelpersGm_ceiling::draw_calendar($project_id, $project_mounter, $month2, $year2, $current_from, $current_to);

            die($result);
        }
       catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }*/

    public function UpdateCalendarTar() {
        try
        {
            $id = $_POST["id"];
            $month = $_POST["month"];
            $year = $_POST["year"];
            $month2 = $_POST["month2"];
            $year2 = $_POST["year2"];
            $flag1 = $_POST["flag"];
            $flag2 = $_POST["id_dealer"];
            $flag = [$flag1, $flag2];
            if($flag1 == 5) {
                $calendar1 = Gm_ceilingHelpersGm_ceiling::DrawCalendarTar($id, $month, $year, $flag);
                $calendar2 = Gm_ceilingHelpersGm_ceiling::DrawCalendarTar($id, $month2, $year2, $flag);
                $result = [
                    "calendar1" => $calendar1,
                    "calendar2" => $calendar2
                ];
                die(json_encode($result));
            }
            else{
                $result = Gm_ceilingHelpersGm_ceiling::DrawCalendarTar($id, $month, $year, $flag);
                die($result);
            }
        }
       catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    /*  функция AJAX-отображения календаря монтажей
        смотреть дальше функцию draw_calendar2 в файле /helpers/gm_ceiling.php
    */
/*    public function update_calendar2()
    {
        try
        {
            $jinput = JFactory::getApplication()->input;

            $calculated_by = $jinput->get('calculated_by', '0', 'INT');
            $month1 = $jinput->get('month', '0', 'INT');
            $year1 = $jinput->get('year', '0', 'INT');

            if ($month1 == 0)
                $month1 = date('n');
            if ($year1 == 0) {
                $year1 = date('Y');
            }

            if ($month1 == 12) {
                $month2 = 1;
                $year2 = $year1 + 1;
            } else {
                $month2 = $month1 + 1;
                $year2 = $year1;
            }

            $result = Gm_ceilingHelpersGm_ceiling::draw_calendar2($calculated_by, $month1, $year1);
            $result .= Gm_ceilingHelpersGm_ceiling::draw_calendar2($calculated_by, $month2, $year2);

            die($result);
        }
       catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }*/

    public function getClientByPhone()
    {
        try
        {
            $user = JFactory::getUser();

            $jinput = JFactory::getApplication()->input;
            $number = $jinput->get('phone', '', 'STRING');
            $number = mb_ereg_replace('[^\d]', '', $number);
            if (mb_substr($number, 0, 1) === '7') {
                $number = mb_substr($number, 1);
            }

            $model = Gm_ceilingHelpersGm_ceiling::getModel('client_phones');
            $result = $model->getItemsByPhoneNumber($number, $user->dealer_id);
            if (empty($result)) {
                die($result);
            }
            
            die(json_encode($result));
        }
       catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function getProjectsByPhone()
    {
        try
        {
            $user = JFactory::getUser();

            $jinput = JFactory::getApplication()->input;
            $number = $jinput->get('phone', '', 'STRING');
            $number = preg_replace('/[^\d]/', '', $number);
            if (mb_substr($number, 0, 1) === '7') {
                $number = mb_substr($number, 1);
            }

            $model = Gm_ceilingHelpersGm_ceiling::getModel('client_phones');
            $result = $model->getItemsByPhoneNumber($number, $user->dealer_id);
            if (empty($result)) {
                die($result);
            }

            $model_project = Gm_ceilingHelpersGm_ceiling::getModel('project');
            $result_project = $model_project->getProjectsByClientID($result->id);
            
            die(json_encode($result_project));
        }
       catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function nearestCallback()
    {
        try
        {
            $user = JFactory::getUser();
            $model = Gm_ceilingHelpersGm_ceiling::getModel('callback');
            $result = $model->getNearestCallback($user->id);
            if (empty($result)) {
                die(false);
            } else {
                die(json_encode($result));
            }
        }
       catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function changephones()
    {   
        try{
            $model = Gm_ceilingHelpersGm_ceiling::getModel('clients');
            $result = $model->getphones();
            foreach ($result as $item) {
                $phone = str_replace(" ", "", $item->client_contacts);
                $phone = str_replace("(", "", $phone);
                $phone = str_replace(")", "", $phone);
                $phone = str_replace("-", "", $phone);
                $item->client_contacts = $phone;
            }

            die(json_encode($result));
        }
       catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public static function send_estimate()
    {
        try
        {
            $jinput = JFactory::getApplication()->input;
            $email = $jinput->get('email', '', 'STRING');
            $client_id = $jinput->get('client_id', '', 'INT');
            $filenames = $jinput->get('filenames', '', 'STRING');
            $filenames = json_decode($filenames);
            $dop_file = $_FILES['dopfile'];
            $mailer = JFactory::getMailer();
            $config = JFactory::getConfig();
            $sender = array(
                $config->get('mailfrom'),
                $config->get('fromname')
            );
            $mailer->setSender($sender);
            $mailer->addRecipient($email);
            $model = Gm_ceilingHelpersGm_ceiling::getModel('clients_dop_contacts');
            $model->SetEmail($client_id, $email);
            $body = "Здравствуйте. К этому письму прикрепленны pdf-файлы с информацией по потолкам.\n\n Данное письмо сформировано автоматически, отвечать на него не нужно.";
            $mailer->setSubject('Сметы');
            $mailer->setBody($body);

            for ($i = 0; $i < count($filenames); $i++)
                $mailer->addAttachment($_SERVER['DOCUMENT_ROOT'].$filenames[$i]->name, $filenames[$i]->title.'.pdf');
            $mailer->addAttachment($dop_file['tmp_name'], $dop_file['name']);
            $send = $mailer->Send();

            die(json_encode($mailer));
        }
       catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public static function create_empty_project()
    {
        try
        {
            $jinput = JFactory::getApplication()->input;
            $client_id = $jinput->get('client_id', 1, 'INT');
            $api_phone_id = $jinput->get('api_id');
            $client_model = Gm_ceilingHelpersGm_ceiling::getModel('client');
            $client = $client_model->getClientById($client_id);
            $dealer_id = $client->dealer_id;
            if(empty($dealer_id)){
                $dealer_id = 1;
            }
            $info_model = Gm_ceilingHelpersGm_ceiling::getModel('dealer_info');
            $gm_canvases_margin = $info_model->getMargin('gm_canvases_margin', $dealer_id);
            $gm_components_margin = $info_model->getMargin('gm_components_margin', $dealer_id);
            $gm_mounting_margin = $info_model->getMargin('gm_mounting_margin', $dealer_id);
            $dealer_canvases_margin = $info_model->getMargin('dealer_canvases_margin', $dealer_id);
            $dealer_components_margin = $info_model->getMargin('dealer_components_margin', $dealer_id);
            $dealer_mounting_margin = $info_model->getMargin('dealer_mounting_margin', $dealer_id);
            $project_model = Gm_ceilingHelpersGm_ceiling::getModel('projectform');
            $project_data['state'] = 1;
            if (!empty($client_id)) {
                $project_data['client_id'] = $client_id;
            } elseif($client_id == 0) {
                $project_data['client_id'] = 0;
            }
            if(!empty($api_phone_id)){
                $project_data['api_phone_id'] = $api_phone_id;
            }
            $project_data['project_info'] = "";
            $project_data['project_status'] = 0;
            $project_data['project_calculation_date'] = "0000-00-00 00:00";
            $project_data['project_note'] = "";
            $project_data['who_calculate'] = 0;
            $project_data['created'] = date("Y-m-d");
            $project_data['project_discount'] = 0;
            $project_data['gm_canvases_margin'] = $gm_canvases_margin;
            $project_data['gm_components_margin'] = $gm_components_margin;
            $project_data['gm_mounting_margin'] = $gm_mounting_margin;

            $project_data['dealer_canvases_margin'] = $dealer_canvases_margin;
            $project_data['dealer_components_margin'] = $dealer_components_margin;
            $project_data['dealer_mounting_margin'] = $dealer_mounting_margin;
            $result = $project_model->save($project_data);

            if ($client_id != 1) {
                $pr_model = Gm_ceilingHelpersGm_ceiling::getModel('project');
                $projects = $pr_model->getProjectsByClientID($client_id);
                if (count($projects) > 1) {
                    $repeat_model = Gm_ceilingHelpersGm_ceiling::getModel('repeatrequest');
                    $repeat_model->save($result, NULL);
                }
            }
            die(json_encode($result));
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public static function create_project_and_calc()
    {
        try
        {
            $jinput = JFactory::getApplication()->input;
            $client_id = $jinput->get('client_id', 1, 'INT');
            $project_model = Gm_ceilingHelpersGm_ceiling::getModel('projectform');
            $info_model = Gm_ceilingHelpersGm_ceiling::getModel('dealer_info');
            $client_model = Gm_ceilingHelpersGm_ceiling::getModel('client');
            $calc_model = Gm_ceilingHelpersGm_ceiling::getModel('calculation');

            $client = $client_model->getClientById($client_id);
            $dealer_id = $client->dealer_id;

            $project_data['client_id'] = $client_id;
            $project_data['project_status'] = 0;
            $project_data['gm_canvases_margin'] = $info_model->getMargin('gm_canvases_margin', $dealer_id);
            $project_data['gm_components_margin'] = $info_model->getMargin('gm_components_margin', $dealer_id);
            $project_data['gm_mounting_margin'] = $info_model->getMargin('gm_mounting_margin', $dealer_id);
            $project_data['dealer_canvases_margin'] = $info_model->getMargin('dealer_canvases_margin', $dealer_id);
            $project_data['dealer_components_margin'] = $info_model->getMargin('dealer_components_margin', $dealer_id);
            $project_data['dealer_mounting_margin'] = $info_model->getMargin('dealer_mounting_margin', $dealer_id);

            $project_id = $project_model->save($project_data);
            $calc_id = $calc_model->create_calculation($project_id);

            die(json_encode($calc_id));
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function update_margin()
    {
        try
        {
        // Checking if the user can remove object
            $user = JFactory::getUser();

            $jinput = JFactory::getApplication()->input;

            $DealerInfo = $user->getDealerInfo();
            $margin = 0;

            $new_margin = $jinput->get("new_margin", 0, "INT");
            $type = $jinput->get("type", 0, "INT");

            $locate = "";
            switch ($type) {
                case 1:
                    $locate = 'http://'.$_SERVER['SERVER_NAME'].'/index.php?option=com_gm_ceiling&view=canvases';
                    $margin = $DealerInfo->dealer_canvases_margin;
                    break;
                case 2:
                    $locate = 'http://'.$_SERVER['SERVER_NAME'].'/index.php?option=com_gm_ceiling&view=components';
                    $margin = $DealerInfo->dealer_components_margin;
                    break;
                case 3:
                    $locate = 'http://'.$_SERVER['SERVER_NAME'].'/index.php?option=com_gm_ceiling&view=mount';
                    $margin = $DealerInfo->dealer_mounting_margin;
                    break;
            }

            $result = null;
            if ($new_margin != $margin && $new_margin >= 0 && $new_margin < 100 && $locate != "") {
                $db = JFactory::getDbo();
                $query = $db->getQuery(true);
                switch ($type) {
                    case 1:
                        $fields = array('dealer_canvases_margin' => $new_margin);
                        break;
                    case 2:
                        $fields = array('dealer_components_margin' => $new_margin);
                        break;
                    case 3:
                        $fields = array('dealer_mounting_margin' => $new_margin);
                        break;
                }
                $result = $user->setDealerInfo($fields);
            }

            if ($result != null) die(true);
            else {
                echo json_encode(array("answer_error" => "Ошибка ввода маржинальности!"));
            }

            die(true);
        }
       catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public static function save_data_to_session()
    {
        try{
            $jinput = JFactory::getApplication()->input;
            $proj_id = $jinput->get('proj_id', null, 'INT');
            $data = $jinput->get('data', '', 'STRING');
            $_SESSION["project_card_$proj_id"] = $data;
            //die(json_encode($_SESSION));
            die(json_encode(true));
        }
       catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public static function get_paymanet_status()
    {   
        try{
            $jinput = JFactory::getApplication()->input;
            $data = array(
                'orderId' => urlencode($jinput->get("orderId", '', "STRING")),
                'password' => PASSWORD,
                'userName' => USERNAME,
            );

            $curl = curl_init(); // Инициализируем запрос
            curl_setopt_array($curl, array(
                CURLOPT_URL => GATEWAY_URL . 'getOrderStatus.do', // Полный адрес метода
                CURLOPT_RETURNTRANSFER => true, // Возвращать ответ
                CURLOPT_POST => true, // Метод POST
                CURLOPT_POSTFIELDS => http_build_query($data) // Данные в запросе
            ));
            $response = curl_exec($curl); // Выполненяем запрос

            //$response = json_decode($response, true); // Декодируем из JSON в массив

            //throw new Exception($response);
            curl_close($curl); // Закрываем соединение
            die($response);
        }
       catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public static function get_paymanet_form()
    {
        try{
            $jinput = JFactory::getApplication()->input;
            $project_id = $jinput->get("id", '', "INT");
            $data = array(
                'amount' => urlencode($jinput->get("amount", '', "STRING")),
                'currency' => urlencode(643),
                'language' => urlencode('ru'),
                'orderNumber' => urlencode($jinput->get("orderNumber", '', "STRING")),
                'password' => PASSWORD,
                'returnUrl' => RETURN_URL . $project_id,
                'userName' => USERNAME,
                'descriprion' => urlencode($jinput->get("description", '', "STRING"))
            );

            $curl = curl_init(); // Инициализируем запрос
            curl_setopt_array($curl, array(
                CURLOPT_URL => GATEWAY_URL . 'register.do', // Полный адрес метода
                CURLOPT_RETURNTRANSFER => true, // Возвращать ответ
                CURLOPT_POST => true, // Метод POST
                CURLOPT_POSTFIELDS => http_build_query($data) // Данные в запросе
            ));
            $response = curl_exec($curl); // Выполненяем запрос

            //$response = json_decode($response, true); // Декодируем из JSON в массив
            curl_close($curl); // Закрываем соединение
            $order_id = json_decode($response, true)['orderId'];
            $project_model = Gm_ceilingHelpersGm_ceiling::getModel('project');
            $project_model->update_sb_order_id($project_id, $order_id);
            $project_model->change_status($project_id,13);
            die($response);
        }
       catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public static function get_yandex_metric()
    {
        try{
            $jinput = JFactory::getApplication()->input;
            $date = $jinput->get('date1','','STRING');

            $curl = curl_init(); // Инициализируем запрос
            curl_setopt_array($curl, array(
                CURLOPT_URL => "https://api-metrika.yandex.ru/stat/v1/data?".
                "metrics=ym:s:newUsers&dimensions=ym:s:startURL&date1=$date&date2=$date&".
                "id=46061262&oauth_token=AQAAAAAgrPCpAASna6q0jcvjIk8JiiqlIiWtj2A", // Полный адрес метода
                CURLOPT_RETURNTRANSFER => true, // Возвращать ответ
            ));

            $response = curl_exec($curl); // Выполненяем запрос

            curl_close($curl); // Закрываем соединение
            die(json_encode($response));
        }
       catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public static function send_sketch()
    {
        try{
            $jinput = JFactory::getApplication()->input;
            $from_db = $jinput->get('from_db', '', 'INT');
            if($from_db == 1){
                $id = $jinput->get('id', '', 'INT');
                $calc_model = Gm_ceilingHelpersGm_ceiling::getModel('calculation');
                $calculation = $calc_model->getData($id);
                $str = $calculation->original_sketch;
            }
            if($from_db==0){
                $filename = $jinput->get('filename', '', 'STRING');
                $str = file_get_contents($_SERVER['DOCUMENT_ROOT'] . "/tmp/" . $filename . ".txt");
            }
            //$_SESSION['walls'] = $walls;
            //$_SESSION['diags'] = $diags;
            //$_SESSION['pt'] = $pt;
            //$_SESSION['code'] = $code;
            //$_SESSION['alfavit'] = $alphavite;
            $result = $str;
            die($result);
        }
       catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }

    }

    public function addClient()
    {
        try{
            $jinput = JFactory::getApplication()->input;
            $server_name = $_SERVER['SERVER_NAME'];
            $id = $jinput->get('id', null, 'INT');
            $fio = $jinput->get('fio', null, 'STRING');
            $phone = $jinput->get('phone', null, 'STRING');
            $adress = $jinput->get('adress', null, 'STRING');
            $project_calc_date = $jinput->get('project_calc_date', '00.00.0000', 'DATE');
            $new_project_calculation_daypart = $jinput->get('new_project_calculation_daypart', null, 'STRING');

            $project_calculation_date = $project_calc_date." ".$new_project_calculation_daypart;
            $model = Gm_ceilingHelpersGm_ceiling::getModel('client');
            $model->updateClientNew($id, $fio, $phone,$adress, $project_calculation_date );
            $this->setRedirect(JRoute::_("http://$server_name/components/com_gm_ceiling/views/saverclient/default_1.php?complite=1&id=".$id, false));
            return 1;
        }
       catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function getAnaliticByPeriod()
    {
        try{
            $jinput = JFactory::getApplication()->input;
            $date1 = $jinput->get('date1','','STRING');
            $date2 = $jinput->get('date2','','STRING');
            $dealer_id = JFactory::getUser()->dealer_id;
            $analitic_model = Gm_ceilingHelpersGm_ceiling::getModel('analytic_new');
            $data = $analitic_model->getData($dealer_id,$date1,$date2);
            die(json_encode($data));
        }
       catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }
    public function getDetailedAnaliticByPeriod()
    {
        try{
            $jinput = JFactory::getApplication()->input;
            $date1 = $jinput->get('date1','','STRING');
            $date2 = $jinput->get('date2','','STRING');
            $dealer_id = JFactory::getUser()->dealer_id;
            $analitic_model = Gm_ceilingHelpersGm_ceiling::getModel('analytic_detailed_new');
            $data = $analitic_model->getData($dealer_id,$date1,$date2);            
            die(json_encode($data));
        }
       catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }
    public function addemailtoclient()
    {
        try{
            $jinput = JFactory::getApplication()->input;
            $email = $jinput->get('email','','STRING');
            $client_id = $jinput->get('client_id','','INT');
            $dop_contacts = Gm_ceilingHelpersGm_ceiling::getModel('Clients_dop_contacts');
            $result = $dop_contacts->save($client_id,1,$email);
            die($result);
        }
       catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }
    public function getAnaliticProjects(){
        try{
            $jinput = JFactory::getApplication()->input;
            $ids = $jinput->get('ids','','STRING');
            $ids = explode(';',$ids);
            if(empty(end($ids))){
                array_pop($ids);
            }
            $ids = "(".implode(',', $ids).")";
            $date1 = $jinput->get('date1','','STRING');
            $date2 = $jinput->get('date2','','STRING');
            $project_model = Gm_ceilingHelpersGm_ceiling::getModel('projects');
            $projects = $project_model->getDataByIds($ids);
            die(json_encode($projects));       
            

        }
       catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }
    public function saveRecoil(){
        try{
            $jinput = JFactory::getApplication()->input;
            $name =  $jinput->get('fio','','STRING');
            $phone =  $jinput->get('phone','','STRING');
            $recoil_model = Gm_ceilingHelpersGm_ceiling::getModel('Recoil');
            $result = $recoil_model->save($name,$phone);
            die(json_encode($result));
        }
       catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }
    public function update_descriptions_api_phones(){
        try{
            $jinput = JFactory::getApplication()->input;
            $descriptions =  $jinput->get('descriptions',array(),'ARRAY');
            
            $api_model = Gm_ceilingHelpersGm_ceiling::getModel('api_phones');
            foreach ($descriptions as $key => $value)
            {
                if (!empty($value))
                {
                    $api_model->update_description($key, $value);
                }
            }
            die(true);
        }
       catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public static function getAuthToken(){
        try{
            $data = array(
                "grant_type"=>"client_credentials",
                "client_id"=>"05e1b3d1-bad1-4966-919d-dc76f71e4c5a",
                "client_secret"=>100
            );
            $curl = curl_init(); // Инициализируем запрос
            curl_setopt_array($curl, array(
                CURLOPT_URL => "https://api.yandex.mightycall.ru/api/v2/auth/token", // Полный адрес метода
                CURLOPT_RETURNTRANSFER => true, // Возвращать ответ
                CURLOPT_POSTFIELDS => http_build_query($data)
            ));

            $response = curl_exec($curl); // Выполненяем запрос

            curl_close($curl); // Закрываем соединение
            return $response;
        }
       catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }

    }
    public function missedCalls($date=null,$filter=null,$flag=null){
        try{            
            $jinput = JFactory::getApplication()->input;
            //$descriptions =  $jinput->get('descriptions',array(),'ARRAY');
            if(empty($date) && empty($filter)){
                $date = $jinput->get('date','','STRING');
                $filter = $jinput->get('filter','','STRING');
            }
            $date2 =  date('Y-m-d', strtotime($date . ' +1 day'));
            $date =  date('Y-m-d', strtotime($date . ' -7 day'));
            $token = json_decode(self::getAuthToken())->access_token;
            $curl = curl_init(); // Инициализируем запрос
            curl_setopt_array($curl, array(
                CURLOPT_URL => "https://api.yandex.mightycall.ru/api/v2/calls?callFilter=$filter&startUtc=$date&endUtc=$date2&pageSize=1000", // Полный адрес метода
                CURLOPT_RETURNTRANSFER => true, // Возвращать ответ
                CURLOPT_HTTPHEADER => array(
                    'Authorization : bearer '.$token
                    )
            ));

            $response = curl_exec($curl); // Выполненяем запрос
            
            curl_close($curl); // Закрываем соединение
            
            if(!empty($flag)){
                $response = json_decode($response);                
                return($response->data->calls);
            }
            else{
                die($response);
            }
        }
       catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function getAvailableSum(){
        try{
            $jinput = JFactory::getApplication()->input;
            $id = $jinput->get('id','','INT');
            $model = $this->getModel('recoil_map_project', 'Gm_ceilingModel');
            $result = $model->getSum($id);
            die($result);

        }
        catch (Exception $e) {
           Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());

        }
    }

    public function saveGivenOutSum(){
        try{
            $jinput = JFactory::getApplication()->input;
            $sum = $jinput->get('sum','','STRING');
            $id = $jinput->get('id','','INT');
            $model = $this->getModel('recoil_map_project', 'Gm_ceilingModel');
            die(json_encode($model->save($id,null,$sum)));
        }
        catch (Exception $e) {
           Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());

        }
    }

    public function UpdateCutData()
    {
        try {
            $jinput = JFactory::getApplication()->input;
            $data = $jinput->get('image', '0', 'string');
            $data_og = $jinput->get('og_image', '0', 'string');
            $arr_points = $jinput->get('koordinats_poloten', '', 'array');
            $calc_id = $jinput->get('calc_id', '', 'INT');
            $width = $jinput->get('width', '', 'INT');
            $cuts = $jinput->get('cuts', '', 'string');
            $width = (string)$width/100;
            $p_usadki = $jinput->get('p_usadki', '', 'string');
            if(empty(strpos($width,'.'))){
                $width.='.0';
            }

            for ($i = 0; $i < count($arr_points); $i++)
            {
                $points_polonta = '';
                for ($j = 0; $j < count($arr_points[$i]); $j++)
                {
                    $points_polonta .= implode($arr_points[$i][$j]).', ';
                }
                $points_polonta = substr($points_polonta, 0, -2);

                $str .= "Полотно" . ($i + 1) . ": " . $points_polonta . "; ";
            }
            $str.='||'.$p_usadki;

            $calc_model = Gm_ceilingHelpersGm_ceiling::getModel('calculations');
            $result = $calc_model->update_cut_data($calc_id, $str, $width);

            $filename = md5('cut_sketch' . $calc_id);
            file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/cut_images/' . $filename . ".svg", base64_decode($data));
            $filename = md5('calculation_sketch' . $calc_id);
            file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/calculation_images/' . $filename . ".svg", base64_decode($data_og));
            if (!empty($cuts))
            {
                $canvases_model = Gm_ceilingHelpersGm_ceiling::getModel('canvases', 'Gm_ceilingModel');
                $canvases_model->saveCuts($calc_id,$cuts);
            }
            die(true);

        } catch (Exception $e) {
           Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());

        }
    }

    public  function saveEncashment(){
        try{
            $jinput = JFactory::getApplication()->input;
            $sum = $jinput->get('sum','','STRING');
            $manager_id = $jinput->get('id','','INT');
            $encash_model = Gm_ceilingHelpersGm_ceiling::getModel('encashment');
            $result  = $encash_model->save($sum,$manager_id);
            die(json_encode($result));
        }
        catch (Exception $e) {
           Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());

        }
    }
    public function getCashboxByMonth(){
        try{
            $jinput = JFactory::getApplication()->input;
            $month = $jinput->get('month','','STRING');
            $year = $jinput->get('year','','INT');
            $date1 = date("$year-$month-01");
            $date2 = date("$year-$month-t");
            $cashbox_model = Gm_ceilingHelpersGm_ceiling::getModel('Cashbox');
            $result  = $cashbox_model->getData($date1,$date2);
            die(json_encode($result));
        }
        catch (Exception $e) {
           Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());

        }
    }

    public function saveCommercialOffer(){
        try
        {
            $user = JFactory::getUser();

            $jinput = JFactory::getApplication()->input;
            $text = $jinput->get('text', null, 'STRING');
            $subj = $jinput->get('subj', null, 'STRING');
            $name = $jinput->get('name', null, 'STRING');
            $manufac_id = $user->dealer_id;

            $comm_model = Gm_ceilingHelpersGm_ceiling::getModel('commercial_offer');
            $result = $comm_model->addCommOffer($subj, $text, $name, $manufac_id);

            die($result);
        }
        catch (Exception $e) {
           Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());

        }
    }


   public function sendCommercialOffer($user_id = null, $email = null, $dealer_type = null, $type = null){
        try
        {
            $user = JFactory::getUser();
            $groups = $user->get('groups');
            $server_name = $_SERVER['SERVER_NAME'];
            if (in_array("16", $groups))
            {
                if (is_null($user_id) || is_null($email) || is_null($dealer_type) || is_null($type))
                {
                    $jinput = JFactory::getApplication()->input;
                    $user_id = $jinput->get('user_id', null, 'INT');
                    $email = $jinput->get('email', null, 'STRING');
                    $dealer_type = $jinput->get('dealer_type', null, 'INT');
                    $type = $jinput->get('type', null, 'INT');
                    $die_bool = true;
                }
                else
                {
                    $die_bool = false;
                }

                if (empty($email))
                {
                    throw new Exception('empty email');
                }
                $code = md5($user_id.'commercial_offer');
                $code_instruction = md5($user_id.'dealer_instruction');
                $code_quick = md5($user_id.'quick');
                $site = "http://$server_name/index.php?option=com_gm_ceiling&task=big_smeta.commercialOffer&code=$code";
                $site2 = "http://$server_name/index.php?option=com_gm_ceiling&task=big_smeta.dealerInstruction&short=1&code=$code_instruction";
                $site3 = "http://$server_name/index.php?option=com_gm_ceiling&task=big_smeta.dealerInstruction&short=2&code=$code_quick";
                $site4 = "http://$server_name/index.php?option=com_gm_ceiling&task=big_smeta.commercialOffer&code=$code&type=1";
                $site5 = "http://$server_name/index.php?option=com_gm_ceiling&task=big_smeta.commercialOffer&code=$code&type=1";
                $site6 = "http://$server_name/index.php?option=com_gm_ceiling&task=big_smeta.commercialOffer&code=$code&type=0";
                $site_dev = "http://$server_name/index.php?option=com_gm_ceiling&task=big_smeta.commercialOffer&code=$code";
                $site_errors_mount = "http://$server_name/index.php?option=com_gm_ceiling&task=big_smeta.commercialOffer&code=$code&type=2";
                $site_moskow_build = "http://$server_name/index.php?option=com_gm_ceiling&task=big_smeta.commercialOffer&code=$code&type=3";
                // письмо
                $mailer = JFactory::getMailer();
                $config = JFactory::getConfig();
                $sender = array(
                    $config->get('mailfrom'),
                    $config->get('fromname')
                );
                $mailer->setSender($sender);
                $mailer->addRecipient($email);
                $body = '<html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8"><link rel="stylesheet" type="text/css" href="CSS/style_index.css"/></head>';
                $body .= '<body style="margin: 10px;">';
                $body .= '<table cols=2  cellpadding="20px"style="width: 100%; border: 0px solid; color: #414099; font-family: Cuprum, Calibri; font-size: 16px;">';
                $body .= '<tr><td style="vertical-align:middle;"><a href="http://'. $server_name.'/">';
                $body .= '<img src="http://'.$server_name.'/images/gm-logo.png" alt="Логотип" style="padding-top: 15px; height: 70px; width: auto;">';
                $body .= '</a></td><td><div style="vertical-align:middle; padding-right: 50px; padding-top: 7px; text-align: right; line-height: 0.5;">';
                if ($dealer_type == 3 || $dealer_type == 5)
                {
                    $body .= '<p>Тел.: +7(473)212-23-59</p>';
                }
                elseif ($dealer_type == 1)
                {
                    $body .= '<p>Тел.: +7(473)212-34-01</p>';
                }
                elseif ($dealer_type == 6)
                {
                    $body .= '<p>Тел.: +7(473)212-34-40</p>';
                }
                elseif ($dealer_type == 7)
                {
                    $body .= '<p>Тел.: +7(930)417-10-58</p>';
                }
                $body .= '<p>Почта: gm-partner@mail.ru</p>';
                $body .= '<p>Адрес: г. Воронеж, Проспект Труда, д. 48, литер. Е-Е2</p>';
                $body .= '</div></td></tr></table>';
                if ($dealer_type==3) {
                        $body .= "<div style=\"width: 100%\">
                            <a href=\"$site4\"><img src=\"http://".$server_name."/images/KP_OTD.jpg\"></a><br>";
                    }

                if ($dealer_type==1) {
                    if ($type == 2) {
                        $body .= "Вас приветствует компания ООО \"Гильдия Мастеров\".<br>
                        Во вложении важная информация о дефекте \"полосы на полотне\".<br>
                        <br><a href=\"$site_errors_mount\">Посмотреть</a><br>";
                    }
                    else
                    {
                        $body .= "<div style=\"width: 100%\">В продолжение нашего телефонного разговора отправляю ссылку:
                        <ul>
                            <li> на <a href=\"$site\">коммерческое предложение</a></li>
                            <br>
                            <li>краткий обзор программы</li>
                            <br>
                            <a href=\"$site2\"><img src=\"http://".$server_name."/images/short_instruction2.png\"></a>
                            <br>
                            <br>
                            <li>инструкцию по быстрому заказу</li>
                            <br>
                            <a href=\"$site3\"><img src=\"http://".$server_name."/images/video.jpg\"></a>
                        </ul>";
                    }
                }

                if ($dealer_type==6) {
                    $body .= '<div><div style="color:rgb(0,0,0);font-family:arial,helvetica,sans-serif;font-size:15px;font-style:normal;font-variant-ligatures:normal;font-variant-caps:normal;font-weight:400;text-transform:none;white-space:normal;text-align:center"><strong><span style="background-color:#4866e7;font-size:36px"><font color="#ffffff">Привлечь</font></span><span style="background-color:#4866e7;color:#ffffff;font-size:36px">&nbsp;новых дилеров - легко!</span></strong><br><br><span style="color:#173bd3">Устанавливай систему<span>&nbsp;</span><strong>IT-Ceiling</strong><span>&nbsp;</span>и получай новых дилеров.</span></div><div style="color:rgb(0,0,0);font-family:arial,helvetica,sans-serif;font-size:15px;font-style:normal;font-variant-ligatures:normal;font-variant-caps:normal;font-weight:400;text-transform:none;white-space:normal;margin-left:30px;text-align:center"><br><br>Вы наверняка пробовали привлекать новых дилеров через акции или демпинг, обзвон или емайл-рассылку?<br><br>Но не получили желаемого результата!(<br><br><strong>А что Вы дали своим дилерам? Как анализировали всю проделанную гигантскую работу?<br>&nbsp;<br><span style="color:#173bd3">Система IT-Ceiling дает Вашим дилерам совершенно простой способ заказа Натяжных потолков, при этом показывая им точную себестоимость всех расходных материалов и полотен с учетом обрезков на замере и дает возможность забрать любой заказ и полноценно вести клиентскую базу, при чем все это она дает через простое в использовании мобильное приложение.<br><br><span style="background-color:#173bd3;color:#ffffff">Как это решит Ваши проблемы?&nbsp;<br><br><span style="background-color:#ffffff;color:#000000">Заказ дилера сделанный через приложение попадает в вашу CRM - систему IT-Ceiling и Вы точно знаете какой дилер сколько заказывает, как часто и когда он перестал заказывать. Каждого дилера можно проследить от куда он пришел и по какой акции, только так можно определить успешный канал привлечения и порядок работ с дилером.</span><br><br>Так же IT-Ceiling сама рекламирует себя для дилеров и у нас уже есть те кто работает с приложением в разных городах, но заказывает не у Вас!!!</span></span></strong></div><div style="color:rgb(0,0,0);font-family:arial,helvetica,sans-serif;font-size:15px;font-style:normal;font-variant-ligatures:normal;font-variant-caps:normal;font-weight:400;text-transform:none;white-space:normal;margin-left:30px;text-align:center">&nbsp;</div><div style="color:rgb(0,0,0);font-family:arial,helvetica,sans-serif;font-size:15px;font-style:normal;font-variant-ligatures:normal;font-variant-caps:normal;font-weight:400;text-transform:none;white-space:normal;margin-left:30px;text-align:center"><span style="color:#0000ff"><span style="font-size:18px">Хотите узнать подробнее?</span></span><br><a class="btn-success" href="'.$site5.'">Да</a> <a class="btn-danger" href="'.$site6.'">Нет</a></div></div><style>
                    .btn {
                      display: inline-block;
                      font-weight: 400;
                      text-align: center;
                      white-space: nowrap;
                      vertical-align: middle;
                      -webkit-user-select: none;
                      -moz-user-select: none;
                      -ms-user-select: none;
                      user-select: none;
                      border: 1px solid transparent;
                      padding: 0.375rem 0.75rem;
                      font-size: 1rem;
                      line-height: 1.5;
                      border-radius: 0.25rem;
                      transition: color 0.15s ease-in-out, background-color 0.15s ease-in-out, border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
                    }
                    .btn:hover, .btn:focus {
                      text-decoration: none;
                    }

                    .btn:focus, .btn.focus {
                      outline: 0;
                      box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
                    }

                    .btn.disabled, .btn:disabled {
                      opacity: 0.65;
                    }

                    .btn:not(:disabled):not(.disabled) {
                      cursor: pointer;
                    }

                    .btn:not(:disabled):not(.disabled):active, .btn:not(:disabled):not(.disabled).active {
                      background-image: none;
                    }
                    .btn-success {
                      color: #fff;
                      background-color: #28a745;
                      border-color: #28a745;
                    }

                    .btn-success:hover {
                      color: #fff;
                      background-color: #218838;
                      border-color: #1e7e34;
                    }

                    .btn-success:focus, .btn-success.focus {
                      box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.5);
                    }

                    .btn-success.disabled, .btn-success:disabled {
                      color: #fff;
                      background-color: #28a745;
                      border-color: #28a745;
                    }

                    .btn-success:not(:disabled):not(.disabled):active, .btn-success:not(:disabled):not(.disabled).active,
                    .show > .btn-success.dropdown-toggle {
                      color: #fff;
                      background-color: #1e7e34;
                      border-color: #1c7430;
                    }

                    .btn-success:not(:disabled):not(.disabled):active:focus, .btn-success:not(:disabled):not(.disabled).active:focus,
                    .show > .btn-success.dropdown-toggle:focus {
                      box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.5);
                    }
                    .btn-danger {
                      color: #fff;
                      background-color: #dc3545;
                      border-color: #dc3545;
                    }

                    .btn-danger:hover {
                      color: #fff;
                      background-color: #c82333;
                      border-color: #bd2130;
                    }

                    .btn-danger:focus, .btn-danger.focus {
                      box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.5);
                    }

                    .btn-danger.disabled, .btn-danger:disabled {
                      color: #fff;
                      background-color: #dc3545;
                      border-color: #dc3545;
                    }

                    .btn-danger:not(:disabled):not(.disabled):active, .btn-danger:not(:disabled):not(.disabled).active,
                    .show > .btn-danger.dropdown-toggle {
                      color: #fff;
                      background-color: #bd2130;
                      border-color: #b21f2d;
                    }

                    .btn-danger:not(:disabled):not(.disabled):active:focus, .btn-danger:not(:disabled):not(.disabled).active:focus,
                    .show > .btn-danger.dropdown-toggle:focus {
                      box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.5);
                    }</style>';
                }

                if ($dealer_type == 7) {
                    if ($type == 2) {
                        $body .= "<div style=\"width: 100%\">Вас приветствует компания ООО \"Гильдия Мастеров\".<br>
                        <a href=\"$site_moskow_build\"><img style=\"width: 20%\" src=\"http://".$server_name."/images/Moscow_zastr.jpg\"></a><br>
                        <a href=\"$site_moskow_build\">Коммерческое предложение</a><br>";
                    }
                    else {
                        $body .= "<div style=\"width: 100%\">
                        <center><a href=\"$site_dev\"><img style=\"width: 20%\" src=\"http://".$server_name."/images/KP_DEV.jpg\"></a></center><br>
                        <a href=\"$site_dev\">Коммерческое предложение</a><br>";
                    }
                    $body .= "По всем вопросам писать на почту gm-vrn84@bk.ru или mgildiya@bk.ru или звонить по телефону.</div></body>";
                }
                else
                {
                    $body .= "По всем вопросам писать на почту gm-partner@mail.ru или mgildiya@bk.ru или звонить по телефону.</div></body>";
                }
                if($dealer_type == 3){
                    $mailer->setSubject('+15 000 руб/в мес. каждому Отделочнику ');
                }
                elseif($dealer_type == 7)
                {
                    if ($type == 2) {
                        $mailer->setSubject('Для отдела снабжения');
                    }
                    else {
                        $mailer->setSubject('Для отдела снабжения');
                    }
                }
                elseif($dealer_type == 1 && $type == 2)
                {
                    $mailer->setSubject('Полосы на полотне');
                }
                else{
                    $mailer->setSubject('Коммерческое предложение');
                }
                
                $mailer->isHtml(true);
                $mailer->Encoding = 'base64';
                $mailer->setBody($body);
                $send = $mailer->Send();
                
                $users_model = Gm_ceilingHelpersGm_ceiling::getModel('users');
                $result = $users_model->addDealerInstructionCode($user_id, $code_instruction, $user->id);
                $result = $users_model->addDealerInstructionCode($user_id, $code_quick, $user->id);
                $result = $users_model->addCommercialOfferCode($user_id, $code, $user->id);

                $dop_contacts_model = Gm_ceilingHelpersGm_ceiling::getModel('clients_dop_contacts');
                $client_id = JFactory::getUser($user_id)->associated_client;
                $email_id = $dop_contacts_model->save($client_id, 1, $email);

                if ($die_bool)
                {
                    die(json_encode($result));
                }
                else
                {
                    return true;
                }
            }
            else
            {
                throw new Exception('Not GmManager', 403);
            }
        }
        catch (Exception $e) {
           Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function sendCommercialQuickWay($user_id = null, $email = null){
        try
        {
            $user = JFactory::getUser();
            if (is_null($user_id) || is_null($email))
            {
                $jinput = JFactory::getApplication()->input;
                $user_id = $jinput->get('user_id', null, 'INT');
                $email = $jinput->get('email', null, 'STRING');
                $die_bool = true;
            }
            else
            {
                $die_bool = false;
            }
            
            if (empty($email))
            {
                die(json_encode(false));
            }
            $code = md5($user_id.'commercial_offer');
            $code_instruction = md5($user_id.'dealer_instruction');
            $server_name = $_SERVER['SERVER_NAME'];
            $site = "http://$server_name/index.php?option=com_gm_ceiling&task=big_smeta.commercialOffer&code=$code";
            $site2 = "http://$server_name/index.php?option=com_gm_ceiling&task=big_smeta.dealerInstruction&short=2&code=$code_instruction";
            $site3 = "http://$server_name/index.php?option=com_gm_ceiling&task=big_smeta.dealerRequest&id=$user_id";
            // письмо
            $mailer = JFactory::getMailer();
            $config = JFactory::getConfig();
            $sender = array(
                $config->get('mailfrom'),
                $config->get('fromname')
            );
            $mailer->setSender($sender);
            $mailer->addRecipient($email);
            $body = '<html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8"><link rel="stylesheet" type="text/css" href="CSS/style_index.css"/></head>';
            $body .= '<body style="margin: 10px;">';
            $body .= '<table cols=2  cellpadding="20px"style="width: 100%; border: 0px solid; color: #414099; font-family: Cuprum, Calibri; font-size: 16px;">';
            $body .= '<tr><td style="vertical-align:middle;"><a href="http://'.$server_name.'/">';
            $body .= '<img src="http://'.$server_name.'/images/gm-logo.png" alt="Логотип" style="padding-top: 15px; height: 70px; width: auto;">';
            $body .= '</a></td><td><div style="vertical-align:middle; padding-right: 50px; padding-top: 7px; text-align: right; line-height: 0.5;">';
            $body .= '<p style="margin: 10px;">Тел.: +7(473)212-34-01</p>';
            $body .= '<p style="margin: 10px;">Почта: gm-partner@mail.ru</p>';
            $body .= '<p style="margin: 10px;">Адрес: г. Воронеж, Проспект Труда, д. 48, литер. Е-Е2</p>';
            $body .= '</div></td></tr></table>';
            $body .= "<div style=\"width: 100%\"> Быстрый способ заказа натяжных потолков по 60 р/м<sup>2</sup><br> 
                        <a href=\"$site2\"><img src=\"http://".$server_name."/images/video.jpg\"></a><br>
                        При заказе через приложение мат MSD Classic до 3.20м по 60р/м<sup>2</sup>.<br>";
            $body .= "<a href=\"$site3&type=info\"><img src=\"http://".$server_name."/images/btn_moreinfo.jpg\"></a>
            <a href=\"$site3&type=access\"><img src=\"http://".$server_name."/images/btn_getaccess.jpg\"></a></div></body>";
            $mailer->setSubject('Быстрый способ заказа натяжных потолков по 60 р/м2');
            $mailer->isHtml(true);
            $mailer->Encoding = 'base64';
            $mailer->setBody($body);
            $send = $mailer->Send();
            
            $users_model = Gm_ceilingHelpersGm_ceiling::getModel('users');
            $result = $users_model->addDealerInstructionCode($user_id, $code_instruction, $user->id);
            $result = $users_model->addCommercialOfferCode($user_id, $code, $user->id);

            $dop_contacts_model = Gm_ceilingHelpersGm_ceiling::getModel('clients_dop_contacts');
            $client_id = JFactory::getUser($user_id)->associated_client;
            $email_id = $dop_contacts_model->save($client_id, 1, $email);

            if ($die_bool)
            {
                die(json_encode($result));
            }
            else
            {
                return true;
            }
        }
        catch (Exception $e) {
           Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());

        }
    }


    public function RepeatSendCommercialOffer(){
    }


     public function sendLogin(){
        try
        {
            $user = JFactory::getUser();
            $groups = $user->get('groups');
            if (in_array("16", $groups))
            {
                $jinput = JFactory::getApplication()->input;
                $user_id = $jinput->get('user_id', null, 'INT');
                $email = $jinput->get('email', null, 'STRING');

                if (empty($email))
                {
                    throw new Exception('empty email');
                }

                $server_name = $_SERVER['SERVER_NAME'];
                $site = "http://$server_name/index.php?option=com_users&view=login";
                $dealer = JFactory::getUser($user_id);
                $code = md5($user_id.'dealer_instruction');
                $site2 = "http://$server_name/index.php?option=com_gm_ceiling&task=big_smeta.dealerInstruction&short=0&code=$code";

                // письмо
                $mailer = JFactory::getMailer();
                $config = JFactory::getConfig();
                $sender = array(
                    $config->get('mailfrom'),
                    $config->get('fromname')
                );
                $mailer->setSender($sender);
                $mailer->addRecipient($email);
                $body = '<html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8"><link rel="stylesheet" type="text/css" href="CSS/style_index.css"/></head>';
                $body .= '<body style="margin: 10px;">';
                $body .= '<table cols=2  cellpadding="20px"style="width: 100%; border: 0px solid; color: #414099; font-family: Cuprum, Calibri; font-size: 16px;">';
                $body .= '<tr><td style="vertical-align:middle;"><a href="http://'.$server_name.'/">';
                $body .= '<img src="http://'.$server_name.'/images/gm-logo.png" alt="Логотип" style="padding-top: 15px; height: 70px; width: auto;">';
                $body .= '</a></td><td><div style="vertical-align:middle; padding-right: 50px; padding-top: 7px; text-align: right; line-height: 0.5;">';

                $body .= '<p>Тел.: +7(473)212-34-01</p>';

                $body .= '<p>Почта: gm-partner@mail.ru</p>';
                $body .= '<p>Адрес: г. Воронеж, Проспект Труда, д. 48, литер. Е-Е2</p>';
                $body .= '</div></td></tr></table>';
                $body .= "<div style=\"width: 100%\">Инструкция по использованию: <a href=\"$site2\">Посмотреть видео</a><br>Ссылка для входа в кабинет: <a href=\"$site\">Войти</a><br>
                        Логин: $dealer->username<br>Пароль: $dealer->username<br></div></body>";
                $mailer->setSubject('Доступ в кабинет');
                $mailer->isHtml(true);
                $mailer->Encoding = 'base64';
                $mailer->setBody($body);
                $send = $mailer->Send();
                
                $users_model = Gm_ceilingHelpersGm_ceiling::getModel('users');
                $result = $users_model->addDealerInstructionCode($user_id, $code, $user->id);
                $result = $users_model->updateEmail($user_id, $email);
                $end_date = date('Y-m-d', strtotime(date('Y-m-d').'+1 months'));
                $users_model->update_demo_date($user_id,$end_date);
                $dop_contacts_model = Gm_ceilingHelpersGm_ceiling::getModel('clients_dop_contacts');
                $client_id = $dealer->associated_client;
                $email_id = $dop_contacts_model->save($client_id, 1, $email);

                die(json_encode($result));
            }
            else
            {
                throw new Exception('Not GmManager', 403);
            }
        }
        catch (Exception $e) {
           Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());

        }
    }

    public function firstSignIn(){
        try
        {
            $jinput = JFactory::getApplication()->input;
            $user_id = $jinput->get('user_id',null,'INT');
            $cookie = $jinput->get('cookie',null,'ARRAY');
            $dealer = JFactory::getUser($user_id);
            $client_model = Gm_ceilingHelpersGm_ceiling::getModel('client');
            $client = $client_model->getClientById($dealer->associated_client);
            $callback_model = Gm_ceilingHelpersGm_ceiling::getModel('callback');
            $callback_model->save(date('Y-m-d H:i:s'),'Дилер вошел первый раз',$client->id,$client->manager_id);
            foreach ($cookie as $key => $value)
                setcookie($key, $value);
            die(true);
        }
        catch (Exception $e) {
           Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());

        }
    }

    public function createPdfs(){
        try{
            $jinput = JFactory::getApplication()->input;
            $project_id = $jinput->get('id','','INT');
            $projectModel = Gm_ceilingHelpersGm_ceiling::getModel('Project');
            $calculationsModel = Gm_ceilingHelpersGm_ceiling::getModel('calculations');
            $model_calcform = $this->getModel('CalculationForm', 'Gm_ceilingModel');
            $calculations = $calculationsModel->new_getProjectItems($project_id);
            $project = $projectModel->getData($project_id);
            $dealer_id = $project->dealer_id;
            $mount_data = json_decode(htmlspecialchars_decode($project->mount_data));
            foreach ($calculations as $calculation) {
                if(!empty($calculation->n3)){
                    $calculation_data["extra_mounting_array"] = [];
                    foreach (json_decode($calculation->extra_mounting) as $extra_mounting){
                        $calculation_data["extra_mounting_array"][] = $extra_mounting;
                    }

                    $calculation_data["need_mount_extra"] = !empty($calculation_data["extra_mounting_array"]);

                    if (floatval($calculation->mounting_sum) == 0)
                        $need_mount = 0;
                    else if (!$calculation_data["need_mount_extra"])
                        $need_mount = 1;
                    else {
                        $need_mount = 0;
                        $first = Gm_ceilingHelpersGm_ceiling::calculate_mount(0, $calculation->id);
                        $first = round($first["total_gm_mounting"], 0);

                        if ($first == floatval($calculation->mounting_sum))
                            $need_mount = 0;
                        else
                            $need_mount = 1;
                    }
                        Gm_ceilingHelpersGm_ceiling::create_cut_pdf_old($calculation->id);
                        Gm_ceilingHelpersGm_ceiling::create_client_single_estimate_old($need_mount,$calculation->id);
                        Gm_ceilingHelpersGm_ceiling::create_manager_estimate_old(1,$calculation->id);
                        if(!empty($mount_data)){
                            foreach ($mount_data as $value) {
                                Gm_ceilingHelpersGm_ceiling::create_mount_estimate_by_stage_old($calculation->id,$value->mounter,$value->stage,$value->time);
                            }
                        }
                        else{
                            Gm_ceilingHelpersGm_ceiling::create_mount_estimate_by_stage_old($calculation->id,null,1,null);
                        }

                }
                else{
                    $data_for_manager_estimate = [];
                    /*Генерируем сметы и наряды попотолочно/поэтапно*/
                    $all_goods = $model_calcform->getGoodsPricesInCalculation($calculation->id, $dealer_id);
                    if (!empty($calculation->cancel_metiz)) {
                        $all_goods = Gm_ceilingHelpersGm_ceiling::deleteMetizFromGoods($all_goods);
                    }
                    if (!empty($calculation->need_mount)) {
                        if ($calculation->need_mount == 1) {
                            $all_jobs = $model_calcform->getJobsPricesInCalculation($calculation->id, $dealer_id); // Получение работ по прайсу дилера
                        } elseif ($calculation->need_mount == 2) {
                            $all_jobs = $model_calcform->getMountingServicePricesInCalculation($calculation->id, $dealer_id); // Получение работ по прайсу монажной службы
                        }
                    }
                    $factory_jobs = $model_calcform->getFactoryWorksPricesInCalculation($calculation->id);
                    foreach ($all_goods as $value) {
                        if ($value->category_id == 1) { // если полотно
                            $data_for_manager_estimate['canvas'] = $value;
                            $canvas_price = $value->dealer_price;
                            break;
                        }
                    }
                    if (empty($calculation->cancel_cuts) && !empty($calculation->offcut_square) && $calculation->offcut_square > $calculation->n4*0.5) {
                        $data_for_manager_estimate['offcuts'] = (object)array("name"=>"Обрезки","count"=>$calculation->offcut_square,"price"=>$canvas_price * 0.5);
                    }
                    $data_for_manager_estimate['photoprint'] = json_decode($calculation->photo_print);
                    $data_for_manager_estimate['factory_jobs'] = $factory_jobs;
                    $data_for_manager_estimate['calculation'] = $calculation;
                    Gm_ceilingHelpersGm_ceiling::create_cut_pdf($data_for_manager_estimate);
                    //для менеджера
                    Gm_ceilingHelpersGm_ceiling::create_manager_estimate($data_for_manager_estimate);
                    //клиентская смета
                    $data_for_client_estimate = $data_for_manager_estimate;
                    $data_for_client_estimate['dealer_id'] = $dealer_id;
                    $data_for_client_estimate['jobs'] = $all_jobs;
                    $data_for_client_estimate['goods'] = $all_goods;
                    Gm_ceilingHelpersGm_ceiling::create_client_single_estimate($data_for_client_estimate);
                    $data_for_mount_estimate = [];
                    $data_for_mount_estimate['calculation'] = $calculation;
                    $data_for_mount_estimate['jobs'] = $all_jobs;
                    if($calculation->need_mount == 1){
                        $data_for_mount_estimate['gm_jobs'] = [];
                    }
                    if($calculation->need_mount == 2 || !empty($project->calcs_mounting_sum)){
                        $data_for_mount_estimate['gm_jobs'] = $model_calcform->getJobsPricesInCalculation($calculation->id, 1);
                    }
                    Gm_ceilingHelpersGm_ceiling::create_mount_estimate_by_stage($data_for_mount_estimate,null,1);

                    if (!empty($mount_data)) {
                        foreach ($mount_data as $value) {
                            Gm_ceilingHelpersGm_ceiling::create_mount_estimate_by_stage($data_for_mount_estimate, $value->mounter, $value->stage);

                        }
                    } else {
                        Gm_ceilingHelpersGm_ceiling::create_mount_estimate_by_stage($data_for_mount_estimate,null,1);
                    }
                }

            }

            /*Генерируем общие сметы и наряды*/
            Gm_ceilingHelpersGm_ceiling::create_common_estimate_mounters($project_id);
            Gm_ceilingHelpersGm_ceiling::create_client_common_estimate($project_id);
            Gm_ceilingHelpersGm_ceiling::create_common_cut_pdf($project_id);
            Gm_ceilingHelpersGm_ceiling::create_common_manager_estimate($project_id);
            Gm_ceilingHelpersGm_ceiling::create_estimate_of_consumables($project_id);
            die(json_encode(true));
        }
        catch (Exception $e) {
           Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());

        }
    }

    public function printInProductionOnGmMainPage(){
        try
        {
            $model = Gm_ceilingHelpersGm_ceiling::getModel('projects');
            // в производстве
            $answer1 = $model->getDataByStatus("InProduction");
            die(json_encode($answer1));
        }
        catch (Exception $e) {
           Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());

        }
    }
    public function printZapushennieOnGmMainPage(){
        try
        {
            $model = Gm_ceilingHelpersGm_ceiling::getModel('projects');
            //запущенные
            $answer2 = $model->getDataByStatus("Zapushennie");
            die(json_encode($answer2));
        }
        catch (Exception $e) {
           Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());

        }
    }
    public function printZayavkiSSaitaOnGmMainPage(){
        try
        {
            $model = Gm_ceilingHelpersGm_ceiling::getModel('projects');
            // заявки с сайта
            $answer3 = $model->getDataByStatus("ZayavkiSSaita");
            die(json_encode($answer3));
        }
        catch (Exception $e) {
           Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());

        }
    }
    public function printZvonkiOnGmMainPage(){
        try
        {
            $model = Gm_ceilingHelpersGm_ceiling::getModel('projects');
            // звонки
            $date = date('Y-m-d');
            $answer4 = $model->getDataByStatus('Zvonki', $date);
            die(json_encode($answer4));
        }
        catch (Exception $e) {
           Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());

        }
    }
    public function printMissedCallsOnGmMainPage(){
        try
        {
            $model = Gm_ceilingHelpersGm_ceiling::getModel('projects');
            $date = date('Y-m-d');
            // пропущенные
            $answer5 = Gm_ceilingController::missedCalls($date, "missed", 1);
            $answer6 = $model->getDataByStatus("MissedCalls");
            $missAnswer1 = [];
            $missAnswer2 = [];

            if(!empty($answer5)){
                foreach ($answer5 as $value) {
                    array_push($missAnswer1, $value->id);
                }
            }

            if(!empty($answer6)){
                foreach ($answer6 as $value) {
                    array_push($missAnswer2, $value->call_id);
                }
            }

            $answer7 = array_diff($missAnswer1, $missAnswer2);
            die(json_encode(count($answer7)));
        }
        catch (Exception $e) {
           Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());

        }
    }

    public function UserRefuseToCooperate()
    {
        try
        {
            $jinput = JFactory::getApplication()->input;
        
            $id = $jinput->get('user_id', null, 'INT');
            $coop = $jinput->get('coop',1,'INT');
            $user = JFactory::getUser();
            if (!$user->guest) {
                $users_model = Gm_ceilingHelpersGm_ceiling::getModel('users');
                $result = $users_model->refuseToCooperate($id,$coop);
            }
    
            die($result);
        }
       catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }
    public function sendClientEstimate(){
        try{
            $jinput = JFactory::getApplication()->input;
            $id = $jinput->get('id', null, 'INT');
            $email = $jinput->get('email', null, 'STRING');
            $mailer = JFactory::getMailer();
            $config = JFactory::getConfig();
            $sender = array(
                $config->get('mailfrom'),
                $config->get('fromname')
            );
            $sheets_dir = $_SERVER['DOCUMENT_ROOT'] . '/tmp/';
            $client_estimate[] = $_SERVER['DOCUMENT_ROOT'] . "/costsheets/". md5($id."client_single") . ".pdf";
            $filename =  "Подробная смета.pdf";
            Gm_ceilingHelpersGm_ceiling::save_pdf($client_estimate, $sheets_dir . $filename, "A4");
            $mailer->setSender($sender);
            $mailer->addRecipient($email);
            $body = "Здравствуйте. Вы запросили подробную смету потолка. Смета во вложении";
            $mailer->setSubject('Подробная смета');
            $mailer->setBody($body);
            $mailer->addAttachment($sheets_dir.$filename);
            $send = $mailer->Send();
            unlink($_SERVER['DOCUMENT_ROOT'] . "/tmp/" . $filename);
            die($send);
        }
       catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }


    }
    public function filterProjectForStatus() {
        try
        {
            $user = JFactory::getUser();
            $jinput = JFactory::getApplication()->input;
            $status = $jinput->get('status', '0', 'int');
            $search = $jinput->get('search', '', 'string');
            $dealer_id = $jinput->get('dealer_id', $user->dealer_id, 'int');
            $projects_model = Gm_ceilingHelpersGm_ceiling::getModel('projects');
            $result =  $projects_model->filterProjectForStatus($status, $search, $dealer_id);
            foreach ($result as $key => $value) {
                $result[$key]->created = date("d.m.Y H:i", strtotime($value->created));
            }

            die(json_encode($result));
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());

        }
    }

    public function filterDateScore() {
        try
        {
            $jinput = JFactory::getApplication()->input;
            $date1 = $jinput->get('date1', '0000-00-00 00:00:00', 'datetime');
            $date2 = $jinput->get('date2', '0000-00-00 00:00:00', 'datetime');
            $recoil_map_project_model = Gm_ceilingHelpersGm_ceiling::getModel('recoil_map_project');
            $result =  $recoil_map_project_model->filterDateScore($date1, $date2);
            foreach ($result as $key => $value) {
                $result[$key]->date_time = date("d.m.Y H:i", strtotime($value->date_time));
            }


            die(json_encode($result));
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());

        }
    }

    function regenerate_common_estimate(){
        try
        {
            $jinput = JFactory::getApplication()->input;
            $project_id = $jinput->get('proj_id','','STRING');
            $ids = $jinput->get('calc_ids', array(), 'Array');
            Gm_ceilingHelpersGm_ceiling::create_client_common_estimate($project_id,$ids);
            Gm_ceilingHelpersGm_ceiling::create_common_estimate_mounters($project_id,$ids);
            die(json_encode(true));
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());

        }
    }

    function get_measurement_from_app()
    {
        $f = fopen('php://input', 'r');
        $data = stream_get_contents($f);

        if ($data) {
            die($data);
        }
    }

    function FromGMPotolkiRF() {
        try
        {
            header('Access-Control-Allow-Origin: https://гмпотолки.рф');
            header('Access-Control-Allow-Credentials: true');
            header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
            header('Access-Control-Allow-Headers: Content-Type');
            header('Access-Control-Max-Age: 1000');
            header('Access-Control-Allow-Headers: Content-Type, Content-Range, Content-Disposition, Content-Description');

            $what_funct = $_POST["what_funct"];

             switch ($what_funct) {
                case 'get_factures':
                    $canvases_model = Gm_ceilingHelpersGm_ceiling::getModel("canvases");
                    $answer = json_encode($canvases_model->getFilteredItemsCanvas("count>0"));
                    break;
            }
            
            die($answer);
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());

        }
    }
        
    function acceptFromCall() {
        try
        {
            $files = "components/com_gm_ceiling/";
            file_put_contents($files.'calls.txt', json_encode($_POST)."\n----------\n", FILE_APPEND);
            $phone = $_POST['phone'];
            $advt = $_POST['advt'];
            $jinput = JFactory::getApplication()->input;
            if (empty($_POST['phone'])) {
                $phone = $jinput->get('phone','','STRING');
            }
            if (empty($_POST['advt'])) {
                $advt = $jinput->get('advt','','STRING');
            }
            if(empty($advt)){
                $advt = 43;
            }
            file_put_contents($files.'calls.txt', json_encode($phone)."\n==========\n", FILE_APPEND);
            $phone = mb_ereg_replace('[^\d]', '', $phone);
            $clientform_model = Gm_ceilingHelpersGm_ceiling::getModel('clientform');
            $clienthistory_model = Gm_ceilingHelpersGm_ceiling::getModel('client_history');
            $callback_model = Gm_ceilingHelpersGm_ceiling::getModel('callback');
            $clientsphones_model = Gm_ceilingHelpersGm_ceiling::getModel('client_phones');

            $data['client_name'] = 'Клиент с обзвона';
            $data['client_contacts'] = $phone;
            $data['dealer_id'] = 697;
            $data['manager_id'] = 697;
            //die($_POST['phone'].' '.$data['client_contacts']);
            $result = $clientform_model->save($data);


            if (mb_ereg('[\d]', $result)) {
                $clienthistory_model->save($result, 'Клиент создан автоматически в результате аудиообзвона');
                $callback_model->save(date("Y-m-d H:i:s"), 'Клиент прослушал сообщение аудиообзвона', $result, 697);
                $this->createProject($result,$advt,null,null);
            }
            else
            {
                $client = $clientsphones_model->getItemsByPhoneNumber($data['client_contacts'], 697);
                $callback_model->save(date("Y-m-d H:i:s"), 'Клиент прослушал сообщение аудиообзвона', $client->id, 697);
            }
            die(json_encode(true));
           /* }
            else {
                die(false);
            }*/
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }
    function audioCallGM() {
        try
        {
            $files = "components/com_gm_ceiling/";
            file_put_contents($files.'callsGM.txt', json_encode($_POST)."\n----------\n", FILE_APPEND);
           // if (!empty($_POST['phone'])) {
                file_put_contents($files.'callsGM.txt', json_encode($_POST['phone'])."\n==========\n", FILE_APPEND);
                $clientform_model = Gm_ceilingHelpersGm_ceiling::getModel('clientform');
                $clienthistory_model = Gm_ceilingHelpersGm_ceiling::getModel('client_history');
                $callback_model = Gm_ceilingHelpersGm_ceiling::getModel('callback');
                $clientsphones_model = Gm_ceilingHelpersGm_ceiling::getModel('client_phones');

                $data['client_name'] = 'Клиент с обзвона Msk';
                $data['client_contacts'] = explode('+', $_POST['phone'])[1];
                $data['dealer_id'] = 1;
                $data['manager_id'] = 62;
                //die($_POST['phone'].' '.$data['client_contacts']);
                $result = $clientform_model->save($data);
                $this->createProject($result,77,null,null);

                if (mb_ereg('[\d]', $result)) {
                    $clienthistory_model->save($result, 'Клиент создан автоматически в результате аудиообзвона Msk');
                    $callback_model->save(date("Y-m-d H:i:s"), 'Клиент прослушал сообщение аудиообзвона Msk', $result, 62);
                }
                else
                {
                    $client = $clientsphones_model->getItemsByPhoneNumber($data['client_contacts'], 62);
                    $callback_model->save(date("Y-m-d H:i:s"), 'Клиент прослушал сообщение аудиообзвона Msk', $client->id, 62);
                }
                die(true);
           /* }
            else {
                die(false);
            }*/
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }
    function audioCallTU() {
        try
        {
            $files = "components/com_gm_ceiling/";
            file_put_contents($files.'callsTU.txt', json_encode($_POST)."\n----------\n", FILE_APPEND);
           // if (!empty($_POST['phone'])) {
                file_put_contents($files.'callsTU.txt', json_encode($_POST['phone'])."\n==========\n", FILE_APPEND);
                $clientform_model = Gm_ceilingHelpersGm_ceiling::getModel('clientform');
                $clienthistory_model = Gm_ceilingHelpersGm_ceiling::getModel('client_history');
                $callback_model = Gm_ceilingHelpersGm_ceiling::getModel('callback');
                $clientsphones_model = Gm_ceilingHelpersGm_ceiling::getModel('client_phones');

                $data['client_name'] = 'Клиент с обзвона Msk';
                $data['client_contacts'] = explode('+', $_POST['phone'])[1];
                $data['dealer_id'] = 697;
                $data['manager_id'] = 697;
                //die($_POST['phone'].' '.$data['client_contacts']);
                $result = $clientform_model->save($data);
                $this->createProject($result,78,null,null);

                if (mb_ereg('[\d]', $result)) {
                    $clienthistory_model->save($result, 'Клиент создан автоматически в результате аудиообзвона Msk');
                    $callback_model->save(date("Y-m-d H:i:s"), 'Клиент прослушал сообщение аудиообзвона Msk', $result, 697);
                }
                else
                {
                    $client = $clientsphones_model->getItemsByPhoneNumber($data['client_contacts'], 697);
                    $callback_model->save(date("Y-m-d H:i:s"), 'Клиент прослушал сообщение аудиообзвона Msk', $client->id, 697);
                }
                die(true);
           /* }
            else {
                die(false);
            }*/
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    function addCallHistory() {
        try
        {
            $jinput = JFactory::getApplication()->input;
            $client_id = $jinput->get('client_id', null,'INT');
            $status = $jinput->get('status', null, 'INT');
            $manager_id = JFactory::getUser()->id;
            if (empty($manager_id)) {
                throw new Exception('empty manager id!');
            }
            $model_call = Gm_ceilingHelpersGm_ceiling::getModel('callback');
            $model_client_history = Gm_ceilingHelpersGm_ceiling::getModel('client_history');

            $model_call->addCallHistory($manager_id, $client_id, $status);
            if ($status == 1) {
                $model_client_history->save($client_id, 'Исходящий недозвон');
                //$model_call->save(date("Y-m-d H:i", strtotime(date("Y-m-d H:i")."+ 1 day")), 'Исходящий недозвон', $client_id, $manager_id);
            }
            elseif ($status == 2) {
                $model_client_history->save($client_id, 'Исходящий дозвон');
            }
            elseif ($status == 3) {
                $model_client_history->save($client_id, 'Входящий звонок');
            }
            elseif ($status == 4) {
                $model_client_history->save($client_id, 'Презентация');
                $model_call->addCallHistory($manager_id, $client_id, 2);
            }
            elseif ($status == 5) {
                $model_client_history->save($client_id, 'Лид');
                $model_call->addCallHistory($manager_id, $client_id, 2);
                $model_call->addCallHistory($manager_id, $client_id, 4);
            }
            die(true);
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function getArrayForMeasuresCalendar()
    {
        try {
            $user = JFactory::getUser();
            $model_projects = $this->getModel('Projects', 'Gm_ceilingModel');
            $model_gaugers = $this->getModel('Gaugers', 'Gm_ceilingModel');
            $result = $model_projects->getMeasuresAndDayoffsByDealerId($user->dealer_id);
            $result_gaugers = $model_gaugers->getDealerGaugers($user->dealer_id);

            $final_result = (object)['data' => null, 'gaugers' => $result_gaugers];

            if (!empty($result)) {
                foreach ($result as $key => $value) {
                    if (!empty($value->calc_dates)) {
                        $prj_dates = explode('!', $value->calc_dates);
                        foreach ($prj_dates as $key2 => $value2) {
                            $prj_dates[$key2] = explode('|', $value2);
                        }
                        $result[$key]->dates = $prj_dates;
                    }
                    $off_dates1 = explode(',', $value->off_dates);
                    foreach ($off_dates1 as $key2 => $value2) {
                        $off_dates2 = explode('|', $value2);
                        while ($off_dates2[0] < $off_dates2[1]) {
                            $result[$key]->dates[] = [$off_dates2[0], null, null];
                            $off_dates2[0] = date('Y-m-d H:i:s', strtotime($off_dates2[0].' +1 hour'));
                        }
                    }
                    foreach ($result[$key]->dates as $key2 => $value2) {
                        $datetime = strtotime($value2[0]);
                        $y = intval(date("Y", $datetime));
                        $m = intval(date("m", $datetime));
                        $d = intval(date("d", $datetime));
                        $h = intval(date("H", $datetime));
                        $final_result->data[$y][$m][$d][$result[$key]->project_calculator][$h] = (object)['id' => $value2[1], 'info' => $value2[2]];
                    }
                }
            }

            die(json_encode($final_result));
        }
        catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());

        }
    }

    public function getArrayForMountsCalendar()
    {
        try {
            $user = JFactory::getUser();
            $model_projects = $this->getModel('Projects', 'Gm_ceilingModel');
            $model_users = $this->getModel('Users', 'Gm_ceilingModel');
            $result = $model_projects->getMountsAndDayoffsByDealerId($user->dealer_id);
            $gm_result = $model_projects->getMountsAndDayoffsByDealerId(1);
            $result_mounters = $model_users->getDealerMounters($user->dealer_id);
            $brigades_count = $model_users->getCountOfUsersByGroupAndDealer(11,1);
            $final_result = (object)['data' => null, 'mounters' => $result_mounters];
            $gm_final_result = (object)['data' => null];
            if (!empty($result)) {
                foreach ($result as $key => $value) {
                    if (!empty($value->mount_dates)) {
                        $prj_dates = explode('!', $value->mount_dates);
                        foreach ($prj_dates as $key2 => $value2) {
                            $prj_dates[$key2] = explode('|', $value2);
                        }
                        $result[$key]->dates = $prj_dates;
                    }
                    $off_dates1 = explode(',', $value->off_dates);
                    foreach ($off_dates1 as $key2 => $value2) {
                        $off_dates2 = explode('|', $value2);
                        while ($off_dates2[0] < $off_dates2[1]) {
                            $result[$key]->dates[] = [$off_dates2[0], null, null];
                            $off_dates2[0] = date('Y-m-d H:i:s', strtotime($off_dates2[0].' +1 hour'));
                        }
                    }
                    foreach ($result[$key]->dates as $key2 => $value2) {
                        $datetime = strtotime($value2[0]);
                        $y = intval(date("Y", $datetime));
                        $m = intval(date("m", $datetime));
                        $d = intval(date("d", $datetime));
                        $h = intval(date("H", $datetime));
                        $final_result->data[$y][$m][$d][$result[$key]->project_mounter][$h] = (object)['id' => $value2[1], 'info' => $value2[2]];
                    }
                }
            }
            if(!empty($gm_result)){
                foreach ($gm_result as $key => $value) {
                    if (!empty($value->mount_dates)) {
                        $prj_dates = explode('!', $value->mount_dates);
                        foreach ($prj_dates as $key2 => $value2) {
                            $prj_dates[$key2] = explode('|', $value2);
                        }
                        $gm_result[$key]->dates = $prj_dates;
                    }
                    $off_dates1 = explode(',', $value->off_dates);
                    foreach ($off_dates1 as $key2 => $value2) {
                        $off_dates2 = explode('|', $value2);
                        while ($off_dates2[0] < $off_dates2[1]) {
                            $gm_result[$key]->dates[] = [$off_dates2[0], null, null];
                            $off_dates2[0] = date('Y-m-d H:i:s', strtotime($off_dates2[0].' +1 hour'));
                        }
                    }
                    foreach ($gm_result[$key]->dates as $key2 => $value2) {
                        $datetime = strtotime($value2[0]);
                        $y = intval(date("Y", $datetime));
                        $m = intval(date("m", $datetime));
                        $d = intval(date("d", $datetime));
                        $h = intval(date("H", $datetime));
                        $gm_final_result->data[$y][$m][$d][$gm_result[$key]->project_mounter][$h] = (object)['id' => $value2[1], 'info' => $value2[2]];
                    }
                }
            }
            $mount_service_brigade = [];
            $final_result->brigades_count = $brigades_count->brigades_count;
            foreach ($gm_final_result->data as $year=>$month_array){
                foreach($month_array as $month=>$days_array){
                    foreach ($days_array as $day=>$mounters_array){
                        foreach ($mounters_array as $mounter=>$times_array){
                            foreach($times_array as $time=>$project){
                                if(!empty($mount_service_brigade[$year][$month][$day][$time])){
                                    $mount_service_brigade[$year][$month][$day][$time] -= 1;
                                }
                                else{
                                    $mount_service_brigade[$year][$month][$day][$time] = $brigades_count->brigades_count-1;
                                }
                            }
                        }
                    }
                }
            }
            $final_result->free_brigades_data = $mount_service_brigade;

            //throw new Exception(json_encode($final_result));
            die(json_encode($final_result));
        }
        catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());

        }
    }

    function getDealersAnalyticData(){
        try{
            $jinput = JFactory::getApplication()->input;
            $date1 = $jinput->get('date_from','','STRING');
            $date2 = $jinput->get('date_to','','STRING');
            $status = $jinput->getInt('status');
            $newAnalytic = $jinput->getInt('new');
            $model = Gm_ceilingHelpersGm_ceiling::getModel('Analytic_Dealers');
            $data = $model->getData($date1,$date2,$status,$newAnalytic);
            die(json_encode($data));
        }
        catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    function generateBuilderPDF(){
       try{
           $jinput = JFactory::getApplication()->input;
           $builderId = $jinput->getInt('id');
           $stage = $jinput->getInt('stage');
           $stageName = $jinput->get('stageName','',"STRING");
           $objectName = JFactory::getUser($builderId)->name;
           $model = Gm_ceilingHelpersGm_ceiling::getModel('clients');
           $data = $model->getClientsAndprojectsData($builderId,$stage);
           $valueTitle = ($stage == 3) ? "S=" : "P=";
           $html = "<h1>Объект: $objectName($stageName)</h1>";
           $html .= "<table style='width:100%;border-collapse: collapse;'>";
           foreach($data as $floor){
               $html .="<tr>";
               $html .="<td style='width: 7%'>";
               $html .= $floor['name'];
               $html .="</td>";
               foreach ($floor['projects'] as $project){
                   $html .= "<td>";
                   $html .= "<div style='font-size:9pt;'>";
                        $html .= "<div><b>".$project->title."</b></div>";
                   $html .= "</div>";
                   $html .= "<div  style='font-size:9pt;font-style:italic;'>";
                        $html .= "<div>".$valueTitle.$project->value." (".$project->sum.")</div>";
                   $html .="</div>";
                   if($stage == 2) {
                       $html .= "<div style='font-size:9pt;font-style:italic;'>";
                            $html .= "<div>Плитка=" . $project->n7 . "(" . $project->n7_cost . ")</div>";
                       $html .="</div>";
                   }
                   $html .= "<div>_____________</div>";
                   $html .= "</td>";
               }
               $html .= "</tr>";
           }
           $html .= "</table>";
           $sheets_dir = $_SERVER['DOCUMENT_ROOT'] . '/tmp/';
           $filename = md5($builderId."blank".$stage) . ".pdf";
           $mpdf = new mPDF('utf-8', "A4", '6', '', 1, 2, 1, 1, 1, 1);
           $mpdf->showImageErrors = true;
           $mpdf->SetDisplayMode('fullpage');
           $mpdf->list_indent_first_level = 0;
           $stylesheet = file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/libraries/mpdf/gm_documents.css');
           $mpdf->WriteHTML($stylesheet, 1);
           $mpdf->WriteHTML($html, 2);
           $mpdf->Output($sheets_dir . $filename, 'F');

           $result['url'] = "/tmp/".$filename;
           die(json_encode($result));
       }
       catch(Exception $e) {
           Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
       }
    }

    function acceptFromCallGM() {
        try {
            $clientform_model = Gm_ceilingHelpersGm_ceiling::getModel('clientform');
            $clienthistory_model = Gm_ceilingHelpersGm_ceiling::getModel('client_history');
            $callback_model = Gm_ceilingHelpersGm_ceiling::getModel('callback');
            $clientsphones_model = Gm_ceilingHelpersGm_ceiling::getModel('client_phones');
            $postData = file_get_contents('php://input');
            $path = "components/com_gm_ceiling/";
            file_put_contents(
                $path.'acceptFromCallGM_log.txt',
                date('Y-m-d H:i:s')."\n".print_r($postData, true)."\n----------\n",
                FILE_APPEND
            );

            $data = json_decode($postData, true);
            if (isset($data['call'])) {
                $call = $data['call'];
                if (!empty($call['answer'])) {
                    $data['client_name'] = 'Клиент с обзвона';
                    $data['client_contacts'] = $call['phone'];
                    $data['dealer_id'] = 1;
                    $data['manager_id'] = 1;
                    $result = $clientform_model->save($data);
                    if (mb_ereg('[\d]', $result)) {
                        $clienthistory_model->save($result, 'Клиент создан автоматически в результате аудиообзвона');
                        $callback_model->save(date("Y-m-d H:i:s"), 'Клиент прослушал сообщение аудиообзвона', $result, 1);
                    } else {
                        $client = $clientsphones_model->getItemsByPhoneNumber($data['client_contacts'], 1);
                        $callback_model->save(date("Y-m-d H:i:s"), 'Клиент прослушал сообщение аудиообзвона', $client->id, 1);
                    }
                }
            }
            die(true);
        } catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    function register_from_login(){
        try{
            $jinput = JFactory::getApplication()->input;
            $fio = $jinput->get('fio','','STRING');
            $phone = $jinput->get('phone','','STRING');
            $isDealer = $jinput->getInt('isDealer');


            $clienthistory_model = Gm_ceilingHelpersGm_ceiling::getModel('client_history');
            $callback_model = Gm_ceilingHelpersGm_ceiling::getModel('callback');
            $clientsphones_model = Gm_ceilingHelpersGm_ceiling::getModel('client_phones');
            $clientform_model =Gm_ceilingHelpersGm_ceiling::getModel('ClientForm');

            $client_data['client_name'] = $fio;
            $client_data['client_contacts'] = $phone;
            $client_id = $clientform_model->save($client_data);
            if(!$isDealer){
                if (mb_ereg('[\d]', $client_id)) {
                    $clienthistory_model->save($client_id, 'Клиент создан в результате регистрации на calc.gm-vrn');
                    $callback_model->save(date("Y-m-d H:i:s"), 'Клиент c формы захвата calc.gm-vrn.ru', $client_id, 1);

                } else {
                    $client = $clientsphones_model->getItemsByPhoneNumber($phone, 1);
                    $callback_model->save(date("Y-m-d H:i:s"), 'Существующий клиент пытался зарегистрироваться на calc.gm-vrn', $client->id, 1);
                }
            }
            else{
                $email = $jinput->get('email', '', 'STRING');
                $city = $jinput->get('city', '', 'STRING');

                if (mb_ereg('[\d]', $client_id)) {
                    //создание user'а
                    $dealer_id = Gm_ceilingHelpersGm_ceiling::registerUser($fio, $phone, $email, $client_id, 1);
                    $client_model = Gm_ceilingHelpersGm_ceiling::getModel('Client', 'Gm_ceilingModel');
                    $client_model->updateClient($client_id,null,$dealer_id);
                    $dealer_info_model = Gm_ceilingHelpersGm_ceiling::getModel('dealer_info');
                    $dealer_info_model->update_city($dealer_id,$city);
                    $clients_dop_contacts = Gm_ceilingHelpersGm_ceiling::getModel('clients_dop_contacts');
                    $clients_dop_contacts->save($client_id,1,$email);
                    $client = $clientsphones_model->getItemsByPhoneNumber($phone, 1);
                    $callback_model->save(date("Y-m-d H:i:s"), 'На calc.gm-vrn зарегистрировался новый дилер', $client->id, 1);

                }
                else{
                    $client = $clientsphones_model->getItemsByPhoneNumber($phone, 1);
                    $callback_model->save(date("Y-m-d H:i:s"), 'Существующий клиент пытался зарегистрироваться как дилер на calc.gm-vrn', $client->id, 1);
                }

            }
            die(json_encode(true));
        }
        catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    function registerBySMS(){
        try{
            $jinput = JFactory::getApplication()->input;
            $fio = $jinput->get('fio','','STRING');
            $phone = $jinput->get('phone','','STRING');
            $phone = mb_ereg_replace('[^\d]', '', $phone);
            $isDealer = $jinput->getInt('isDealer');
            $email = $jinput->get('email', '', 'STRING');
            $city = $jinput->get('city', '', 'STRING');
            if(!empty($phone)) {

                $chars = "1234567890";
                $max = 6;
                $size = StrLen($chars) - 1;
                $code = '';
                while ($max--) {
                    $code .= $chars[rand(0, $size)];
                }

                $userModel = Gm_ceilingHelpersGm_ceiling::getModel('users');
                $user = $userModel->getUserByUsername($phone);
                if (!empty($user)) {
                    if(!empty($user->verification_code)){
                        $now = date('Y-m-d H:i:s');
                        $diff = strtotime($now) - strtotime($user->code_creation_time);
                        if($diff<300){
                            $error = (object)array(
                                'type'=>'error',
                                'text'=>'Время ожидание еще не вышло!',
                                'value'=>$diff,
                                'sendtime'=>$user->code_creation_time);
                            die(json_encode($error));
                        }
                    }
                    $userModel->setVerificationCode($user->id, $code);
                    $this->sendSMS($code,$phone);
                } else {
                    /*Пользователя с таким номером не существует пробуем регистрировать, генерируем пароль и отправляем в смс*/
                    $clienthistory_model = Gm_ceilingHelpersGm_ceiling::getModel('client_history');
                    $callback_model = Gm_ceilingHelpersGm_ceiling::getModel('callback');
                    $clientsphones_model = Gm_ceilingHelpersGm_ceiling::getModel('client_phones');
                    $clientform_model = Gm_ceilingHelpersGm_ceiling::getModel('ClientForm');
                    $client_model = Gm_ceilingHelpersGm_ceiling::getModel('Client', 'Gm_ceilingModel');

                    $client_data['client_name'] = $fio;
                    $client_data['client_contacts'] = $phone;
                    $client_data['dealer_id'] = 1;
                    $client_id = $clientform_model->save($client_data);

                    if (mb_ereg('[\d]', $client_id)) {
                        /*Новый клиент*/
                        $clienthistory_model->save($client_id, 'Клиент создан в результате регистрации на calc.gm-vrn');
                        $callback_model->save(date("Y-m-d H:i:s"), 'Новый клиент c формы захвата calc.gm-vrn.ru', $client_id, 1);
                        if(!empty($advt)){
                            $dealer_id = 1;
                            $info_model = Gm_ceilingHelpersGm_ceiling::getModel('dealer_info');
                            $dealer_canvases_margin = $info_model->getMargin('dealer_canvases_margin', $dealer_id);
                            $dealer_components_margin = $info_model->getMargin('dealer_components_margin', $dealer_id);
                            $dealer_mounting_margin = $info_model->getMargin('dealer_mounting_margin', $dealer_id);
                            $project_model = Gm_ceilingHelpersGm_ceiling::getModel('projectform');
                            $project_data['state'] = 1;
                            $project_data['client_id'] = $client_id;
                            $project_data['api_phone_id'] = $advt;
                            $project_data['created'] = date("Y-m-d");
                            $project_data['project_status'] = 1;
                            $project_data['dealer_canvases_margin'] = $dealer_canvases_margin;
                            $project_data['dealer_components_margin'] = $dealer_components_margin;
                            $project_data['dealer_mounting_margin'] = $dealer_mounting_margin;
                            $project_model->save($project_data);
                        }
                    } else {
                        /*такой клиент уже есть в базе*/
                        $client = $clientsphones_model->getItemsByPhoneNumber($phone, 1);
                        $client_id = $client->id;
                        $clienthistory_model->save($client_id, 'Существующий клиент получил кабинет на calc.gm-vrn');
                        $callback_model->save(date("Y-m-d H:i:s"), 'Существующий клиент получил кабинет на calc.gm-vrn.ru', $client_id, 1);
                    }

                    if (mb_ereg('[\d]', $client_id)) {
                        //создание user'а
                        if(!$isDealer){
                            $email = "$client_id@$client_id";
                            $dealer_id = Gm_ceilingHelpersGm_ceiling::registerUser($fio, $phone, $email, $client_id, 2);
                            //$callback_model->save(date("Y-m-d H:i:s"), 'На calc.gm-vrn зарегистрировался новый клиент с личным кабинетом', $client_id, 1);
                        }
                        else{
                            $dealer_id = Gm_ceilingHelpersGm_ceiling::registerUser($fio, $phone, $email, $client_id, 1);
                            $client_model->updateClient($client_id, null, $dealer_id);
                            $dealer_info_model = Gm_ceilingHelpersGm_ceiling::getModel('dealer_info');
                            $dealer_info_model->update_city($dealer_id, $city);
                            $clients_dop_contacts = Gm_ceilingHelpersGm_ceiling::getModel('clients_dop_contacts');
                            $clients_dop_contacts->save($client_id, 1, $email);
                            $callback_model->save(date("Y-m-d H:i:s"), 'На calc.gm-vrn зарегистрировался новый дилер', $client_id, 1);
                        }
                        /*меняем и отправляем пароль*/
                        $userModel->setVerificationCode($dealer_id, $code);
                        $this->sendSMS($code,$phone);

                    } else {
                        $client = $clientsphones_model->getItemsByPhoneNumber($phone, 1);
                        $callback_model->save(date("Y-m-d H:i:s"), 'Существующий клиент пытался зарегистрироваться как дилер или получить ЛК но что-то пошло не так ', $client->id, 1);
                    }

                }
                die(json_encode($code));
            }
            else{
                /*Пустой телефон вернуть ошибосик*/
            }

        }
        catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }
    function sendSMS($code,$phone){
        try{
            $apiKey = "w7wt3IiHHFtxzlkPsPargPciMBrY";
            $credentials = base64_encode("it.gmvrn@gmail.com:$apiKey");
            if( $curl = curl_init() ) {
                $url = "https://gate.smsaero.ru/v2/sms/send?numbers[]=$phone&text=Код+для+входа:$code&sign=G_MASTEROV&channel=DIRECT";

                curl_setopt_array($curl, array(
                    CURLOPT_URL => $url,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => "",
                    CURLOPT_CUSTOMREQUEST => "GET",
                    CURLOPT_HTTPHEADER => array(
                        "Accept: */*",
                        "Authorization: Basic $credentials"
                    ),
                ));
                $result = curl_exec($curl);

                curl_close($curl);
            }
        }
        catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    function registrationBAU(){
        try{
            $data = $_POST;
            if(!empty($data)){
                $name = $data['name'];
                $surname = $data['surname'];
                $password = $data['password'];
                $phone = $data['phone'];
                $email = $data['email'];
            }
            else{
                $jinput = JFactory::getApplication()->input;
                $name = $jinput->get('name','','STRING');
                $surname = $jinput->get('surname','','STRING');
                $password = $jinput->get('password','','STRING');
                $phone = $jinput->get('phone','','STRING');
                $email = $jinput->get('email', '', 'STRING');
            }
            $phone = mb_ereg_replace('[^\d]', '', $phone);
            if(!empty($phone)){
                $userModel = Gm_ceilingHelpersGm_ceiling::getModel('users');
                $user = $userModel->getUserByUsername($phone);
                if (!empty($user)) {
                    /*Существующий пользователь*/
                    $dealer_id = $user->id;
                }
                else{
                    /*Новый пользователь*/
                    $clientform_model = Gm_ceilingHelpersGm_ceiling::getModel('ClientForm');
                    $clienthistory_model = Gm_ceilingHelpersGm_ceiling::getModel('client_history');
                    $callback_model = Gm_ceilingHelpersGm_ceiling::getModel('callback');
                    $clientsphones_model = Gm_ceilingHelpersGm_ceiling::getModel('client_phones');

                    $client_data['client_name'] = "$surname $name";
                    $client_data['client_contacts'] = $phone;
                    $client_data['dealer_id'] = 1;
                    $client_id = $clientform_model->save($client_data);

                    if (mb_ereg('[\d]', $client_id)) {
                        /*Новый клиент*/
                        $clienthistory_model->save($client_id, 'Клиент создан в результате регистрации в BAUNET');
                        $callback_model->save(date("Y-m-d H:i:s"), 'Новый клиент из прилодения BAUNET', $client_id, 1);
                    } else {
                        /*такой клиент уже есть в базе*/
                        $client = $clientsphones_model->getItemsByPhoneNumber($phone, 1);
                        $client_id = $client->id;
                        $clienthistory_model->save($client_id, 'Существующий клиент получил кабинет на calc.gm-vrn');
                        $callback_model->save(date("Y-m-d H:i:s"), 'Существующий клиент получил кабинет на calc.gm-vrn.ru', $client_id, 1);
                    }
                    if (mb_ereg('[\d]', $client_id)) {
                        //создание user'а
                        $dealer_id = Gm_ceilingHelpersGm_ceiling::registerUser("$surname $name", $phone, $email, $client_id, 2,0,$password);
                    }
                }
                $response = $this->sendCall($phone);
                if(count($response) == 3) {
                    $code = $response[2];
                    $code = substr($code,-4);
                    $userModel->setVerificationCode($dealer_id, $code);
                    die("{\"result\":".json_encode((object)["type"=>"success","id"=>$dealer_id],JSON_UNESCAPED_UNICODE)."}");
                }
                elseif(count($response) == 2){
                    $text = '';
                    switch($response[1]){
                        case -1:
                            $text = "Ошибка в параметрах.";
                            break;
                        case -2:
                            $text = "Неверный логин или пароль";
                            break;
                        case -4:
                            $text = "IP-адрес временно заблокирован из-за частых ошибок в запросах";
                            break;
                        case -5:
                            $text = "Неверный формат даты.";
                            break;
                        case -9:
                            $text = "Превышено кол-во одинаковых запросов в минуту";
                            break;
                    }
                    die("{\"result\":".json_encode((object)["type"=>"error","text"=>$text],JSON_UNESCAPED_UNICODE)."}");
                }
            }
            else{
                die("{\"result\":".json_encode((object)["type"=>"error","text"=>"Пустой номер телефона"],JSON_UNESCAPED_UNICODE)."}");
            }

        }
        catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    function sendCall($phone){
        try{
            include_once ($_SERVER['DOCUMENT_ROOT'] .'/components/com_gm_ceiling/smsc_api.php');
            return callRequest($phone);
        }
        catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    function verifyCodeBAU(){
        try{
            $result = (object)array("type"=>"","data"=>"");
            $data = $_POST;
            if(!empty($data)){
                $userId = $data['id'];
                $code = $data['code'];
            }
            else{
                $jinput = JFactory::getApplication()->input;
                $userId = $jinput->getInt('id');
                $code = $jinput->getInt('code');
            }

            $userModel = Gm_ceilingHelpersGm_ceiling::getModel('users');
            $user = JFactory::getUser($userId);
            if(intval($user->verification_code) == intval($code)){
                $result->type = 'success';
                $result->data = $user->associated_client;
                $userModel->setVerificationCode($user->id,null);
                Gm_ceilingHelpersGm_ceiling::forceLogin($user->id);
            }
            else{
                $result->type = 'error';
                $result->data = 'Введен неверный код!';
            }

            die("{\"result\":".json_encode($result,JSON_UNESCAPED_UNICODE)."}");
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    function authorisationBAU(){
        try{
            $result = (object)["type"=>"","data"=>""];
            $data = $_POST;
            if(!empty($data)){
                $phone = $data['phone'];
                $password = $data['password'];
            }
            else{
                $jinput = JFactory::getApplication()->input;
                $phone = $jinput->getString('phone');
                $password = $jinput->getString('password');
            }
            if(empty($phone)){
                $result->type = 'error';
                $result->data = 'Пустой логин';
                die("{\"result\":".json_encode($result,JSON_UNESCAPED_UNICODE)."}");
            }
            if(empty($password)){
                $result->type = 'error';
                $result->data = 'Пустой пароль';
                die("{\"result\":".json_encode($result,JSON_UNESCAPED_UNICODE)."}");
            }
            $password = trim($password);
            $username = mb_ereg_replace('[^\d]', '', $phone);
            if (mb_substr($username, 0, 1) == '9' && strlen($username) == 10)
            {
                $username = '7'.$username;
            }
            if (strlen($username) != 11)
            {
                $result->type = 'error';
                $result->data = 'Неверный формат номера телефона';
                die("{\"result\":".json_encode($result,JSON_UNESCAPED_UNICODE)."}");
            }
            if (mb_substr($username, 0, 1) != '7')
            {
                $username = substr_replace($username, '7', 0, 1);
            }
            $usersModel = Gm_ceilingHelpersGm_ceiling::getModel('users');
            $user = JFactory::getUser($usersModel->getUserByUsername($username)->id);
            $verifyPass = JUserHelper::verifyPassword($password, $user->password, $user->id);
            if ($verifyPass) {
                $result->type = 'success';
                $result->data = $user->associated_client;
                die("{\"result\":".json_encode($result,JSON_UNESCAPED_UNICODE)."}");
            }
            else{
                $result->type = 'error';
                $result->data = 'Неверный логин или пароль';
                die("{\"result\":".json_encode($result,JSON_UNESCAPED_UNICODE)."}");
            }
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    function acceprFromQuiz(){
        try{
            $data = json_decode(file_get_contents('php://input'), true);

            $answers = $data['answers'];
            $phone = $data['contacts']['phone'];
            $email = $data['contacts']['email'];
            $name = $data['contacts']['name'];
            $createdDate = strtotime($data['created']);
            $utm = $data['extra']['utm'];
            $rawAnswers = $data['raw'];
            $answerStr = '';
            foreach ($answers as $key=>$value){
                $answerStr .= $value['q'].' : ';
                $answerStr .= $value['a'].'; <br>';
            }
            /*Форматируем номер телефона*/
            $phone = mb_ereg_replace('[^\d]', '', $phone);
            if (mb_substr($phone, 0, 1) == '9' && strlen($phone) == 10)
            {
                $phone = '7'.$phone;
            }
            if (strlen($phone) != 11)
            {
                throw new Exception('Неверный формат номера телефона.');
            }
            if (mb_substr($phone, 0, 1) != '7')
            {
                $phone = substr_replace($phone, '7', 0, 1);
            }
            /*-------*/

            $clientform_model = Gm_ceilingHelpersGm_ceiling::getModel('clientform');
            $clienthistory_model = Gm_ceilingHelpersGm_ceiling::getModel('client_history');
            $callback_model = Gm_ceilingHelpersGm_ceiling::getModel('callback');
            $clientsphones_model = Gm_ceilingHelpersGm_ceiling::getModel('client_phones');
            $clientsDopContactsModel = Gm_ceilingHelpersGm_ceiling::getModel('clients_dop_contacts');
            $clientData['client_name'] = $name;
            $clientData['client_contacts'] = $phone;
            $clientData['dealer_id'] = 1;
            $clientData['manager_id'] = 1;
            $result = $clientform_model->save($clientData);

            if (mb_ereg('[\d]', $result)) {
                $clienthistory_model->save($result, 'Клиент создан автоматически после прохождения теста');
            } else {
                $client = $clientsphones_model->getItemsByPhoneNumber($phone, 1);
                $clienthistory_model->save($client->id, 'Существующий клиент прошел тест');
                $result = $client->id;
            }
            $callback_model->save(date("Y-m-d H:i:s"), 'Клиент прошел тест', $result, 1);
            if(!empty($email)){
                $clientsDopContactsModel->save($result,1,$email);
            }
            $clienthistory_model->save($result, $answerStr);
            http_response_code(200);
            exit;
        }
        catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    function test(){
       /* $model_mount = Gm_ceilingHelpersGm_ceiling::getModel('mount');
        $servicePrice = $model_mount->getServicePrice();
        $data = [];
        foreach ($servicePrice as $price){
            $data[] = "$price->id,'$price->price',1";
        }
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query
            ->insert('`rgzbn_gm_ceiling_jobs_service_price`')
            ->columns('`job_id`,`price`,`dealer_id`')
            ->values($data);
        $db->setQuery($query);
        $db->execute();*/
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query
            ->select('id')
            ->from('`rgzbn_gm_ceiling_projects`')
            ->where('project_status = 11');
        $db->setQuery($query);
        $ids = $db->loadObjectList();
        foreach ($ids as $id) {
            Gm_ceilingHelpersGm_ceiling::createImgArchive($id->id);
        }
    }
    function duplicate(){
        $from = [64886,64887,64888,64889,64890,64891,64892,64893,64894];
        $to = [65234,65235,65236,65237,65238,65239,65240,65241,65242];
        $projectsModel = Gm_ceilingHelpersGm_ceiling::getModel('projects');
        $calcModel = Gm_ceilingHelpersGm_ceiling::getModel('calculation');
        $calculationsModel = Gm_ceilingHelpersGm_ceiling::getModel('calculations');
        $calcsMountModel = Gm_ceilingHelpersGm_ceiling::getModel('calcs_mount');
        $canvasesModel = Gm_ceilingHelpersGm_ceiling::getModel('canvases');
        foreach ($from as $key => $value) {
            $fromProjects = $projectsModel->getClientsProjects($value);
            $toDupProjects = $projectsModel->getClientsProjects($to[$key]);
            foreach ($fromProjects as $proj) {
                $calcsId = $calculationsModel->getIdsByProjectId($proj->id);
                foreach ($calcsId as $calcId) {
                    $calcData = $calcModel->new_getData($calcId->id);
                    unset($calcData->id);
                    $calcData = get_object_vars($calcData);
                    $mountData = $calcsMountModel->getData($calcId->id);
                    $calcData['mountData'] = $mountData;
                    foreach ($toDupProjects as $dupProj) {
                        if ($dupProj->project_info == $proj->project_info) {
                            $calcData['canvas_area'] = $canvasesModel->getCutsData($calcId->id);
                            $calcData['project_id'] = $dupProj->id;
                            $newCalcId = $calcModel->duplicate($calcData);

                            $oldFileName = md5('calculation_sketch' . $calcId->id);
                            $oldImage = $_SERVER['DOCUMENT_ROOT'] . "/calculation_images/$oldFileName.svg";
                            $newFileName = md5('calculation_sketch' . $newCalcId);
                            $newImage = $_SERVER['DOCUMENT_ROOT'] . "/calculation_images/$newFileName.svg";
                            copy($oldImage, $newImage);
                             //раскрой
                            $oldCutFileName = md5('cut_sketch' . $calcId->id);
                            $oldCutImage = $_SERVER['DOCUMENT_ROOT'] . "/cut_images/$oldCutFileName.svg";
                            $newCutFileName = md5('cut_sketch' . $newCalcId);
                            $newCutImage = $_SERVER['DOCUMENT_ROOT'] . "/cut_images/$newCutFileName.svg";
                            copy($oldCutImage, $newCutImage);
                        }
                    }
                }
            }
        }
    }

    function getInfoImg(){
        $dir = '/uploaded_calc_images';
        $files = glob($dir."/*.*",GLOB_NOSORT);
        do{
            $dir = $dir."/*";
            $files2 = glob($dir."/*.*",GLOB_NOSORT);

            $files = array_merge($files,$files2);
        }while(sizeof($files2)>0);

        throw new Exception(print_r($files,true));
    }

}

?>
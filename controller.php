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
            $start = $app->input->getInt('start', 0);

            /*if ($start == 0)
            {
                $app->input->set('limitstart', 0);
            }*/

            $view = $app->input->getCmd('view', 'components');
            $task = $app->input->getCmd('task', 'components');
            $subtype = $app->input->getCmd('subtype', NULL);


            $app->input->set('subtype', $subtype);
            $type = $app->input->getCmd('type', NULL);

            if ($type == NULL) {
                $user = JFactory::getUser();
                $groups = $user->get('groups');
                $_SESSION['user_group'] = $groups;
                $_SESSION['dealer_type'] = $user->dealer_type;
                if ($task == "mainpage") {
                    if (!$user->guest) {
                        if (in_array("13", $groups)) {
                            $type = "managermainpage"; //Менеджер дилера
                        } elseif (in_array("21", $groups)) {
                            $type = "calculatormainpage"; //Замерщик дилера
                        } elseif (in_array("12", $groups)) {
                            $type = "chiefmainpage"; //Начальник МС дилера
                        } elseif (in_array("14", $groups)) {
                            $type = "dealermainpage"; //Дилер
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
                        if (!empty($type)) {
                            $this->setRedirect(JRoute::_('index.php?option=com_gm_ceiling&view=mainpage&type=' . $type, false));
                            $app->input->set('type', $type);
                        } else {
                            $this->setRedirect(JRoute::_('index.php', false));
                        }
                    } else {
                        $this->setRedirect(JRoute::_('index.php?option=com_users&view=login', false));
                    }
                }
            }
            $app->input->set('view', $view);

            parent::display($cachable, $urlparams);

            return $this;
        }
        catch(Exception $e)
        {
            add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

//KM_CHANGED START

    /* Функция для AJAX-изменения комментария бухгалтера. */
    public function change_buh_note()
    {
        try
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
            add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
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
            add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
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
            add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
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
            add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    /* функция для AJAX-сохранения дополнительных затрат по договору */
    public function add_spend()
    {
        try
        {
            $jinput = JFactory::getApplication()->input;
        
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
    
            die($result);
        }
       catch(Exception $e)
        {
            add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function addPhoneToClient()
    {
        $user = JFactory::getUser();
        $user_group = $user->groups;
        if(array_search('16', $user_group))
        {
            try
            {
                $jinput = JFactory::getApplication()->input;
                $id = $jinput->get('id', '', 'INT');
                $phones = $jinput->get('phones', '', 'ARRAY');
                $comments_string = $jinput->get('comments', '', 'STRING');
                $cl_history = Gm_ceilingHelpersGm_ceiling::getModel('client_history');
                if (!empty($comments_string))
                    $comments_id = explode(";", $comments_string);
                array_pop($comments_id);
                if (count($comments_id) != 0) {
                    $cl_history->updateClientId($id, $comments_id);
                }
                $project_id = $jinput->get('p_id', '', 'INT');
                $client_model = Gm_ceilingHelpersGm_ceiling::getModel('client_phones');
                $cl_phones = $client_model->save($id, $phones);
                $project_model = Gm_ceilingHelpersGm_ceiling::getModel('project');
                $cl_history->save($id, "Клиент звонил с нового номера");
                $project_model->delete($project_id);
                die(true); 
            }
            catch(Exception $e)
            {
                add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());

            }           
        }
        else
        {
            throw new Exception("Forbidden", 403);
        }
    }

    public function findOldClients()
    {
        try
        {
            $jinput = JFactory::getApplication()->input;
            $FIO = $jinput->get('fio', '', 'STRING');
            $flag = $jinput->get('flag', 'clients', 'STRING');
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
                $result = $clients_model->getDesignersByClientName($FIO, 3);
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
        }
       catch(Exception $e)
        {
            add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
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
            add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
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
            add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }
    /* public function update_old_mnfct(){
            $clientform_model =Gm_ceilingHelpersGm_ceiling::getModel('ClientForm', 'Gm_ceilingModel');
            $clients_dop_contacts = Gm_ceilingHelpersGm_ceiling::getModel('clients_dop_contacts');
            $user_model = Gm_ceilingHelpersGm_ceiling::getModel('users');
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);
            $query->select('`u`.`id`,`u`.`name`,u.email,u.username');
            $query->from('`#__users` AS `u`');
            $query->where('`dealer_type` = 6 AND ISNULL(`associated_client`)');
            $db->setQuery($query);
            $item = $db->loadObjectList();
            foreach ($item as $dealer) {
                $client_data['client_name'] = $dealer->name;
                $client_data['client_contacts'] = $dealer->username;
                $client_data['dealer_id'] = $dealer->id;
                $client_id = $clientform_model->save($client_data);
                $clients_dop_contacts->save($client_id,1,$dealer->email);
                $user_model->updateAssocClient($dealer->id,$client_id);
            }

        }*/

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
            add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
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
            $data = array(
               "name" => $FIO,
               "username" => $login,
               "password" => $password,
               "password2" => $password2,
               "email" => $email,
               "groups" => array(2, 14),
               "phone" => $phone,
               "block" => 0,
               "dealer_type" => 2
            );
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
            add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
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
            add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
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
            $model_client->updateClient($client_id,$new_fio);
            $user_model->updateUserNameByAssociatedClient($client_id, $new_fio);
            $history_model = Gm_ceilingHelpersGm_ceiling::getModel('client_history');
            $history_model->save($client_id,"Изменено ФИО пользователя");
            die($new_fio);
        }
       catch(Exception $e)
        {
            add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
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
            add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
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
                throw new Exception();
            }
            $model = Gm_ceilingHelpersGm_ceiling::getModel($model_name);
            $model->updateDealerId($client_id,$dealer_id,$project_id);
        }
       catch(Exception $e)
        {
            add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
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
            add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
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
            add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
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
            add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function createProject($client_id, $api_phone_id, $project_info, $project_calculation_date)
    {
        try
        {
            $info_model = Gm_ceilingHelpersGm_ceiling::getModel('dealer_info');
            $gm_canvases_margin = $info_model->getMargin('gm_canvases_margin', 1);
            $gm_components_margin = $info_model->getMargin('gm_components_margin', 1);
            $gm_mounting_margin = $info_model->getMargin('gm_mounting_margin', 1);
            $dealer_canvases_margin = $info_model->getMargin('dealer_canvases_margin', 1);
            $dealer_components_margin = $info_model->getMargin('dealer_components_margin', 1);
            $dealer_mounting_margin = $info_model->getMargin('dealer_mounting_margin', 1);
            $project_model = Gm_ceilingHelpersGm_ceiling::getModel('projectform');
            $project_data['state'] = 1;
            $project_data['client_id'] = $client_id;
            $project_data['project_info'] = $project_info;
            $project_data['project_status'] = 0;
            $project_data['project_calculation_daypart'] = 0;
            $project_data['project_calculation_date'] = $project_calculation_date;
            $project_data['project_mounting_date'] = "00.00.0000";
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
            add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function getDataFromPromo()
    {
        try
        {
            $fromDomain = "gm-vrn.ru";
            header('Access-Control-Allow-Origin: http://' . $fromDomain);
            header('Access-Control-Allow-Credentials: true');
            header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
            header('Access-Control-Allow-Headers: Content-Type');
            header('Access-Control-Max-Age: 1000');
            header('Access-Control-Allow-Headers: Content-Type, Content-Range, Content-Disposition, Content-Description');

            $jinput = JFactory::getApplication()->input;
            $name = $jinput->get('name', 'Клиент с promo', 'STRING');
            $phones[] = $jinput->get('phone', '', 'STRING');
            $phones[0] = preg_replace('/[\(\)\-\+\s]/', '', $phones[0]);
            $email = $jinput->get('email', '', 'STRING');
            $action = $jinput->get('action', '', 'STRING');
            $api_phone_id = $jinput->get('api_phone_id', 0, 'INT');
            $adress = $jinput->get('adress', '', 'STRING');
            $date = $jinput->get('date', '0000-00-00', 'STRING');
            $time = $jinput->get('time', '00:00', 'STRING');
            $date_time = $date . ' ' . $time;
            $model = Gm_ceilingHelpersGm_ceiling::getModel('client_phones');
            $result = $model->getItemsByPhoneNumber($phones[0], 1);
            $from_promo_model = Gm_ceilingHelpersGm_ceiling::getModel('requestfrompromo');
            $dop_contacts_model = Gm_ceilingHelpersGm_ceiling::getModel('clients_dop_contacts');
            /*проверка на существование этого клиента*/
            if (empty($result)) {
                //регистрация клиента
                $client_model = Gm_ceilingHelpersGm_ceiling::getModel('ClientForm');
                $client_data['client_name'] = $name;
                $client_data['type_id'] = 1;
                $client_data['dealer_id'] = 1;//GM
                $client_data['created'] = date("Y-m-d");
                $client_id = $client_model->save($client_data);
                //добавляем номер телефона
                $cl_phones_model = Gm_ceilingHelpersGm_ceiling::getModel('Client_phones');
                $cl_phones_model->save($client_id, $phones);
                $proj_id = $this->createProject($client_id, $api_phone_id, $adress, $date_time);
            } else {
                $client_id = $result->client_id;
                $pr_model = Gm_ceilingHelpersGm_ceiling::getModel('project');
                $projects = $pr_model->getProjectsByClientID($client_id);
                if ($adress != '' && $date_time != '0000-00-00 00:00') {
                    if (count($projects) > 0) {
                        $proj_id = $this->createProject($client_id, 10, $adress, $date_time);
                        $repeat_model = Gm_ceilingHelpersGm_ceiling::getModel('repeatrequest');
                        $repeat_model->save($proj_id, $api_phone_id);
                    } else {
                        $proj_id = $this->createProject($client_id, $api_phone_id, $adress, $date_time);
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
                            $proj_id = $this->createProject($client_id, 10, $adress, $date_time);
                            $repeat_model = Gm_ceilingHelpersGm_ceiling::getModel('repeatrequest');
                            $repeat_model->save($proj_id, $api_phone_id);
                        } else {
                            $proj_id = $this->createProject($client_id, $api_phone_id, $adress, $date_time);
                        }
                    }
                }
            }
            if ($email != "") {
                $dop_contacts_model->save($client_id, 1, $email);
            }
            $api_phones_model = Gm_ceilingHelpersGm_ceiling::getModel('api_phones');
            $advt_name = $api_phones_model->getDataById($api_phone_id)->name;
            $history_model = Gm_ceilingHelpersGm_ceiling::getModel('client_history');
            $history_model->save($client_id, "Произведено действие на сайте: \"$action\" ($advt_name)");
            $from_promo_model->save($action, $client_id);
            die(true);
        }
       catch(Exception $e)
        {
            add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function addCall()
    {
        try
        {
            $user = JFactory::getUser();
            $jinput = JFactory::getApplication()->input;
            $jdate = new JDate($jinput->get('date', '01.01.1970', 'STRING'));
            $id_client = $jinput->get('id_client', '0', 'INT');
            $manager_id = $user->id;
            $comment = $jinput->get('comment', '', 'STRING');

            $callback_model = Gm_ceilingHelpersGm_ceiling::getModel('callback');
            $result = $callback_model->save($jdate, $comment, $id_client, $manager_id);

            die($result);
        }
       catch(Exception $e)
        {
            add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function delCall($call_id)
    {
        try
        {
            $jinput = JFactory::getApplication()->input;
            $call_id = $jinput->get('call_id', 0, 'INT');
            $callback_model = Gm_ceilingHelpersGm_ceiling::getModel('callback');
            $result = $callback_model->deleteCall($call_id);

            die(true);
        }
       catch(Exception $e)
        {
            add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
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
            add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
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
            add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
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
            add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
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
            add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
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
            add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
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
            add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
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
                    $filter = '(`component_id` = 19 OR `component_id` = 20)';
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
                                                'title' => 'Круглая вентиляция',
                                            );
                    $items->n22_type[] = array(
                                                'id' => 6,
                                                'title' => 'Квадратная вентиляция',
                                            );
                    $items->n22_type[] = array(
                                                'id' => 7,
                                                'title' => 'Круглая электровытяжка',
                                            );
                    $items->n22_type[] = array(
                                                'id' => 8,
                                                'title' => 'Квадратная электровытяжка',
                                            );
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

                    break;
            }
            
            die(json_encode($items));
        }
       catch(Exception $e)
        {
            add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
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
            $need_mount = $jinput->get('need_mount', '0', 'INT');
            
            $del_flag = $jinput->get('del_flag', '0', 'INT');
           
            $result = Gm_ceilingHelpersGm_ceiling::calculate($from_db, $id, $save, $pdf, $del_flag, $need_mount);
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

    public function payComponents() {
        try
        {
            $user = JFactory::getUser();
            $user->getDealerInfo();

            $jinput = JFactory::getApplication()->input;

            $DATA = (object) [];

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
                $project_data['project_mounting_date'] = "00.00.0000";
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
            $this->setRedirect(JRoute::_('http://test1.gm-vrn.ru/index.php?option=com_gm_ceiling&view=project&type=calculator&subtype=project&id='.$ID_PROJECT, false));
        }
       catch(Exception $e)
        {
            add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
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

            $data->client_id = $client_id;
            $data->date_time = $date_time;
            $data->comment = $comment;
            $data->manager_name = $manager_name;
            $data->email = $email;
            $result = Gm_ceilingHelpersGm_ceiling::notify($data, $type);

            $model_call = Gm_ceilingHelpersGm_ceiling::getModel('callback');
            $items = $model_call->updateNotify($id);
            
            die(true);
        }
       catch(Exception $e)
        {
            add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
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
            add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    /*  функция AJAX-отображения календаря монтажей
        смотреть дальше функцию draw_calendar в файле /helpers/gm_ceiling.php
    */
    public function update_calendar()
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
            add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function UpdateCalendarTar() {
        try
        {
            $id = $_POST["id"];
            $month = $_POST["month"];
            $year = $_POST["year"];
            $flag1 = $_POST["flag"];
            $flag2 = $_POST["id_dealer"];
            $flag = [$flag1, $flag2];
            $result = Gm_ceilingHelpersGm_ceiling::DrawCalendarTar($id, $month, $year, $flag);
            die($result);
        }
       catch(Exception $e)
        {
            add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    /*  функция AJAX-отображения календаря монтажей
        смотреть дальше функцию draw_calendar2 в файле /helpers/gm_ceiling.php
    */
    public function update_calendar2()
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
            add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function getClientByPhone()
    {
        try
        {
            $user = JFactory::getUser();

            $jinput = JFactory::getApplication()->input;
            $number = $jinput->get('phone', '', 'STRING');
            $number = preg_replace('/[\(\)\-\+\s]/', '', $number);
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
            add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function getProjectsByPhone()
    {
        try
        {
            $user = JFactory::getUser();

            $jinput = JFactory::getApplication()->input;
            $number = $jinput->get('phone', '', 'STRING');
            $number = preg_replace('/[\(\)\-\+\s]/', '', $number);
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
            add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
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
            add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
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
            add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
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
            $body = "Здравствуйте. К этому письму прикрепленны pdf-файлы с информацией по потолкам.";
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
            add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
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
            } else {
                $project_data['client_id'] = $id;
            }
            if(!empty($api_phone_id)){
                $project_data['api_phone_id'] = $api_phone_id;
            }
            $project_data['project_info'] = "";
            $project_data['project_status'] = 0;
            $project_data['project_calculation_daypart'] = 0;
            $project_data['project_calculation_date'] = "0000-00-00 00:00";

            $project_data['project_mounting_date'] = "00.00.0000";
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
            add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    //KM_CHANGED END


    public static function createColorImage()
    {
        try
        {
            $width = 150;
            $height = 110;
            $jinput = JFactory::getApplication()->input;
            $color_code = $jinput->get('code', '', 'STRING');
            $glyanec = $jinput->get('glyanec', '', 'INT');
            $satin = $jinput->get('satin', '', 'INT');
            $name = $jinput->get('col_name', '', 'STRING');

            $red = hexdec(substr($color_code, 0, 2));
            $green = hexdec(substr($color_code, 2, 2));
            $blue = hexdec(substr($color_code, 4, 2));


            $img = imagecreatetruecolor($width, $height) or die("Ошибка");
            $color = imagecolorallocate($img, $red, $green, $blue);

            imagefill($img, 0, 0, $color);
            $filename = $name . "mat.png";
            if ($satin) {
                $filename = $name . 'sat.png';
            }
            if ($glyanec) {
                $gl = imagecreatefrompng($_SERVER['DOCUMENT_ROOT'] . '/images/glyanec.png');
                imagecopy($img, $gl, 0, 0, 0, 0, $width, $height);
                $filename = $name . 'glan.png';
            }
            imagepng($img, $_SERVER['DOCUMENT_ROOT'] . '/images/canvases/' . $filename);
            $result = '/images/canvases/' . $filename . '?' . rand();

            imagedestroy($img);
            die($result);
        }
       catch(Exception $e)
        {
            add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
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
                    $locate = "http://test.gm-vrn.ru/index.php?option=com_gm_ceiling&view=canvases";
                    $margin = $DealerInfo->dealer_canvases_margin;
                    break;
                case 2:
                    $locate = "http://test.gm-vrn.ru/index.php?option=com_gm_ceiling&view=components";
                    $margin = $DealerInfo->dealer_components_margin;
                    break;
                case 3:
                    $locate = "http://test.gm-vrn.ru/index.php?option=com_gm_ceiling&view=mount";
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

            if ($result != null) exit;
            else {
                echo json_encode(array("answer_error" => "Ошибка ввода маржинальности!"));
            }

            exit;
        }
       catch(Exception $e)
        {
            add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
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
            add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
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
            add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
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
            add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
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
            add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
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
            add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }

    }

    public function addClient()
    {
        try{
            $jinput = JFactory::getApplication()->input;
            $id = $jinput->get('id', null, 'INT');
            $fio = $jinput->get('fio', null, 'STRING');
            $phone = $jinput->get('phone', null, 'STRING');
            $adress = $jinput->get('adress', null, 'STRING');
            $project_calc_date = $jinput->get('project_calc_date', '00.00.0000', 'DATE');
            $new_project_calculation_daypart = $jinput->get('new_project_calculation_daypart', null, 'STRING');

            $project_calculation_date = $project_calc_date." ".$new_project_calculation_daypart;
            $model = Gm_ceilingHelpersGm_ceiling::getModel('client');
            $model->updateClientNew($id, $fio, $phone,$adress, $project_calculation_date );
            $this->setRedirect(JRoute::_('http://test1.gm-vrn.ru/components/com_gm_ceiling/views/saverclient/default_1.php?complite=1&id='.$id, false));
            return 1;
        }
       catch(Exception $e)
        {
            add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function getClientInfoApi()
    {
        try{
            $jinput = JFactory::getApplication()->input;
            $id = $jinput->get('id', null, 'INT');
            $model = Gm_ceilingHelpersGm_ceiling::getModel('client');
            $data = $model->getClientInfoApi($id);
            echo json_encode($data);
            exit;
        }
       catch(Exception $e)
        {
            add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function getAnaliticByPeriod()
    {
        try{
            $jinput = JFactory::getApplication()->input;
            $date1 = $jinput->get('date1','','STRING');
            $date2 = $jinput->get('date2','','STRING');
            $dealer_id = $jinput->get('dealer_id','','STRING');
            $analitic_model = Gm_ceilingHelpersGm_ceiling::getModel('analiticcommon');
            $data = $analitic_model->getDataByPeriod($date1,$date2,$dealer_id);
            die(json_encode($data));
        }
       catch(Exception $e)
        {
            add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }
    public function getDetailedAnaliticByPeriod()
    {
        try{
            $jinput = JFactory::getApplication()->input;
            $date1 = $jinput->get('date1','','STRING');
            $date2 = $jinput->get('date2','','STRING');
             $dealer_id = $jinput->get('dealer_id','','STRING');
            $analitic_model = Gm_ceilingHelpersGm_ceiling::getModel('analiticdetailed');
            $data = $analitic_model->getData($date1,$date2,$dealer_id);
            die(json_encode($data));
        }
       catch(Exception $e)
        {
            add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
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
            add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }
    public function getAnaliticProjects(){
        try{
            $jinput = JFactory::getApplication()->input;
            $type = $jinput->get('type','','STRING');
            $advt_name = $jinput->get('advt','','STRING');
            $statuses = $jinput->get('statuses','','STRING');
            $date1 = $jinput->get('date1','','STRING');
            $date2 = $jinput->get('date2','','STRING');
            $dealer_id = $jinput->get('dealer_id','','STRING');
            $phones_model = Gm_ceilingHelpersGm_ceiling::getModel('api_phones');
            $advt_id = $phones_model->getIdByName($advt_name);
            if(empty($advt_id)){
                $advt_id = $advt_name;
            }
            $project_model = Gm_ceilingHelpersGm_ceiling::getModel('projects');
            if($type == 0){
                if(empty($date1)&&empty($date2)){
                    $projects = $project_model->getDataByStatusAndAdvt($dealer_id,$advt_id,$statuses);
                }
                else{
                    $projects = $project_model->getDataByStatusAndAdvt($dealer_id,$advt_id,$statuses,$date1,$date2);
                };
            }
            if($type == 1){
                $project_history = Gm_ceilingHelpersGm_ceiling::getModel('Projectshistory');
                $projects = $project_history->getIdsByStatusAndAdvt($dealer_id,$advt_id,$statuses,$date1,$date2);
                
            }
            die(json_encode($projects));       
            

        }
       catch(Exception $e)
        {
            add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
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
            add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
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
            add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
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
            add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
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
            add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
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
           add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());

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
           add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());

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
           add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());

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
           add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());

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
           add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());

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
           add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());

        }
    }


   public function sendCommercialOffer($user_id = null, $email = null, $dealer_type = null, $type = null){
        try
        {
            $user = JFactory::getUser();
            $groups = $user->get('groups');
            if (in_array("16", $groups))
            {
                if (is_null($user_id) || is_null($email) || is_null($dealer_type) || is_null($type))
                {
                    $jinput = JFactory::getApplication()->input;
                    $user_id = $jinput->get('user_id', null, 'INT');
                    $email = $jinput->get('email', null, 'STRING');
                    $dealer_type = $jinput->get('dealer_type', null, 'STRING');
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
                $server_name = $_SERVER['SERVER_NAME'];
                $site = "http://$server_name/index.php?option=com_gm_ceiling&task=big_smeta.commercialOffer&code=$code";
                $site2 = "http://$server_name/index.php?option=com_gm_ceiling&task=big_smeta.dealerInstruction&short=1&code=$code_instruction";
                $site3 = "http://$server_name/index.php?option=com_gm_ceiling&task=big_smeta.dealerInstruction&short=2&code=$code_quick";
                $site4 = $site = "http://$server_name/index.php?option=com_gm_ceiling&task=big_smeta.commercialOffer&code=$code&type=1";
                $site5 = "http://$server_name/index.php?option=com_gm_ceiling&task=big_smeta.commercialOffer&code=$code&type=1";
                $site6 = "http://$server_name/index.php?option=com_gm_ceiling&task=big_smeta.commercialOffer&code=$code&type=0";
                $site_dev = "http://$server_name/index.php?option=com_gm_ceiling&task=big_smeta.commercialOffer&code=$code";
                $site_errors_mount = "http://$server_name/index.php?option=com_gm_ceiling&task=big_smeta.commercialOffer&code=$code&type=2";
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
                $body .= '<tr><td style="vertical-align:middle;"><a href="test1.gm-vrn.ru/">';
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
                    $body .= "<div style=\"width: 100%\">
                        <a href=\"$site_dev\"><img style=\"width: 20%\" src=\"http://".$server_name."/images/KP_DEV.jpg\"></a><br>
                        <a href=\"$site_dev\">Коммерческое предложение</a><br>";
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
                    $mailer->setSubject('Натяжные потолки для застройщиков от 280 руб.');
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
           add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());

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
            $body .= '<tr><td style="vertical-align:middle;"><a href="test1.gm-vrn.ru/">';
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
           add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());

        }
    }


    //вызов из урл
    //не удалять
    /*public function Send_all_from_url(){
        try
        {
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);
            $query->select('`u`.`id`,`u`.`name`,`u`.`associated_client`,`c`.created,GROUP_CONCAT(`b`.`phone` SEPARATOR \', \') AS `client_contacts`,`u`.`refused_to_cooperate`, `u`.`dealer_type`');
            $query->from('`#__users` AS `u`');
            $query->innerJoin('`#__gm_ceiling_clients` AS `c` ON `u`.`associated_client` = `c`.`id`');
            $query->leftJoin('`#__gm_ceiling_clients_contacts` AS `b` ON `c`.`id` = `b`.`client_id`');
            $query->where('`dealer_type` = 1');
            $query->group('`id`');
            $query->order('`id` DESC');
            $db->setQuery($query);
            $items = $db->loadObjectList();

            $count = 0;

            $dop_contacts_model = Gm_ceilingHelpersGm_ceiling::getModel('clients_dop_contacts');
            foreach ($items as $i => $item)
            {
                $client_id = $item->associated_client;
                if (!empty($client_id))
                {
                    $emails = $dop_contacts_model->getEmailByClientID($client_id);
                    foreach ($emails as $j => $email)
                    {
                        $this->sendCommercialOffer($item->id, $email->contact, 1, 2);
                        echo "$item->name $email->contact $item->dealer_type<br>";
                        $count++;
                    }
                }
            }
            echo $count;
            exit();
        }
        catch (Exception $e) {
           add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());

        }
    }*/


    public function RepeatSendCommercialOffer(){
        /*try
        {
            $user = JFactory::getUser();
            $groups = $user->get('groups');
            if (in_array("16", $groups))
            {
                $users_model = Gm_ceilingHelpersGm_ceiling::getModel('users');
                $items = $users_model->findNotViewCommercialOfferAfterWeek();
                $count = 0;

                $dop_contacts_model = Gm_ceilingHelpersGm_ceiling::getModel('clients_dop_contacts');
                foreach ($items as $i => $item)
                {
                    $client_id = JFactory::getUser($item->user_id)->associated_client;
                    $dealer_type = JFactory::getUser($item->user_id)->dealer_type;
                    $emails = $dop_contacts_model->getEmailByClientID($client_id);
                    foreach ($emails as $j => $email)
                    {
                        $this->sendCommercialOffer($item->user_id, $email->contact, $dealer_type);
                        $count++;
                    }
                }

                die(json_encode($count));
            }
            else
            {
                throw new Exception('Not GmManager', 403);
            }
        }
        catch (Exception $e) {
           add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());

        }*/
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
                $body .= '<tr><td style="vertical-align:middle;"><a href="test1.gm-vrn.ru/">';
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
           add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());

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
           add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());

        }
    }

    public function createPdfs(){
        try{
            $jinput = JFactory::getApplication()->input;
            $project_id = $jinput->get('id','','INT');
           /*throw new Exception($project_id);*/
            $calculations_model = Gm_ceilingHelpersGm_ceiling::getModel('calculations');
            $calculations = $calculations_model->new_getProjectItems($project_id);
            foreach($calculations as $calc){
                $calculation_data["extra_mounting_array"] = array();
                foreach (json_decode($calc->extra_mounting) as $extra_mounting){
                    $calculation_data["extra_mounting_array"][] = $extra_mounting;
                }

                $calculation_data["need_mount_extra"] = !empty($calculation_data["extra_mounting_array"]);

                if (floatval($calc->mounting_sum) == 0)
                    $need_mount = 0;
                else if (!$calculation_data["need_mount_extra"])
                    $need_mount = 1;
                else {
                    $need_mount = 0;
                    $first = Gm_ceilingHelpersGm_ceiling::calculate_mount(0, $calc->id);
                    $first = round($first["total_gm_mounting"], 0);

                    if ($first == floatval($calc->mounting_sum))
                        $need_mount = 0;
                    else
                        $need_mount = 1;
                }
                if(!empty($calc->n3)){
                    Gm_ceilingHelpersGm_ceiling::create_cut_pdf($calc->id);
                    Gm_ceilingHelpersGm_ceiling::create_client_single_estimate($need_mount,$calc->id);
                    Gm_ceilingHelpersGm_ceiling::create_manager_estimate(1,$calc->id);
                    Gm_ceilingHelpersGm_ceiling::create_single_mount_estimate($calc->id);  
                }
            }
            Gm_ceilingHelpersGm_ceiling::create_common_estimate_mounters($project_id);
            Gm_ceilingHelpersGm_ceiling::create_client_common_estimate($project_id);
            Gm_ceilingHelpersGm_ceiling::create_estimate_of_consumables($project_id);
            Gm_ceilingHelpersGm_ceiling::create_common_manager_estimate($project_id);
            Gm_ceilingHelpersGm_ceiling::create_common_cut_pdf($project_id);

            die(json_encode(true));
        }
        catch (Exception $e) {
           add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());

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
           add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());

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
           add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());

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
           add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());

        }
    }
    public function printZvonkiOnGmMainPage(){
        try
        {
            $model = Gm_ceilingHelpersGm_ceiling::getModel('projects');
            // звонки
            $date = date("Y")."-".date("n")."-".date("d");
            $answer4 = $model->getDataByStatus("Zvonki", $date);
            die(json_encode($answer4));
        }
        catch (Exception $e) {
           add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());

        }
    }
    public function printMissedCallsOnGmMainPage(){
        try
        {
            $model = Gm_ceilingHelpersGm_ceiling::getModel('projects');
            $date = date("Y")."-".date("n")."-".date("d");
            // пропущенные
            $answer5 = Gm_ceilingController::missedCalls($date, "missed", 1);
            $answer6 = $model->getDataByStatus("MissedCalls");
            $missAnswer1 = [];
            $missAnswer2 = [];
            foreach ($answer5 as $value) {
                array_push($missAnswer1, $value->id);
            }
            foreach ($answer6 as $value) {
                array_push($missAnswer2, $value->call_id);
            }
            $answer7 = array_diff($missAnswer1, $missAnswer2);
            die(json_encode(count($answer7)));
        }
        catch (Exception $e) {
           add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());

        }
    }

    public function UserRefuseToCooperate()
    {
        try
        {
            $jinput = JFactory::getApplication()->input;
        
            $id = $jinput->get('user_id', null, 'INT');
    
            $user = JFactory::getUser();
            if (!$user->guest) {
                $users_model = Gm_ceilingHelpersGm_ceiling::getModel('users');
                $result = $users_model->refuseToCooperate($id);
            }
    
            die($result);
        }
       catch(Exception $e)
        {
            add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
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
            add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
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
            add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());

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
            add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());

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
            add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());

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
            add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());

        }
    }
        
    function acceptFromCall() {
        try
        {
            $files = "components/com_gm_ceiling/";
            file_put_contents($files.'calls.txt', json_encode($_POST)."\n----------\n", FILE_APPEND);
            if (!empty($_POST['phone'])) {
                file_put_contents($files.'calls.txt', json_encode($_POST['phone'])."\n==========\n", FILE_APPEND);
                $clientform_model = Gm_ceilingHelpersGm_ceiling::getModel('clientform');
                $clienthistory_model = Gm_ceilingHelpersGm_ceiling::getModel('client_history');
                $callback_model = Gm_ceilingHelpersGm_ceiling::getModel('callback');
                $clientsphones_model = Gm_ceilingHelpersGm_ceiling::getModel('client_phones');

                $data['client_name'] = 'Клиент с обзвона';
                $data['client_contacts'] = explode('+', $_POST['phone'])[1];
                //die($_POST['phone'].' '.$data['client_contacts']);
                $result = $clientform_model->save($data);
                if (mb_ereg('[\d]', $result)) {
                    $clienthistory_model->save($result, 'Клиент создан автоматически в результате аудиообзвона');
                    $callback_model->save(date("Y-m-d H:i:s"), 'Клиент прослушал сообщение аудиообзвона', $result, 1);
                }
                else
                {
                    $client = $clientsphones_model->getItemsByPhoneNumber($data['client_contacts'], 1);
                    $callback_model->save(date("Y-m-d H:i:s"), 'Клиент прослушал сообщение аудиообзвона', $client->id, 1);
                }
                die(true);
            }
            else {
                die(false);
            }
        }
        catch(Exception $e)
        {
            add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());

        }
    }
}

?>
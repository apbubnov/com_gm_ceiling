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
            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);
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
            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);
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
            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);
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
            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);
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
            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);
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
            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);
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
                $date = date("d.m.Y H:i:s");
                $files = "components/com_gm_ceiling/";
                file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
                throw new Exception('Ошибка!', 500);
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
            $clients_model = Gm_ceilingHelpersGm_ceiling::getModel('clients');
            if ($flag == 'clients')
            {
                $result = $clients_model->getItemsByClientName($FIO);
            }
            elseif ($flag == 'dealers')
            {
                $result = $clients_model->getDealersByClientName($FIO);
            }
            elseif ($flag == 'designers')
            {
                $result = $clients_model->getDesignersByClientName($FIO);
            }

            die(json_encode($result));
        }
        catch(Exception $e)
        {
            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);
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
            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);
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
            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);
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
            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);
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
            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);
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
            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);
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
            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);
        }
    }
    /*public function register_user()
    {
        $jinput = JFactory::getApplication()->input;
        $phone = $jinput->get('phone', '', 'STRING');
        $phone = preg_replace('/[\(\)\-\+\s]/', '', $phone);

        $FIO = $jinput->get('FIO', '', 'STRING');
        $email = $jinput->get('email', '', 'STRING');
        $password = $jinput->get('pass', '', 'STRING');
        $password2 = $jinput->get('pass2', '', 'STRING');
        $login = $jinput->get('login', '', 'STRING');
        if(empty($login)){
            $login = $phone;
        }
        if(empty($password2)){
            $password2 = $password;
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
            $credentials = array('username' => $login, 'password' => $password);
            //В этом массиве параметры авторизации! в данном случае это установка запоминания пользователя
            $options = array('remember' => true);
            //выполняем авторизацию

            $login_site =& JFactory::getApplication('site');
            $login_site->login($credentials, $options = array());
            if (!$login_site->login($credentials, $options = array())) {
                throw new Exception("");
            }

            $user =& JUser::getInstance((int)$userID);
            $post['dealer_id'] = $userID;
            if (!$user->bind($post)) return false;
            if (!$user->save()) return false;
            $margin_model = $this->getModel('dealer_info', 'Gm_ceilingModel');
            $margin_model->save(50,50,60,0,0,0,$userID,0,0);
            //создание клиента
            $client_model = $this->getModel('ClientForm', 'Gm_ceilingModel');
            $client_data['state'] = 1;
            $client_data['created_by'] = $userID;
            $client_data['modified_by'] = $userID;
            $client_data['created'] = date("Y-m-d");
            $client_data['client_name'] = $FIO;
            $client_data['client_contacts'] = $phone;
            $client_data['dealer_id'] = $userID;
            $client_data['manager_id'] = $userID;
            $client_id = $client_model->save($client_data);

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
            $result = json_encode($client_id);
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
    }*/

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
            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);
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
            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);
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
            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);
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
            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);
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
            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);
        }
    }

    public function addCall()
    {
        try
        {
            $jinput = JFactory::getApplication()->input;
            $jdate = new JDate($jinput->get('date', '01.01.1970', 'STRING'));
            $id_client = $jinput->get('id_client', '0', 'INT');
            $manager_id = $jinput->get('manager_id', '0', 'INT');
            $comment = $jinput->get('comment', '', 'STRING');
            $phone_client = $jinput->get('phone', '', 'STRING');

            $callback_model = Gm_ceilingHelpersGm_ceiling::getModel('callback');
            $result = $callback_model->save($jdate, $comment, $id_client, $manager_id, $phone_client);
            //throw new Exception($result == 'call_find', 1);

            if (!$result) {
                $result = json_encode('call_find');
            }

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
            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);
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
            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);
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
            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);
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
            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);
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
            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);
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
            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);
        }
    }

    /* функция для AJAX-получения списка цветов для конкретного полотна */
    public function getColorList()
    {
        try
        {
            $jinput = JFactory::getApplication()->input;
            $texture_id = $jinput->get('texture_id', '0', 'INT');


            if ($texture_id > 0) {
                $filter = "`texture_id` = " . $texture_id . " AND `count`>0";
            } else {
                die();
            }
            //$canvases = Gm_ceilingHelpersGm_ceiling::getModel('canvases');
            //$color_canvases = $canvases->getFilteredItems($filter);
            $model = Gm_ceilingHelpersGm_ceiling::getModel('colors');
            //throw new Exception($filter, 1);

            $items = $model->getFilteredItems($filter);
            //throw new Exception(implode('|', $items), 11);
            $colors = array();
            foreach ($items as $i => $item) {
                $colors[] = array($item->id, $item->title, $item->file);
            }
            //throw new Exception(implode('|', $colors), 11);
            die(json_encode($colors));
        }
        catch(Exception $e)
        {
            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);
        }
    }

    /* функция для AJAX-получения списка типов полотен */
    public function getTypesList()
    {
        try
        {
            $jinput = JFactory::getApplication()->input;

            $model = Gm_ceilingHelpersGm_ceiling::getModel('types');
            $items = $model->getItems();
            $types = array();
            foreach ($items as $i => $item) {
                $types[] = array($item->id, $item->type_title);
            }

            die(json_encode($types));
        }
        catch(Exception $e)
        {
            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);
        }
    }

    /* функция для AJAX-получения списка фактур определенного типа полотна */
    public function getTexturesList()
    {
        try
        {
            $jinput = JFactory::getApplication()->input;
            $model = Gm_ceilingHelpersGm_ceiling::getModel('textures');

            $items = $model->getFilteredItems();

            $textures = array();
            foreach ($items as $i => $item) {
                if ($item->id != 28) {
                    $textures[] = array($item->id, $item->texture_title, $item->texture_colored);
                }
            }
            die(json_encode($textures));
        }
        catch(Exception $e)
        {
            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);
        }
    }

    public function getEcolaList()
    {
        try
        {
            /*SELECT b.id,b.title FROM `rgzbn_gm_ceiling_components` AS a INNER JOIN `rgzbn_gm_ceiling_components_option` AS b ON a.id = b.component_id
         WHERE a.title = 'Светильник' AND b.title LIKE('%Эcola%')*/
            $model = Gm_ceilingHelpersGm_ceiling::getModel('components');
            $items = $model->getAllList_Price();
            $filter = "component.title = 'Светильник' AND (a.title LIKE('%Эcola%') OR a.title LIKE('%Экола%') )";
            $items = $model->getFilteredItems($filter);
            die(json_encode($items));
        }
        catch(Exception $e)
        {
            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);
        }
    }

    public function getEcolaBulbs()
    {
        try
        {
            /*SELECT b.id,b.title FROM `rgzbn_gm_ceiling_components` AS a INNER JOIN `rgzbn_gm_ceiling_components_option` AS b ON a.id = b.component_id
             WHERE a.title = 'Светильник' AND b.title LIKE('%Эcola%')*/
            $model = Gm_ceilingHelpersGm_ceiling::getModel('components');
            $items = $model->getAllList_Price();
            $filter = "component.title = 'Лампа' AND (a.title LIKE('%Эcola%') OR a.title LIKE('%Экола%') )";
            $items = $model->getFilteredItems($filter);
            die(json_encode($items));
        }
        catch(Exception $e)
        {
            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);
        }
    }

    public function getListRings()
    {
        try
        {
            /*SELECT b.id,b.title FROM `rgzbn_gm_ceiling_components` AS a INNER JOIN `rgzbn_gm_ceiling_components_option` AS b ON a.id = b.component_id
             WHERE a.title = 'Светильник' AND b.title LIKE('%Эcola%')*/
            $model = Gm_ceilingHelpersGm_ceiling::getModel('components');
            $items = $model->getAllList_Price();
            $filter = "component.title = 'Круглое кольцо'";
            $items = $model->getFilteredItems($filter);
            die(json_encode($items));
        }
        catch(Exception $e)
        {
            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);
        }
    }

    public function getListThermalSquare()
    {
        try
        {
            /*SELECT b.id,b.title FROM `rgzbn_gm_ceiling_components` AS a INNER JOIN `rgzbn_gm_ceiling_components_option` AS b ON a.id = b.component_id
             WHERE a.title = 'Светильник' AND b.title LIKE('%Эcola%')*/
            $model = Gm_ceilingHelpersGm_ceiling::getModel('components');
            $items = $model->getAllList_Price();
            $filter = "component.title = 'Термоквадрат' AND (a.title NOT LIKE('%Эcola%') OR a.title NOT LIKE('%Экола%') )";
            $items = $model->getFilteredItems($filter);
            die(json_encode($items));
        }
        catch(Exception $e)
        {
            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);
        }
    }

    public function getListCornice()
    {
        try
        {
            $model = Gm_ceilingHelpersGm_ceiling::getModel('components');
            $items = $model->getAllList_Price();
            $filter = "component.title = 'Карниз для штор' AND a.title LIKE('%3 ряд%')";
            $items = $model->getFilteredItems($filter);

            die(json_encode($items));
        }
        catch(Exception $e)
        {
            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);
        }
    }

    public function getListProfil()
    {
        try
        {
            $model = Gm_ceilingHelpersGm_ceiling::getModel('components');
            $items = $model->getListProfil();
            //$filter = "component.title = 'Профиль'";
            //$items = $model->getFilteredItems($filter);
            $items->image = base64_encode($items->image);
            die(json_encode($items));
        }
        catch(Exception $e)
        {
            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);
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
            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);
        }
    }

    //получаем обводы трубы
    public function getListBypass()
    {
        try
        {
            /*SELECT b.id,b.title FROM `rgzbn_gm_ceiling_components` AS a INNER JOIN `rgzbn_gm_ceiling_components_option` AS b ON a.id = b.component_id
             WHERE a.title = 'Светильник' AND b.title LIKE('%Эcola%')*/
            $model = Gm_ceilingHelpersGm_ceiling::getModel('components');
            $items = $model->getAllList_Price();
            $filter = "component.title = 'Пластина обвод трубы'";
            $items = $model->getFilteredItems($filter);
            die(json_encode($items));
        }
        catch(Exception $e)
        {
            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);
        }
    }

    public function getListDiffuzor()
    {
        try
        {
            /*SELECT b.id,b.title FROM `rgzbn_gm_ceiling_components` AS a INNER JOIN `rgzbn_gm_ceiling_components_option` AS b ON a.id = b.component_id
             WHERE a.title = 'Светильник' AND b.title LIKE('%Эcola%')*/
            $model = Gm_ceilingHelpersGm_ceiling::getModel('components');
            $items = $model->getAllList_Price();
            $filter = "component.title = 'Дифузор'";
            $items = $model->getFilteredItems($filter);
            die(json_encode($items));
        }
        catch(Exception $e)
        {
            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);
        }
    }

    /* функция для AJAX-получения списка полотен определенной фактуры */
    public function getCanvasesList()
    {
        try
        {
            $jinput = JFactory::getApplication()->input;
            $texture_id = $jinput->get('jform_n2', '0', 'INT');
            $color_id = $jinput->get('jform_color', '0', 'INT');
            $name = $jinput->get('jform_proizv', '', 'STRING');
            /*SELECT DISTINCT `name`, `country`, `width`, `price` FROM `rgzbn_gm_ceiling_canvases` WHERE `count` > 0 AND `texture_id` = 2 AND `color_id` = 2*/

            if ($texture_id > 0 && $color_id > 0 && !empty($name)) {
                $filter = "`count`>0 AND `texture_id` = " . $texture_id . " AND `color_id` = " . $color_id . " AND `name`=" . "'" . $name . "'";
            } elseif ($texture_id > 0 && $color_id == 0 && !empty($name)) {
                $filter = "`count`>0 AND `texture_id` = " . $texture_id . " AND `name`=" . "'" . $name . "'";
            } else {
                die();
            }
            $model = Gm_ceilingHelpersGm_ceiling::getModel('canvases');
            $items = $model->getFilteredItems($filter);
            //$canvases = array();
            foreach ($items as $i => $item) {
                $width = (float)$item->width * 100;
                //$canvases.= str_replace('.','',$item->width)."0;".$item->price.";";
                $canvases .= $width . ";" . $item->price . ";";
            }
            die(json_encode($canvases));
        }
        catch(Exception $e)
        {
            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);
        }
    }

    /* функция для AJAX-получения списка производителей полотен */
    public function getManufacturersList()
    {
        try
        {
            $jinput = JFactory::getApplication()->input;
            $id = $jinput->get('jform_proizv', '0', 'INT');
            $texture_id = $jinput->get('jform_n2', '0', 'INT');
            $color_id = $jinput->get('jform_color', '0', 'INT');
            if ($texture_id > 0 && $color_id > 0) {
                $filter = (($id != 0) ? "" : "`count`>0 AND ") . "`texture_id` = " . $texture_id . " `color_id` = " . $color_id;
            } elseif ($texture_id > 0 && $color_id == 0) {
                $filter = (($id != 0) ? "" : "`count`>0 AND ") . "`texture_id` = " . $texture_id;
            } else {
                die();
            }
            $model = Gm_ceilingHelpersGm_ceiling::getModel('canvases');
            $items = $model->getNameCountryFilteredItems($filter);
            $canvases = array();
            foreach ($items as $i => $item) {
                if (!$canvases[$item->name . $item->country] || $item->id == $id)
                    $canvases[$item->name . $item->country] = array($item->name, $item->country, $item->id);
            }
            //print_r($canvases); exit;
            die(json_encode($canvases));
        }
        catch(Exception $e)
        {
            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);
        }
    }

    /* функция для AJAX-сохранения картинки из чертилки */
    public function save_calculation_img()
    {
        try
        {
            $jinput = JFactory::getApplication()->input;
            $data = $jinput->get('data', '0', 'string');
            $auto = $jinput->get('auto', '', 'string');
            $user_id = $jinput->get('id', '0', 'int');
            $length_arr = $jinput->get('arr_length', '', 'array');
            for ($i = 0; $i < count($length_arr); $i++) {
                $str .= implode('=', $length_arr[$i]);
                $str .= ';';
            }

            $filename = md5($user_id . "-" . date("d-m-Y H:i:s"));

            list($type, $data) = explode(';', $data);
            list(, $data) = explode(',', $data);
            $data = base64_decode($data);

            file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/tmp/' . $filename . ".png", $data);
            file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/tmp/' . $filename . ".txt", $str);

            session_start();
            $_SESSION['jform_n4'] = $jinput->get('jform_n4', '0', 'string');
            $_SESSION['jform_n5'] = $jinput->get('jform_n5', '0', 'string');
            $_SESSION['jform_n9'] = $jinput->get('jform_n9', '0', 'string');
            $_SESSION['texture'] = $jinput->get('texture', '0', 'int');
            $_SESSION['color'] = $jinput->get('color', '0', 'int');
            $_SESSION['manufacturer'] = $jinput->get('manufacturer', '', 'STRING');
            $_SESSION['calc_title'] = $jinput->get('calc_title', '', 'STRING');
            $_SESSION['data'] = $filename;
            if($auto==1){
                $_SESSION['need_calc'] = 1;
            }

            die($filename);
        }
        catch(Exception $e)
        {
            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);
        }
    }

    public function save_cut_img()
    {
        try
        {
            $jinput = JFactory::getApplication()->input;
            $data = $jinput->get('data', '0', 'string');
            $user_id = $jinput->get('id', '0', 'int');
            $arr_points = $jinput->get('arr_points', '', 'array');
            $offcut_square = $jinput->get('square_obrezkov', '', 'FLOAT');
            $cuts = $jinput->get('cuts', '', 'string');
            throw new Exception($cuts);
            
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

            $filename = md5($user_id . "cut-" . date("d-m-Y H:i:s"));

            list($type, $data) = explode(';', $data);
            list(, $data) = explode(',', $data);
            $data = base64_decode($data);

            file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/tmp/' . $filename . ".png", $data);
            file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/tmp/' . $filename . ".txt", $str);

            session_start();
            $_SESSION['cut'] = $filename;
            $_SESSION['width'] = $jinput->get('width', '0', 'INT');
            $_SESSION['offcut'] = $offcut_square;
            //throw new Exception($_SESSION['cut'].' | '.$_SESSION['width'].' | '.$_SESSION['offcut']);

            die($filename);
        }
        catch(Exception $e)
        {
            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);
        }
    }

    public function texturesId($textures,$manufacturer,$width,$color)
    {
        try
        {
            $color = $color ? "= " .$color : "IS NULL";

            $filter = "texture_id = ".$textures." AND name = '" . $manufacturer . "' AND width = '" . $width . "' AND color_id " . $color . "";
            $model = Gm_ceilingHelpersGm_ceiling::getModel('canvases');
            $items = $model->getIdFilteredItems($filter);
            $_SESSION['n3'] = $items[0]->id;
            unset($_SESSION['texture'], $_SESSION['manufacturer'], $_SESSION['width'], $_SESSION['color']);
        }
        catch(Exception $e)
        {
            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);
        }
    }

    public function isId($n3){
        $filter = "id = $n3";
        $model = Gm_ceilingHelpersGm_ceiling::getModel('canvases');
        $items = $model->getFilteredItems($filter);

        return $items;

    }
    /*  функция для AJAX-запроса расчета потолка из calculationform
        функция вызывает одноименную функцию в файле /helpers/gm_ceiling.php
    */
    public function calculate()
    {
        try
        {
            $jinput = JFactory::getApplication()->input;

            //if ($_SESSION['manufacturer']) ;
            $from_db = 0;
            $id = $jinput->get('id', '0', 'INT');
            $save = $jinput->get('save', '0', 'INT');
            $pdf = $jinput->get('pdf', '0', 'INT');
            $ajax = $jinput->get('ajax', '0', 'INT');
            $need_mount = $jinput->get('need_mount', '0', 'INT');
            $del_flag = $jinput->get('del_flag', '0', 'INT');
          
            $data1 = $jinput->getArray(array(
                'jform' => array(
                    'n2' => 'int', //тип фактуры
                    'n3' => 'STRING', //Производитель и ширина
                    'proizv' => 'STRING',
                    'color' => 'int'
                )
            ));
            $data1 = $data1['jform'];
            if(!empty($data1)) $this->texturesId($data1['n2'],$data1['proizv'],$data1['n3'],$data1['color']);
            $print_components = 0;

            $result = Gm_ceilingHelpersGm_ceiling::calculate($from_db, $id, $save, $ajax, $pdf, $print_components, $del_flag, $need_mount);
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
            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);
        }
    }

    public function image()
    {
        try
        {
            $jinput = JFactory::getApplication()->input;
            $id = $jinput->get('id', '0', 'INT');
            $result = Gm_ceilingHelpersGm_ceiling::get_image_from_db($id);
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

    /* 	функция AJAX-отметки договора и назначения новой суммы
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
            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);
        }
    }

    /* 	функция AJAX-отображения календаря монтажей
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
            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);
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
            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);
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
            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);
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
            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);
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
            $model_client = Gm_ceilingHelpersGm_ceiling::getModel('client');
            $result_client = $model_client->getClientById($result->client_id);

            $model_project = Gm_ceilingHelpersGm_ceiling::getModel('project');
            $result_project = $model_project->getProjectsByClientID($result_client->id);
            
            die(json_encode($result_project));
        }
        catch(Exception $e)
        {
            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);
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
            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);
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
            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);
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

            $dop_file = $_FILES['dopfile'];//$jinput->get('dop_file', '', 'STRING');
            //$targetPath = $_SERVER['DOCUMENT_ROOT']  ."files/feedback/".$_FILES['dopfile']['name'];
            $type = $jinput->get('type', '', 'INT');
            //throw new Exception($type, 1);
            $dop_file1 = $_FILES['dopfile1'];
            $dop_file2 = $_FILES['dopfile2'];
            $mailer = JFactory::getMailer();
            $config = JFactory::getConfig();
            $sender = array(
                $config->get('mailfrom'),
                $config->get('fromname')
            );
            $dec = json_decode($filenames);
            $filenames = $dec;
            $mailer->setSender($sender);
            $mailer->addRecipient($email);
            $model = Gm_ceilingHelpersGm_ceiling::getModel('clients_dop_contacts');
            if ($type == 2) {
                $body = "Здравствуйте. Вы запросили подробную смету. Смета во вложении";
                $mailer->setSubject('Подробная смета');
                $mailer->setBody($body);
            }
            elseif ($type == 1) {
                $body = "Здравствуйте. Вы запросили подробый наряд на монтаж потолка. Наряд на монтаж во вложении";
                $mailer->setSubject('Подробный наряд на монтаж');
                $mailer->setBody($body);
            } else {
                
                $model->SetEmail($client_id, $email);
                $body = "Здравствуйте. Вы запросили подробную смету потолка. Смета во вложении";
                $mailer->setSubject('Подробные сметы');
                $mailer->setBody($body);
            }
            for ($i = 0; $i < count($filenames); $i++)
                $mailer->addAttachment($_SERVER['DOCUMENT_ROOT'] . "/costsheets/" . $filenames[$i]->filename, $filenames[$i]->name . ".pdf");
            if ($type == 2) {
                $id = $jinput->get('id', '', 'INT');
                $model->SetEmail($client_id, $email);
                //print_r($id); exit;
                $mailer->addAttachment($_SERVER['DOCUMENT_ROOT'] . "/costsheets/" . md5($id . "-9") . ".pdf", "Общая подробная смета". ".pdf");
                $mailer->addAttachment($dop_file2['tmp_name'], $dop_file2['name']);
            }
            elseif ($type == 1) {
                $mailer->addAttachment($dop_file1['tmp_name'], $dop_file1['name']);
            } else {
                $mailer->addAttachment($dop_file['tmp_name'], $dop_file['name']);
            }
            $send = $mailer->Send();
            //$mailer->addRecipient("gm-partner@mail.ru");
            //$body = "Здравствуйте. Клиент запросил подробную смету на адрес: ".$email;
            //$mailer->setSubject('Подробная смета');
            //$mailer->setBody($body);
            //$mailer->addAttachment($_SERVER['DOCUMENT_ROOT']."/costsheets/".$filename);
            //$send = $mailer->Send();

            $result = json_encode(200);
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
            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);
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
            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);
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
            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);
        }
    }

    public static function save_data_to_session()
    {
        try{
            $jinput = JFactory::getApplication()->input;
            $_SESSION['FIO'] = $jinput->get('fio', '', 'STRING');
            $_SESSION['address'] = $jinput->get('address', '', 'STRING');
            $_SESSION['date'] = $jinput->get('date', '', 'STRING');
            $_SESSION['time'] = $jinput->get('time', '', 'STRING');
            $_SESSION['manager_comment'] = $jinput->get('manager_comment', '', 'STRING');
            $_SESSION['phones'] = $jinput->get('phones', '', 'ARRAY');
            $_SESSION['comments'] = $jinput->get('comments', '', 'STRING');
            $_SESSION['url'] = $jinput->get('s', '', 'STRING');
            $_SESSION['gauger'] = $jinput->get('gauger', '', 'STRING');            
            
            die(true);
        }
        catch(Exception $e)
        {
            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);
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
            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);
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
            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);
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
            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);
        }
    }

    public static function get_original_sketch()
    {
        try{
            $jinput = JFactory::getApplication()->input;
            $wp = $jinput->get('walls_points', array(), 'ARRAY');
            $dp = $jinput->get('diags_points', array(), 'ARRAY');
            $pp = $jinput->get('pt_points', array(), 'ARRAY');
            $code = $jinput->get('code', '0', 'INT');
            $alphavite = $jinput->get('alfavit', '0', 'INT');
            $user_id = $jinput->get('user_id', '0', 'int');
            $filename = md5($user_id . "original-" . date("d-m-Y H:i:s"));
            for ($i = 0; $i < count($wp); $i++) {
                $str .= implode(';', $wp[$i]);
                $str .= ';';
            }
            $str .= '||';
            for ($i = 0; $i < count($dp); $i++) {
                $str .= implode(';', $dp[$i]);
                $str .= ';';
            }
            $str .= '||';
            for ($i = 0; $i < count($pp); $i++) {
                $str .= implode(';', $pp[$i]);
                $str .= ';';
            }
            $str .= '||' . $code . '||' . $alphavite;

            file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/tmp/' . $filename . ".txt", $str);
            session_start();
            $_SESSION['original'] = $filename;
            die($filename);
        }
        catch(Exception $e)
        {
            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);
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
            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);
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
            $this->setRedirect(JRoute::_('http://test1.gm-vrn.ru/components/com_gm_ceiling/views/saverclient/default.php?complite=1&id='.$id, false));
            return 1;
        }
        catch(Exception $e)
        {
            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);
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
            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);
        }
    }

    public function getAnaliticByPeriod()
    {
        try{
            $jinput = JFactory::getApplication()->input;
            $date1 = $jinput->get('date1','','STRING');
            $date2 = $jinput->get('date2','','STRING');
            $analitic_model = Gm_ceilingHelpersGm_ceiling::getModel('analiticcommon');
            $data = $analitic_model->getDataByPeriod($date1,$date2);
            die(json_encode($data));
        }
        catch(Exception $e)
        {
            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);
        }
    }
    public function getDetailedAnaliticByPeriod()
    {
        try{
            $jinput = JFactory::getApplication()->input;
            $date1 = $jinput->get('date1','','STRING');
            $date2 = $jinput->get('date2','','STRING');
            $analitic_model = Gm_ceilingHelpersGm_ceiling::getModel('analiticdetailed');
            $data = $analitic_model->getData($date1,$date2);
            die(json_encode($data));
        }
        catch(Exception $e)
        {
            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);
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
            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);
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
            $phones_model = Gm_ceilingHelpersGm_ceiling::getModel('api_phones');
            $advt_id = $phones_model->getIdByName($advt_name);
            $project_model = Gm_ceilingHelpersGm_ceiling::getModel('projects');
            if($type == 0){
                if(empty($date1)&&empty($date2)){
                    $projects = $project_model->getDataByStatusAndAdvt($advt_id,$statuses);
                }
                else{
                    $projects = $project_model->getDataByStatusAndAdvt($advt_id,$statuses,$date1,$date2);
                };
            }
            if($type == 1){
                $project_history = Gm_ceilingHelpersGm_ceiling::getModel('Projectshistory');
                $projects = $project_history->getIdsByStatusAndAdvt($advt_id,$statuses,$date1,$date2);
                
            }
            die(json_encode($projects));       
            

        }
        catch(Exception $e)
        {
            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);
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
            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);
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
            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);
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
            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);
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
            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);
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
            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files . 'error_log.txt', (string)$date . ' | ' . __FILE__ . ' | ' . __FUNCTION__ . ' | ' . $e->getMessage() . "\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);
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
            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files . 'error_log.txt', (string)$date . ' | ' . __FILE__ . ' | ' . __FUNCTION__ . ' | ' . $e->getMessage() . "\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);
        }
    }

    public function getImage() {
        try {
            $app = JFactory::getApplication();

            $type = $app->input->get('type', null, 'string');
            $calculation = $app->input->get('calc', null, 'int');

            if (empty($type) || empty($calculation))
                die(json_encode(["status" => "error", "message" => "Не все параметры переданы!"]));

            $model = $this->getModel('Calculation', 'Gm_ceilingModel');
            $image = ((array)$model->getImage($calculation, $type))[$type];

            header('Content-Type: image/png');
            header('Cache-control: no-cache, no-store');
            echo $image;
            exit;
        } catch (Exception $e) {
            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files . 'error_log.txt', (string)$date . ' | ' . __FILE__ . ' | ' . __FUNCTION__ . ' | ' . $e->getMessage() . "\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);
        }
    }

    public function UpdateCutData()
    {
        try {
            $jinput = JFactory::getApplication()->input;
            $data = $jinput->get('image', '0', 'string');
            $arr_points = $jinput->get('koordinats_poloten', '', 'array');
            $calc_id = $jinput->get('calc_id', '', 'INT');
            $width = $jinput->get('width', '', 'INT');
            $width = (string)$width/100;
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

            list($type, $data) = explode(';', $data);
            list(, $data) = explode(',', $data);
            $data = base64_decode($data);

            $calc_model = Gm_ceilingHelpersGm_ceiling::getModel('calculations');
            $result = $calc_model->update_cut_data($calc_id, $str, $width);

            $filename = md5('cut_sketch' . $calc_id);
            file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/cut_images/' . $filename . ".png", $data);
            die(true);

        } catch (Exception $e) {
            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files . 'error_log.txt', (string)$date . ' | ' . __FILE__ . ' | ' . __FUNCTION__ . ' | ' . $e->getMessage() . "\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);
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
            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files . 'error_log.txt', (string)$date . ' | ' . __FILE__ . ' | ' . __FUNCTION__ . ' | ' . $e->getMessage() . "\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);
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
            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files . 'error_log.txt', (string)$date . ' | ' . __FILE__ . ' | ' . __FUNCTION__ . ' | ' . $e->getMessage() . "\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);
        }
    }

    public function sendCommercialOffer($user_id = null, $email = null){
        try
        {
            $user = JFactory::getUser();
            $groups = $user->get('groups');
            if (in_array("16", $groups))
            {
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
                    throw new Exception('empty email');
                }
                $code = md5($user_id.'commercial_offer');

                $site = "http://test1.gm-vrn.ru/index.php?option=com_gm_ceiling&task=big_smeta.commercialOffer&code=$code";
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
                $body .= '<img src="http://test1.gm-vrn.ru/images/gm-logo.png" alt="Логотип" style="padding-top: 15px; height: 70px; width: auto;">';
                $body .= '</a></td><td><div style="vertical-align:middle; padding-right: 50px; padding-top: 7px; text-align: right; line-height: 0.5;">';
                $body .= '<p>Тел.: +7(473)2122359</p>';
                $body .= '<p>Почта: gm-partner@mail.ru</p>';
                $body .= '<p>Адрес: г. Воронеж, Проспект Труда, д. 48, литер. Е-Е2</p>';
                $body .= '</div></td></tr></table>';
                $body .= "<div style=\"width: 100%\">В продолжение нашего телефонного разговора отправляю ссылку на <a href=\"$site\">коммерческое предложение</a>, где Вы можете получить более подробную информацию.</div></body>";
                $mailer->setSubject('Коммерческое предложение');
                $mailer->isHtml(true);
                $mailer->Encoding = 'base64';
    			$mailer->setBody($body);
                $send = $mailer->Send();
                
                $users_model = Gm_ceilingHelpersGm_ceiling::getModel('users');
                $result = $users_model->addCommercialOfferCode($user_id, $code);

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
            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files . 'error_log.txt', (string)$date . ' | ' . __FILE__ . ' | ' . __FUNCTION__ . ' | ' . $e->getMessage() . "\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);
        }
    }

    public function RepeatSendCommercialOffer(){
        try
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
                    $emails = $dop_contacts_model->getEmailByClientID($client_id);
                    foreach ($emails as $j => $email)
                    {
                        $this->sendCommercialOffer($item->user_id, $email->contact);
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
            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files . 'error_log.txt', (string)$date . ' | ' . __FILE__ . ' | ' . __FUNCTION__ . ' | ' . $e->getMessage() . "\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);
        }
    }

    public function createPdfs(){
        try{
        $jinput = JFactory::getApplication()->input;
        $project_id = $jinput->get('id','','INT');
        $proj_model = Gm_ceilingHelpersGm_ceiling::getModel('project');
        $project = $proj_model->getData($project_id);
        $project_total = 0;
        $project_total_discount = 0;
        $total_square = 0;
        $total_perimeter = 0;
        $model = Gm_ceilingHelpersGm_ceiling::getModel('calculations');
        $calculations = $model->getProjectItems($project->id);

        foreach ($calculations as $calculation) {

            $calculation->dealer_canvases_sum = double_margin($calculation->canvases_sum, 0/*$project->gm_canvases_margin*/, $project->dealer_canvases_margin);
            $calculation->dealer_components_sum = double_margin($calculation->components_sum, 0 /*$project->gm_components_margin*/, $project->dealer_components_margin);
            $calculation->dealer_gm_mounting_sum = double_margin($calculation->mounting_sum, 0 /*$project->gm_mounting_margin*/, $project->dealer_mounting_margin);

            $calculation->dealer_canvases_sum_1 = margin($calculation->canvases_sum, 0/*$project->gm_canvases_margin*/);
            $calculation->dealer_components_sum_1 = margin($calculation->components_sum, 0/* $project->gm_components_margin*/);
            $calculation->dealer_gm_mounting_sum_1 = margin($calculation->mounting_sum, 0/* $project->gm_mounting_margin*/);

            $calculation->calculation_total = $calculation->dealer_canvases_sum + $calculation->dealer_components_sum + $calculation->dealer_gm_mounting_sum;
            $calculation->calculation_total_discount = $calculation->calculation_total * ((100 - $calculation->discount) / 100);
            $project_total += $calculation->calculation_total;
            $project_total_discount += $calculation->calculation_total_discount;

            if ($user->dealer_type != 2) {
                $dealer_canvases_sum_1 = margin($calculation->canvases_sum, 0/*$project->gm_canvases_margin*/);
                $dealer_components_sum_1 = margin($calculation->components_sum, 0/*$project->gm_components_margin*/);
                $dealer_gm_mounting_sum_1 = margin($calculation->mounting_sum, 0/*$project->gm_mounting_margin*/);
                $calculation_total_1 = $dealer_canvases_sum_1 + $dealer_components_sum_1;
                $dealer_gm_mounting_sum_11 += $dealer_gm_mounting_sum_1;
                $calculation_total_11 += $calculation_total_1;
                $project_total_1 = $calculation_total_1 + $dealer_gm_mounting_sum_1;
            }
            $project_total_11 += $project_total_1;

            $calculation_total = $calculation->calculation_total;

        }
        $sum_transport = 0;  $sum_transport_discount = 0;
        $mountModel = Gm_ceilingHelpersGm_ceiling::getModel('mount');
        $mount_transport = $mountModel->getDataAll();

        if($project->transport == 0 ) $sum_transport = 0;
        if($project->transport == 1 ) $sum_transport = double_margin($mount_transport->transport * $project->distance_col, $project->gm_mounting_margin, $project->dealer_mounting_margin);
        if($project->transport == 2 ) $sum_transport = ($mount_transport->distance * $project->distance + $mount_transport->transport)  * $project->distance_col;
        if($project->transport == 1 ) {
        $min = 100;
        foreach($calculations as $d) {
            if($d->discount < $min) $min = $d->discount;
        }
        if  ($min != 100) $sum_transport = $sum_transport * ((100 - $min)/100);
        }
        if($sum_transport < double_margin($mount_transport->transport, $project->gm_mounting_margin, $project->dealer_mounting_margin) && $sum_transport != 0) {
            $sum_transport = double_margin($mount_transport->transport, $project->gm_mounting_margin, $project->dealer_mounting_margin);
        }
        $project_total_discount_transport = $project_total_discount + $sum_transport;
        $del_flag = 0;
        $project_total = $project_total  + $sum_transport;
        $project_total_discount = $project_total_discount  + $sum_transport;
            $calculationsModel = Gm_ceilingHelpersGm_ceiling::getModel('calculations');
            $calculations1 = $calculationsModel->getProjectItems($project->id);
            $components_data = array();
            $project_sum = 0;
            $counter = 0;
        foreach ($calculations1 as $calculation) {
            $counter++;
            $from_db = 1;
            $save = 1;
            $ajax = 0;
            $pdf = 1;
            $print_components = 0;
            if($calculation->mounting_sum == 0) $need_mount = 0;
            else $need_mount = 1;
            Gm_ceilingHelpersGm_ceiling::calculate($from_db, $calculation->id, $save, $ajax, $pdf, $print_components, $del_flag, $need_mount);
            $from_db = 1;
            $save = 0;
            $ajax = 0;
            $pdf = 0;
            $print_components = 1;
            $components_data[] = Gm_ceilingHelpersGm_ceiling::calculate($from_db, $calculation->id, $save, $ajax, $pdf, $print_components, $del_flag, $need_mount);
            $project_sum += margin($calculation->components_sum, $project->gm_components_margin);
            $project_sum += margin($calculation->canvases_sum, $project->gm_canvases_margin);
            $project_sum += margin($calculation->mounting_sum, $project->gm_mounting_margin);
            if ($counter == count($calculations1)) {
                $flag_last = 1;
                Gm_ceilingHelpersGm_ceiling::calculate($from_db, $calculation->id, $save, $ajax, $pdf, $print_components, $del_flag, $need_mount);

            }
        } 
       Gm_ceilingHelpersGm_ceiling::print_components($project_id, $components_data);
       
            
            $sum = 0;
            $data->id = $project->id;
            $data->transport = $project->transport;
            $data->distance = $project->distance;
            $distance_col = $project->distance_col;
            $data->distance_col = $distance_col ;
            
            $model_project = Gm_ceilingHelpersGm_ceiling::getModel('Project');
            $res = $model_project->transport($data);
            $dealer_info_model = Gm_ceilingHelpersGm_ceiling::getModel('Dealer_info');
            if(empty($res->user_id)) $res->user_id = 1;
            $margin = $dealer_info_model->getMargin('dealer_mounting_margin',$res->user_id);
           
            if($res) {
                if($data->transport == 1) { $transport_sum = margin($res->transport * $distance_col, $margin);
                $transport_sum_1 = $res->transport * $distance_col;
                }
                elseif($data->transport == 2) {
                    $transport_sum = ($res->distance  * $data->distance + $res->transport) * $distance_col;
                    $transport_sum_1 = ($res->distance  * $data->distance + $res->transport) * $distance_col;
                    if($transport_sum < margin($res->transport, $margin))
                      { 
                          $transport_sum = margin($res->transport, $margin);
                          $transport_sum_1 = $res->transport;
                      }  
                }
                else { $transport_sum = 0; $transport_sum_1 = 0; } 
            }
            $model = Gm_ceilingHelpersGm_ceiling::getModel('Big_smeta');
            if(!empty($calculations)) {
                
            
                $sheets_dir = $_SERVER['DOCUMENT_ROOT'] . '/costsheets/';
                $html = ' <h1>Номер договора: ' . $project->id . '</h1><br>';
                $html .= '<h2>Дата: ' . date("d.m.Y") . '</h2>';
                $html .= '<h2>Краткая информация по выбранным(-ому) потолкам(-у): </h2>';
                $html .= '<table border="0" cellspacing="0" width="100%">
                <tbody><tr><th>Название</th><th class="center">Площадь, м<sup>2</sup>.</th><th class="center">Периметр, м </th><th class="center">Стоимость, руб.</th></tr>';
                //написать модель, которая будет возвращать данные о калькуляции
                foreach ($calculations as $calc) {
                    $html .= '<tr>';
                    $html .= '<td>' . $calc->calculation_title . '</td>';
                    $html .= '<td class="center">' . $calc->n4 . '</td>';
                    $html .= '<td class="center">' . $calc->n5 . '</td>';
                    $html .= '<td class="center">' . 0 . '</td>';
                    $html .= '</tr>';
                    $sum +=0;
                }
                $html .= '<tr><th colspan="3" class="right">Итого, руб:</th><th class="center">' . $sum . '</th></tr>';
                $html .= '</tbody></table><p>&nbsp;</p><br>';
    
            }
            
            $html .= '<h2>Транспортные расходы: </h2>';
            $html .= '<table border="0" cellspacing="0" width="100%">
			<tbody><tr><th>Вид транспорта</th><th class="center">Кол-во км<sup>2</sup>.</th><th class="center">Кол-во выездов  </th><th class="center">Стоимость, руб.</th></tr>';
                if($project->transport == '2' ) {
                    $html .= '<tr>';
                    $html .= '<td>' . 'Выезд за город' . '</td>';
                    $html .= '<td class="center">' . $project->distance . '</td>';
                    $html .= '<td class="center">' .$project->distance_col . '</td>';
                    $html .= '<td class="center">' . $transport_sum . '</td>';
                    $html .= '</tr>';
                }
                elseif($project->transport == '1' ) {
                    $html .= '<tr>';
                    $html .= '<td>' . 'Транспорт по городу' . '</td>';
                    $html .= '<td class="center"> - </td>';
                    $html .= '<td class="center">' . $project->distance_col . '</td>';
                    $html .= '<td class="center">' . $transport_sum . '</td>';
                    $html .= '</tr>';
                }
                elseif($project->transport == '0' ) {
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

            foreach ($calculations as $calc) {

               $patch = $_SERVER['DOCUMENT_ROOT'] . "/costsheets/" . md5($calc . "-0-0") . ".pdf";
               $array_html[] = $patch;
            }
            //print_r($components_data); exit;
            $filename = md5($project->id . "-9") . ".pdf";
            Gm_ceilingHelpersGm_ceiling::save_pdf($array_html, $sheets_dir . $filename, "A4");


             $mount = $model_project->getMount($project->id);
             if(!empty($mount->id)) $mount_name = $model_project->getMounterBrigade($mount->id);
            //смета по монтажным работам
            $html = ' <h1>Номер договора: ' . $project->id . '</h1><br>';
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
            if(!empty($calculations)) {
                $html .= '<h2>Краткая информация по выбранным(-ому) потолкам(-у): </h2>';
                $html .= '<table border="0" cellspacing="0" width="100%">
                <tbody><tr><th>Название</th><th class="center">Площадь, м<sup>2</sup>.</th><th class="center">Периметр, м </th><th class="center">Стоимость, руб.</th></tr>';
                //написать модель, которая будет возвращать данные о калькуляции
                foreach ($calculations as $calc) {
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
                if($project->transport == '2' ) {
                    $html .= '<tr>';
                    $html .= '<td>' . 'Выезд за город' . '</td>';
                    $html .= '<td class="center">' . $project->distance . '</td>';
                    $html .= '<td class="center">' . $project->distance_col . '</td>';
                    $html .= '<td class="center">' . $transport_sum_1 . '</td>';
                    $html .= '</tr>';
                }
                elseif($project->transport == '1' ) {
                    $html .= '<tr>';
                    $html .= '<td>' . 'Транспорт по городу' . '</td>';
                    $html .= '<td class="center"> - </td>';
                    $html .= '<td class="center">' .$project->distance_col . '</td>';
                    $html .= '<td class="center">' . $transport_sum_1 . '</td>';
                    $html .= '</tr>';
                }
                elseif($project->transport == '0' ) {
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

            foreach ($calculations as $calc) {

               $patch = $_SERVER['DOCUMENT_ROOT'] . "/costsheets/" . md5($calc . "-2") . ".pdf";
               $array_html[] = $patch;
            }
            //print_r($components_data); exit;
            $filename = md5($project->id . "-10") . ".pdf";
            Gm_ceilingHelpersGm_ceiling::save_pdf($array_html, $sheets_dir . $filename, "A4");
        }
        catch (Exception $e) {
            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files . 'error_log.txt', (string)$date . ' | ' . __FILE__ . ' | ' . __FUNCTION__ . ' | ' . $e->getMessage() . "\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);
        }
    }
    public function test_estimate(){
        $jinput = JFactory::getApplication()->input;
        $id = $jinput->get('id','','INT');
        $result = Gm_ceilingHelpersGm_ceiling::create_single_mount_estimate($id);
        die(json_encode($result));
    }
}

?>
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
class Gm_ceilingControllerApi extends JControllerLegacy
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

    const STATIC_KEY = 'crutch';

    public function getModel($name = 'Api', $prefix = 'Gm_ceilingModel', $config = array()) {
        return parent::getModel($name, $prefix, array('ignore_request' => true));
    }

    public function getPublicKey() {
        try {
            header('Content-type:application/json');
            if (empty($_POST['data']) || $_POST['data'] !== 'give me a public key') {
                die(json_encode(null));
            }
            $model = $this->getModel();
            $result = $model->generateKeypair();
            die(json_encode($result));
        }
        catch(Exception $e) {
            die($e->getMessage());
        }
    }

    private function getSecret($user_id) {
        try {
            $user = JFactory::getUser($user_id);
            $secret = md5(self::STATIC_KEY.$user->username);
            return $secret;
        }
        catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    private function xor_string($string, $key) {
        try {
            for($i = 0; $i < strlen($string); $i++) 
                $string[$i] = ($string[$i] ^ $key[$i % strlen($key)]);
            return $string;
        }
        catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    private function crypt($key_number, $data) {
        try {
            $secret = $this->getSecret($key_number);
            $data = base64_encode($data);
            $crypt = base64_encode($this->xor_string($data, $secret));
            $hash = hash('sha512', $data.$secret);
            $result = (object)array('key_number' => $key_number, 'data' => $crypt, 'hash' => $hash);
            return $result;
        } catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    private function decrypt($data) {
        try {
            if (empty($data->key_number) || empty($data->data) || empty($data->hash)) {
                return false;
            }
            $secret = $this->getSecret($data->key_number);
            $decode_data = base64_decode($data->data);
            $decrypt = $this->xor_string($decode_data, $secret);
            $newHash = hash('sha512', $decrypt.$secret);
            if ($newHash !== $data->hash) {
                throw new Exception('bad hash');
            }
            $decrypt = base64_decode($decrypt);
            return json_decode($decrypt);
        } catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function checkSubscription($key_number) {
        try {
            if (empty($key_number)) {
                return false;
            }
            $user = JFactory::getUser((int)$key_number);
            $currentDate = date('Y-m-d H:i:s');
            if (empty($user->period_start_date || $user->period)) {
                return true;
            }
            $periodEndDate = date('Y-m-d H:i:s', strtotime($user->period_start_date.' + '.$user->period));
            if ($currentDate > $periodEndDate) {
                header('Content-type:application/json');
                $result = (object) array('b' => '0', 'l' => 't', 'period' => $user->period);
                $result = $this->crypt($key_number, json_encode($result));
                die(json_encode($result));
            }
        } catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function updateSubscription() {
        try {
            $result = 'null';
            if (empty($_POST['data'])) {
                die($result);
            }
            $data = json_decode($_POST['data']);
            $key_number = $data->key_number;
            $decrypt = $this->decrypt($data);
            if (empty($decrypt)) {
                die($result);
            }

            $result = [];
            $table_data = [];

            $model = Gm_ceilingHelpersGm_ceiling::getModel('api');
            $decrypt->android_id = $decrypt->id;
            $decrypt->period_start_date = date('Y-m-d H-i-s');
            $table_data[] = $decrypt;
            $result[] = $model->save_or_update_data_from_android('#__users', $table_data);

            if (empty($result)) {
                die('null');
            }
            $result = json_encode($result);
            $result = json_encode($this->crypt($key_number, $result));
            die($result);
        } catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function Authorization_FromAndroid() {
        try {
            header('Content-type:application/json');
            if (empty($_POST['data'])) {
                die(json_encode(null)); 
            }
            $data = json_decode($_POST['data']);
            if (empty($data->key_number) || empty($data->data)) {
                die(null);
            }
            $model = $this->getModel();
            $key_number = (int)$data->key_number;
            $privatekey = $model->getKeypair($key_number)->private_key;
            openssl_private_decrypt(base64_decode($data->data), $authorization, $privatekey);
            $authorization = json_decode($authorization);

            $username = mb_ereg_replace('[^a-zA-Z\d\.\-\_]', '', $authorization->username);

            $user = JFactory::getUser($model->getUserId($username));
            $password = $authorization->password;
            $verifyPass = JUserHelper::verifyPassword($password, $user->password, $user->id);
            if ($verifyPass) {
                $user = clone $user;
                unset($user->username, $user->password);
                $user->datetime = date('Y-m-d H:i:s');
                $result = $this->crypt($user->id, json_encode($user));
                die(json_encode($result));
            } else {
                die('Invalid login or password!');
            }
        } catch(Exception $e) {
            die($e->getMessage());
        }
    }

    public function register($register_data, $from_browser) {
        try {
            header('Content-type:application/json');
            $result = json_encode(null);
            if (!empty($_POST['data'])) {
                $data = json_decode($_POST['data']);
                if (empty($data->key_number) || empty($data->data)) {
                    die(null);
                }
                $model = $this->getModel();
                $key_number = (int)$data->key_number;
                $privatekey = $model->getKeypair($key_number)->private_key;
                openssl_private_decrypt(base64_decode($data->data), $register_data, $privatekey);
                $register_data = json_decode($register_data);
            } elseif (empty($register_data) || empty($from_browser)) {
                return false;
            }
            
            if (empty($register_data)) {
                die(json_encode(null));
            }
           // $str = Gm_ceilingHelpersGm_ceiling::rus2translit($register_data->fio);
            $str = explode('@', $register_data->email)[0];
            // в нижний регистр
            $str = strtolower($str);
            // заменям все ненужное нам на "-"
            $str = preg_replace('~[^a-z0-9\_\-\.]+~u', '-', $str);
            // удаляем начальные и конечные '-'
            $username = trim($str, '-');

            $userModel = Gm_ceilingHelpersGm_ceiling::getModel('users');
            $id = $userModel->getUserByEmailAndUsername($register_data->email, $username);
            if (!empty($id)) {
                $user = clone JFactory::getUser($id->id);
                unset($user->username, $user->password);
                $user->datetime = date('Y-m-d H:i:s');
                if (empty($from_browser)) {
                    $result = json_encode($this->crypt($user->id, json_encode($user)));
                } else {
                    $result = json_encode($user);
                }
            } else {
                $pass = Gm_ceilingHelpersGm_ceiling::generatePassword(6);
                if (!empty($from_browser) && $from_browser == 1) {
                    $data = array(
                        "name" => $register_data->fio,
                        "username" => $username,
                        "password" => $pass,
                        "password2" => $pass,
                        "email" => $register_data->email,
                        "groups" => array(2, 14),
                        "dealer_type" => 1,
                        "requireReset" => 0
                    );
                } else {
                    $data = array(
                        "name" => $register_data->fio,
                        "username" => $username,
                        "password" => $pass,
                        "password2" => $pass,
                        "email" => $register_data->email,
                        "groups" => array(2),
                        "dealer_type" => 1,
                        "settings" => "{\"CheckTimeCallback\":10,\"CheckTimeCall\":5}"
                    );
                }
                
                $user = new JUser;
                if (!$user->bind($data)) {
                    throw new Exception($user->getError());
                }
                if (!$user->save()) {
                    throw new Exception($user->getError());
                }
                $userID = $user->id;
                $user =& JUser::getInstance((int)$userID);
                //cсздание associated_client
                $clientform_model = Gm_ceilingHelpersGm_ceiling::getModel('ClientForm', 'Gm_ceilingModel');
                $client_data['client_name'] = $register_data->fio;
                $client_id = $clientform_model->save($client_data);
                $update['dealer_id'] = $userID;
                $update['associated_client'] = $client_id;
                if (empty($from_browser)) {
                    $update['android_id'] = $userID;
                }
                if (!$user->bind($update)) return false;
                if (!$user->save()) return false;
                $client_model = Gm_ceilingHelpersGm_ceiling::getModel('Client', 'Gm_ceilingModel');
                $client_model->updateClient($client_id, null, $userID);

                $dop_contacts_model = Gm_ceilingHelpersGm_ceiling::getModel('Clients_dop_contacts', 'Gm_ceilingModel');
                $dop_contacts_model->save($client_id, 1, $email);

                $user = clone JFactory::getUser($userID);
                unset($user->username, $user->password);
                $user->datetime = date('Y-m-d H:i:s');
                if (empty($from_browser)) {
                    $result = json_encode($this->crypt($userID, json_encode($user)));
                } else {
                    $result = json_encode($user);
                }

                $mailer = JFactory::getMailer();
                $config = JFactory::getConfig();
                $sender = array(
                    $config->get('mailfrom'),
                    $config->get('fromname')
                );
                $mailer->setSender($sender);
                $mailer->addRecipient($register_data->email);
                $body .= "Здавствуйте! Благодарим вас за регистрацию в приложении!\n";
                $body .= "Логин: $username\n";
                $body .= "Пароль: $pass\n";
                $mailer->setSubject('Регистрация в приложении');
                $mailer->setBody($body);
                $mailer->send();
            }

            if (empty($from_browser)) {
                die($result);
            } else {
                return $result;
            }
            
        }
        catch (Exception $e){
            die($e->getMessage());
        }
    }

    public function registerUser() {
        try {
            header('Content-type:application/json');
            $result = 'null';
            if (empty($_POST['data'])) {
                die($result);
            }
            $data = json_decode($_POST['data']);
            $key_number = $data->key_number;
            //$this->checkSubscription($data->key_number);
            $decrypt = $this->decrypt($data);
            if (empty($decrypt)) {
                die($result);
            }
            $register_data = $decrypt;

            $userModel = Gm_ceilingHelpersGm_ceiling::getModel('users');
            $id = $userModel->getUserByEmailAndUsername($register_data->email, $register_data->username);
            if (!empty($id)) {
                $result = json_encode((object)array("id" => $id->id, "username" => $register_data->username));
                $result = json_encode($this->crypt($key_number, $result));
            } else {
                $data = array(
                    "name" => $register_data->fio,
                    "username" => $register_data->username,
                    "password" => $register_data->username,
                    "password2" => $register_data->username,
                    "email" => $register_data->email,
                    "dealer_id" => $register_data->dealer_id,
                    "groups" => array(2,$register_data->group)
                );
                $user = new JUser;
                if (!$user->bind($data)) {
                    throw new Exception($user->getError());
                }
                if (!$user->save()) {
                    throw new Exception($user->getError());
                }
                $userID = $user->id;
                $result = json_encode((object)array("id" => $userID, "username" => $register_data->username));
                $result = json_encode($this->crypt($key_number, $result));

                $mailer = JFactory::getMailer();
                $config = JFactory::getConfig();
                $sender = array(
                    $config->get('mailfrom'),
                    $config->get('fromname')
                );
                $mailer->setSender($sender);
                $mailer->addRecipient($register_data->email);
                $body = '<html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8"><link rel="stylesheet" type="text/css" href="CSS/style_index.css"/></head>';
                $body .= '<body style="margin: 10px;">';
                $body .= "Здравствуйте! Данные для доступа к приложению:<br>";
                $body .= "Логин:$register_data->username<br>Пароль:$register_data->username";
                $body .= "</body>";
                $mailer->setSubject('Регистрационные данные');
                $mailer->isHtml(true);
                $mailer->Encoding = 'base64';
                $mailer->setBody($body);
                $send = $mailer->Send();
            }
            die($result);
        }
        catch (Exception $e){
            die($e->getMessage());
        }
    }

    public function addDataFromAndroid() {
        try {
            header('Content-type:application/json');
            $result = 'null';
            if (empty($_POST['data'])) {
                die($result);
            }
            $data = json_decode($_POST['data']);
            $key_number = $data->key_number;
            //$this->checkSubscription($data->key_number);
            $decrypt = $this->decrypt($data);
            if (empty($decrypt)) {
                die($result);
            }

            $model = Gm_ceilingHelpersGm_ceiling::getModel('api');
            $result = [];
            foreach ($decrypt as $item) {
                $table_name = $item->table_name;
                $table_data = $item->rows;
                $result[$table_name] = $model->save_or_update_data_from_android($table_name, $table_data);
            }
            if (empty($result)) {
                die('null');
            }
            $result = json_encode($result);
            $result = json_encode($this->crypt($key_number, $result));
            die($result);
        } catch(Exception $e) {
            die($e->getMessage());
        }
    }

    public function checkDataFromAndroid() {
        try {
            header('Content-type:application/json');
            $result = 'null';
            if (empty($_POST['data'])) {
                die($result);
            }
            $data = json_decode($_POST['data']);
            $key_number = $data->key_number;
            //$this->checkSubscription($data->key_number);
            $decrypt = $this->decrypt($data);
            if (empty($decrypt)) {
                die($result);
            }

            $model = Gm_ceilingHelpersGm_ceiling::getModel('api');
            $result = [];
            foreach ($decrypt as $item) {
                $table_name = $item->table_name;
                $table_data = $item->rows;
                $result[$table_name] = $model->update_android_ids_from_android($table_name, $table_data);
            }
            if (empty($result)) {
                die('null');
            }
            $result = json_encode($result);
            $result = json_encode($this->crypt($key_number, $result));
            die($result);
        } catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function deleteDataFromAndroid() {
        try {
            header('Content-type:application/json');
            $result = 'null';
            if (empty($_POST['data'])) {
                die($result);
            }
            $data = json_decode($_POST['data']);
            $key_number = $data->key_number;
            //$this->checkSubscription($data->key_number);
            $decrypt = $this->decrypt($data);
            if (empty($decrypt)) {
                die($result);
            }

            $model = Gm_ceilingHelpersGm_ceiling::getModel('api');
            $result = [];
            foreach ($decrypt as $item) {
                $table_name = $item->table_name;
                $table_data = $item->rows;
                $result[] = $model->delete_from_android($table_name, $table_data);
            }
            if (empty($result)) {
                die('null');
            }
            $result = (object)array('delete' => $result);
            $result = json_encode($result);
            $result = json_encode($this->crypt($key_number, $result));
            die($result);
        } catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    /*public function sendDataToAndroid() {
        try
        {
            $result = 'null';
            if (empty($_POST['data'])) {
                die($result);
            }
            $data = json_decode($_POST['data']);
            $key_number = $data->key_number;
            $decrypt = $this->decrypt($data);
            if (empty($decrypt)) {
                die($result);
            }

            $model = Gm_ceilingHelpersGm_ceiling::getModel('api');
            $result = $model->get_data_android($decrypt);
            if (empty($result)) {
                die('null');
            }
            $result = json_encode($result);
            $result = json_encode($this->crypt($key_number, $result));
            die($result);
        } catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }*/

	public function sendInfoToAndroidCallGlider() {
        try {
            header('Content-type:application/json');
            $result = 'null';
            if (empty($_POST['data'])) {
                die($result);
            }
            $data = json_decode($_POST['data']);
            $key_number = $data->key_number;
            //$this->checkSubscription($data->key_number);
            $decrypt = $this->decrypt($data);
            if (empty($decrypt)) {
                die($result);
            }

            $model = Gm_ceilingHelpersGm_ceiling::getModel('api');
            $result = $model->get_dealerInfo_androidCallGlider($decrypt);

            if (empty($result)) {
                die('null');
            }
            $result = json_encode($result);
            $result = json_encode($this->crypt($key_number, $result));
            die($result);
        } catch(Exception $e) {
            die($e->getMessage());
        }
    }

    /*public
    function sendImagesToAndroid()
    {
        try
        {
            $model = Gm_ceilingHelpersGm_ceiling::getModel('api');
            if(!empty($_POST['calculation_images']))
            {
                $data = json_decode($_POST['calculation_images']);

                $filename = md5("calculation_sketch".$data->id);
                $calc_image = file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/calculation_images/' . $filename . ".png");

                $filename = md5("cut_sketch".$data->id);
                $cut_image = file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/cut_images/' . $filename . ".png");                  

                $result = '{"id":';
                $result .= '"'.$data->id.'",';
                $result .= '"calc_image":';
                $result .= '"data:image/png;base64,'.base64_encode($calc_image).'",';
                $result .= '"cut_image":';
                $result .= '"data:image/png;base64,'.base64_encode($cut_image).'"}';
            }
            die($result);
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }*/

    /*public function sendMaterialToAndroid()
    {
        try
        {
            $model = Gm_ceilingHelpersGm_ceiling::getModel('api');
            if(!empty($_POST['sync_data']))
            {
                $table_data = json_decode($_POST['sync_data']);
                $result = $model->get_material_android($table_data);
            }
            
            
            die(json_encode($result));
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }*/

    /*public function sendMountersToAndroid()
    {
        try
        {
            $model = Gm_ceilingHelpersGm_ceiling::getModel('api');
            if(!empty($_POST['sync_data']))
            {
                $table_data = json_decode($_POST['sync_data']);
                $result = $model->get_mounters_android($table_data);
            }
            
            die(json_encode($result));
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }*/

    /*public function sendDealerInfoToAndroid()
    {
        try
        {
            $model = Gm_ceilingHelpersGm_ceiling::getModel('api');
            if(!empty($_POST['sync_data']))
            {
                $table_data = json_decode($_POST['sync_data']);
                $result = $model->get_dealerInfo_android($table_data);
            }
            die(json_encode($result));
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }*/

    public function getManagersAnalytic() {
        try {
            header('Content-type:application/json');
            $result = 'null';
            if (empty($_POST['data'])) {
                die($result);
            }
            $data = json_decode($_POST['data']);
            $key_number = $data->key_number;
            //$this->checkSubscription($data->key_number);
            $decrypt = $this->decrypt($data);
            if (empty($decrypt)) {
                die($result);
            }

            $model = Gm_ceilingHelpersGm_ceiling::getModel('api');

            $date1 = $decrypt->date1;
            $date2 = $decrypt->date2;
            $managers = implode(',', $decrypt->managers);
            $result = $model->getProjectsAnalytic($date1, $date2, $managers);

            if (empty($result)) {
                die('null');
            }
            $result = json_encode($result);
            $result = json_encode($this->crypt($key_number, $result));
            die($result);
        } catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    /*
         * Розничная версия
     */
    /*public function getMeasureTimes(){
        try{
            header('Access-Control-Allow-Origin: http://гмпотолки.рф');
            header('Access-Control-Allow-Credentials: true');
            header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
            header('Access-Control-Allow-Headers: Content-Type');
            header('Access-Control-Max-Age: 1000');
            header('Access-Control-Allow-Headers: Content-Type, Content-Range, Content-Disposition, Content-Description');

            $model = Gm_ceilingHelpersGm_ceiling::getModel('api');
            if(!empty($_POST['date']))
            {
                $date = json_decode($_POST['date']);
                $times = $model->get_measure_time($date->date);
                $result = $times;
                die(json_encode($result));
            }
            else die($_POST['date']);
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }*/

    /*public function recToMeasure() {
        try{
            $model = Gm_ceilingHelpersGm_ceiling::getModel('api');
            if(!empty($_POST['rec_data'])) {
                $data = json_decode($_POST['rec_data']);
                $result = $model->rec_to_measure($data);

                die(json_encode($result));
            }
            else {
                throw new Exception("Empty post data");
            }
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }*/

    public function changePwd() {
        header('Content-type:application/json');
        try {
            $result = 'null';
            if (empty($_POST['data'])) {
                die($result);
            }
            $data = json_decode($_POST['data']);
            $key_number = $data->key_number;
            //$this->checkSubscription($data->key_number);
            $decrypt = $this->decrypt($data);
            if (empty($decrypt)) {
                die($result);
            }

            $model = Gm_ceilingHelpersGm_ceiling::getModel('api');

            $result = $model->change_password($decrypt);
            //отправить письмо с новым паролем
            $dealer = JFactory::getUser($decrypt->user_id);
            $email = $dealer->email;
            $server_name = $_SERVER['SERVER_NAME'];
            $site = "http://$server_name/index.php?option=com_users&view=login";
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

            $body .= '<p>Тел.: +7(473)212-34-01</p>';

            $body .= '<p>Почта: gm-partner@mail.ru</p>';
            $body .= '<p>Адрес: г. Воронеж, Проспект Труда, д. 48, литер. Е-Е2</p>';
            $body .= '</div></td></tr></table>';
            $body .= "<div style=\"width: 100%\">Здравствуйте! Пароль от Вашего кабинет на сайте $site был изменен.<br> Обновленные данные регистрации:<br>
                    Логин: $dealer->username<br>Пароль: $data->password<br>
                    Ссылка для входа в кабинет: <a href=\"$site\">Войти</a><br></div></body>
                    ";
            $mailer->setSubject('Изменение пароля от личного кабинета');
            $mailer->isHtml(true);
            $mailer->Encoding = 'base64';
            $mailer->setBody($body);
            $send = $mailer->Send();

            if (empty($result)) {
                die('null');
            }
            $result = json_encode($result);
            $result = json_encode($this->crypt($key_number, $result));
            die($result);
        } catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    /*
     * CEH4TOP IOS Клиентская версия
     *
     * Входящие данные в $Data
     * Address, ApartmentNumber, Date, Name, Phone, Type = Client;
     * Ответ: любой
     * */
    /*public function getTimes() {
        try{
            $model = Gm_ceilingHelpersGm_ceiling::getModel('api');
            $f = fopen('php://input', 'r');
            $jsonData = stream_get_contents($f);
            $Data = json_decode($jsonData);

            if(!empty($Data)) {
                $times = $model->get_measure_time($Data->date);
                foreach ($times as $key => $value)
                    $times[$key] = substr($value, 0, 5);
                $Answer = ["status" => "success", "times" => $times];
            }
            else {
                $Answer = ["status" => "error", "title" => "Не успешно", "message" => "Произошла ошибка при получении свободного времени замера, попробуйте позже"];
            }

            die(json_encode($Answer)); 
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());

            $Answer = ["status" => "error", "title" => "Не успешно", "message" => $e->getMessage()];
            die(json_encode($Answer));
        }
    }
    public function addNewClient() {
        try{
            $f = fopen('php://input', 'r');
            $jsonData = stream_get_contents($f);
            $Data = json_decode($jsonData);

            $model = Gm_ceilingHelpersGm_ceiling::getModel('api');
            if(!empty($Data)){
                $Data->address = "$Data->address, квартира: $Data->apartmentNumber";
                $Answer = ["status" => "success", "title" => "Замер сформирован", "message" => "В ближайшее время с Вами свяжется менеджер для подтверждения."];
                $Answer["answer"] = $model->rec_to_measure($Data);
                if ($Answer["answer"] == 'client_found') {
                    die('client_found');
                }
            }
            else {
                 $Answer = ["status" => "error", "title" => "Не успешно", "message" => "Замер не успешно добавлен в базу, попробуйте позже"];
            }

            die(json_encode($Answer));
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());

            $Answer = ["status" => "error", "title" => "Не успешно", "message" => $e->getMessage()];
            die(json_encode($Answer));
        }
    }
    public function changePswd(){
        try{
            $model = Gm_ceilingHelpersGm_ceiling::getModel('api');
            $f = fopen('php://input', 'r');
            $jsonData = stream_get_contents($f);
            $Data = json_decode($jsonData);
            if(!empty($Data)){
                if ($model->change_password($Data) == 1) {
                    $Answer = ["status" => "success", "title" => "Успешно", "message" => "Пароль успешно изменен!"];
                } else {
                    $Answer = ["status" => "error", "title" => "Не успешно", "message" => "Неверный старый пароль!"];
                }
            }
            else {
                $Answer = ["status" => "error", "title" => "Не успешно", "message" => "Пароль не изменен, попробуйте позже"];
            }
            die(json_encode($Answer));
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());

            $Answer = ["status" => "error", "title" => "Не успешно", "message" => $e->getMessage()];
            die(json_encode($Answer));
        }
    }

    public function iOSauthorisation(){
        try{
            $model = Gm_ceilingHelpersGm_ceiling::getModel('api');
            $f = fopen('php://input', 'r');
            $jsonData = stream_get_contents($f);
            $Data = json_decode($jsonData);
            if (empty($Data->username) || empty($Data->password)) {
                throw new Exception('Empty Data', 777);
            }

            $username = mb_ereg_replace('[^\d]', '', $Data->username);
            if (mb_substr($username, 0, 1) == '9' && strlen($username) == 10)
            {
                $username = '7'.$username;
            }
            if (strlen($username) != 11)
            {
                throw new Exception('Invalid phone number', 777);
            }
            if (mb_substr($username, 0, 1) != '7')
            {
                $username = substr_replace($username, '7', 0, 1);
            }

            $user = JFactory::getUser($model->getUserId($username));
            $Password = $Data->password;
            $verifyPass = JUserHelper::verifyPassword($Password, $user->password, $user->id);
            if ($verifyPass)
            {
                $table_data->dealer_id = $user->id;
                $result = $model->get_data_android($table_data);
                $Answer = ["status" => "success", "title" => "Успешно", "message" => "Вы успешно авторизовались и ваши заказы востановленны", "data"=>$result];
                die(json_encode($Answer));
            }
            else
            {
                throw new Exception("Wrong password", 777);
            }
        }
        catch(Exception $e)
        {
            $e_message = $e->getMessage();
            if ($e->getCode() == 777) {
                $Answer = ["status" => "error", "title" => "Не успешно", "message" => $e_message];
                die(json_encode($Answer));
            }
            else {
                Gm_ceilingHelpersGm_ceiling::add_error_in_log($e_message, __FILE__, __FUNCTION__, func_get_args());
            }
        }
    }*/

    public function getGoodsCategories(){
        $model = Gm_ceilingHelpersGm_ceiling::getModel('stock');
        $result = $model->getGoodsCategories();
        die(quotemeta(json_encode($result,JSON_UNESCAPED_UNICODE )));
    }
    public function getGoodsByCategory(){
        $result = null;
        $category = $_POST['category'];
        if(!empty($category)) {
            $model = Gm_ceilingHelpersGm_ceiling::getModel('stock');
            $result = $model->getGoodsByCategory($category);
        }
        die(quotemeta(json_encode($result,JSON_UNESCAPED_UNICODE)));
    }

    public function getGoodsInCategories(){
        $model = Gm_ceilingHelpersGm_ceiling::getModel('stock');
        $result = $model->getGoodsInCategories();
        die(quotemeta(json_encode($result,JSON_UNESCAPED_UNICODE)));
    }

    public function getProjectByDealerId($dealerId){

    }

    public function getClientsByDealerId($dealerId){

    }
    public function getJobs(){
        $model = Gm_ceilingHelpersGm_ceiling::getModel('mount');
        $result = $model->getJobs();
        die(json_encode($result,JSON_UNESCAPED_UNICODE));
    }

}

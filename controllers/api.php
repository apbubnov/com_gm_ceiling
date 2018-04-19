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
    public function &getModel($name = 'Api', $prefix = 'Gm_ceilingModel', $config = array())
    {
        return parent::getModel($name, $prefix, array('ignore_request' => true));
    }

    public function Authorization_FromAndroid()
    {
        try
        {
            $authorization = json_decode($_POST['authorizations']);
            $model = $this->getModel();

            $username = mb_ereg_replace('[^\d]', '', $authorization->username);
            if (mb_substr($username, 0, 1) == '9' && strlen($username) == 10)
            {
                $username = '7'.$username;
            }
            if (strlen($username) != 11)
            {
                throw new Exception('Invalid phone number');
            }
            if (mb_substr($username, 0, 1) != '7')
            {
                $username = substr_replace($username, '7', 0, 1);
            }

            $user = JFactory::getUser($model->getUserId($username));
            $Password = $authorization->password;
            $verifyPass = JUserHelper::verifyPassword($Password, $user->password, $user->id);
            if ($verifyPass)
            {

                die(json_encode($user));

            }
            else
            {
                die(json_encode(null));
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
    public function register_from_android(){
        try
        {
            if(!empty($_POST['r_data'])){
                $register_data = json_decode($_POST['r_data']);
                if(!empty($register_data)){
                    $model = Gm_ceilingHelpersGm_ceiling::getModel('api');
                    $result = $model->register_from_android($register_data);
                    $dealer_id = $result->new_id;
                    if(!empty($dealer_id)){
                        $dealer = JFactory::getUser($dealer_id);
                        $email = $dealer->email;
                        $server_name = $_SERVER['SERVER_NAME'];
                        $site = "http://$server_name/index.php?option=com_users&view=login";
                        $code = md5($dealer_id.'dealer_instruction');
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
                        $users_model->addDealerInstructionCode($dealer_id, $code, 1);    
                        $callback_model = Gm_ceilingHelpersGm_ceiling::getModel('callback');
                        $callback_model->save(date('Y-m-d H:i:s'),"Регистрация в android-приложении",$dealer->associated_client,1);
                    }
                    die(json_encode($result));
                }
            }
            else{
                die(json_encode(null));
            }
        }
        catch(Exception $e)
        {
            die($e);
        }
    }
        public
        function addDataFromAndroid()
        {
            try {
                $model = Gm_ceilingHelpersGm_ceiling::getModel('api');
                $result = [];
                foreach ($_POST as $key => $value) {
                    $table_name = $key;
                    $table_data = json_decode($_POST[$key]);
                    $result[$key] = $model->save_or_update_data_from_android($table_name, $table_data);
                }
                if (!$result) {
                    die(json_encode($_POST));
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

        public
        function checkDataFromAndroid()
        {
            try {
                $model = Gm_ceilingHelpersGm_ceiling::getModel('api');
                $result = [];
                foreach ($_POST as $key => $value) {
                    $table_name = $key;
                    $table_data = json_decode($_POST[$key]);
                    $result[$key] = $model->update_android_ids_from_android($table_name, $table_data);
                }
                if (!$result) {
                    die(json_encode($_POST));
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

        public
        function deleteDataFromAndroid()
        {
            try
            {
                $model = Gm_ceilingHelpersGm_ceiling::getModel('api');
                foreach ($_POST as $key => $value) {
                    $table_name = $key;
                    $table_data = json_decode($_POST[$key]);
                    $result = $model->delete_from_android($table_name, $table_data);
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

        public
        function sendDataToAndroid()
        {
            try
            {
                $model = Gm_ceilingHelpersGm_ceiling::getModel('api');
                if(!empty($_POST['synchronization']))
                {
                    $table_data = json_decode($_POST['synchronization']);
                    $result = $model->get_data_android($table_data);
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

        public
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
                $date = date("d.m.Y H:i:s");
                $files = "components/com_gm_ceiling/";
                file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
                throw new Exception('Ошибка!', 500);
            }
        }

        public function sendMaterialToAndroid()
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
                $date = date("d.m.Y H:i:s");
                $files = "components/com_gm_ceiling/";
                file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
                throw new Exception('Ошибка!', 500);
            }
        }
        public function sendMountersToAndroid()
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
                $date = date("d.m.Y H:i:s");
                $files = "components/com_gm_ceiling/";
                file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
                throw new Exception('Ошибка!', 500);
            }
        }
        public function sendDealerInfoToAndroid()
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
                $date = date("d.m.Y H:i:s");
                $files = "components/com_gm_ceiling/";
                file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
                throw new Exception('Ошибка!', 500);
            }
        }
        public function check_update(){
            try
            { 
                $result = false;
                if(!empty($_POST['sync_data']))
                {
                    $version = json_decode($_POST['sync_data'])->version.'.apk';
                    $path = $_SERVER['DOCUMENT_ROOT'] . "/files/android_app/";
                    $files = array_diff(scandir($path,1), array('..', '.'));
                    if(!file_exists($path.$version)&&count($files)>0){
                        $result = $files[0];
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
    /*
         * Розничная версия
     */
    public function getMeasureTimes(){
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
            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);
        }
    }

    public function recToMeasure(){
        try{
            $model = Gm_ceilingHelpersGm_ceiling::getModel('api');
            if(!empty($_POST['rec_data'])){
                $data = json_decode($_POST['rec_data']);
                $result = $model->rec_to_measure($data);
                die(json_encode($result));
            }
            else throw new Exception("Empty post data");
        }
        catch(Exception $e)
        {
            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);
        }
    }
    /*
     * CEH4TOP IOS Клиентская версия
     *
     * Входящие данные в $Data
     * Address, ApartmentNumber, Date, Name, Phone, Type = Client;
     * Ответ: любой
     * */

    public function addNewClient() {
        $f = fopen('php://input', 'r');
        $jsonData = stream_get_contents($f);
        $Data = json_decode($jsonData);

        $Answer = ["status" => "success", "title" => "Успешно", "message" => "Замер успешно добавлен в базу"];
        $Error = ["status" => "error", "title" => "Не успешно", "message" => "Замер не успешно добавлен в базу, попробуйте позже"];

        if ($jsonData) {
            die(json_encode($Answer));
        } else {
            die(json_encode($Error));
        }
    }

    }
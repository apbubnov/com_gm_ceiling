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
            $user = JFactory::getUser($model->getUserId($authorization->username));
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
        function addImagesFromAndroid()
        {
            try {
                $model = Gm_ceilingHelpersGm_ceiling::getModel('api');
                if (!empty($_POST['calculation_images'])) {
                    $data = json_decode($_POST['calculation_images']);
                    $calc_image = base64_decode(str_replace('data:image/png;base64,', '', $data->calc_image));
                    $cut_image = base64_decode(str_replace('data:image/png;base64,', '', $data->cut_image));

                    $filename = md5("calculation_sketch".$data->android_id);
                    file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/calculation_images/' . $filename . ".png", $calc_image);

                    $filename = md5("cut_sketch".$data->android_id);
                    file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/cut_images/' . $filename . ".png", $cut_image);
                }
                die(json_encode($data->android_id));
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

    }
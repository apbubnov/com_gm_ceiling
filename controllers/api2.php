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
class Gm_ceilingControllerApi2 extends JControllerLegacy
{
    public function sendMaterialToAndroid()
    {
        try
        {
            $model = Gm_ceilingHelpersGm_ceiling::getModel('api2');
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
            $model = Gm_ceilingHelpersGm_ceiling::getModel('api2');
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
            $model = Gm_ceilingHelpersGm_ceiling::getModel('api2');
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


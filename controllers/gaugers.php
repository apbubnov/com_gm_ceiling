<?php

/**
 * @version    CVS: 1.0.0
 * @package    Com_Gm_ceiling
 * @author     Mikhail  <vms@itctl.ru>
 * @copyright  2016 Mikhail 
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access
defined('_JEXEC') or die;

/**
 * Team controller class.
 *
 * @since  1.6
 */
class Gm_ceilingControllerGaugers extends JControllerForm
{

	public function SaveDayOff() {
		try
		{
			$date1 = $_POST['datetime1'];
			$date2 = $_POST['datetime2'];
			$id = $_POST['id_gauger'];
			$model = Gm_ceilingHelpersGm_ceiling::getModel('gaugers');
			$model->SaveDayOff($id, $date1, $date2);
		}
		catch(Exception $e)
        {
            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);
        }
	}

	public function GetGaugersWorkDayOff() {
		try
		{
			$date = $_POST['date'];
			$id = $_POST['id'];
			$model = Gm_ceilingHelpersGm_ceiling::getModel('gaugers');
			$request = $model->GetGaugersWorkDayOff($id, $date);

			die(json_encode($request));
		}
		catch(Exception $e)
        {
            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);
        }
	}

	public function FindFreeDay() {
		try
		{
			$date = $_POST["date"];
			$id = $_POST["id"];

			$model = Gm_ceilingHelpersGm_ceiling::getModel('teams');
			$request = $model->FindFreeDay($id, $date);
					
			die(json_encode($request));
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

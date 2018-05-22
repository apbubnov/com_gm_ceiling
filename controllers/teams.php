<?php
/**
 * @version    CVS: 1.0.0
 * @package    Com_Gm_ceiling
 * @author     Mikhail  <vms@itctl.ru>
 * @copyright  2016 Mikhail 
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

/**
 * Teams list controller class.
 *
 * @since  1.6
 */
class Gm_ceilingControllerTeams extends Gm_ceilingController {

	public function GetMounting() {
		try
		{
			$date = $_POST["date"];
			$id = $_POST["id"];
			$model = Gm_ceilingHelpersGm_ceiling::getModel('teams');
			$mounting = $model->GetMountingBrigadeDay($id, $date);
					
			die(json_encode($mounting));
		}
		catch(Exception $e)
        {
          Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());

        }
	}

	public function SaveFreeDay() {
		try
		{
			$date = $_POST["date"];
			$id = $_POST["id"];
			$time1 = $_POST["time1"];
			$time2 = $_POST["time2"];
			$date1 = $date." ".$time1;
			$date2 = $date." ".$time2;

			$model = Gm_ceilingHelpersGm_ceiling::getModel('teams');
			$request = $model->SaveFreeDay($id, $date1, $date2);
				
			if ($request->id_user != null) {
				$answer = "ok";
			} else {
				$answer = "no";
			}
					
			die(json_encode($answer));
		}
		catch(Exception $e)
        {
          Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());

        }
	}

	public function DeleteFreeDay() {
		try
		{
			$date = $_POST["date"];
			$id = $_POST["id"];

			$model = Gm_ceilingHelpersGm_ceiling::getModel('teams');
			$request = $model->DeleteFreeDay($id, $date);
				
			if (empty($request)) {
				$answer = "ok";
			} else {
				$answer = "no";
			}
					
			die(json_encode($answer));
		}
		catch(Exception $e)
        {
          Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());

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
          Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());

        }
	}

	public function GetMountingForSaveDayOff() {
		try
		{
			$datetime1 = $_POST["date"]." ".$_POST["time1"];
			$datetime2 = $_POST["date"]." ".$_POST["time2"];
			$id = $_POST["id"];
			$model = Gm_ceilingHelpersGm_ceiling::getModel('teams');
			$mounting = $model->GetMountingForSaveDayOff($id, $datetime1, $datetime2);

			if ($mounting->count == 0) {
				$ansver = "ok";
			} else {
				$ansver = "no";
			}
					
			die($ansver);
		}
		catch(Exception $e)
        {
          Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());

        }
	}

	/**
	 * Proxy for getModel.
	 *
	 * @param   string  $name    The model name. Optional.
	 * @param   string  $prefix  The class prefix. Optional
	 * @param   array   $config  Configuration array for model. Optional
	 *
	 * @return object	The model
	 *
	 * @since	1.6
	 */
	public function &getModel($name = 'Teams', $prefix = 'Gm_ceilingModel', $config = array())
	{
		try
		{
			$model = parent::getModel($name, $prefix, array('ignore_request' => true));

			return $model;
		}
		catch(Exception $e)
        {
          Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());

        }
	}
}

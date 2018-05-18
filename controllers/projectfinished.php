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
class Gm_ceilingControllerProjectfinished extends JControllerLegacy {
	
	function ChangeStatusOfRead() {
		try
		{
			$masID = $_POST['masID'];

			$model = Gm_ceilingHelpersGm_ceiling::getModel('Projectfinished');
			foreach ($masID as $value) {
				$model->ChangeStatusOfRead($value);	
			}
			
			die(null);
		}
		catch(Exception $e)
        {
            add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());

        }
	}
	
	/* function GetProjectsFilter() {
		try
		{
			$datetime1 = $_POST['datetime1'];
			$datetime2 = $_POST['datetime2'];
			$id = $_POST['$id'];
			$model = Gm_ceilingHelpersGm_ceiling::getModel('Team');
			$projects = $model->GetProjectsFilter($id, $datetime1, $datetime2);
			die(json_encode($projects));
		}
		catch(Exception $e)
        {
            add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());

        }
	}

	function MoveBrigade() {
		try
		{
			$id_mounter = $_POST['id_mounter'];
			$id_brigade = $_POST['brigade'];
			$model = Gm_ceilingHelpersGm_ceiling::getModel('Team');
			$model->MoveBrigade($id_mounter, $id_brigade);
			$current_brigade = $_POST['current_brigade'];
			$mounters = $model->GetMounters($current_brigade);
			
			$json = [];
			$i = 0;
			if (!empty($mounters)) {
				$str = "[";
				foreach ($mounters as $value) {
					$json[$i] = ["id" => $value->id, "name" => $value->name, "phone" => $value->phone];
					$str .= substr(json_encode($json[$i]), 0, -1).",\"passport\":\"data:image/png;base64,".base64_encode($value->pasport)."\"},";
					$i++;
				}
				$str = substr($str, 0, -1)."]";
				//throw new Exception($str);
				die($str);
			}
		}
		catch(Exception $e)
        {
            add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());

        }
	} */

}

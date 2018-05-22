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
 * Calculations list controller class.
 *
 * @since  1.6
 */
class Gm_ceilingControllerCalculations extends Gm_ceilingController
{
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
	public function &getModel($name = 'Calculations', $prefix = 'Gm_ceilingModel', $config = array())
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

	public function GetBusyMounters() {
		try
		{
			$date = $_POST["date"];
			$dealer = $_POST["dealer"];
	        
			$model = Gm_ceilingHelpersGm_ceiling::getModel('calculations');
			$mounters = $model->FindBusyMounters($date, $date, $dealer);

			die(json_encode($mounters));
		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());

        }
	}

	public function GetBusyGauger() {
		try
		{
			$date = $_POST["date"];
			$dealer = $_POST["dealer"];
	        
			$model = Gm_ceilingHelpersGm_ceiling::getModel('calculations');
			$gauger = $model->FindBusyGauger($date, $date, $dealer);

			die(json_encode($gauger));
		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());

        }
	}

}

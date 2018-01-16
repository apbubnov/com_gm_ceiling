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

/**
 * Client controller class.
 *
 * @since  1.6
 */
class Gm_ceilingControllerMissed_Calls extends JControllerLegacy
{
	public function addObrCall()
	{
		try
		{
			$jinput = JFactory::getApplication()->input;
            $call_id = $jinput->get('call_id', null, 'STRING');
            $user       = JFactory::getUser();
			$userId     = $user->get('id');

            $model = Gm_ceilingHelpersGm_ceiling::getModel('missed_calls');
            $result = $model->addCall($call_id, $userId);

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

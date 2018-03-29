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
 * Clients list controller class.
 *
 * @since  1.6
 */
class Gm_ceilingControllerClients extends Gm_ceilingController
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
	public function &getModel($name = 'Clients', $prefix = 'Gm_ceilingModel', $config = array())
	{
		try
		{
			$model = parent::getModel($name, $prefix, array('ignore_request' => true));

			return $model;
		}
		catch(Exception $e)
        {
            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);
        }
	}

	public function searchClients($search)
	{
		try
		{
            $jinput = JFactory::getApplication()->input;
            $search = $jinput->get('search_text', '', 'STRING');
            $model_clients = $this->getModel('clients', 'Gm_ceilingModel');
            $result = $model_clients->searchClients($search);
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

	public function getEmailsByIds()
	{
		try
		{
            $jinput = JFactory::getApplication()->input;
            $ids = $jinput->get('ids', [], 'ARRAY');
            $model_dop_contacts = $this->getModel('clients_dop_contacts', 'Gm_ceilingModel');
            $emails = [];
            foreach ($ids as $key => $value)
            {
            	$result = $model_dop_contacts->getEmailByClientID($value);
            	foreach ($result as $key2 => $item)
	            {
	            	array_push($emails, $item);
	            }
            }
            
			die(json_encode($emails));
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

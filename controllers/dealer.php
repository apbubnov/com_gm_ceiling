<?php
/**
 * @version    CVS: 1.0.0
 * @package    Com_Gm_ceiling
 * @author     Mikhail "Spectral Eye" Vinyukov <vms@itctl.ru>
 * @copyright  2016 Mikhail "Spectral Eye" Vinyukov
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

/**
 * Parts list controller class.
 *
 * @since  1.6
 */
class Gm_ceilingControllerDealer extends Gm_ceilingController
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
		$model = parent::getModel($name, $prefix, array('ignore_request' => true));

		return $model;
	}
	
	/**
	 * Method to save a user's profile data.
	 *
	 * @return void
	 *
	 * @throws Exception
	 * @since  1.6
	 */
	public function updatedata($key = NULL, $urlVar = NULL)
	{
		try
		{
	        $app = JFactory::getApplication();
	        $user = JFactory::getUser();
	        $userID = $user->id;
	        $jinput = $app->input;
	        $data = $jinput->get('jform', array(), 'array');
	        $model_dealer_info = Gm_ceilingHelpersGm_ceiling::getModel('dealer_info');
	        $result = $model_dealer_info->updateMarginAndMount($user->dealer_id, $data);
	        if($result == 1) $message = "Успешно сохранено!";
	        else $message = "Возникла ошибка сохранения!";
	        $this->setMessage($message);
	        $url  = 'index.php?option=com_gm_ceiling&view=mainpage&type=dealermainpage';
			$this->setRedirect(JRoute::_($url, false));
		}
		catch(Exception $e)
        {
            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);
        }
	}

	public function create_dealer()
	{
        try
		{
			$app = JFactory::getApplication();
	        $jinput = $app->input;
	        $name = $jinput->get('fio', null, 'STRING');
	        $phone = $jinput->get('phone', null, 'STRING');
			//Создание клиента
			$clientform_model =Gm_ceilingHelpersGm_ceiling::getModel('ClientForm', 'Gm_ceilingModel');
			$client_data['client_name'] = $name;
			$client_data['manager_id'] = $user->id;
			$client_data['created'] = date("Y-m-d");
			$client_data['client_contacts'] = $phone;
			$client_id = $clientform_model->save($client_data);
			//создание user'а
			$dealer_id = Gm_ceilingHelpersGm_ceiling::registerUser($name, $phone, "$client_id@$client_id", $client_id);
			$client_model = Gm_ceilingHelpersGm_ceiling::getModel('Client', 'Gm_ceilingModel');
			$client_model->updateClient($client_id,null,$dealer_id);
	        die($dealer_id);
	    }
	    catch(Exception $e)
        {
            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);
        }
	}
	
	public function create_designer()
	{
        try
		{
			$app = JFactory::getApplication();
	        $jinput = $app->input;
	        $name = $jinput->get('fio', null, 'STRING');
	        $phone = $jinput->get('phone', null, 'STRING');
			//Создание клиента
			$clientform_model =Gm_ceilingHelpersGm_ceiling::getModel('ClientForm', 'Gm_ceilingModel');
			$client_data['client_name'] = $name;
			$client_data['manager_id'] = $user->id;
			$client_data['created'] = date("Y-m-d");
			$client_data['client_contacts'] = $phone;
			$client_id = $clientform_model->save($client_data);
			//создание user'а
			$dealer_id = Gm_ceilingHelpersGm_ceiling::registerUser($name, $phone, "$client_id@$client_id", $client_id, 3);
			$client_model = Gm_ceilingHelpersGm_ceiling::getModel('Client', 'Gm_ceilingModel');
			$client_model->updateClient($client_id,null,$dealer_id);
	        die($dealer_id);
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

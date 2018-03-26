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
            $user = JFactory::getUser();
	        $name = $jinput->get('fio', null, 'STRING');
			$phone = $jinput->get('phone', null, 'STRING');
			$city  = $jinput->get('city',null,'STRING');
			//Создание клиента
			$clientform_model =Gm_ceilingHelpersGm_ceiling::getModel('ClientForm', 'Gm_ceilingModel');
			$client_data['client_name'] = $name;
			$client_data['manager_id'] = $user->id;
			$client_data['client_contacts'] = $phone;

			$client_id = $clientform_model->save($client_data);
			if ($client_id == 'client_found')
			{
				die('client_found');
			}
			else
			{
				//создание user'а
				$dealer_id = Gm_ceilingHelpersGm_ceiling::registerUser($name, $phone, "$client_id@$client_id", $client_id);
				$client_model = Gm_ceilingHelpersGm_ceiling::getModel('Client', 'Gm_ceilingModel');
				$client_model->updateClient($client_id,null,$dealer_id);
				$dealer_info_model = Gm_ceilingHelpersGm_ceiling::getModel('Dealer_info', 'Gm_ceilingModel');
				$dealer_info_model->update_city($dealer_id,$city);
		        die($dealer_id);
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
	
	public function create_designer()
	{
        try
		{
			$app = JFactory::getApplication();
	        $jinput = $app->input;
	        $name = $jinput->get('fio', null, 'STRING');
	        $phone = $jinput->get('phone', null, 'STRING');
	        $designer_type = $jinput->get('designer_type', null, 'INT');
            $user = JFactory::getUser();
			//Создание клиента
			$clientform_model =Gm_ceilingHelpersGm_ceiling::getModel('ClientForm', 'Gm_ceilingModel');
			$client_data['client_name'] = $name;
			$client_data['manager_id'] = $user->id;
			$client_data['client_contacts'] = $phone;
			$client_id = $clientform_model->save($client_data);
			if ($client_id == 'client_found')
			{
				die('client_found');
			}
			else
			{
				//создание user'а
				$dealer_id = Gm_ceilingHelpersGm_ceiling::registerUser($name, $phone, "$client_id@$client_id", $client_id, $designer_type);
				$client_model = Gm_ceilingHelpersGm_ceiling::getModel('Client', 'Gm_ceilingModel');
				$client_model->updateClient($client_id,null,$dealer_id);
		        die($dealer_id);
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

    public function add_in_table_recoil_map_project()
    {
        try
        {
            $app = JFactory::getApplication();
            $jinput = $app->input;
            $id = $jinput->get('id', null, 'int');
            $sum = $jinput->get('sum', null, 'STRING');

            $recoil_map_model =Gm_ceilingHelpersGm_ceiling::getModel('recoil_map_project');
            $result = $recoil_map_model->save($id, NULL, $sum);
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
	public function sendEmail($email=null,$subject=null,$text=null){
        try{
			$flag = 0;
            $jinput = JFactory::getApplication()->input;
            $email = (empty($email)) ? $jinput->get('email', null, 'STRING') : $email;
			$client_id = $jinput->get('client_id', null, 'STRING');
			$flag_ajax=  $jinput->get('ajax', 0, 'INT');
			if(!empty($client_id)){
				$dop_contact_model = Gm_ceilingHelpersGm_ceiling::getModel('clients_dop_contacts');
				$dop_contact_model->save($client_id,1,$email);
				$flag_ajax = 1;
			}
            
            $subject = (empty($subject)) ? $jinput->get('subj', null, 'STRING') : $subject;
            $text = (empty($text)) ? $jinput->get('text', null, 'STRING') : $text;
            $mailer = JFactory::getMailer();
            $config = JFactory::getConfig();
            $sender = array(
                $config->get('mailfrom'),
                $config->get('fromname')
            );
            $mailer->setSender($sender);
            $mailer->addRecipient($email);
            $mailer->setSubject($subject);
            $mailer->setBody($text);
            //$mailer->addAttachment($sheets_dir.$filename);
			$send = $mailer->Send();
			if($flag_ajax == 1){
				die(json_encode(true));
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
	public function send_out_to_dealers(){
		try
        {
            $app = JFactory::getApplication();
            $jinput = $app->input;
			$text = $jinput->get('text', null, 'STRING');
			$subject = $jinput->get('subj', null, 'STRING');
			$user_model = Gm_ceilingHelpersGm_ceiling::getModel('users');
			$dop_contact_model = Gm_ceilingHelpersGm_ceiling::getModel('clients_dop_contacts');
			$dealers = $user_model->getDealers();
			$emails = [];
			foreach($dealers as $dealer){
				$tmp = $dop_contact_model->getEmailByClientID($dealer->associated_client);
				foreach ($tmp as $value) {
					if(!empty($value->contact)){
						$emails[]=$value->contact;
					}
				}
			}

			foreach($emails as $email){
				$res = $this->sendEmail($email,$subject,$text);
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

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
class Gm_ceilingControllerDealer extends JControllerLegacy
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
	        $jinput = $app->input;
	        $data = $jinput->get('jform', array(), 'array');
            $user = JFactory::getUser($data['dealer_id']);
            unset($data['dealer_id']);
	        $model_dealer_info = Gm_ceilingHelpersGm_ceiling::getModel('dealer_info');
	        $result = $model_dealer_info->updateMarginAndMount($user->id, $data);
	        if($result == 1) $message = "Успешно сохранено!";
	        else $message = "Возникла ошибка сохранения!";
	        $this->setMessage($message);
	        $url  = ($user->dealer_type != 7) ? 'index.php?option=com_gm_ceiling&view=mainpage&type=dealermainpage' : 'index.php?option=com_gm_ceiling&view=clientcard&type=builder&id='.$user->associated_client ;
			$this->setRedirect(JRoute::_($url, false));
		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());

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
			$client_data['client_name'] = mb_ereg_replace('[^\dA-Za-zА-ЯЁа-яё ]', '', $name);
			$client_data['manager_id'] = mb_ereg_replace('[^\d]', '', $user->id);
			$client_data['client_contacts'] = mb_ereg_replace('[^\d]', '', $phone);

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
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());

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
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());

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
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());

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
            $mailer->isHtml(true);
            $mailer->Encoding = 'base64';
            $mailer->setBody($text);
            //$mailer->addAttachment($sheets_dir.$filename);
			$send = $mailer->Send();
			if($flag_ajax == 1){
				die(json_encode(true));
			}
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());

        }
    }
	public function send_out_to_dealers(){
		try
        {
            $app = JFactory::getApplication();
            $jinput = $app->input;
			$dealer_ids = $jinput->get('dealer_ids', null, 'ARRAY');
			$comm_id = $jinput->get('comm_id', null, 'INT');

			$emails = $this->getEmailsByIds($dealer_ids);

			$comm_model = Gm_ceilingHelpersGm_ceiling::getModel('commercial_offer');
			$comm_offers = $comm_model->getData("`id` = $comm_id");

			$subject = $comm_offers[0]->subject;
			$text = urldecode(base64_decode($comm_offers[0]->text));
			$text = html_entity_decode(preg_replace("/\%u+([0-9A-F]{4})/", "&#x\\1;", $text), ENT_NOQUOTES, 'UTF-8');

			foreach($emails as $email){
				$res = $this->sendEmail($email->contact,$subject,$text);
			}
            die(json_encode($emails));
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());

        }
	}

	public function getEmailsByIds($ids)
	{
		try
		{
            $jinput = JFactory::getApplication()->input;
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
            
			return($emails);
		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());

        }
	}

	function change_city(){
		try
        {
			$app = JFactory::getApplication();
            $jinput = $app->input;
			$dealer_id = $jinput->get('dealer_id', null, 'STRING');
			$city =  $jinput->get('city', null, 'STRING');
			$dealer_info_model = Gm_ceilingHelpersGm_ceiling::getModel('dealer_info');
			$dealer_info_model->update_city($dealer_id,$city);
			die(json_encode(true));
		}
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());

        }
	}

	function save_data_to_session(){
		try{			
			$jinput = JFactory::getApplication()->input;
			$user_id = $jinput->get("user",null,'INT');
			$filter_city = $jinput->get('filter_city',"","STRING");
			$filter_manager = $jinput->get('filter_manager',"","STRING");
			$filter_status = $jinput->get('filter_status',"","STRING");
			$client = $jinput->get('client','',"STRING");
			$limit = $jinput->get('limit','','STRING');
			$dealer_id = $jinput->get("dealer_id","","STRING");
			if(!empty($user_id)&&((!empty($filter_city)))||!empty($limit)||!empty($filter_manager)||!empty($filter_status)){
				$_SESSION["dealers_$user_id"] = ["filter_city"=>$filter_city,"filter_manager"=>$filter_manager,"limit"=>$limit,"filter_status"=>$filter_status,'dealer_id'=>$dealer_id,"client"=>$client];
			}
			die(json_encode(true));
		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());

        }
	}

	function getFilteredData(){
		try{
			$jinput = JFactory::getApplication()->input;
			$filter_city = $jinput->get('filter_city',"","STRING");
			$filter_manager = $jinput->get('filter_manager',"","STRING");
			$filter_status = $jinput->get('filter_status',"","STRING");
			$limit = $jinput->get("limit",null,"INT");
			$select_size = $jinput->get("select_size",null,"STRING");
			$client_name = $jinput->get("client","","STRING");
			$coop = $jinput->get('coop',0,'INT');
			$model =  Gm_ceilingHelpersGm_ceiling::getModel('clients');
			$result = $model->getDealersByFilter($filter_manager,$filter_city,$filter_status,$client_name,$limit,$select_size,$coop);
			die(json_encode($result));
		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}
	function select_dealers_city(){
		try{
			$model =  Gm_ceilingHelpersGm_ceiling::getModel('dealers');
			$result = $model->select_dealers_city();
			die(json_encode($result));
		}
		catch(Exception $e)
		{
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}

	function get_dealers_groups(){
		try{
			$model =  Gm_ceilingHelpersGm_ceiling::getModel('dealers');
			$result = $model->get_dealers_groups();
			die(json_encode($result));
		}
		catch(Exception $e)
		{
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }	
	}

	function delete(){
		try{
			$model =  Gm_ceilingHelpersGm_ceiling::getModel('client');
			$jinput = JFactory::getApplication()->input;
			$dealer_id = $jinput->get('dealer_id',null,'INT');
			$dealer = JFactory::getUser($dealer_id); 
			$user = JFactory::getUser();
			if(in_array('16', $user->groups) && !empty($dealer_id)){
				$result = $model->delete($dealer->associated_client);
			}
			else{
				throw new Exception('403 Forbidden!');
			}
			die(json_encode($result));
		}
		catch(Exception $e)
		{
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}
}

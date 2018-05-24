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
 
 jimport('joomla.application.component.modelitem');
 jimport('joomla.event.dispatcher');

/**
 * Methods supporting a list of Gm_ceiling records.
 *
 * @since  1.6
 */
class Gm_ceilingModelClient_phones extends JModelList
{
	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @see        JController
	 * @since      1.6
	 */
	
	public function getItemsByPhoneNumber($number, $dealer_id)
	{
		try
		{
			$number = mb_ereg_replace('[^\d]', '', $number);
	        if (mb_substr($number, 0, 1) == '9' && strlen($number) == 10)
	        {
	            $number = '7'.$number;
	        }
	        if (strlen($number) != 11)
	        {
	            throw new Exception('Неверный формат номера телефона.');
	        }
	        if (mb_substr($number, 0, 1) != '7')
	        {
	            $number = substr_replace($number, '7', 0, 1);
	        }
			$db    = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query
				->select("`b`.`id`, `b`.`client_name`, `b`.`dealer_id` AS `client_dealer_id`, `a`.`phone`, `u`.`dealer_type`, `u`.`dealer_id` AS `user_dealer_id`")
				->from("`#__gm_ceiling_clients_contacts` AS `a`")
				->innerJoin('`rgzbn_gm_ceiling_clients` AS `b` ON `a`.`client_id` = `b`.`id`')
				->leftJoin('`rgzbn_users` AS `u` ON `b`.`id` = `u`.`associated_client`')
				->where("`a`.`phone` LIKE(".$db->quote("%".$number."%").")")
				->order('`b`.`dealer_id`');
			$db->setQuery($query);
			$db->execute();
			$items = $db->loadObjectList();
			$result = null;

			foreach ($items as $key => $item)
			{
				if ($item->client_dealer_id == $dealer_id && is_null($item->dealer_type))
				{
					$result = $item;
					break;
				}
				if ($item->client_dealer_id != $dealer_id && ($item->dealer_type == 3 || $item->dealer_type == 5) && $item->user_dealer_id == $dealer_id)
				{
					$result = $item;
					break;
				}
				if ($item->client_dealer_id != $dealer_id && ($item->dealer_type == 0 || $item->dealer_type == 1) && $item->user_dealer_id == $item->client_dealer_id)
				{
					$result = $item;
					break;
				}
			}
			return $result;
		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}

	public function getItemsByClientId($client_id)
	{
		try
		{
			$db    = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query
				->select("*")
				->from("#__gm_ceiling_clients_contacts")
				->where("`client_id` = $client_id");
			$db->setQuery($query);
			$db->execute();
			$items = $db->loadObjectList();
			return $items;
		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}
	
	public function save($client_id,$phones){
		try
		{
			$columns = array('client_id', 'phone');
			$values=[];
			foreach($phones as $phone)
			{
	            if(!empty($phone))
	            {
	            	$phone = preg_replace('/[^\d]/', '', $phone);
					if (strlen($phone) != 11)
					{
		            	throw new Exception('Invalid phone number');
		            }
		            if (mb_substr($phone, 0, 1) != '7')
		            {
		                $phone = substr_replace($phone, '7', 0, 1);
		            }
				    array_push($values ,array($client_id.",'".$phone."'"));
	            }
			}

			$db    = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->insert($db->quoteName('#__gm_ceiling_clients_contacts'));
			$query->columns($db->quoteName($columns));
			foreach($values as $value) {
				
				   $query->values($value);
			}
		
			$db->setQuery($query);
			$db->execute();
			return true;
		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }
    
    public function update($client_id,$phones){
    	try
    	{
	        $db    = JFactory::getDbo();
	        foreach ($phones as $key => $value) {
	            $query = $db->getQuery(true);
				$query->update('#__gm_ceiling_clients_contacts');
				$query->set('phone = '.$value);
				$query->where("client_id = $client_id AND phone = $key");
				$db->setQuery($query);
				$db->execute();
	        }
	    }
	    catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }
}
?>
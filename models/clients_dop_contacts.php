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
class Gm_ceilingModelClients_dop_contacts extends JModelList
{
	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @see        JController
	 * @since      1.6
	 */
	

	
	public function save($client_id,$type_id,$contact){
		try{
			$columns = array('client_id', 'type_id', 'contact');
			$values = "$client_id, $type_id, '$contact'";

			$db    = JFactory::getDbo();

			$query = $db->getQuery(true);
			$query->select('*');
			$query->from('`#__gm_ceiling_clients_dop_contacts`');
			$query->where("`client_id` = $client_id AND `contact` = '$contact'");
			$db->setQuery($query);
			$items = $db->loadObjectList();

			if (count($items) == 0)
			{
				$query = $db->getQuery(true);
				$query->insert($db->quoteName('#__gm_ceiling_clients_dop_contacts'));
				$query->columns($db->quoteName($columns));
				$query->values($values);
			
				$db->setQuery($query);
				$db->execute();
				$result = $db->insertid();
			}
			else
			{
				$result = $items[0]->id;
			}
			return $result;
		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function getEmailByClientID($id){
    	try
		{
	        $db    = JFactory::getDbo();
	        $query = $db->getQuery(true);
	        $query
	            ->select('`contact`')
	            ->from('`#__gm_ceiling_clients_dop_contacts`')
	            ->where("`client_id` = $id AND `type_id` = 1");
	        $db->setQuery($query);
	        $items = $db->loadObjectList();
	        return $items;
	    }
	    catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

	public function update_client_id($emails,$client_id){
		try
		{
			$db = JFactory::getDbo();
			foreach($emails as $id){
				$query = $db->getQuery(true);
				$query->update('#__gm_ceiling_clients_dop_contacts');
				$query->set('client_id = '.$client_id);
				$query->where('id = '.$id);
				$db->setQuery($query);
				$db->execute();
			}
			
		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}
	
	public function SetEmail($client_id,$contact){
		try{
			//print_r($contact); exit;
	        $db    = JFactory::getDbo();
	        $query = $db->getQuery(true);
	        $query
	            ->select('id')
	            ->from('#__gm_ceiling_clients_dop_contacts')
	            ->where("client_id = $client_id AND contact = ".$db->quote($contact));
	        $db->setQuery($query);
	        $items = $db->loadObject();
			//print_r($contact); exit;
			if(empty($items->id))
			{
			$columns = array('client_id', 'type_id', 'contact');
			//$values = "$client_id, 1 , '$db->quote($contact)'";

			$db    = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->insert($db->quoteName('#__gm_ceiling_clients_dop_contacts'));
			$query->columns($db->quoteName($columns));
			$query->values(
				$client_id.','
				.'1'.','
				.$db->quote($contact));
		
			$db->setQuery($query);
			$db->execute();

			}
		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }
	public function getContact($id){
		try {
	        $db    = JFactory::getDbo();
	        $query = $db->getQuery(true);
	        $query
	            ->select('contact')
	            ->from('#__gm_ceiling_clients_dop_contacts')
	            ->where("client_id = $id");
	        $db->setQuery($query);
	        $items = $db->loadObjectList();
	        return $items;
	    }
	    catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}
	public function removeEmail($id,$email){
		try{
			$db    = JFactory::getDbo();
	        $query = $db->getQuery(true);
	        $query->delete('#__gm_ceiling_clients_dop_contacts');
			$query->where("client_id = $id and contact = '$email'");
			$db->setQuery($query);
			$result = $db->execute();
	        return $result;
	    }
	    catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}

	function updateEmail($client_id,$new_value,$old_value){
		try{
			$db = JFactory::getDbo();
	        $query = $db->getQuery(true);
	        $query->update('#__gm_ceiling_clients_dop_contacts');
			$query->set("contact = '$new_value'");
			$query->where("contact LIKE '%$old_value%' and client_id = $client_id");
			$db->setQuery($query);
			$result = $db->execute();
	        return $result;

		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}
}
?>
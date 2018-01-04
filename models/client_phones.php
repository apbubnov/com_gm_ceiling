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
	
	public function getItemsByPhoneNumber($number)
	{
		try
		{
			$db    = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query
				->select("*")
				->from("#__gm_ceiling_clients_contacts")
				->where("phone LIKE(".$db->quote("%".$number."%").")");
			$db->setQuery($query);
			$db->execute();
			$items = $db->loadObject();
			return $items;
		}
		catch(Exception $e)
        {
            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);
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
            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);
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
				    array_push($values ,array($client_id.",'".preg_replace('/[\(\)\-\s]/', '', $phone)."'"));
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
		}
		catch(Exception $e)
        {
            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);
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
            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);
        }
    }
}
?>
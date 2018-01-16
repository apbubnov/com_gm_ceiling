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
class Gm_ceilingModelRecoil extends JModelList
{
	
	function getData()
	{
		try
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
            $subquery = $db->getQuery(true);
            $subquery
                ->select('sum(coalesce(m.sum,0))')
                ->from('#__gm_ceiling_recoil_map_project as m')
                ->where('m.recoil_id = r.id');
	        $query
                ->select('*')
                ->select("ifnull(($subquery),0) as `sum`")
				->from('#__gm_ceiling_recoil as r');
			$db->setQuery($query);
			

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
    
    function getRecoilInfo($id){
        try
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
	        $query
	            ->select('*')
                ->from('#__gm_ceiling_recoil')
                ->where("id=$id");
			$db->setQuery($query);
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

	function save($name,$phone)
	{
		try
		{
			$phones = [];
			array_push($phones,$phone);
			$user = JFactory::getUser();
			//Создание клиента
			$clientform_model = $this->getModel('ClientForm', 'Gm_ceilingModel');
			$client_data['client_name'] = $name;
			$client_data['manager_id'] = $user->id;
			$client_data['created'] = date("Y-m-d");
			$client_id = $clientform_model->save($client_data);
			//сохранение телефонов
			$cl_phones_model = Gm_ceilingHelpersGm_ceiling::getModel('Client_phones', 'Gm_ceilingModel');
			$cl_phones_model->save($client_id,$phones);
			//создание user'а
			$dealer_id = Gm_ceilingHelpersGm_ceiling::registerUser($name,$phone,"$client_id@$client_id",$client_id);
			$client_model = $this->getModel('Client', 'Gm_ceilingModel');
			$client_model->updateClient($client_id,null,$userID);
	        return $dealer_id;
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
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
 * Gm_ceiling model.
 *
 * @since  1.6
 */
class Gm_ceilingModelMounterscalendar extends JModelItem {

/*function getData($userId) {
		try
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);

			$query->select('projects.id') // id заказа
				->select('projects.project_status') // статус заказа
				->select('projects.read_by_mounter') // статус заказа прочитан или нет
				->select('projects.project_info') // улица
				->select('projects.project_mounting_date') // дата и время монтажа
				->select('calculations.n5') // периметры
				->select('projects.gm_chief_note') // примечание монтажнику от ГМ НМС
				->select('projects.dealer_chief_note') // примечание монтажнику от дилера НМС
				->select('calculations.id as calculation_id')// id калькуляции (потолках)
				->from('#__gm_ceiling_projects as projects')
				->innerJoin('#__gm_ceiling_calculations as calculations ON calculations.project_id = projects.id')
				->where('projects.project_mounter = '.$userId)
	            ->orderby('id');
	        
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
	function GetNforSalary6($masid) {
		try
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);

			$whereAll = "";
			foreach ($masid as $value) {
				if ($whereAll == "") {
					$whereAll .= "id = ".$value;
				} else {
					$whereAll .= " or id = ".$value;				
				}
			}

			$query
				->select('transport')
				->select('distance')
				->select('distance_col') // транспорт
				->from('#__gm_ceiling_projects')
				->where("$whereAll");
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
*/

	function ChangeStatusOfRead($id) {
		try
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query2 = $db->getQuery(true);
			
			$query->update('#__gm_ceiling_projects')
			->set("read_by_mounter = '1'")
			->where("id = '$id'");
			$db->setQuery($query);
			$db->execute();

			$query2->select('read_by_mounter')
			->from('#__gm_ceiling_projects')
			->where("id = '$id'");
			$db->setQuery($query2);

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

	function GetAllMountingOfBrigade($id, $date1, $date2) {
		try
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query2 = $db->getQuery(true);
			$query3 = $db->getQuery(true);
			
			$query->select('id, project_mounting_date, read_by_mounter, project_status, project_info, gm_chief_note, dealer_chief_note, transport, distance, distance_col')
				->from('#__gm_ceiling_projects')
				->where("project_mounter = '$id' and project_mounting_date between '$date1 00:00:00' and '$date2 23:59:59'")
				->order('project_mounting_date');
			$db->setQuery($query);
			$items = $db->loadObjectList();

			$query2->select('calculations.project_id, calculations.n5, calculations.mounting_sum')
				->from('#__gm_ceiling_calculations as calculations')
				->innerJoin('#__gm_ceiling_projects as projects ON calculations.project_id = projects.id')
				->where("projects.project_mounter = '$id' and projects.project_mounting_date between '$date1 00:00:00' and '$date2 23:59:59'");
			$db->setQuery($query2);
			$items2 = $db->loadObjectList();

			$user = JFactory::getUser();

			$query3->select('transport as transport_mp, distance as distance_mp')
				->from('#__gm_ceiling_mount')
				->where("user_id = '$user->dealer_id'");
			$db->setQuery($query3);
			$items3 = $db->loadObjectList();
			
			foreach ($items as $value) {
				foreach ($items2 as $val) {
					if ($value->id == $val->project_id) {
						$value->n5 += $val->n5;
						$value->mounting_sum += $val->mounting_sum;
					}
				}
				$calc_transport = 0;
				if ($value->transport == 1) {
					$calc_transport = $items3[0]->transport_mp * $value->distance_col;
				} else if ($value->transport == 2) {
					$calc_transport = $items3[0]->distance_mp * $value->distance_col * $value->distance;
				}
				$value->mounting_sum += $calc_transport;
				$value->transport_sum = $calc_transport;
				$value->transport_mp = $items3[0]->transport_mp;
				$value->distance_col = $value->distance_col;
				$value->distance = $value->distance;
				$value->distance_mp = $items3[0]->distance_mp;
				$value->transport = $value->transport;
			}

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

	function AllNMSEmails() {
		try
		{
			$user       = JFactory::getUser();
			$userId     = $user->get('id');
			if ($user->dealer_id == 1) {
				$groupe = 17;
			} else {
				$groupe = 12;
			}
			
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);

			$query->select('users.email')
			->from('#__users as users')
			->innerJoin('#__user_usergroup_map as map ON users.id = map.user_id')		
			->where('users.dealer_id = '.$user->dealer_id.' and map.group_id = '.$groupe);
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

	function DataOrder($id) {
		try
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);

			$query->select('project_info, project_mounting_date')
			->from('#__gm_ceiling_projects')
			->where("id = $id");
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

	// получение данных о дне для вывода в таблицу
	function GetDayMountingOfBrigade($id, $date) {
        try
        {
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);
            $query2 = $db->getQuery(true);
            $query3 = $db->getQuery(true);
            $query4 = $db->getQuery(true);
            
            $query->select('id, project_mounting_date, read_by_mounter, project_status, project_info, gm_chief_note, dealer_chief_note, transport, distance, distance_col')
                ->from('#__gm_ceiling_projects')
                ->where("project_mounter = '$id' and project_mounting_date between '$date 00:00:00' and '$date 23:59:59'")
                ->order('project_mounting_date');
            $db->setQuery($query);
            $items = $db->loadObjectList();

            $query2->select('calculations.project_id, calculations.n5, calculations.mounting_sum')
                ->from('#__gm_ceiling_calculations as calculations')
                ->innerJoin('#__gm_ceiling_projects as projects ON calculations.project_id = projects.id')
                ->where("projects.project_mounter = '$id' and projects.project_mounting_date between '$date 00:00:00' and '$date 23:59:59'");
            $db->setQuery($query2);
            $items2 = $db->loadObjectList();

            $user = JFactory::getUser();

            $query3->select('transport as transport_mp, distance as distance_mp')
                ->from('#__gm_ceiling_mount')
                ->where("user_id = '$user->dealer_id'");
            $db->setQuery($query3);
            $items3 = $db->loadObjectList();
            
            foreach ($items as $value) {
                foreach ($items2 as $val) {
                    if ($value->id == $val->project_id) {
                        $value->n5 += $val->n5;
                        $value->mounting_sum += $val->mounting_sum;
                    }
                }
                $calc_transport = 0;
                if ($value->transport == 1) {
                    $calc_transport = $items3[0]->transport_mp * $value->distance_col;
                } else if ($value->transport == 2) {
                    $calc_transport = $items3[0]->distance_mp * $value->distance_col * $value->distance;
                }
                $value->mounting_sum += $calc_transport;
                $value->transport_sum = $calc_transport;
                $value->transport_mp = $items3[0]->transport_mp;
                $value->distance_col = $value->distance_col;
                $value->distance = $value->distance;
                $value->distance_mp = $items3[0]->distance_mp;
                $value->transport = $value->transport;
            }

            $query4->select('date_from, date_to')
                ->from('#__gm_ceiling_day_off')
                ->where("id_user = $id and date_from between '$date 00:00:00' and '$date 23:59:59'");
            $db->setQuery($query4);
            $items4 = $db->loadObject();

            // объединение с выходным днем
            $index = 0;
            //поиск индекса для вставки и замена даты на просто время
            for ($i=0; $i < count($items); $i++) {
                if (strtotime($items[$i]->project_mounting_date) >= strtotime($items4->date_from)) {
                    $index = $i;
                    break;
                }
            }
            for ($i=0; $i < count($items); $i++) {
                $items[$i]->project_mounting_date = substr($items[$i]->project_mounting_date, 11, 5);
            }

            //создание нового массива
            if (!empty($items4)) {
                $day = array(
                    'id'=>NULL,
                    'project_mounting_date' => substr($items4->date_from, 11, 5) .' - '. substr($items4->date_to, 11, 5),
                    'project_info' =>'Выходной',
                    'perimeter' => NULL,
                    'salary' => NULL,
                    'gm_chief_note' => NULL,
                    'read_by_mounter' => null,
                    'project_status' => NULL,
                    'dealer_chief_note' => NULL,
                    'transport' => NULL,
                    'distance' => NULL,
                    'distance_col' => NULL
                );
                $day = array((object)$day);
                array_splice($items,$index,0,$day);
            }

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

	
}

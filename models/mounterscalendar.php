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
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}

	function GetAllMountingOfBrigade($id, $date1, $date2) {
		try
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query2 = $db->getQuery(true);
			$query3 = $db->getQuery(true);
			$n5subquery = $db->getQuery(true);
            $n5subquery
                ->select('SUM(c.n5)')
                ->from('`rgzbn_gm_ceiling_calculations` AS c')
                ->where('c.project_id = p.id');
			$query
				->select("p.id,pm.date_time AS project_mounting_date,p.read_by_mounter,($n5subquery) AS n5, p.project_status,pm.type, p.project_info,p.transport, p.distance, p.distance_col")
				->from('`#__gm_ceiling_projects_mounts` as pm')
				->innerJoin('`#__gm_ceiling_projects` as p on p.id = pm.project_id')
				->where("pm.mounter_id = '$id' and pm.date_time between '$date1 00:00:00' and '$date2 23:59:59'")
				->order('pm.date_time');
			/*$query->select('id, project_mounting_date, read_by_mounter, project_status, project_info, gm_chief_note, dealer_chief_note, transport, distance, distance_col')
				->from('#__gm_ceiling_projects')
				->where("project_mounter = '$id' and project_mounting_date between '$date1 00:00:00' and '$date2 23:59:59'")
				->order('project_mounting_date');*/
			$db->setQuery($query);
			$items = $db->loadObjectList();

			$query2->select('calculations.project_id, calculations.mounting_sum')
				->from('#__gm_ceiling_calculations as calculations')
				->innerJoin('#__gm_ceiling_projects_mounts as pm ON calculations.project_id = pm.project_id')
				->where("pm.mounter_id = '$id' and pm.date_time between '$date1 00:00:00' and '$date2 23:59:59'");
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
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}

	function GetAllDayOff($id, $date1, $date2) {
		try
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);

			$query->select('date_from, date_to')
				->from('#__gm_ceiling_day_off')
				->where("id_user = '$id' and date_from between '$date1 00:00:00' and '$date2 23:59:59'")
				->order('date_from');
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
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}

	function DataOrder($id) {
		try
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query2 = $db->getQuery(true);

			$query2->select("users.name")
			->from('#__users as users')
			->where("users.id = m.mounter_id");

			$query->select("p.project_info, m.date_time as project_mounting_date, m.mounter_id as project_mounter, ($query2) as project_mounter_name")
			->from('#__gm_ceiling_projects as p')
			->leftJoin('#__gm_ceiling_projects_mounts as m ON p.id = m.project_id')
			->where("p.id = $id");
			$db->setQuery($query);
			$items = $db->loadObjectList();
			return $items;
		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}

	function NamesMounters($id) {
		try
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);

			$query->select("mounters.name")
			->from('#__gm_ceiling_mounters as mounters')
			->innerJoin('#__gm_ceiling_mounters_map as map ON mounters.id = map.id_mounter')
			->where("map.id_brigade = $id");
			$db->setQuery($query);
			$items = $db->loadObjectList();

			return $items;
		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}

	// получение данных о дне для вывода в таблицу
	function GetDayMountingOfBrigade($id, $date) {
        try
        {
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);
            $contactsSubquery = $db->getQuery(true);
            $mountSumSubquery = $db->getQuery(true);
            $perimeterSubquery = $db->getQuery(true);
            /*
             * оставил на всякий если будет косячить новый код*/
            /*$query
 	           ->select('p.id, pm.date_time as project_mounting_date, p.read_by_mounter, s.title as project_status, p.project_info, p.gm_chief_note, p.dealer_chief_note, p.gm_calculator_note, p.dealer_calculator_note, pm.type,p.calcs_mounting_sum')
                ->from('`#__gm_ceiling_projects_mounts` as pm')
				->innerJoin('`#__gm_ceiling_projects` as p on p.id = pm.project_id')
				->innerJoin('`#__gm_ceiling_status` as s on p.project_status = s.id')
                ->where("pm.mounter_id = '$id' and p.project_status > 3 and pm.date_time between '$date 00:00:00' and '$date 23:59:59'")
                ->order('pm.date_time');

            $db->setQuery($query);
            $items = $db->loadObjectList();
            $query->clear();
            $query->select('calculations.id, calculations.project_id, calculations.n5, calculations.mounting_sum , calculations.details')
                ->from('#__gm_ceiling_calculations as calculations')
                ->innerJoin('#__gm_ceiling_projects as projects ON calculations.project_id = projects.id')
                ->innerJoin('#__gm_ceiling_projects_mounts as pm on calculations.project_id = pm.project_id')
                ->where("pm.mounter_id = '$id' and projects.`project_status` > 3 and pm.date_time between '$date 00:00:00' and '$date 23:59:59'");
            $db->setQuery($query);
            $items2 = $db->loadObjectList();
            $query->clear();*/

            $contactsSubquery
                ->select('GROUP_CONCAT(cp.phone SEPARATOR \';\n\')')
                ->from('`rgzbn_gm_ceiling_clients_contacts` AS cp')
                ->where('cp.client_id = p.client_id');
            $mountSumSubquery
                ->select('SUM(`sum`)')
                ->from('`rgzbn_gm_ceiling_calcs_mount` AS cm')
                ->innerJoin('`rgzbn_gm_ceiling_calculations` AS c ON c.id = cm.calculation_id')
                ->where('c.project_id = p.id AND cm.stage_id = pm.type');
            $perimeterSubquery
                ->select('SUM(n5)')
                ->from('`rgzbn_gm_ceiling_calculations`')
                ->where('project_id = p.id');
            $query
                ->select('p.id, pm.date_time AS project_mounting_date, p.read_by_mounter, s.title AS project_status, p.project_info, pm.type,p.calcs_mounting_sum')
                ->select("($contactsSubquery) as client_phones")
                ->select("($mountSumSubquery) as m_sum")
                ->select("($perimeterSubquery) as n5")
                ->from('`rgzbn_gm_ceiling_projects_mounts` AS pm')
                ->innerJoin('`rgzbn_gm_ceiling_projects` AS p ON p.id = pm.project_id')
                ->innerJoin('`rgzbn_gm_ceiling_status` AS s ON p.project_status = s.id')
                ->where("pm.mounter_id = '$id' and p.project_status > 3 and pm.date_time between '$date 00:00:00' and '$date 23:59:59'");
            $db->setQuery($query);
            $items = $db->loadObjectList();
            if(count($items == 1) && empty($items[0]->id)){
                $items = [];
            }
            $summed = [];$result = [];
            for($i=0;$i<count($items);$i++){
                for($j=$i+1;$j<count($items);$j++){
                    if($items[$i]->id == $items[$j]->id){
                        $clone_object = clone $items[$i];
                        $clone_object->m_sum += $items[$j]->m_sum;
                        $clone_object->type = [$items[$i]->type,$items[$j]->type];
                        $summed[$clone_object->id] = $clone_object;
                    }
                }
            }
            foreach ($items as $item) {
                if(!in_array($item->id,array_keys($summed))){
                    array_push($result,$item);
                }
            }
            foreach ($summed as $sum_value) {
                array_push($result,$sum_value);
            }
            $items = $result;
            //throw new Exception(print_r($items,true));
            //$user = JFactory::getUser();
            //$service = ($user->dealer_id == 1) ? "serviceSelf" : "";
            /*foreach ($items as $value) {
                if(!empty($value->id)) {
                    $value->m_sum = 0;
                    $calcs = explode(';', $value->calcs_id);
                    foreach ($calcs as $val) {
                        $mount_sum = Gm_ceilingHelpersGm_ceiling::calculate_mount(0, $val, null,$service)["total_dealer_mounting"];
                        $value->m_sum += $mount_sum;
                    }
                    if (!empty($value->calcs_mounting_sum)) {
                        $transport = Gm_ceilingHelpersGm_ceiling::calculate_transport($value->id, "mount");
                    } else {

                        $transport = Gm_ceilingHelpersGm_ceiling::calculate_transport($value->id);
                    }
                    $value->m_sum += $transport["mounter_sum"];
                }
            }*/

            $query->clear();
            $query->select('date_from, date_to')
                ->from('#__gm_ceiling_day_off')
                ->where("id_user = $id and date_from between '$date 00:00:00' and '$date 23:59:59'");
            $db->setQuery($query);
            $items4 = $db->loadObject();

            // объединение с выходным днем
			$index = 0;
			$was_break = false;
            //поиск индекса для вставки и замена даты на просто время
            for ($i=0; $i < count($items); $i++) {
                if (strtotime($items[$i]->project_mounting_date) >= strtotime($items4->date_from)) {
					$index = $i;
					$was_break = true;
                    break;
                }
			}
			($index == 0 && !$was_break) ? $index = count($items) : 0;
            for ($i=0; $i < count($items); $i++) {
                $items[$i]->project_mounting_date = substr($items[$i]->project_mounting_date, 11, 5);
            }
            //создание нового массива
            if (!empty($items4)) {
                $day = array(
                    'id'=>NULL,
                    'project_mounting_date' => substr($items4->date_from, 11, 5) .' - '. substr($items4->date_to, 11, 5),
                    'project_info' =>'Выходные часы',
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
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

	
}

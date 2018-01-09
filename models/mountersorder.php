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
class Gm_ceilingModelMountersorder extends JModelItem {

	function getData($id) {
		try
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			
			$query->select('calculations.id as calculation_id') // id калькуляции (потолках)
				->from('#__gm_ceiling_projects as projects')
				->innerJoin('#__gm_ceiling_calculations as calculations ON calculations.project_id = projects.id')
				->where("projects.id = '$id'")
				->orderby('calculations.id');
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

	function GetNPack1($masidcalc) {
		try
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);

			$whereAll = "";
			foreach ($masidcalc as $value) {
				if ($whereAll == "") {
					$whereAll .= 'calculations.id = '.$value;
				} else {
					$whereAll .= ' or calculations.id = '.$value;				
				}
			}

			$query->select('calculations.id')
			->select('calculations.calculation_title') // название
			->select('calculations.n1') // тип полотна
			->select('calculations.n5') // периметр
			->select('calculations.n6') // вставка
			->select('calculations.n7') // крепление в плитку
			->select('calculations.n8') // крепление в керамогранит
			->select('calculations.n9') // обработка одного угла
			->select('calculations.n11') // внутренний вырез
			->select('calculations.n12') // установка люстр
			->select('calculations.n17') // закладная брусом
			->select('calculations.n18') // укрепление стен
			->select('calculations.n20') // разделитель стен
			->select('calculations.n21') // пожарная сигнализация
			->select('calculations.n24') // сложность доступа
			->select('calculations.n27') // шторный карниз
			->select('calculations.n28') // багет для парящего потолка
			->select('calculations.n30') // парящий потолок
			->select('calculations.dop_krepezh') // доп. крепеж
			->select('calculations.extra_mounting') // доп. монтаж
			->from('#__gm_ceiling_calculations as calculations')
			->where($whereAll)
			->orderby('calculations.project_id');
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
	
	function GetNPack2($masidcalc, $project_id) {
		try
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query1 = $db->getQuery(true);

			$whereAll = "";
			foreach ($masidcalc as $value) {
				if ($whereAll == "") {
					$whereAll .= 'calculations.id = '.$value;
				} else {
					$whereAll .= ' or calculations.id = '.$value;				
				}
			}	
			
			$query->select("calculations.id as id_calculation, fixtures.n13_count, calculations.n1") // количество светильников
			->from('#__gm_ceiling_calculations as calculations')
			->innerJoin('#__gm_ceiling_fixtures as fixtures ON calculations.id = fixtures.calculation_id')
			->where($whereAll)
			->orderby('calculations.project_id');
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

	function GetNPack3($masidcalc) {
		try
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);

			$whereAll = "";
			foreach ($masidcalc as $value) {
				if ($whereAll == "") {
					$whereAll .= 'calculations.id = '.$value;
				} else {
					$whereAll .= ' or calculations.id = '.$value;				
				}
			}

			$query->select('calculations.id, pipes.n14_count, calculations.n1') // трубы
			->from('#__gm_ceiling_calculations as calculations')
			->innerJoin('#__gm_ceiling_pipes as pipes ON calculations.id = pipes.calculation_id')
			->where($whereAll)
			->orderby('calculations.project_id');
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

	function GetNPack4($masidcalc) {
		try
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);

			$whereAll = "";
			foreach ($masidcalc as $value) {
				if ($whereAll == "") {
					$whereAll .= 'calculations.id = '.$value;
				} else {
					$whereAll .= ' or calculations.id = '.$value;				
				}
			}

			$query->select('calculations.id as id_calculation, calculations.n1')
			->select('hoods.id') // вытяжки
			->select('hoods.n22_count') // количество вентиляций и электровытяжек
			->select('hoods.n22_type') // тип вентиляция (5,6) или электровытяжка (7,8)
			->from('#__gm_ceiling_calculations as calculations')
			->innerJoin('#__gm_ceiling_hoods as hoods ON calculations.id = hoods.calculation_id')
			->where($whereAll)
			->orderby('calculations.project_id');
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

	function GetNPack5($masidcalc) {
		try
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);

			$whereAll = "";
			foreach ($masidcalc as $value) {
				if ($whereAll == "") {
					$whereAll .= 'calculations.id = '.$value;
				} else {
					$whereAll .= ' or calculations.id = '.$value;				
				}
			}

			$query->select('calculations.id')
			->select('diffusers.n23_count') // диффузор
			->from('#__gm_ceiling_calculations as calculations')
			->innerJoin('#__gm_ceiling_diffusers as diffusers ON calculations.id = diffusers.calculation_id')
			->where($whereAll)
			->orderby('calculations.project_id');
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

	function GetNPack6($id) {
		try
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);

			$query
				->select('transport')
				->select('distance')
				->select('distance_col') // транспорт
				->from('#__gm_ceiling_projects')
				->where("id = '$id'");
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

	function GetNPack7($masidcalc) {
		try
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);

			$whereAll = "";
			foreach ($masidcalc as $value) {
				if ($whereAll == "") {
					$whereAll .= 'calculations.id = '.$value;
				} else {
					$whereAll .= ' or calculations.id = '.$value;				
				}
			}

			$query->select('calculations.id as id_calculation, calculations.n1')
			->select('profil.id') // профиль
			->select('profil.n29_count') // количество профиля
			->select('profil.n29_type') // тип профиля
			->from('#__gm_ceiling_calculations as calculations')
			->innerJoin('#__gm_ceiling_profil as profil ON calculations.id = profil.calculation_id')
			->where($whereAll)
			->orderby('calculations.project_id');
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

	function GetMp($dealer) {
		try
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);

			$query->select('*')
			->from('#__gm_ceiling_mount as mount')
			->where('mount.user_id = '.$dealer);
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

	function GetDates($id) {
		try
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);

			$query->select('project_mounting_start, project_mounting_end, project_status')
			->from('#__gm_ceiling_projects')
			->where('id = '.$id);
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

	function MountingStart($id, $date) {
		try
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query2 = $db->getQuery(true);
			$query3 = $db->getQuery(true);
			$query4 = $db->getQuery(true);
			$query5 = $db->getQuery(true);

			$query->update('#__gm_ceiling_projects')
			->set('project_mounting_start = \''.$date.'\'')
			->set('project_status = 16')
			->where('id = '.$id);
			$db->setQuery($query);
			$db->execute();

			$query3->select('client_id')
			->from('#__gm_ceiling_projects')
			->where('id = '.$id);
			$db->setQuery($query3);
			$items = $db->loadObjectList();
			$client = $items[0]->client_id;
	
			$note = "Монтаж по проекту №$id начат.";
			$query4->insert('#__gm_ceiling_client_history')
			->columns('client_id, date_time, text')
			->values("'$client', '$date', '$note'");
			$db->setQuery($query4);
			$db->execute();

			// запись в project_history
			$query5->insert('#__gm_ceiling_projects_history')
			->columns('project_id, new_status, date_of_change')
			->values("'$id', '16', '$date'");
			$db->setQuery($query5);
			$db->execute();

			$query2->select('project_status')
			->from('#__gm_ceiling_projects')
			->where('id = '.$id);
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

	function MountingComplited($id, $date, $note2, $note) {
		try
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query2 = $db->getQuery(true);
			$query3 = $db->getQuery(true);
			$query4 = $db->getQuery(true);
			$query5 = $db->getQuery(true);
			$query6 = $db->getQuery(true);

			$query->update('#__gm_ceiling_projects')
			->set('project_mounting_end = \''.$date.'\'')
			->set('project_status = 11')
			->where('id = '.$id);
			$db->setQuery($query);
			$db->execute();

			if (strlen($note2) != 0) {
				$query2->select('client_id')
				->from('#__gm_ceiling_projects')
				->where('id = '.$id);
				$db->setQuery($query2);
				$items = $db->loadObjectList();
				$client = $items[0]->client_id;
		
				$query3->insert('#__gm_ceiling_client_history')
				->columns('client_id, date_time, text')
				->values("'$client', '$date', '$note2'");
				$db->setQuery($query3);
				$db->execute();
			}

			// запись в project_history
			$query5->insert('#__gm_ceiling_projects_history')
			->columns('project_id, new_status, date_of_change')
			->values("'$id', '11', '$date'");
			$db->setQuery($query5);
			$db->execute();

			// запись в note
			$user = JFactory::getUser();
			if ($user->dealer_id == 1) {
				$ForWho = "gm_mounter_note";
			} else {
				$ForWho = "mounter_note";
			}
			$query6->update('#__gm_ceiling_projects')
			->set("$ForWho = '$note'")
			->where("id = '$id'");
			$db->setQuery($query6);
			$db->execute();

			$query4->select('project_status')
			->from('#__gm_ceiling_projects')
			->where('id = '.$id);
			$db->setQuery($query4);

			$items2 = $db->loadObjectList();
			return $items2;
		}
		catch(Exception $e)
        {
            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);
        }
	}

	function MountingUnderfulfilled($id, $date, $note) {
		try
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query2 = $db->getQuery(true);
			$query3 = $db->getQuery(true);
			$query4 = $db->getQuery(true);
			$query5 = $db->getQuery(true);

			$query->update('#__gm_ceiling_projects')
			->set('project_status = 17')
			->where('id = '.$id);
			$db->setQuery($query);
			$db->execute();

			if (strlen($note) != 0) {
				$query2->select('client_id')
				->from('#__gm_ceiling_projects')
				->where('id = '.$id);
				$db->setQuery($query2);
				$items = $db->loadObjectList();
				$client = $items[0]->client_id;
		
				$query3->insert('#__gm_ceiling_client_history')
				->columns('client_id, date_time, text')
				->values("'$client', '$date', '$note'");
				$db->setQuery($query3);
				$db->execute();
			}

			// запись в project_history
			$query5->insert('#__gm_ceiling_projects_history')
			->columns('project_id, new_status, date_of_change')
			->values("'$id', '17', '$date'");
			$db->setQuery($query5);
			$db->execute();

			$query4->select('project_status')
			->from('#__gm_ceiling_projects')
			->where('id = '.$id);
			$db->setQuery($query4);
			
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

}

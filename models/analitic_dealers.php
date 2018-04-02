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
class Gm_ceilingModelAnalitic_dealers extends JModelList
{
    public function getData($date1, $date2)
    {
        try {
            $result = (object)array(
                "common" => $this->getCommonDealersCount(),
                "ordering_dealers" => $this->getOrderingDealers($date1, $date2),
                "new_order_dealers" =>  $this->getNewOrderingDealers($date1, $date2), 
                "fallen" => $this->getFallenOffDealers($date1, $date2)
            );
            return $result;
        } catch (Exception $e) {
            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);
        }
    }
    public function getCommonDealersCount()
    {
        //общее количество дилеров
        try {
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);
            $query
                ->select('count(id)')
                ->from('#__users')
                ->where('dealer_id <> 1 and dealer_type in(0,1)');
            $db->setQuery($query);
            $count = $db->loadResult();
            return $count;
        } catch (Exception $e) {
            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);
        }
    }
    public function getOrderingDealers($date1, $date2)
    {
        //заказывающие диллеры
        /* SELECT COUNT(u.id)
            FROM `rgzbn_users` AS u
            WHERE u.id IN (SELECT dealer_id FROM `rgzbn_gm_ceiling_clients` AS c  WHERE u.id = c.dealer_id AND c.id IN
            ( SELECT p.client_id FROM `rgzbn_gm_ceiling_projects` AS p INNER JOIN `rgzbn_gm_ceiling_projects_history` AS h ON p.id = h.project_id
            AND h.new_status IN(4,5,10,12) AND h.date_of_change BETWEEN '2018-02-25' AND '2018-03-27'))
            AND u.dealer_id <> 1 AND u.dealer_type IN(0,1) */
        try {
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);
            $sub_query1 = $db->getQuery(true);
            $sub_query2 = $db->getQuery(true);
            $sub_query2
                ->select('p.client_id')
                ->from("#__gm_ceiling_projects as p")
                ->innerJoin("#__gm_ceiling_projects_history as h on p.id = h.project_id and h.new_status IN(4,5,10,12) AND h.date_of_change BETWEEN '$date1' and '$date2'");
            $sub_query1
                ->select('c.dealer_id')
                ->from('#__gm_ceiling_clients as c')
                ->where("u.id = c.dealer_id AND c.id IN ($sub_query2)");
            $query
                ->select('u.id')
                ->from('#__users as u')
                ->where("u.id IN ($sub_query1)");
            $db->setQuery($query);
           
            $items = $db->loadObjectList();
            return $items;
        } catch (Exception $e) {
            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);
        }
    }
    public function getNewOrderingDealers($date1, $date2)
    {
        //дилеры которые не заказывали до выбранного промежутка и заказали в выбранном
        try {
            $date1 = new DateTime($date1);
            $date2 = new DateTime($date2);
            $day_diff = $date2->diff($date1)->format("%d");
            $prev_date1 = clone $date1;
            $prev_date1->modify('- '.++$day_diff.' days');
            $prev_date2 = clone $prev_date1;
            $prev_date2->modify('+ '.--$day_diff.' days');
            $dealers = $this->getOrderingDealers($date1->format('Y-m-d'), $date2->format('Y-m-d'));//те кто заказал в этом периоде
            $prev_dealers = $this->getOrderingDealers($prev_date1->format('Y-m-d'), $prev_date2->format('Y-m-d')); // те кто заказал в предыдущем
            $need_dealers = array_udiff($dealers, $prev_dealers,'compare_objects');
            return $need_dealers;
        } catch (Exception $e) {
            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);
        }
    }
    function compare_objects($obj_a, $obj_b) {
        return $obj_a->id != $obj_b->id;
      }
    public function getFallenOffDealers($date1, $date2)
    {
        //дилеры которые заказывали до выбранного промежутка и не заказывали в выбранный
        try {
            $date1 = new DateTime($date1);
            $date2 = new DateTime($date2);
            $day_diff = $date2->diff($date1)->format("%d");
            $prev_date1 = clone $date1;
            $prev_date1->modify('- '.++$day_diff.' days');
            $prev_date2 = clone $prev_date1;
            $prev_date2->modify('+ '.--$day_diff.' days');
            $dealers = $this->getOrderingDealers($date1->format('Y-m-d'), $date2->format('Y-m-d'));//те кто заказал в этом периоде
            $prev_dealers = $this->getOrderingDealers($prev_date1->format('Y-m-d'), $prev_date2->format('Y-m-d')); // те кто заказал в предыдущем
            $need_dealers = array_udiff($prev_dealers, $dealers,'compare_objects');
            return $need_dealers;
        } catch (Exception $e) {
            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);
        }
    }
}

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
    function getData(){
        try{

        }
        catch(Exception $e)
        {
            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);
        }            
    }
    function getCommonDealersCount(){
        //общее количество дилеров
        try{
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);
            
            
        }
        catch(Exception $e)
        {
            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);
        }        
        
    }
    function getOrderingDealers(){
        //заказывающие диллеры
        /* SELECT COUNT(u.id)
            FROM `rgzbn_users` AS u
            WHERE u.id IN (SELECT dealer_id FROM `rgzbn_gm_ceiling_clients` AS c  WHERE u.id = c.dealer_id AND c.id IN 
            ( SELECT p.client_id FROM `rgzbn_gm_ceiling_projects` AS p INNER JOIN `rgzbn_gm_ceiling_projects_history` AS h ON p.id = h.project_id AND h.new_status IN(4,5,10,12) AND h.date_of_change BETWEEN '2018-02-25' AND '2018-03-27')) 
            AND u.dealer_id <> 1 AND u.dealer_type IN(0,1) */
        try{

        }
        catch(Exception $e)
        {
            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);
        }        
    }
    function getNewOrderingDealers(){
        //дилеры которые не заказывали до выбранного промежутка и заказали в выбранном
        try{

        }
        catch(Exception $e)
        {
            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);
        }        
    }
    function getFallenOffDealers(){
        //дилеры которые заказывали до выбранного промежутка и не заказывали в выбранный
        try{

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
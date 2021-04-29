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
class Gm_ceilingModelCashbox extends JModelList
{
	
	function getFilteredData($dateFrom,$dateTo,$cashBoxTypes,$counterparty,$userId){
		try{
		    $db = JFactory::getDbo();
		    $query = $db->getQuery(true);
		    $query
                ->select('c.id,u.id AS counterparty_id,IFNULL(u.name,\'-\') AS counterparty,c.sum,ct.name AS cashbox,d.title AS operation,c.operation_type,m.id AS user_id,c.comment,m.name AS user_name,c.datetime')
                ->from('`rgzbn_gm_ceiling_cashbox` AS c ')
                ->leftJoin('`rgzbn_users` AS u ON u.id = c.dealer_id')
                ->leftJoin('`rgzbn_gm_ceiling_debt_type` AS d ON d.id = c.operation_type')
                ->leftJoin('`rgzbn_gm_ceiling_cashbox_type` AS ct ON ct.id = c.cashbox_type')
                ->leftJoin('`rgzbn_users` AS m ON m.id = c.user_id');
		    if(!empty($dateFrom)&&!empty($dateTo)){
		        $query->where(" c.datetime BETWEEN '$dateFrom 00:00:00' AND '$dateTo 23:59:59'");
            }
		    elseif (!empty($dateFrom) && empty($dateTo)){
		        $query->where("c.datetime >= '$dateFrom 00:00:00'");
            }
		    elseif (empty($dateFrom) && !empty($dateTo)){
                $query->where("c.datetime <= '$dateTo 23:59:59'");
            }
		    if(!empty($cashBoxTypes)){
		        $query->where("c.cashbox_type in ($cashBoxTypes)");
            }
		    if(!empty($counterparty)){
		        $query->where("c.dealer_id = $counterparty");
            }
            if(!empty($userId)){
                $query->where("c.user_id = $userId");
            }
		    $db->setQuery($query);
            //throw new Exception($query);
		    $data = $db->loadObjectList();
		    //throw new Exception(print_r($data,true));
		    return $data;
		}
		catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}

	function getDataById($id){
	    try{
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);
            $query
                ->select('c.*,u.name as dealer_name')
                ->from('`rgzbn_gm_ceiling_cashbox` as c')
                ->leftJoin('`rgzbn_users` as u on u.id = c.dealer_id')
                ->where("c.id=$id");
            $db->setQuery($query);
            $data = $db->loadObject();
            return $data;
        }
        catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

	function getCashBoxTypes(){
	    try{
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);
            $query
                ->select('*')
                ->from('`rgzbn_gm_ceiling_cashbox_type`');
            $db->setQuery($query);
            $types = $db->loadObjectList('id');
            return $types;
        }
        catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    function save($data){
	    try{
	        if(!empty($data)) {
	            $columns = [];
	            $values = [];
                $db = JFactory::getDbo();
                foreach ($data as $key => $value) {
                    array_push($columns,$key);
                    array_push($values,$db->quote($value));
	            }
                $columns = implode(',',$columns);
                $values = implode(',',$values);
                $query = $db->getQuery(true);
                $query
                    ->insert('`rgzbn_gm_ceiling_cashbox`')
                    ->columns($columns)
                    ->values($values);
                $db->setQuery($query);

                $db->execute();
                return $db->lastId();
            }
	        else{
	            return null;
            }
        }
        catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }
	
	function getCashboxSum($type){
	    try{
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);
            $query
                ->select('c.cashbox_type, ct.name')
                ->select('SUM(CASE WHEN c.operation_type = 1 THEN c.sum ELSE 0 END) AS incoming')
                ->select('SUM(CASE WHEN c.operation_type IN (2,3) THEN c.sum ELSE 0 END) AS outcoming')
                ->from('`rgzbn_gm_ceiling_cashbox` AS c')
                ->innerJoin('`rgzbn_gm_ceiling_cashbox_type` AS ct ON ct.id = c.cashbox_type')
                ->group('c.cashbox_type');
            $db->setQuery($query);
            $result = $db->loadAssocList('cashbox_type');
            return $result;
        }
        catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }
}
?>
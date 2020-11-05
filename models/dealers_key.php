<?php 
	defined('_JEXEC') or die;

	jimport('joomla.application.component.modellist');

	/**
	 * Methods supporting a list of Gm_ceiling records.
	 *
	 * @since  1.6
	 */
	class Gm_ceilingModelDealers_Key extends JModelList {

	    public function getData($dealer_id) {
	        try
	        {
	            $db = $this->getDbo();
	            $query = $db->getQuery(true);

	            $query->select('`id`,`key`,`secret`')
	                ->from('`#__gm_ceiling_dealers_key`')
	                ->where("dealer_id = $dealer_id");
	            $db->setQuery($query);
	            $items = $db->loadObject();
	    		return $items;
	        }
	        catch(Exception $e) {
	            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
	        }
	    }

	    function save($id,$key,$secret,$dealerId,$telephonyId = 2){
	        try{
                $db = $this->getDbo();
                $query = $db->getQuery(true);
	            if(!empty($id)){
	                $query
                        ->update('`rgzbn_gm_ceiling_dealers_key`')
                        ->set("`key` = '$key'")
                        ->set("`secret` = '$secret'")
                        ->where("id = $id and dealer_id = $dealerId");
                }
	            else{
	                $query
                        ->insert('`rgzbn_gm_ceiling_dealers_key`')
                        ->columns('`dealer_id`,`key`,`secret`,`telephony_id`')
                        ->values("$dealerId,'$key','$secret',$telephonyId");
                }
	            $db->setQuery($query);
	            $db->execute();
	            return true;
            }
            catch(Exception $e) {
                Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
            }
        }
	}
?>
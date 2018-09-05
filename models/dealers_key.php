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

	            $query->select('`key`')
	                ->from('`#__gm_ceiling_dealers_key`')
	                ->where("dealer_id = $dealer_id");
	            $db->setQuery($query);
	            $items = $db->loadObject();
	    		return $items;
	        }
	        catch(Exception $e)
	        {
	            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
	        }
	    }

	}
?>
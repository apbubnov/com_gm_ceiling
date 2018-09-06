<?php
// No direct access.
defined('_JEXEC') or die;
class Gm_ceilingControllerApi_phones extends JControllerLegacy{

	/**
     * Proxy for getModel.
     *
     * @param   string $name The model name. Optional.
     * @param   string $prefix The class prefix. Optional
     * @param   array $config Configuration array for model. Optional
     *
     * @return object    The model
     *
     * @since    1.6
     */
    public function &getModel($name = 'Api_phones', $prefix = 'Gm_ceilingModel', $config = array())
    {
        return parent::getModel($name, $prefix, array('ignore_request' => true));
    }

	function getAdvtByPhone(){
		try{
			$jinput = JFactory::getApplication()->input;
			$dealer_id = JFactory::getUser()->dealer_id;
			$phone = $jinput->get('phone','','STRING');
			if(!empty($phone)){
				$model = $this->getModel();
				$advt = $model->getNumberInfo($phone);
				if($advt->dealer_id == $dealer_id){
					$result = $advt;	
				}
				else{
					$result = "Не ваш номер";
				 }
			}
			else{
				$result = "Не найдено";
			}
			die(json_encode($result));
		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}
}
?>

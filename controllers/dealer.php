<?php
/**
 * @version    CVS: 1.0.0
 * @package    Com_Gm_ceiling
 * @author     Mikhail "Spectral Eye" Vinyukov <vms@itctl.ru>
 * @copyright  2016 Mikhail "Spectral Eye" Vinyukov
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

/**
 * Parts list controller class.
 *
 * @since  1.6
 */
class Gm_ceilingControllerDealer extends Gm_ceilingController
{
	/**
	 * Proxy for getModel.
	 *
	 * @param   string  $name    The model name. Optional.
	 * @param   string  $prefix  The class prefix. Optional
	 * @param   array   $config  Configuration array for model. Optional
	 *
	 * @return object	The model
	 *
	 * @since	1.6
	 */
	public function &getModel($name = 'Clients', $prefix = 'Gm_ceilingModel', $config = array())
	{
		$model = parent::getModel($name, $prefix, array('ignore_request' => true));

		return $model;
	}
	
	/**
	 * Method to save a user's profile data.
	 *
	 * @return void
	 *
	 * @throws Exception
	 * @since  1.6
	 */
	public function updatedata($key = NULL, $urlVar = NULL)
	{
        $app = JFactory::getApplication();
        $user = JFactory::getUser();
        $userID = JFactory::getUser()->id;
        $jinput = JFactory::getApplication()->input;
        $data = JFactory::getApplication()->input->get('jform', array(), 'array');
        $model_dealer_info = Gm_ceilingHelpersGm_ceiling::getModel('dealer_info');
        $result = $model_dealer_info->updateMarginAndMount($userID,$data );
        if($result == 1) $message = "Успешно сохранено!";
        else $message = "Возникла ошибка сохранения!";
        $this->setMessage($message);
        $url  = 'index.php?option=com_gm_ceiling&view=mainpage&type=dealermainpage';
		$this->setRedirect(JRoute::_($url, false));
		
	}
}

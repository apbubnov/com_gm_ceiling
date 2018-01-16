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
        /* переделать*/
		$message = "Успешно сохранено!";
		$data = JFactory::getApplication()->input->get('jform', array(), 'array');
		$userID = JFactory::getUser()->id;
		$user =& JUser::getInstance((int)$userID);
		$post['dealer_canvases_margin'] = $data['dealer_canvases_margin'];
		$post['dealer_components_margin'] = $data['dealer_components_margin'];
		$post['dealer_mounting_margin'] = $data['dealer_mounting_margin'];
		$post['mp1'] = $data['mp1'];
		$post['mp2'] = $data['mp2'];
		$post['mp3'] = $data['mp3'];
		$post['mp4'] = $data['mp4'];
		$post['mp5'] = $data['mp5'];
		$post['mp6'] = $data['mp6'];
		$post['mp7'] = $data['mp7'];
		$post['mp8'] = $data['mp8'];
		$post['mp9'] = $data['mp9'];
		$post['mp10'] = $data['mp10'];
		$post['mp11'] = $data['mp11'];
		$post['mp12'] = $data['mp12'];
		$post['mp13'] = $data['mp13'];
		$post['mp14'] = $data['mp14'];
		$post['mp15'] = $data['mp15'];
		$post['mp16'] = $data['mp16'];
		$post['mp17'] = $data['mp17'];
		$post['mp18'] = $data['mp18'];
		$post['mp19'] = $data['mp19'];
		$post['transport'] = $data['transport'];
		if (!$user->bind($post))
		{
			$message = $user->getError();
		} 
		if ( !$user->save() )
		{
			$message = $user->getError();
		}
		$this->setMessage($message);
		$menu = JFactory::getApplication()->getMenu();
		$item = $menu->getActive();
		$url  = 'index.php?option=com_gm_ceiling&view=mainpage&type=dealermainpage';
		$this->setRedirect(JRoute::_($url, false));
	}
}

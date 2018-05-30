<?php

/**
 * @version    CVS: 1.0.0
 * @package    Com_Gm_ceiling
 * @author     Mikhail  <vms@itctl.ru>
 * @copyright  2016 Mikhail 
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access
defined('_JEXEC') or die;

/**
 * Team controller class.
 *
 * @since  1.6
 */
class Gm_ceilingControllerGaugerForm extends JControllerForm
{

	public function RegisterGauger() {
		try
		{
			$user     = JFactory::getUser();
			$userId   = $user->get('id');
			$dealerId = $user->dealer_id;
			
			$jinput = JFactory::getApplication()->input;
			$name = $jinput->get('name', '', 'STRING');
			$phone = $jinput->get('phone', '', 'STRING');
			$phone = preg_replace('/[\(\)\-\+\s]/', '', $phone);
			$email = $jinput->get('email', '', 'STRING');

			//генератор пароля
			$chars="qazxswedcvfrtgbnhyujmkiolp1234567890QAZXSWEDCVFRTGBNHYUJMKIOLP";  
			$max=6; 
			$size=StrLen($chars)-1; 
			$password=null; 
			while($max--) {
				$password.=$chars[rand(0,$size)];
			}
			//-----------------------------------
			jimport('joomla.user.helper');

			if ($dealerId == 1) {
				$groups = array(2, 22);
			} else {
				$groups = array(2, 21);
			}

			$data = array(
				"name" => $name,
				"username" => $phone,
				"password" => $password,
				"email" => $email,
				"groups" => $groups,
				"dealer_id" => $dealerId
			);

		    $user = new JUser;
			if (!$user->bind($data)) {
				throw new Exception($user->getError());
			}
			if (!$user->save()) {
				throw new Exception($user->getError());
			}

			$id_gauger = $user->id;
			
			// письмо
			$mailer = JFactory::getMailer();
			$config = JFactory::getConfig();
			$sender = array(
				$config->get('mailfrom'),
				$config->get('fromname')
			);
			$mailer->setSender($sender);
			$mailer->addRecipient($email);
			$body = "Здравствуйте. Вас зарегистрировали на сайте Гильдии Мастеров как замерщика. Данные учетной записи: \n Логин: ".$phone." \n Пароль: ".$password;
			$mailer->setSubject('Регистрация на сайте Гильдии Мастеров');
			$mailer->setBody($body);
			$send = $mailer->Send();

			// сохранение паспорта
			$model = $this->getModel('Gaugerform', 'Gm_ceilingModel');

			$passport = [];
			for ($i=0; $i<count($_FILES['passport']['name']); $i++) {
				if (!empty($_FILES['passport']['name'][$i])) {
					// Проверяем, что при загрузке не произошло ошибок
					if ($_FILES['passport']['error'][$i] == 0) {
						// Если файл загружен успешно, то проверяем - графический ли он
						if (substr($_FILES['passport']['type'][$i], 0, 5) == 'image') {
							// Читаем содержимое файла
							$passport[$i] = file_get_contents($_FILES['passport']['tmp_name'][$i]);
						}
					}
				}
			}

			// вызов функции сохранения паспорта
			for ($i=0; $i<count($passport); $i++) {
				if ($passport[$i] != null) {
					$model->SaveGaugerPassport($id_gauger, $passport[$i]);
				}
			}
			
			// редирект после сохранения
			if ($dealerId == 1) {
				header('Location: /index.php?option=com_gm_ceiling&view=gaugers&type=gmchief');
			} else {
				header('Location: /index.php?option=com_gm_ceiling&view=gaugers&type=chief');				
			}

			
		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());

        }
	}

}

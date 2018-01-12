<?php

/**
 * @version    CVS: 0.1.7
 * @package    Com_Gm_ceiling
 * @author     SpectralEye <Xander@spectraleye.ru>
 * @copyright  2016 SpectralEye
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access
defined('_JEXEC') or die;

/**
 * Project controller class.
 *
 * @since  1.6
 */
class Gm_ceilingControllerMountersorder extends JControllerLegacy {
	/* НАКЛАДНАЯ, РАСЧЕТЫ И ПРОЧЕЕ МОНТАЖНИКА */

	public function GetData() {
		try
		{
			$DataOfNPack1 = $_POST["DataOfNPack1"];
			$DataOfNPack2 = $_POST["DataOfNPack2"];
			$DataOfNPack3 = $_POST["DataOfNPack3"];
			$DataOfNPack4 = $_POST["DataOfNPack4"];
			$DataOfNPack5 = $_POST["DataOfNPack5"];
			$DataOfNPack6 = $_POST["DataOfNPack6"];
			$DataOfNPack7 = $_POST["DataOfNPack7"];
			$DataOfMp = $_POST["DataOfMp"];

			$all = [];

			foreach ($DataOfNPack1 as $value) {
				if ($value["n1"] == 28) { // только ПВХ
					$price_n5 = 0;
					if ($value["n28"] == 0) {
						$price_n5 = $DataOfMp[0]["mp1"];
					} else if ($value["n28"] == 1) {
						$price_n5 = $DataOfMp[0]["mp31"];
					} else if ($value["n28"] == 2) {
						$price_n5 = $DataOfMp[0]["mp32"];
					}
					$calc_n5 = $value["n5"] * $price_n5;
					
					if ($value->n6 > 0) {
						$calc_n6 = $value["n5"] * $DataOfMp[0]["mp10"];
					} else {
						$calc_n6 = 0;
					}
					$value["n9"] = 0;
					$calc_n9 = 0;
					$price_n11 = $DataOfMp[0]["mp22"];
					$calc_n11 = $value["n11"] * $price_n11;
					$calc_n12 = $value["n12"] * $DataOfMp[0]["mp2"];
					$calc_n17 = $value["n17"] * $DataOfMp[0]["mp11"];
					$calc_n20 = $value["n20"] * $DataOfMp[0]["mp9"];
					$calc_n21 = $value["n21"] * $DataOfMp[0]["mp6"];
					$calc_n27 = $value["n27"] * $DataOfMp[0]["mp11"];
					$calc_n30 = $value["n30"] * $DataOfMp[0]["mp30"];
				} else if ($value["n1"] == 29) { // только ткань
					$price_n5 =  $DataOfMp[0]["mp33"];
					$calc_n5 = $value["n5"] * $price_n5;
					$calc_n9 = $value["n9"] * $DataOfMp[0]["mp43"];
					$price_n11 = $DataOfMp[0]["mp33"];
					$calc_n11 = $value["n11"] * $DataOfMp[0]["mp33"];
					$calc_n12 = $value["n12"] * $DataOfMp[0]["mp34"];
					$calc_n17 = $value["n17"] * $DataOfMp[0]["mp41"];
					$calc_n21 = $value["n21"] * $DataOfMp[0]["mp38"];
					$calc_n27 = $value["n27"] * $DataOfMp[0]["mp41"];
				} // и ПВХ и ткань
				$calc_n7 = $value["n7"] * $DataOfMp[0]["mp13"];
				$calc_n8 = $value["n8"] * $DataOfMp[0]["mp14"];
				$calc_n18 = $value["n18"] * $DataOfMp[0]["mp15"];
				$calc_n24 = $value["n24"] * $DataOfMp[0]["mp17"];
				$calc_dop_krepezh = $value["dop_krepezh"] * $DataOfMp[0]["mp18"];
				
				$extra_mounting = json_decode($value["extra_mounting"], true);

				$image = md5("calculation_sketch".$value["id"]);

				$all[$value["id"]] = ["image"=>$image, "name"=>$value["calculation_title"], 
				"n5_price"=>$price_n5, "n5_count"=>$value["n5"], "n5_sum"=>$calc_n5, 
				"n6_price"=>$DataOfMp[0]["mp10"], "n6_count"=>$value["n6"], "n6_sum"=>$calc_n6, 
				"n7_price"=>$DataOfMp[0]["mp13"], "n7_count"=>$value["n7"], "n7_sum"=>$calc_n7, 
				"n8_price"=>$DataOfMp[0]["mp14"], "n8_count"=>$value["n8"], "n8_sum"=>$calc_n8, 
				"n9_price"=>$DataOfMp[0]["mp43"], "n9_count"=>$value["n9"], "n9_sum"=>$calc_n9, 
				"n11_price"=>$price_n11, "n11_count"=>$value["n11"], "n11_sum"=>$calc_n11,
				"n12_price"=>$DataOfMp[0]["mp2"], "n12_count"=>$value["n12"], "n12_sum"=>$calc_n12, 
				"n17_price"=>$DataOfMp[0]["mp11"], "n17_count"=>$value["n17"], "n17_sum"=>$calc_n17, 
				"n18_price"=>$DataOfMp[0]["mp15"], "n18_count"=>$value["n18"], "n18_sum"=>$calc_n18, 
				"n20_price"=>$DataOfMp[0]["mp9"], "n20_count"=>$value["n20"], "n20_sum"=>$calc_n20, 
				"n21_price"=>$DataOfMp[0]["mp6"], "n21_count"=>$value["n21"], "n21_sum"=>$calc_n21, 
				"n24_price"=>$DataOfMp[0]["mp17"], "n24_count"=>$value["n24"], "n24_sum"=>$calc_n24, 
				"n27_price"=>$DataOfMp[0]["mp11"], "n27_count"=>$value["n27"], "n27_sum"=>$calc_n27, 
				"n30_price"=>$DataOfMp[0]["mp30"], "n30_count"=>$value["n30"], "n30_sum"=>$calc_n30,
				"dop_krepezh_price"=>$DataOfMp[0]["mp18"], "dop_krepezh_count"=>$value["dop_krepezh"], "dop_krepezh_sum"=>$calc_dop_krepezh,  
				"extra_mounting"=>$extra_mounting];
			}			
			
			foreach ($DataOfNPack2 as $value) {
				if ($value["n1"] == 28) { // только ПВХ
					$price = max($DataOfMp[0]["mp4"], $DataOfMp[0]["mp5"]);
					$count = $value["n13_count"];
					$calc_n13 = $value["n13_count"] * max($DataOfMp[0]["mp4"], $DataOfMp[0]["mp5"]);
				} else if ($value["n1"] == 29) { // только ткань
					$price = max($DataOfMp[0]["mp36"], $DataOfMp[0]["mp37"]);
					$count = $value["n13_count"];
					$calc_n13 = $value["n13_count"] * max($DataOfMp[0]["mp36"], $DataOfMp[0]["mp37"]);
				}	
				$all[$value["id_calculation"]] += ["n13_price" => $price, "n13_count" => $count, "n13_sum" => $calc_n13];
			}

			foreach ($DataOfNPack3 as $value) {
				if ($value["n1"] == 28) { // только ПВХ
					$price = $DataOfMp[0]["mp8"];
					$count = $value["n14_count"];
					$calc_n14 = $value["n14_count"] * $DataOfMp[0]["mp8"];
				} else if ($value["n1"] == 29) { // только ткань
					$price = $DataOfMp[0]["mp40"];
					$count = $value["n14_count"];
					$calc_n14 = $value["n14_count"] * $DataOfMp[0]["mp40"];
				}
				$all[$value["id"]] += ["n14_price" => $price, "n14_count" => $count, "n14_sum" => $calc_n14];
			}

			foreach ($DataOfNPack4 as $value) {
				if ($value["n1"] == 28) { // только ПВХ
					$price_56 = $DataOfMp[0]["mp12"];
				} else if ($value["n1"] == 29) { // только ткань
					$price_56 = $DataOfMp[0]["mp42"];
				}
				$price_78 = $DataOfMp[0]["mp16"];

				if ($value["n22_type"] == 5 || $value["n22_type"] == 6) {
					$count_56 = $value["n22_count"];
					$calc_n22_56 = $value["n22_count"] * $price_56;
					$all[$value["id_calculation"]] += ["n22_56_count" => $count_56, "n22_56_sum" => $calc_n22_56, "n22_56_price" => $price_56];
				} else if ($value["n22_type"] == 7 || $value["n22_type"] == 8) {
					$count_78 = $value["n22_count"];
					$calc_n22_78 = $value["n22_count"] * $price_78;
					$all[$value["id_calculation"]] += ["n22_78_count" => $count_78, "n22_78_sum" => $calc_n22_78, "n22_78_price" => $price_78];
				}
			}

			foreach ($DataOfNPack5 as $value) {
				$price = $DataOfMp[0]["mp19"];
				$count = $value["n23_count"];
				$calc_n23 = $value["n23_count"] * $DataOfMp[0]["mp19"];
				$all[$value["id"]] += ["n23_price" => $price, "n23_count" => $count, "n23_sum" => $calc_n23];
			}
						
			if ($DataOfNPack6[0]["transport"] == 1) {
				$calc_transport = $DataOfMp[0]["transport"] * $DataOfNPack6[0]["distance_col"];
				$transport_price = $DataOfMp[0]["transport"];
				$transport_count = $DataOfNPack6[0]["distance_col"];
			} else if ($DataOfNPack6[0]["transport"] == 2) {
				$calc_transport = $DataOfMp[0]["distance"] * $DataOfNPack6[0]["distance_col"] * $DataOfNPack6[0]["distance"];
				$transport_price = $DataOfMp[0]["distance"];
				$transport_count = $DataOfNPack6[0]["distance_col"] * $DataOfNPack6[0]["distance"];
			}
			$all += ["transport_price" => $transport_price];
			$all += ["transport_count" => $transport_count];
			$all += ["transport_sum" => $calc_transport];
			
			foreach ($DataOfNPack7 as $value) {
				if ($value["n1"] == 28) { // только ПВХ
					if ($value["n29_type"] == 13) {
						$count_29_24 = $value["n29_count"];
						$price_29_24 = $DataOfMp[0]["mp24"];
						$calc_n29_24 = $count_29_24 * $price_29_24;
						$all[$value["id_calculation"]] += ["n29_count_24" => $count_29_24, "n29_sum_24" => $calc_n29_24, "n29_price_24" => $price_29_24];
					} else if ($value["n29_type"] == 16) {
						$count_29_26 = $value["n29_count"];
						$price_29_26 = $DataOfMp[0]["mp26"];
						$calc_n29_26 = $count_29_26 * $price_29_26;
						$all[$value["id_calculation"]] += ["n29_count__26" => $count_29_26, "n29_sum_26" => $calc_n29_26, "n29_price_26" => $price_29_26];
					}
				} 
				if ($value["n29_type"] == 12) {
					$count_29_23 = (int)$value["n29_count"];
					$price_29_23 = (int)$DataOfMp[0]["mp23"];
					$calc_n29_23 = floatval($count_29_23) * floatval($price_29_23);
					$all[$value["id_calculation"]] += ["n29_count_23" => $count_29_23, "n29_sum_23" => $calc_n29_23, "n29_price_23" => $price_29_23];
				} else if ($value["n29_type"] == 15) {
					$count_29_25 = $value["n29_count"];
					$price_29_25 = $DataOfMp[0]["mp25"];
					$calc_n29_25 = $count_29_25 * $price_29_25;
					$all[$value["id_calculation"]] += ["n29_count_25" => $count_29_25, "n29_sum_25" => $calc_n29_25, "n29_price_25" => $price_29_25];
				}				
			}

			echo json_encode($all);

			exit;
		}
		catch(Exception $e)
        {
            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);
        }
	}

	public function GetDates() {
		try
		{
			$id = $_POST["url_proj"];
			
			$model = $this->getModel('Mountersorder', 'Gm_ceilingModel');
			$model_request = $model->GetDates($id);

			echo json_encode($model_request);

			exit;
		}
		catch(Exception $e)
        {
            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);
        }
	}

	public function MountingStart() {
		try
		{
			$id = $_POST["url_proj"];
			$date = $_POST["date"];

			$model = $this->getModel('Mountersorder', 'Gm_ceilingModel');
			$model_request = $model->MountingStart($id, $date);

			// письмо
			$emails = $model->AllNMSEmails();
			$DataOrder = $model->DataOrder($id);
			$NamesMounters = $model->NamesMounters($DataOrder[0]->project_mounter);
			$mailer = JFactory::getMailer();
			$config = JFactory::getConfig();
			$sender = array(
				$config->get('mailfrom'),
				$config->get('fromname')
			);
			$mailer->setSender($sender);
			foreach ($emails as $value) {
				$mailer->addRecipient($value->email);
			}
			$body = "Здравствуйте. \n";
			$body .= "Проект №$id перешел в статус \"Монтаж\".\n";
			$body .= "\n";
			$body .= "Монтажная Бригада: ".$DataOrder[0]->project_mounter_name." (";
			foreach ($NamesMounters as $value) {
				$names .= "$value->name, ";
			}
			$body .= substr($names, 0, -2);
			$body .= ").\n";
			$body .= "Адрес: ".$DataOrder[0]->project_info."\n";
			$body .= "Дата и время монтажа: ".substr($DataOrder[0]->project_mounting_date,8, 2).".".substr($DataOrder[0]->project_mounting_date,5, 2).".".substr($DataOrder[0]->project_mounting_date,0, 4)." ".substr($DataOrder->project_mounting_date,11, 5)." \n";
			$body .= "Чтобы перейти на сайт, щелкните здесь: <a href=\"http://test1.gm-vrn.ru/\">http://test1.gm-vrn.ru</a>";
			$mailer->setSubject('Новый статус монтажа');
			$mailer->setBody($body);
			$send = $mailer->Send();

			echo json_encode($model_request);

			exit;
		}
		catch(Exception $e)
        {
            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);
        }
	}

	public function MountingComplited() {
		try
		{
			$id = $_POST["url_proj"];
			$date = $_POST["date"];
			$note = $_POST["note"];

			if (strlen($note) != 0) {
				$note2 = "Монтаж по проекту №$id выполнен. Примечание от монтажной бригады: ".$note;			
			} /*else {
				$note = "Монтаж выполнен. Примечание от монтажной бригады: отсутствует";
			}*/

			$model = $this->getModel('Mountersorder', 'Gm_ceilingModel');
			$model_request = $model->MountingComplited($id, $date, $note2, $note);

			// письмо
			$emails = $model->AllNMSEmails();
			$DataOrder = $model->DataOrder($id);
			$NamesMounters = $model->NamesMounters($DataOrder[0]->project_mounter);
			$mailer = JFactory::getMailer();
			$config = JFactory::getConfig();
			$sender = array(
				$config->get('mailfrom'),
				$config->get('fromname')
			);

			$mailer->setSender($sender);
			foreach ($emails as $value) {
				$mailer->addRecipient($value->email);
			}
			$body = "Здравствуйте.\n";
			$body .= "Проект №$id перешел в статус \"Монтаж закончен\".\n";
			$body .= "\n";
			$body .= "Монтажная Бригада: ".$DataOrder[0]->project_mounter_name." (";
			foreach ($NamesMounters as $value) {
				$names .= "$value->name, ";
			}
			$body .= substr($names, 0, -2);
			$body .= ").\n";
			$body .= "Адреc: ".$DataOrder[0]->project_info."\n";
			$body .= "Дата и время: ".substr($DataOrder[0]->project_mounting_date,8, 2).".".substr($DataOrder[0]->project_mounting_date,5, 2).".".substr($DataOrder[0]->project_mounting_date,0, 4)." ".substr($DataOrder->project_mounting_date,11, 5)." \n";
			if (strlen($note) != 0) {
				$body .= "Примечание монтажника: ".$note."\n";			
			}
			$body .= "Чтобы перейти на сайт, щелкните здесь: <a href=\"http://test1.gm-vrn.ru/\">http://test1.gm-vrn.ru</a>";		
			$mailer->setSubject('Новый статус монтажа');
			$mailer->setBody($body);
			$send = $mailer->Send();

			echo json_encode($model_request);

			exit;
		}
		catch(Exception $e)
        {
            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);
        }
	}

	public function MountingUnderfulfilled() {
		try
		{
			$id = $_POST["url_proj"];
			$note = $_POST["note"];
			$date = $_POST["date"];

			$note = "Монтаж по проекту №$id недовыполнен. Примечание от монтажной бригады: ".$note;

			$model = $this->getModel('Mountersorder', 'Gm_ceilingModel');
			$model_request = $model->MountingUnderfulfilled($id, $date, $note);

			// письмо
			$emails = $model->AllNMSEmails();
			$DataOrder = $model->DataOrder($id);
			$NamesMounters = $model->NamesMounters($DataOrder[0]->project_mounter);		
			$mailer = JFactory::getMailer();
			$config = JFactory::getConfig();
			$sender = array(
				$config->get('mailfrom'),
				$config->get('fromname')
			);

			$mailer->setSender($sender);
			foreach ($emails as $value) {
				$mailer->addRecipient($value->email);
			}
			$body = "Здравствуйте. \n";
			$body .= "Проект №$id перешел в статус \"Монтаж незавершен\".\n";
			$body .= "\n";
			$body .= "Монтажная Бригада: ".$DataOrder[0]->project_mounter_name." (";
			foreach ($NamesMounters as $value) {
				$names .= "$value->name, ";
			}
			$body .= substr($names, 0, -2);
			$body .= ").\n";
			$body .= "Адрес: ".$DataOrder[0]->project_info."\n";
			$body .= "Дата и время: ".substr($DataOrder[0]->project_mounting_date,8, 2).".".substr($DataOrder[0]->project_mounting_date,5, 2).".".substr($DataOrder[0]->project_mounting_date,0, 4)." ".substr($DataOrder->project_mounting_date,11, 5)." \n";
			$body .= "Примечание монтажника: ".$note."\n";
			$body .= "Чтобы перейти на сайт, щелкните здесь: <a href=\"http://test1.gm-vrn.ru/\">http://test1.gm-vrn.ru</a>";				
			$mailer->setSubject('Новый статус монтажа');
			$mailer->setBody($body);
			$send = $mailer->Send();

			echo json_encode($model_request);

			exit;
		}
		catch(Exception $e)
        {
            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);
        }
	}

	/**
	 * Method to check out an item for editing and redirect to the edit form.
	 *
	 * @return void
	 *
	 * @since    1.6
	 */
	/*public function edit()
	{
		try
		{
			$app = JFactory::getApplication();

			// Get the previous edit id (if any) and the current edit id.
			$previousId = (int) $app->getUserState('com_gm_ceiling.edit.project.id');
			$editId     = $app->input->getInt('id', 0);

			// Set the user id for the user to edit in the session.
			$app->setUserState('com_gm_ceiling.edit.project.id', $editId);

			// Get the model.
			$model = $this->getModel('Project', 'Gm_ceilingModel');

			/*//*/ Check out the item
			if ($editId)
			{
				$model->checkout($editId);
			}

			// Check in the previous user.
			if ($previousId && $previousId !== $editId)
			{
				$model->checkin($previousId);
			}*/

			// Redirect to the edit screen.
			/*$this->setRedirect(JRoute::_('index.php?option=com_gm_ceiling&view=projectform&layout=edit', false));
		}
		catch(Exception $e)
        {
            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);
        }
	}

	/**
	 * Method to save a user's profile data.
	 *
	 * @return    void
	 *
	 * @throws Exception
	 * @since    1.6
	 */
	/*public function publish()
	{
		try
		{
			// Initialise variables.
			$app = JFactory::getApplication();

			// Checking if the user can remove object
			$user = JFactory::getUser();

			if ($user->authorise('core.edit', 'com_gm_ceiling') || $user->authorise('core.edit.state', 'com_gm_ceiling'))
			{
				$model = $this->getModel('Project', 'Gm_ceilingModel');

				// Get the user data.
				$id    = $app->input->getInt('id');
				$state = $app->input->getInt('state');

				// Attempt to save the data.
				$return = $model->publish($id, $state);

				// Check for errors.
				if ($return === false)
				{
					$this->setMessage(JText::sprintf('Save failed: %s', $model->getError()), 'warning');
				}

				// Clear the profile id from the session.
				$app->setUserState('com_gm_ceiling.edit.project.id', null);

				// Flush the data from the session.
				$app->setUserState('com_gm_ceiling.edit.project.data', null);

				// Redirect to the list screen.
				$this->setMessage(JText::_('COM_GM_CEILING_ITEM_SAVED_SUCCESSFULLY'));
				$menu = JFactory::getApplication()->getMenu();
				$item = $menu->getActive();

				if (!$item)
				{
					// If there isn't any menu item active, redirect to list view
					$this->setRedirect(JRoute::_('index.php?option=com_gm_ceiling&view=projects', false));
				}
				else
				{
					$this->setRedirect(JRoute::_($item->link . $menuitemid, false));
				}
			}
			else
			{
				throw new Exception(500);
			}
		}
		catch(Exception $e)
        {
            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);
        }
	}

	/**
	 * Remove data
	 *
	 * @return void
	 *
	 * @throws Exception
	 */
	/*public function remove()
	{
		try
		{
			// Initialise variables.
			$app = JFactory::getApplication();

			// Checking if the user can remove object
			$user = JFactory::getUser();

			if ($user->authorise('core.delete', 'com_gm_ceiling'))
			{
				$model = $this->getModel('Project', 'Gm_ceilingModel');

				// Get the user data.
				$id = $app->input->getInt('id', 0);

				// Attempt to save the data.
				$return = $model->delete($id);

				// Check for errors.
				if ($return === false)
				{
					$this->setMessage(JText::sprintf('Delete failed', $model->getError()), 'warning');
				}
				else
				{
					/*//*/ Check in the profile.
					if ($return)
					{
						$model->checkin($return);
					}*/

					// Clear the profile id from the session.
					/*$app->setUserState('com_gm_ceiling.edit.project.id', null);

					// Flush the data from the session.
					$app->setUserState('com_gm_ceiling.edit.project.data', null);

					$this->setMessage(JText::_('COM_GM_CEILING_ITEM_DELETED_SUCCESSFULLY'));
				}

				// Redirect to the list screen.
				$menu = JFactory::getApplication()->getMenu();
				$item = $menu->getActive();
				$this->setRedirect(JRoute::_($item->link, false));
			}
			else
			{
				throw new Exception(500);
			}
		}
		catch(Exception $e)
        {
            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);
        }
	}*/
}


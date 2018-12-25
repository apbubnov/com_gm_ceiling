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
class Gm_ceilingControllerProject extends JControllerLegacy
{

	public function GetBusyMounters() {
		try
		{
	        $date = $_POST["date"];
	        
			$model = Gm_ceilingHelpersGm_ceiling::getModel('project');
			$mounters = $model->FindBusyMounters($date, $date);

			die(json_encode($mounters));
		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());

        }
	}

	public function UpdateDateMountBrigade() {
		try
		{
			$id = $_POST["id_project"];
	        $datatime = $_POST["date"];
			$mounter = $_POST["mounter"];
			$oldmounter = $_POST["oldmounter"];
			$olddatetime = $_POST["olddatetime"];
			$id_client = $_POST["id_client"];;
			
			$model = Gm_ceilingHelpersGm_ceiling::getModel('project');
			$successupdate = $model->UpdateDateMountBrigade($id, $datatime, $mounter);
			
			// комменты
			if ($datatime != $olddatetime) {
				$data = ["datatime" => $datatime, "olddatetime" => $olddatetime];
				$model->AddCommentManager(1, $data, $id_client);
			}
			if ($mounter != $oldmounter) {
				$model->AddCommentManager(2, $mounter, $id_client);
			}

			die(json_encode($successupdate));
		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());

        }
	}

	/**
	 * Method to check out an item for editing and redirect to the edit form.
	 *
	 * @return void
	 *
	 * @since    1.6
	 */
	public function edit()
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

			/*// Check out the item
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
			$this->setRedirect(JRoute::_('index.php?option=com_gm_ceiling&view=projectform&layout=edit', false));
		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());

        }
	}

	public function recToMeasure()
    {
        try {
            $app = JFactory::getApplication();
            $user = JFactory::getUser();

            $model = $this->getModel('Project', 'Gm_ceilingModel');

            extract($_POST); // Элементы массива в переменные

            $data = $model->getData($project_id);

            if (empty($new_discount)) $new_discount = $data->project_discount;

            $emails = (empty($email_str))?[]:explode(";", $email_str);
            array_pop($emails);

            if (isset($data_delete) && isset($idCalcDelete)) {
                $model_calc = Gm_ceilingHelpersGm_ceiling::getModel('calculations');
                $resultDel = $model_calc->delete($idCalcDelete);
                if ($resultDel == 1) {
                    $this->setMessage("Потолок удален");
                    if(!empty($_SESSION['url'])) $this->setRedirect(JRoute::_($_SESSION['url'], false));
                    else $this->setRedirect(JRoute::_($_SERVER['HTTP_REFERER'], false));
                }

            } else {

            }
        } catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());

        }
    }

    public function changeDiscount()
    {
        try
        {
            $jinput = JFactory::getApplication()->input;
            $model = $this->getModel('Project', 'Gm_ceilingModel');
            $project_id = $jinput->get('project_id', '0', 'INT');
            $data = $model->getData($project_id);
            $new_discount = $jinput->get('new_discount', $data->project_discount, 'INT');
            $result = $model->change_discount($project_id, $new_discount);
            die(json_encode($result));
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

	//запись на замер при входящем звонке
	public function recToMeasurement()
	{
		try {
            $app = JFactory::getApplication();
			$user = JFactory::getUser();
			$user_group = $user->groups;
			if (in_array("16", $user_group)) {
				$usertype = "gmmanagermainpage"; 
			} else {
				$usertype = "managermainpage";
			}
			$model = $this->getModel('Project', 'Gm_ceilingModel');
            $jinput = $app->input;
            $project_id = $jinput->get('project_id', '0', 'INT');
            $data = $model->getData($project_id);
            $type = $jinput->get('type', '', 'STRING');
            $subtype = $jinput->get('subtype', '', 'STRING');
            $call_id = $jinput->get('call_id', 0, 'INT');
            $client_id = $jinput->get('client_id', 1, 'INT');
            $api_phone_id = $jinput->get('advt_id', '0', 'INT');
            $selected_advt = $jinput->get('selected_advt', '0', 'INT');
            $call_type = $jinput->get('slider-radio', 'client', 'STRING');
            $recoil = $jinput->get('recoil', '', 'STRING');
            $sex = $jinput->get('slider-sex', "NULL", 'STRING');
            $emails = $jinput->get('email',array(),'ARRAY');
			$without_advt = $jinput->get('without_advt', 0, 'INT');
			$client_form_model = $this->getModel('ClientForm', 'Gm_ceilingModel');
			$client_model = $this->getModel('client', 'Gm_ceilingModel');
            $user_model = $this->getModel('users', 'Gm_ceilingModel');
            $call_comment = $jinput->get('call_comment', '', 'STRING');
            $call_date = $jinput->get('call_date', "0", 'STRING');
            $isDataDelete = $jinput->get('data_delete', '0', 'INT');
            if ($isDataDelete) {
                $idCalc = $jinput->get('idCalcDelete', '0', 'INT');
                //print_r($idCalc); exit;
                $model_calc = Gm_ceilingHelpersGm_ceiling::getModel('calculations');
                $resultDel = $model_calc->delete($idCalc);
                //print_r($_SESSION['url']."test"); exit;
                if ($resultDel == 1) {
                    $this->setMessage("Потолок удален");
                    if(!empty($_SESSION['url'])) $this->setRedirect(JRoute::_($_SESSION['url'], false));
                    else $this->setRedirect(JRoute::_($_SERVER['HTTP_REFERER'], false));
                }

            } else {
                if ($api_phone_id == 0 && $without_advt == 0) {
                    if ($selected_advt != 0) {
                        $api_phone_id = $selected_advt;
                    } else {
                        $api_phone_id = NULL;
                        if ($client_id != 1) {
                            throw new Exception("Пожалуйста, укажите рекламу!");
                        }
                    }
                } elseif ($without_advt == 1) {
                    $api_phone_id = NULL;
                }

                $client_history_model = $this->getModel('Client_history', 'Gm_ceilingModel');
                $status = $jinput->get('status', '0', 'INT');
                $gauger = null;
                $date = $jinput->get('project_new_calc_date', '', 'STRING');
                $time = $jinput->get('new_project_calculation_daypart', '0', 'STRING');
                $date_time = $date . " " . $time;
                switch ($status) {
                    case 1 :
                        $gauger = $jinput->get('project_gauger', '', 'STRING');
                        $result = "записан на замер";
                        $answer = "записан на замер на " . $date_time;
                        break;
                    case 2 :
                        $result = "переведен в статус отказа от замера";
                        break;
                    case 15:
                        $result = "переведен в статус отказа от сотрудничества";
                        break;
                    default:
                        $result = "переведен в статус просчета";
                        break;
                }
                $name = $jinput->get('new_client_name', '', 'STRING');
                $phones = $jinput->get('new_client_contacts', array(), 'ARRAY');
                foreach ($phones as $key => $value) {
                    $phones[$key] = preg_replace('/[\(\)\-\+\s]/', '', $value);
                }

                if(!empty($emails)){
                    $dop_contacts = $this->getModel('clients_dop_contacts', 'Gm_ceilingModel');
                    foreach ($emails as $key => $value) {
                        $dop_contacts->updateEmail($client_id,$value,$key);
                    }
                }
                $street = $jinput->get('new_address', '', 'STRING');
                $house = $jinput->get('new_house', '', 'STRING');
                $bdq = $jinput->get('new_bdq', '', 'STRING');
                $apartment = $jinput->get('new_apartment', '', 'STRING');
                $porch = $jinput->get('new_porch', '', 'STRING');
                $floor = $jinput->get('new_floor', '', 'STRING');
                $code = $jinput->get('new_code', '', 'STRING');
                if (!empty($house)) $address = $street . ", дом: " . $house;
                if (!empty($bdq)) $address .= ", корпус: " . $bdq;
                if (!empty($apartment)) $address .= ", квартира: " . $apartment;
                if (!empty($porch)) $address .= ", подъезд: " . $porch;
                if (!empty($floor)) $address .= ", этаж: " . $floor;
                if (!empty($code)) $address .= ", код: " . $code;

                $cl_phones_model = $this->getModel('Client_phones', 'Gm_ceilingModel');
                $gmmanager_comment = $jinput->get('gmmanager_note', null, 'STRING');
                $manager_comment = $jinput->get('manager_note', null, 'STRING');
                $new_phones = [];
                $change_phones = [];
                $newFIO = $jinput->get('new_client_name', '', 'STRING');
                foreach ($phones as $key => $value) {
                    if (strlen($key) < 3) {
                        array_push($new_phones, $value);
                    } else {
                        if ($key != $value) {
                            $change_phones[$key] = $value;
                        }
                    }
                }

                if (!empty($newFIO)) {
                    if ($newFIO != $data->client_id) {
                        $client_model->updateClient($client_id, $newFIO);
                        $user_model->updateUserNameByAssociatedClient($client_id, $newFIO);
                        $client_history_model->save($client_id, "Изменено ФИО пользователя");
                    }
                }

                $client_model->updateClientSex($client_id, $sex);
                if ($call_type == "client") {
                    if (count($new_phones) > 0) {
                        $cl_phones_model->save($client_id, $new_phones);
                    }

                    if (count($change_phones) > 0) {
                        $cl_phones_model->update($client_id, $change_phones);
                    }
                    if ($api_phone_id == 17) {
                        $rec_model = $this->getModel('recoil_map_project', 'Gm_ceilingModel');
                        $rec_model->save($recoil, $project_id, 0);
                    }
                    $rep_model = Gm_ceilingHelpersGm_ceiling::getModel('repeatrequest');
                    $rep_proj = $rep_model->getDataByProjectId($project_id);
                    if (empty($rep_proj) || $without_advt == 1) {
                        // условия на статус
                        $model->update_project_after_call($project_id, $client_id, $date_time, $address, $gmmanager_comment, $manager_comment, $status, $api_phone_id, $user->id, $gauger);
                    } else {
                        // условия на статус
                        $model->update_project_after_call($project_id, $client_id, $date_time, $address, $gmmanager_comment, $manager_comment, $status, 10, $user->id, $gauger);
                        $rep_upd = $rep_model->update($project_id, $api_phone_id);
                    }

                    $callback_model = $this->getModel('callback', 'Gm_ceilingModel');
                    if ($call_id != 0) {
                        if ($call_date != "") {
                            $callback_model->updateCall($call_id, $call_date, $call_comment);
                        } elseif ($call_date == "") {
                            $callback_model->deleteCall($call_id);
                        }
                    } else {
                        if ($call_date != "") {
                            $callback_model->save($call_date, $call_comment, $client_id, $user->id);
                            $client_history_model->save($client_id, "Добавлен новый звонок. Примечание: $call_comment");
                        }
                    }
                    if (!empty($answer)) $client_history_model->save($client_id, "Проект № " . $project_id . " " . $answer);
                    else $client_history_model->save($client_id, "Проект № " . $project_id . " " . $result);
                } elseif ($call_type == "promo") {
                    $client_history_model->save($client_id, "Клиент помечен как реклама");

                    $rep_model = Gm_ceilingHelpersGm_ceiling::getModel('repeatrequest');
                    $rep_proj = $rep_model->getDataByProjectId($project_id);
                    if (empty($rep_proj) || $without_advt == 1) {
                        $model->update_project_after_call($project_id, $client_id, $date_time, $address, $gmmanager_comment, $manager_comment, 21, $api_phone_id, $user->id, $gauger);
                    } else {
                        $model->update_project_after_call($project_id, $client_id, $date_time, $address, $gmmanager_comment, $manager_comment, 21, 10, $user->id, $gauger);
                        $rep_upd = $rep_model->update($project_id, $api_phone_id);
                    }

                    $model->deleteAdvtProjectsByClientId($client_id);
                    $status = 21;
                    $this->setMessage("Клиент помечен как реклама");
                } elseif ($call_type == "dealer") {
                    //зарегать как user,
                    $dop_contacts = Gm_ceilingHelpersGm_ceiling::getModel('clients_dop_contacts');
                    $emails = $dop_contacts->getEmailByClientID($client_id);
                    if (count($emails) != 0) {
                        $email = $emails[0]->contact;
                    } else {
                        $email = "$client_id@$client_id";
                    }
                    $client_history_model->save($client_id, "Клиент переведен в дилеры.");
                    $username = preg_replace('/[\(\)\-\s]/', '', array_shift($phones));
                    if ($client_model->checkIsDealer($username) == 0) {
                        $userID = Gm_ceilingHelpersGm_ceiling::registerUser($name, $username, $email, $client_id);
                        $client_model->updateClient($client_id, null, $userID);

                        $info_model = Gm_ceilingHelpersGm_ceiling::getModel('dealer_info');
                        $dealer_canvases_margin = $info_model->getMargin('dealer_canvases_margin', $userID);
                        $dealer_components_margin = $info_model->getMargin('dealer_components_margin', $userID);
                        $dealer_mounting_margin = $info_model->getMargin('dealer_mounting_margin', $userID);
                        $gm_canvases_margin = $info_model->getMargin('gm_canvases_margin', $userID);
                        $gm_components_margin = $info_model->getMargin('gm_components_margin', $userID);
                        $gm_mounting_margin = $info_model->getMargin('gm_mounting_margin', $userID);
                    }

                    $rep_model = Gm_ceilingHelpersGm_ceiling::getModel('repeatrequest');
                    $rep_proj = $rep_model->getDataByProjectId($project_id);
                    if (empty($rep_proj) || $without_advt == 1) {
                        $model->update_project_after_call($project_id, $client_id, $date_time, $address, $gmmanager_comment, $manager_comment, 20, $api_phone_id, $user->id, $gauger, $dealer_canvases_margin, $dealer_components_margin,
                            $dealer_mounting_margin, $gm_canvases_margin, $gm_components_margin, $gm_mounting_margin);
                    } else {
                        $model->update_project_after_call($project_id, $client_id, $date_time, $address, $gmmanager_comment, $manager_comment, 20, 10, $user->id, $gauger, $dealer_canvases_margin, $dealer_components_margin,
                            $dealer_mounting_margin, $gm_canvases_margin, $gm_components_margin, $gm_mounting_margin);
                        $rep_upd = $rep_model->update($project_id, $api_phone_id);
                    }
                    $status = 20;
                }
                if ($call_type == "client") {
                    $this->setMessage("Клиент $result!");
                }
                //}
                if ($status == 1) {

                    $data_notify['client_name'] = $name;
                    $data_notify['client_contacts'] = $phones;
                    $data_notify['project_info'] = $address;
                    $data_notify['project_calculation_date'] = $date;
                    $data_notify['project_calculation_daypart'] = $time;
                    $data_notify['project_note'] = $gmmanager_comment;
                    Gm_ceilingHelpersGm_ceiling::notify($data_notify, 0);
                }
                if ($data->project_status != $status) {
                    $model_projectshistory = Gm_ceilingHelpersGm_ceiling::getModel('projectshistory');
                    $model_projectshistory->save($project_id, $status);
                }

				if($usertype=="managermainpage"){
					$this->setRedirect(JRoute::_('/index.php?option=com_gm_ceiling&task=mainpage', false));
				}
				else{
					$this->setRedirect(JRoute::_('/index.php?option=com_gm_ceiling&view=mainpage&type='.$usertype, false));
				}
            }
            unset($_SESSION['FIO'],$_SESSION['address'],$_SESSION['house'],$_SESSION['bdq'],$_SESSION['apartment'],$_SESSION['porch'],$_SESSION['floor'],$_SESSION['code'],$_SESSION['date'],$_SESSION['time'],$_SESSION['phones'],$_SESSION['manager_comment'],$_SESSION['comments'],$_SESSION['url'],$_SESSION['gauger']);
        }
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());

        }
	}

	public function run_in_production(){
		try{
			$app = JFactory::getApplication();
			$user = JFactory::getUser();
            $groups = $user->get('groups');
			//получение нужных моделей
			$model = $this->getModel('Project', 'Gm_ceilingModel');
			$client_history_model = $this->getModel('Client_history', 'Gm_ceilingModel');
			$cl_phones_model = $this->getModel('Client_phones', 'Gm_ceilingModel');
			$client_model =  $this->getModel('client', 'Gm_ceilingModel');
			$calculationsModel = Gm_ceilingHelpersGm_ceiling::getModel('calculations');
            $projects_mounts_model = Gm_ceilingHelpersGm_ceiling::getModel('projects_mounts');
			//--------
			$jinput = JFactory::getApplication()->input;
			$project_id = $jinput->get('project_id', '0', 'INT');
			$data = $model->getData($project_id);
			$type = $jinput->get('type', '', 'STRING');
			$subtype = $jinput->get('subtype', '', 'STRING');
			$call_id =  $jinput->get('call_id',0, 'INT');
			$isDataChange = $jinput->get('data_change', '0', 'INT');
			$client_id = $jinput->get('client_id', 1, 'INT');
            $sex = $jinput->get('slider-sex',"NULL",'STRING');
			$include_calculation = $jinput->get('include_calculation', '', 'ARRAY');
			$call_comment = $jinput->get('call_comment', "Отсутствует", 'STRING');
			$call_date = $jinput->get('call_date', "0", 'STRING');
			$status = $jinput->get('status','','INT');
			$name = $jinput->get('new_client_name', '', 'STRING');
			$phones = $jinput->get('new_client_contacts', array(), 'ARRAY');
            $sum = $jinput->get('project_sum', '', 'STRING');
			foreach ($phones as $key => $value) {
				$phones[$key] = preg_replace('/[\(\)\-\s]/', '', $value);
			}
            $mount_data = json_decode($jinput->get('mount','',"STRING"));
            if(!empty($mount_data)){
                $mount_types = $projects_mounts_model->get_mount_types();
                foreach ($mount_data as $value) {
                    $value->stage_name = $mount_types[$value->stage];
                }
            }
			$street = $jinput->get('new_address', '', 'STRING');
			$house = $jinput->get('new_house', '', 'STRING');
			$bdq = $jinput->get('new_bdq', '', 'STRING');
			$apartment = $jinput->get('new_apartment', '', 'STRING');
			$porch = $jinput->get('new_porch', '', 'STRING');
			$floor = $jinput->get('new_floor', '', 'STRING');
			$code = $jinput->get('new_code', '', 'STRING');
			if(!empty($house)) $address = $street.", дом: ".$house;
			if(!empty($bdq)) $address .= ", корпус: ".$bdq;
			if(!empty($apartment)) $address .= ", квартира: ".$apartment;
			if(!empty($porch)) $address .= ", подъезд: ".$porch;
			if(!empty($floor)) $address .= ", этаж: ".$floor;
			if(!empty($code)) $address .= ", код: ".$code;
            $isDataDelete = $jinput->get('data_delete', '0', 'INT');
            if($isDataDelete) {
                $idCalc = $jinput->get('idCalcDelete','0', 'INT');
				$resultDel = $calculationsModel->delete($idCalc);
                if($resultDel == 1) {
					$this->setMessage("Потолок удален");
                    $this->setRedirect(JRoute::_($_SESSION['url'], false));
                }
			}
			if($isDataChange){
				if(!empty($name)){
					if($name!=$data->client_id){
						$client_model->updateClient($data->id_client,$name);
						$client_history_model->save($data->id_client,"Изменено ФИО");	
					}				
				}
				if(!empty($address)){
					if($address!=$data->project_info){
						$model->update_address($data->id,$address);
						$client_history_model->save($data->id_client,"Адрес замера изменен с ".$data->project_info." на ".$address);
					}							
				}
				$new_phones = [];
				$change_phones = [];
				foreach ($phones as $key => $value) {
					if(strlen($key)<3){
						array_push($new_phones,$value);
					}
					else{
						if($key!=$value){
							$change_phones[$key] = $value;
						}
					}
				}
				$client_model->updateClientSex($client_id,$sex);
				if(count($new_phones)>0){
					$cl_phones_model->save($client_id,$new_phones);
				}
                if(count($change_phones)>0){
                    $cl_phones_model->update($client_id,$change_phones);
                }
            }
            $data->project_sum = $sum;
			//$data->project_verdict = 1;
			$calculations = $calculationsModel->new_getProjectItems($data->id);
			$all_calculations = array();
			foreach($calculations as $calculation){
				$all_calculations[] = $calculation->id;
			}
			$ignored_calculations = array_diff($all_calculations, $include_calculation);
			$return = $model->activate($data, 5);
            if(!empty($mount_data)){
                $projects_mounts_model->save($project_id,$mount_data);
                $service_data = $this->check_mount_for_service($mount_data);
                $send_data['project_id'] = $project_id;
                $send_data['client_id'] = $data->id_client;
                $send_data['mount'] = $service_data;
                $pr_data['id'] = $project_id;
                if(!empty($service_data)){
                    foreach ($include_calculation as $calc) {
                        $result[$calc] = Gm_ceilingHelpersGm_ceiling::calculate_mount(0,$calc,null,"service");
                        $mount_sum[$calc] =  $result[$calc]['total_gm_mounting'];
                        foreach ($service_data as $key => $value) {
                            Gm_ceilingHelpersGm_ceiling::create_mount_estimate_by_stage($calc,$value->mounter,$value->stage,$value->time,true);    
                        }
                    }

                    $pr_data['mounting_check'] = json_encode($mount_sum);
                    $this->change_project_data($pr_data);
                    Gm_ceilingHelpersGm_ceiling::notify((object)$send_data, 14);
                }
                else{
                      $pr_data['mounting_check'] = "";
                }
            }
            $recoil_map_model =Gm_ceilingHelpersGm_ceiling::getModel('recoil_map_project');
            $user_model = Gm_ceilingHelpersGm_ceiling::getModel('users');
            $dealer = $user_model->getUserByAssociatedClient($client_id);
            if(!empty($dealer)){
                if(!empty($sum)){
                    $recoil_map_model->save($dealer->id, $project_id, $sum);
                }
            }
            
			if ($return === false)
				{
					$this->setMessage(JText::sprintf('Save failed: %s', $model->getError()), 'warning');
				}
				else {

					if(count($ignored_calculations) > 0) {

						$client_id = $data->id_client;
						$project_data = $model->getData($project_id);
						$project_data->project_status = 3;
						$project_data->gm_calculator_note = "Не вошедшие в договор №" . $data->id;
						//$project_data->project_verdict = 0;
						$project_data->client_id = 	$client_id;
						$project_data->api_phone_id = 10;

						unset($project_data->id);
						$project_model = Gm_ceilingHelpersGm_ceiling::getModel('projectform');
						$refuse_id = $project_model->save(get_object_vars($project_data));
						$client_history_model->save($client_id, "Не вошедшие в договор № ".$project_id." потолки перемещены в проект №".$refuse_id);
						$calculationModel = Gm_ceilingHelpersGm_ceiling::getModel('calculation');
						foreach($ignored_calculations as $ignored_calculation){
							$calculationModel->changeProjectId($ignored_calculation, $refuse_id);
						}
					}
				}

				if(count($ignored_calculations) > 0 ) {
					$data = $model->getNewData($project_id);
					$data->refuse_id = $refuse_id;
					Gm_ceilingHelpersGm_ceiling::notify($data, 6);
					$this->setMessage("Проект сформирован! <br>  Неотмеченные потолки перемещены в копию проекта с отказом");
				} else {
					Gm_ceilingHelpersGm_ceiling::notify($data, 2);
					$this->setMessage("Проект сформирован");
					Gm_ceilingHelpersGm_ceiling::notify($data, 7);
				}
				
                if (in_array('16', $groups))
                {
                    $this->setRedirect("/index.php?option=com_gm_ceiling&view=project&type=gmmanager&id=$project_id", false);
                }
                else
                {
                    $this->setRedirect(JRoute::_('index.php?option=com_gm_ceiling&task=mainpage', false));
                }
		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());

        }
	}
	public function activate() {
		try
		{
			$app = JFactory::getApplication();
			$user = JFactory::getUser();

			$model = $this->getModel('Project', 'Gm_ceilingModel');
			$model_projectshistory = Gm_ceilingHelpersGm_ceiling::getModel('projectshistory');
			$client_history_model = $this->getModel('Client_history', 'Gm_ceilingModel');
            $model_for_mail = Gm_ceilingHelpersGm_ceiling::getModel('calculations');        
			$callback_model = $this->getModel('callback', 'Gm_ceilingModel');
            $cl_phones_model = $this->getModel('Client_phones', 'Gm_ceilingModel');
            $projects_mounts_model = $this->getModel('projects_mounts','Gm_ceilingModel');

            $jinput = JFactory::getApplication()->input;
            $project_id = $jinput->get('project_id', 0, 'INT');
            $data = $model->getData($project_id);
            $include_calculation = $jinput->get('include_calculation', '', 'ARRAY');
            $type = $jinput->get('type', '', 'STRING');
            $subtype = $jinput->get('subtype', '', 'STRING');
            $activate_by_email = $jinput->get('activate_by_email',null,'INT');
            $email = $jinput->get('email_to_send',null,'STRING');
            $mount_data = json_decode($jinput->get('mount','',"STRING"));
            if(!empty($mount_data)){
                $mount_str = "";
                $mount_dates = array();
                $mount_types = $projects_mounts_model->get_mount_types();
                foreach ($mount_data as $value) {
                    $value->stage_name = $mount_types[$value->stage];
                    $date = new DateTime($value->time);
                    $mount_str .= $date->format('d.m.Y H:i:s')." - ".$mount_types[$value->stage]."; ";
                }
            }
			$data->project_sum =  $jinput->get('project_sum',0, 'INT');

			$chief_note = $jinput->get('chief_note',"","STRING");
			if($type === "gmcalculator") {
				$data->gm_calculator_note = $jinput->get('gm_calculator_note', '', 'STRING');
				$data->gm_chief_note = $chief_note;
			} else {
				$data->dealer_calculator_note = $jinput->get('gm_calculator_note', '', 'STRING');
				$data->dealer_chief_note = $chief_note;
			}
			$street = $jinput->get('new_address', '', 'STRING');
			$house = $jinput->get('new_house', '', 'STRING');
			$bdq = $jinput->get('new_bdq', '', 'STRING');
			$apartment = $jinput->get('new_apartment', '', 'STRING');
			$porch = $jinput->get('new_porch', '', 'STRING');
			$floor = $jinput->get('new_floor', '', 'STRING');
			$code = $jinput->get('new_code', '', 'STRING');
			if(!empty($house)) $address = $street.", дом: ".$house;
			if(!empty($bdq)) $address .= ", корпус: ".$bdq;
			if(!empty($apartment)) $address .= ", квартира: ".$apartment;
			if(!empty($porch)) $address .= ", подъезд: ".$porch;
			if(!empty($floor)) $address .= ", этаж: ".$floor;
			if(!empty($code)) $address .= ", код: ".$code;
			$new_address = $address;
			$isDataChange = $jinput->get('data_change', '0', 'INT');
			$isDataDelete = $jinput->get('data_delete', '0', 'INT');

			$smeta = $jinput->get('smeta', '0', 'INT');
			//print_r($smeta); exit;

			// перимерт и зп бригаде
			$project_info_for_mail = $model_for_mail->InfoForMail($project_id);
			$perimeter = 0;
			$salary = 0;
			foreach ($project_info_for_mail as $value) {
				$perimeter += $value->n5;
				$salary += $value->mounting_sum;
			}
			$data->perimeter = $perimeter;
			$data->salary = $salary;
			if ($isDataChange) {
				if($isDataChange){
					$newFIO = $jinput->get('new_client_name','', 'STRING');
					$newDate = $jinput->get('project_new_calc_date','','STRING');
					$newDayPart = $jinput->get('new_project_calculation_daypart','','STRING');
					$newGauger = $jinput->get('project_gauger','','STRING');
					$phones = [];
					$phones[] = $jinput->get('new_client_contacts',null, 'STRING');
					if($data->id_client!=1){
						if(!empty($newFIO)){
							if($newFIO!=$data->client_id){
								$client_model->updateClient($data->id_client,$newFIO);
								$client_history_model->save($data->id_client,"Изменено ФИО пользователя");	
							}				
						}
					}
					else{
						$client_model = $this->getModel('ClientForm', 'Gm_ceilingModel');
						$client_data['created'] = date("d.m.Y");
						$client_data['client_name'] = $newFIO;
						$client_data['client_contacts'] = $newContacts;
						$client_data['dealer_id'] = $user->dealer_id;
						$client_data['manager_id'] = $user->id;
						$client_id = $client_model->save($client_data);
						$model->update_client($data->id,$client_id);
                        if (empty($newFIO))
                        {
                            $cl_model = $this->getModel('Client', 'Gm_ceilingModel');
                            $newFIO = "$client_id";
                            $cl_model->updateClient($client_id,$newFIO);
                        }
						if(!empty($phones[0])){
							$cl_phones_model->save($client_id,$phones);
						}
					}
					if(!empty($new_address)){
						if($new_address!=$data->project_info){
							$model->update_address($data->id,$new_address);
							$client_history_model->save($data->id_client,"Адрес замера изменен с ".$data->project_info." на ".$new_address);
						}							
					}
					$date_time = $data->project_calculation_date;
					$date_arr = date_parse($date_time);
					$date = $date_arr['year'].'-'.$date_arr['month'].'-'.$date_arr['day'];
					$time = $date_arr['hour'].':00';
					if(!empty($newDate) && !empty($newDayPart))
					{
						if($date!=$newDate && $time!=$newDayPart){
							$model->update_date_time($data->id,$newDate." ".$newDayPart);
							$client_history_model->save($data->id_client,"Замер пернесен с ".$date." в ".$time." на ".$newDate." в ".$newDayPart);
						}
						elseif ($date!=$newDate) {
							$model->update_date_time($data->id,$newDate." ".$time);
							$client_history_model->save($data->id_client,"Замер пернесен с ".$date." в ".$time." на ".$newDate." в ".$time);
						}
						elseif ($newDayPart!=$time) {
							$model->update_date_time($data->id,$date." ".$newDayPart);
							$client_history_model->save($data->id_client,"Замер пернесен с ".$date." в ".$time." на ".$date." в ".$newDayPart);
						}
						
					}
					if (!empty($newGauger)) {
						$model->update_date_gauger($data->id,$newGauger);
						$name_gauger = $model->GetNameGauger($newGauger);
						$client_history_model->save($data->id_client,"Замерщик изменен на $name_gauger->name");
					}
					
				}	

				$this->setMessage("Данные успешно изменены");
				if($type === "gmcalculator" && $subtype === "calendar") {
					$this->setRedirect(JRoute::_('index.php?option=com_gm_ceiling&view=project&type=gmcalculator&subtype=calendar&id='.$project_id, false));
				}
				elseif ($type === "calculator" && $subtype === "calendar") {
					$this->setRedirect(JRoute::_('index.php?option=com_gm_ceiling&view=project&type=calculator&subtype=calendar&id='.$project_id, false));
				}
				else {
					$this->setRedirect(JRoute::_('index.php?option=com_gm_ceiling&task=mainpage', false));
				}
			} else {
                
				if($subtype === "refused") {
					$model->return_project($project_id);
				// Clear the profile id from the session.
					$app->setUserState('com_gm_ceiling.edit.project.id', null);

				// Flush the data from the session.
					$app->setUserState('com_gm_ceiling.edit.project.data', null);

					$this->setMessage("Проект вернулся в Замеры");

				} else {
					$project_verdict = $jinput->get('project_verdict', '0', 'INT');
                    $project_status = $jinput->get('project_status', '0', 'INT');

					//$data->project_verdict = $project_verdict;
					
                    $data->project_status = $project_status;
					$calculationsModel = Gm_ceilingHelpersGm_ceiling::getModel('calculations');
					$calculations = $calculationsModel->new_getProjectItems($data->id);
					$all_calculations = array();
					foreach($calculations as $calculation){
						$all_calculations[] = $calculation->id;
					}
					//$checked_calculations = array_intersect($data['include_calculation'], $all_calculations);
					$ignored_calculations = array_diff($all_calculations, $include_calculation);
                    $gm_calculator_note = $jinput->get('gm_calculator_note','Отсутсвует','STRING');
					// Attempt to save the data.
					if($activate_by_email == 0){
						if($user->dealer_type!=2 && $project_verdict == 1) 
						{
							if(empty($mount_data)){
								//$data->project_verdict = 0;
								$client_history_model->save($data->id_client,"По проекту №".$project_id." заключен договор без даты монтажа");
								$call_mount_date = $jinput->get('calldate_without_mounter','','STRING');
								$call_mount_time = $jinput->get('calltime_without_mounter','','STRING'); 

								if(!empty($data->read_by_manager)){
									$callback_model->save($call_mount_date.' '.$call_mount_time,"Примечание от замерщика : ".$gm_calculator_note,$data->id_client,$data->read_by_manager);
									$client_history_model->save($data->id_client,"Добавлен новый звонок. Примечание от замерщика: ".$gm_calculator_note);
								}
								
								//$return = $model->activate($data,4/*3*/);
								
							}
							else{
								if($project_status == 4){
									//$data->project_verdict = 0;
									$client_history_model->save($data->id_client,"По проекту №".$project_id." заключен договор, но не запущен");
									$client_history_model->save($data->id_client,"Проект №".$project_id." назначен на монтаж.".$mount_str );
									//$return = $model->activate($data, 4);

								} else {
									$client_history_model->save($data->id_client,"По проекту №".$project_id." заключен договор");

									$client_history_model->save($data->id_client,"Проект №".$project_id." назначен на монтаж. ".$mount_str);
									//$return = $model->activate($data, 5/*3*/);
								}
                                if(!empty($data->read_by_manager)){
                                    foreach ($mount_data as $value) {
                                        $c_date = date_create($value->time);
                                        date_sub($c_date, date_interval_create_from_date_string('1 day'));
                                        $callback_model->save(date_format($c_date, 'Y-m-d H:i'),"Уточнить готов ли клиент к этапу монтажа $value->stage_name",$data->id_client,$data->read_by_manager);
                                    $client_history_model->save($data->id_client,"Добавлен новый звонок по причине: Уточнить готов ли клиент к этапу монтажа $value->stage_name");
                                    }
                                }
                                $projects_mounts_model->save($project_id,$mount_data);
                                $service_data = $this->check_mount_for_service($mount_data);
                                $send_data['project_id'] = $project_id;
                                $send_data['client_id'] = $data->id_client;
                                $send_data['mount'] = $service_data;
                                $pr_data['id'] = $project_id;
                                if(!empty($service_data)){
                                    foreach ($include_calculation as $calc) {
                                        $result[$calc] = Gm_ceilingHelpersGm_ceiling::calculate_mount(0,$calc,null,"service");
                                        $mount_sum[$calc] =  $result[$calc]['total_gm_mounting'];
                                        foreach ($service_data as $key => $value) {
                                            Gm_ceilingHelpersGm_ceiling::create_mount_estimate_by_stage($calc,$value->mounter,$value->stage,$value->time,true);    
                                        }
                                    }
                                    $pr_data['calcs_mounting_sum'] = json_encode($mount_sum);
                                    $this->change_project_data($pr_data);
                                    Gm_ceilingHelpersGm_ceiling::notify((object)$send_data, 14);
                                    Gm_ceilingHelpersGm_ceiling::create_common_estimate_mounters($project_id,$include_calculation,"service");
                                }
                                else{
                                      $pr_data['calcs_mounting_sum'] = "";
                                }
							}
							
						}
						else if ($user->dealer_type!=2 && $project_verdict == 0 && $project_status == 3)
						{
							$client_history_model->save($data->id_client,"Отказ от договора по проекту №".$project_id."Примечание замерщика : ".$gm_calculator_note);
							if(!empty($data->read_by_manager)){
								$callback_model->save(date("Y-m-d H:i",strtotime("+30 minutes")),"Отказ от договора",$data->id_client,$data->read_by_manager);
								$client_history_model->save($data->id_client,"Добавлен новый звонок по причине: отказ от договора. Примечание замерщика :".$gm_calculator_note);
							}
                            
						}
                        $return = $model->activate($data, $project_status);
					}
					else{
						$return = $model->activate($data, 23);
						$array = [];
						foreach($include_calculation as $calc){
                            if($calc->n3){
                                Gm_ceilingHelpersGm_ceiling::create_manager_estimate(0,$calc);
                            }
							$array[] = $_SERVER["DOCUMENT_ROOT"] . "/costsheets/" . md5($calc. "managernone") . ".pdf";
						}
						Gm_ceilingHelpersGm_ceiling::create_estimate_of_consumables($project_id,0);
						$array[] = $_SERVER["DOCUMENT_ROOT"] . "/costsheets/" . md5($project_id . "consumablesnone") . ".pdf";
						$filename = "project№".$project_id. ".pdf";
						$sheets_dir = $_SERVER['DOCUMENT_ROOT'] . '/tmp/';
						Gm_ceilingHelpersGm_ceiling::save_pdf($array, $sheets_dir . $filename, "A4");
						$mailer = JFactory::getMailer();
						$config = JFactory::getConfig();
						$sender = array(
							$config->get('mailfrom'),
							$config->get('fromname')
						);

						$mailer->setSender($sender);
						$mailer->addRecipient($email);
						$body = "Запуск в производство";
						$mailer->setSubject('Запуск в производство');
						$mailer->setBody($body);
						$mailer->addAttachment($sheets_dir.$filename);
						$send = $mailer->Send();
						unlink($_SERVER['DOCUMENT_ROOT'] . "/tmp/" . $filename);					
					}
                    $dealer_info_model = $this->getModel('Dealer_info', 'Gm_ceilingModel');
                    $gm_canvases_margin = $dealer_info_model->getMargin('gm_canvases_margin',$user->dealer_id);
                    if($smeta == 0) $gm_components_margin = $dealer_info_model->getMargin('gm_components_margin',$user->dealer_id);
                    $gm_mounting_margin = $dealer_info_model->getMargin('gm_mounting_margin',$user->dealer_id);
                    $dealer_canvases_margin = $dealer_info_model->getMargin('dealer_canvases_margin',$user->dealer_id);
                    if($smeta == 0) $dealer_components_margin = $dealer_info_model->getMargin('dealer_components_margin',$user->dealer_id);
                    $dealer_mounting_margin = $dealer_info_model->getMargin('dealer_mounting_margin',$user->dealer_id);

					// Check for errors.
					if ($return === false)
					{
						$this->setMessage(JText::sprintf('Save failed: %s', $model->getError()), 'warning');
					}
					else {

						if($project_verdict == 1 && count($ignored_calculations) > 0) {
                            $project_data = $model->getData($project_id);
                            $project_data->project_sum = 0;
						    foreach ($calculations as $calculation)
						        if (in_array($calculation->id, $ignored_calculations)) {
                                    $project_data->project_sum += $calculation->dealer_components_sum;
                                    $project_data->project_sum += $calculation->dealer_canvases_sum;
                                    $project_data->project_sum += margin($calculation->mounting_sum, $dealer_mounting_margin);
                                }

							$client_id = $data->id_client_num;
							$project_data->project_status = 3;
							$project_data->gm_calculator_note = "Не вошедшие в договор №" . $data->id;
							//$project_data->project_verdict = 0;
							$old_advt = $project_data->api_phone_id; 
							$project_data->api_phone_id = 10;
							$project_data->client_id = $client_id;

							unset($project_data->id);
							$project_model = Gm_ceilingHelpersGm_ceiling::getModel('projectform');
							$refuse_id = $project_model->save(get_object_vars($project_data));

							$repeat_request = Gm_ceilingHelpersGm_ceiling::getModel('RepeatRequest');
							$repeat_request->save($refuse_id,$old_advt);

							$client_history_model->save($client_id, "Не вошедшие в договор № ".$project_id." потолки перемещены в проект №".$refuse_id);
							$calculationModel = Gm_ceilingHelpersGm_ceiling::getModel('calculation');
							foreach($ignored_calculations as $ignored_calculation){
								$calculationModel->changeProjectId($ignored_calculation, $refuse_id);
							}
						}
					}
					$calculationsModel = Gm_ceilingHelpersGm_ceiling::getModel('calculations');
					$calculations = $calculationsModel->new_getProjectItems($project_id);
					$components_data = array();
					$project_sum = 0;
					foreach($include_calculation as $calculation){                        
						if($smeta == 1) $tmp = $calculationsModel->updateComponents_sum($calculation);
						$calculations = $calculationsModel->new_getProjectItems($calculation);
						$from_db = 1;
						$save = 0;
						$ajax = 0;
						$pdf = 0;
						$print_components = 1;
						$del_flag = 0;

						foreach($calculations as $calc) {
							if($smeta == 0) $project_sum += margin($calc->components_sum, $dealer_components_margin);
							$project_sum += margin($calc->canvases_sum,  $dealer_canvases_margin);
							$project_sum += margin($calc->mounting_sum, $dealer_mounting_margin);
						}
					if($smeta == 1) $tmp = $calculationsModel->updateComponents_sum($calculation);
					} 

					// Clear the profile id from the session.
					$app->setUserState('com_gm_ceiling.edit.project.id', null);

					// Flush the data from the session.
					$app->setUserState('com_gm_ceiling.edit.project.data', null);

					// Redirect to the list screen.
					if($project_verdict == 1) {
						if( count($ignored_calculations) > 0 ) {
							$data = $model->getNewData($project_id);
							$data->refuse_id = $refuse_id;
							if($activate_by_email==0){
								Gm_ceilingHelpersGm_ceiling::notify($data, 6);
							}
							$this->setMessage("Проект сформирован! <br>  Неотмеченные потолки перемещены в копию проекта с отказом");
						} else {
						    if($project_status == 4 )  { $this->setMessage("Проект сохранен");}
						    else {
								if($activate_by_email==0){
									Gm_ceilingHelpersGm_ceiling::notify($data, 2);
								}
                               
                                $this->setMessage("Проект сформирован");
                                Gm_ceilingHelpersGm_ceiling::notify($data, 7);
                            }
						}
					} elseif($project_verdict == 0) {
                        if(in_array($project_status, [2,3,15])){
                            Gm_ceilingHelpersGm_ceiling::notify($data, 4);
                            $this->setMessage("Проект отправлен в список отказов",'error');
                        }
                        else{
                            if($project_status == 1){
                                $this->setMessage("Проект записан на замер",'success');
                            }
                            else{
                                $this->setMessage("Сохранено",'success');
                            }
                        }
                        
						
					}

					$menu = JFactory::getApplication()->getMenu();

					$item = $menu->getActive();

					}

					if($type === "gmcalculator" && $subtype === "calendar") {
						$this->setRedirect(JRoute::_('index.php?option=com_gm_ceiling&view=projects&type=gmcalculator&subtype=calendar&id='.$project_id, false));
					}
					elseif ($type === "calculator" && $subtype === "calendar") {
						if($user->dealer_type!=2)
						    if($project_status == 4 ) $this->setRedirect(JRoute::_('index.php?option=com_gm_ceiling&view=mainpage&type=dealermainpage', false));
							else $this->setRedirect(JRoute::_('index.php?option=com_gm_ceiling&view=project&type=calculator&subtype=calendar&id='.$project_id, false));
						else $this->setRedirect(JRoute::_('index.php?option=com_gm_ceiling&view=project&type=calculator&subtype=project&id='.$project_id, false));
					}
					else {
						$this->setRedirect(JRoute::_('index.php?option=com_gm_ceiling&task=mainpage', false));
					}
					if(!$project_verdict) $this->setRedirect(JRoute::_('index.php?option=com_gm_ceiling&task=mainpage', false));
			}
		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());

        }
	}
	public function activate_by_email(){
		try{
			$app = JFactory::getApplication();
			$jinput = JFactory::getApplication()->input;
			$project_id = $jinput->get('id', null, 'INT');
			$email = $jinput->get('email', null, 'STRING');
			$calculations_model = self::getModel('calculations');
			$calculations = $calculations_model->new_getProjectItems($project_id);
			$array = [];
			foreach($calculations as $calc){
				Gm_ceilingHelpersGm_ceiling::create_manager_estimate(0,$calc->id);
				$array[] = $_SERVER["DOCUMENT_ROOT"] . "/costsheets/" . md5($calc->id . "managernone") . ".pdf";
			}
			print_r($array);
			Gm_ceilingHelpersGm_ceiling::create_estimate_of_consumables($project_id,0);
			$array[] = $_SERVER["DOCUMENT_ROOT"] . "/costsheets/" . md5($project_id . "consumablesnone") . ".pdf";
			$filename = "project№".$project_id. ".pdf";
			$sheets_dir = $_SERVER['DOCUMENT_ROOT'] . '/tmp/';
			Gm_ceilingHelpersGm_ceiling::save_pdf($array, $sheets_dir . $filename, "A4");
			$mailer = JFactory::getMailer();
            $config = JFactory::getConfig();
            $sender = array(
                $config->get('mailfrom'),
                $config->get('fromname')
            );

            $mailer->setSender($sender);
            $mailer->addRecipient($email);
            $body = "Запуск в производство";
            $mailer->setSubject('Запуск в производство');
            $mailer->setBody($body);
            $mailer->addAttachment($sheets_dir.$filename);
            $send = $mailer->Send();
            unlink($_SERVER['DOCUMENT_ROOT'] . "/tmp/" . $filename);
            die($send);
		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());

        }
	}
	public function approve()
	{
		try
		{
			$app = JFactory::getApplication();
			$user = JFactory::getUser();
			$model = $this->getModel('Project', 'Gm_ceilingModel');
			$jinput = JFactory::getApplication()->input;
			$project_id = $jinput->get('jform[id]', '0', 'INT');
            $mount_data = json_decode($jinput->get('mount','','STRING'));
			$get_data = JFactory::getApplication()->input->get('jform', array(), 'array');
            $history_model = Gm_ceilingHelpersGm_ceiling::getModel('Client_history');
            $projects_mounts_model = Gm_ceilingHelpersGm_ceiling::getModel('Projects_mounts');
			$data = $model->getData($get_data['id']);
            $callback_model = Gm_ceilingHelpersGm_ceiling::getModel('Callback');
			$type = $jinput->get('type', '', 'STRING');
			$subtype = $jinput->get('subtype', '', 'STRING');

			$data->gm_chief_note = $get_data['gm_chief_note'];
            $data->dealer_chief_note = $get_data['dealer_chief_note'];
            //$mount_data = json_decode($get_data['mount']);

			// замеры
			$data->project_calculation_date = $get_data['project_new_calc_date'];
			$old_date_gauger = $jinput->get('jform_project_calculation_date_old', '0000-00-00 00:00:00', 'DATE');
			$data->old_date_gauger = $old_date_gauger;
			$old_gauger = $jinput->get('jform_project_gauger_old','0','INT');
			$data->old_gauger = $old_gauger;
			if (!empty($get_data['project_gauger'])) {
				$data->project_calculator = $get_data['project_gauger'];
			}
			if ($data->project_calculation_date != $old_date_gauger) { // если изменилась только дата
				Gm_ceilingHelpersGm_ceiling::notify($data, 10);
			}
			if ($data->project_calculator != $old_gauger) { // если изменился замерщик
				Gm_ceilingHelpersGm_ceiling::notify($data, 0);
				Gm_ceilingHelpersGm_ceiling::notify($data, 11);
			}
			if ($data->project_calculation_date != $old_date_gauger) {
                $text = "У проекта №$data->id дата замера перенесена на $data->project_calculation_date ";
				$history_model->save($data->id_client,$text);
			}
			if ($data->project_calculator != $old_gauger) {
                $gauger_name = JFactory::getUser($data->project_calculator)->name;
                $text = "У проекта №$data->id был изменен замерщик на ".$gauger_name;
				$history_model->save($data->id_client,$text);
			}

            $old_mount_data = $projects_mounts_model->getData($data->id);
            //throw new Exception(print_r($old_mount_data,true));
            $common_mount = array_uintersect($old_mount_data, $mount_data, function ($e1, $e2) {
                return $e1 == $e2;
            });
            $mount_diff = array_udiff($mount_data,$common_mount,
                  function ($obj_a, $obj_b) {
                    if($obj_a != $obj_b){
                        return 1;
                    }
                    else{
                         return 0;
                    }
                  }
                );
			// монтажи
            $change_data  = [];// Массив для уведомления ГММенеджера
            $change_data['id'] = $data->id;
            $change_data['stages'] = [];
            $mount_types = $projects_mounts_model->get_mount_types();
			if (!empty($mount_diff)) {
                foreach ($mount_diff as $value) {
                    foreach ($old_mount_data as $old_value) {
                       if($old_value->stage == $value->stage){
                            if($old_value->mounter == $value->mounter && $old_value->time != $value->time){
                                $change_stage = (object)[];
                                $change_stage->name = $mount_types[$value->stage];
                                $change_stage->old_date = $old_value->time;
                                $change_stage->new_date = $value->time;
                                $change_data['stages'][] = $change_stage;
                                Gm_ceilingHelpersGm_ceiling::notify($data, 8);
                                $text = "У проекта №$data->id дата этапа монтажа '".$mount_types[$value->stage]."' перенесена на $value->time";
                                $history_model->save($data->id_client,$text);
                                $callback_model->updateCallbackDate($value->time,$data->id_client);
                            }
                            if($old_value->mounter != $value->mounter){
                                Gm_ceilingHelpersGm_ceiling::notify($data, 7);
                                Gm_ceilingHelpersGm_ceiling::notify($data, 9);
                                $mounter_name = JFactory::getUser($value->mounter)->name;
                                $text = "У проекта №$data->id монтажная бригада этапа '".$mount_types[$value->stage]."' заменена на $mounter_name";
                                $history_model->save($data->id_client,$text);
                            }
                       }
                    }
                }
			}
            if($data->project_status == 5 || $data->project_status == 10){
                Gm_ceilingHelpersGm_ceiling::notify($change_data, 16);
            }
			// оповещение менеджерам
			if ($user->dealer_type == 1 && $data->project_mounting_date != $data->old_date) {
				Gm_ceilingHelpersGm_ceiling::notify($data, 12);
			}
            $projects_mounts_model->save($data->id,$mount_data);
            $return = $model->approve($data);
            
			if ($return === false) {
				$this->setMessage(JText::sprintf('Save failed: %s', $model->getError()), 'warning');
			} else {
				$this->setMessage("Данные успешно изменены!");
			}

			// редирект
			if ($data->project_status == 1 ) {
				if($type === "gmchief") {
					$this->setRedirect(JRoute::_('index.php?option=com_gm_ceiling&view=projects&type=gmchief&subtype=gaugings', false));
				} elseif ($type === "chief") {
					$this->setRedirect(JRoute::_('index.php?option=com_gm_ceiling&view=projects&type=chief&subtype=gaugings', false));
				}
			} else {
				if($type === "gmchief") {
					$this->setRedirect(JRoute::_('index.php?option=com_gm_ceiling&view=projects&type=gmchief', false));
				} else {
					$this->setRedirect(JRoute::_('index.php?option=com_gm_ceiling&view=mainpage&type=chiefmainpage', false));
				}
			}
			
		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());

        }

	}

    public function approvemanager()
    {
		try
        {
			$jinput = JFactory::getApplication()->input;
            $id = $jinput->get('id', '0', 'INT');
            $ready_date = $jinput->get('ready_date','','STRING');
            $time = $jinput->get('time','','STRING');
            $ready_date_time = $ready_date.' '.$time;
            $quickly = $jinput->get('quick',0,'INT');
			$model = Gm_ceilingHelpersGm_ceiling::getModel('Project');
            $projects_mounts_model = $this->getModel('projects_mounts','Gm_ceilingModel'); 
			$data = $model->approvemanager($id,$ready_date_time,$quickly);
			$res = $model->getNewData($id);

            $mount_data = json_decode($jinput->get('mount','',"STRING"));


            $calc_model = Gm_ceilingHelpersGm_ceiling::getModel('calculations');
            $calculations = $calc_model->new_getProjectItems($id);
			$material_sum = 0;
			$client_model = Gm_ceilingHelpersGm_ceiling::getModel('client');
			$client  = $client_model->getClientById($res->client_id);
			$dealer_id = $client->dealer_id;
			
            foreach ($calculations as $calculation) {
                $material_sum += $calculation->components_sum + $calculation->canvases_sum;
            }
            if(empty($material_sum)) $material_sum = 0;
            else $material_sum = -($material_sum);
            $recoil_map_model =Gm_ceilingHelpersGm_ceiling::getModel('recoil_map_project');
            if($dealer_id != 1 && $dealer_id != 2)
                $recoil_map_model->save($dealer_id, $id, $material_sum);
			if ($data === false)
			{
				$this->setMessage(JText::sprintf('Save failed: %s', $model->getError()), 'warning');
			} else {
				$this->setMessage("Проект ожидает монтажа");
                if(!empty($mount_data)){
                    $projects_mounts_model->save($id,$mount_data);
                }
				Gm_ceilingHelpersGm_ceiling::notify($data, 1);
				Gm_ceilingHelpersGm_ceiling::notify($data, 13);
			}
			$this->setRedirect(JRoute::_('index.php?option=com_gm_ceiling&view=projects&type=gmmanager', false));
		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());

        }
	}
	
	public function GetNameGauger()
	{
		try
		{
			$id = $_POST["id"];
			$model = $this->getModel('Project', 'Gm_ceilingModel');
			$return = $model->GetNameGauger($id);

			die(json_encode($return));
		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());

        }
	}

	public function return()
    {
		try
        {
			$jinput = JFactory::getApplication()->input;
			$id = $jinput->get('id', '0', 'INT');
			$model = $this->getModel('Project', 'Gm_ceilingModel');
            $data = $model->return($id);
            $model_projectshistory = Gm_ceilingHelpersGm_ceiling::getModel('projectshistory');
            $model_projectshistory->delete($id);
            $model_projectshistory->save($id,1);
            $model_recoil_map_project = Gm_ceilingHelpersGm_ceiling::getModel('recoil_map_project');
            $model_recoil_map_project->deleteByProjId($id);
			if ($data === false)
			{
				$this->setMessage(JText::sprintf('Save failed: %s', $model->getError()), 'warning');
			} else {
				$this->setMessage("Проект вернулся на стадию замера!");
				//Gm_ceilingHelpersGm_ceiling::notify($data, 1);
			}
			$this->setRedirect(JRoute::_('index.php?option=com_gm_ceiling&view=mainpage&type=gmmanagermainpage', false));
		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());

        }
	}
	public function update_transport(){
		try{
			$jinput = JFactory::getApplication()->input;
			$project_model = self::getModel('Project');
			$project_id = $jinput->get('id', '', 'INT');
			$transport_type = $jinput->get('transport', '', 'STRING'); 
			$distance = $jinput->get('distance', '', 'STRING');
			$distance_col = $jinput->get('distance_col', '', 'STRING');
            $all = $jinput->get('all', '', 'INT');
			if(!empty($project_id)){
				$project_model->update_transport($project_id,$transport_type,$distance,$distance_col);
				if (empty($all)) $sum = Gm_ceilingHelpersGm_ceiling::calculate_transport($project_id);
				else $sum = Gm_ceilingHelpersGm_ceiling::calculate_transport($project_id);

                Gm_ceilingHelpersGm_ceiling::create_client_common_estimate($project_id);
                Gm_ceilingHelpersGm_ceiling::create_common_estimate_mounters($project_id);

				die(json_encode($sum));
			}
			else{
				throw new Exception("Empty project_id!");
			}
		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());

        }
	}
	public function transport()
	{
		try
		{
			$jinput = JFactory::getApplication()->input;
			$data->id = $jinput->get('id', '0', 'INT');
			
			$data->transport = $jinput->get('transport', '0', 'INT');
			$data->distance = $jinput->get('distance', '', 'FLOAT');
			$distance_col = $jinput->get('distance_col', '', 'INT');
			$distance_col_1 = $jinput->get('distance_col_1', '', 'INT');


			if($data->transport == '1' ) $data->distance_col = $distance_col_1;
				elseif($data->transport == '2') $data->distance_col = $distance_col;
					else  $data->distance_col = 0;

			$model = $this->getModel('Project', 'Gm_ceilingModel');
			$res = $model->transport($data);
			$dealer_info_model = $this->getModel('Dealer_info', 'Gm_ceilingModel');
			$margin = $dealer_info_model->getMargin('dealer_mounting_margin',$res->user_id);

			if($res) {
				if($data->transport == 1) $transport_sum = $this->margin1($res->transport * $data->distance_col, $margin);
				elseif($data->transport == 2) {
					$transport_sum = $this->margin1($res->distance * $data->distance_col * $data->distance, $margin);
					if($transport_sum < $this->margin1($res->transport, $margin))
					$transport_sum = $this->margin1($res->transport, $margin);
				}
				else $transport_sum = 0;
			}
			$discount = $model->getDiscount($data->id);
			$min = 100;
			foreach($discount as $d) {
				if($d->discount < $min) $min = $d->discount;
			}
			$transport_sum = $transport_sum * ((100 - $min)/100);
			//throw new Exception($transport_sum, 1);
			$transport_sum = json_encode($transport_sum);
			die($transport_sum);
		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());

        }
	}
	function margin1($value, $margin)
	{
		try{
			$return = ($value * 100 / (100 - $margin));
			return $return;
		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());

        }
	}

    public function save_mount()
    {
		try{
			$jinput = JFactory::getApplication()->input;
			$id = $jinput->get('id', '0', 'INT');
			$type = $jinput->get('type', '', 'STRING');
			$jform = $jinput->get('jform',array(), 'ARRAY');
			//print_r($id."                ".$type); exit;
			$model = $this->getModel('Project', 'Gm_ceilingModel');
			$return = $model->activate_mount($id, $jform);
			//throw new Exception($id);

			if ($return === false)
			{
				$this->setMessage(JText::sprintf('Save failed: %s', $model->getError()), 'warning');
			} else {
				$this->setMessage("Данные успешно изменены");
			}
			if($type=="gmchief")
			{
				$this->setRedirect(JRoute::_('index.php?option=com_gm_ceiling&view=project&type=gmchief&id='.$id, false));
			}
			else if ($type=="chief")
			{
				$this->setRedirect(JRoute::_('index.php?option=com_gm_ceiling&view=project&type=chief&id='.$id, false));
			}
			}
			catch(Exception $e)
			{
				Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());

			}
		}
	public function done()
	{
		try
		{		
			// Initialise variables.
			$app = JFactory::getApplication();

			// Checking if the user can remove object
			$user = JFactory::getUser();

			$jinput = JFactory::getApplication()->input;
			$project_id = $jinput->get('project_id', '0', 'INT');
            $check = $jinput->get('check', '0', 'INT'); // 1 - монтаж выполнен, 0 - недовыполнен
			$new_value = $jinput->get('new_value', '0', 'FLOAT');
			$mouting_sum = $jinput->get('mouting_sum', '0', 'FLOAT');
            $mouting_sum_itog = $jinput->get('mouting_sum_itog', '0', 'FLOAT'); // сумма, которую получат монтажники сначала без выполненной работы
			$material_sum = $jinput->get('material_sum', '0', 'FLOAT');
			//print_r("project_id - $project_id ||| check - $check ||| new_value - $new_value ||| mouting_sum - $mouting_sum ||| mouting_sum_itog - $mouting_sum_itog ||| material_sum - $material_sum"); exit;
			$map_model = $this->getModel('recoil_map_project', 'Gm_ceilingModel');
			$sum = $new_value*0.1;
			if($check == 1) $result = "Договор закрыт!";
			if($check == 0) $result = "Договор пока не закрыт из-за недовыполненного монтажа!";
            //throw new Exception("1");
            if($user->dealer_type != 1) {
                if($map_model->exist($project_id)==1){
                    $map_model->updateSum($project_id,$sum);
                    $recoil_id = $map_model->getRecoilId($project_id)->recoil_id;
                    $model_recoil =  $this->getModel('recoil', 'Gm_ceilingModel');
                    $recoil = $model_recoil->getRecoilInfo($recoil_id);
                    $result = "Заказ от откатника: ".$recoil->name.";Телефон: ".$recoil->username;

                }
            }

            // если проект был недовыолнен, а сейчас выполнен, то плюсовать сумму ранее записанную в переменнные  new
			$model = $this->getModel('Project', 'Gm_ceilingModel');
			$table = $model->getTable();
			$table->load($project_id);
			$data = $table;
			$data->new_project_sum = $new_value;
			$check_done = $model->new_getProjectItems($project_id);
			if($check_done->check_mount_done == 0 && $check == 1) {
				$new_value = $check_done->new_project_sum + $new_value;
				//$mouting_sum_itog = $mouting_sum + $check_done->new_project_mounting;
				$mouting_sum = $mouting_sum + $check_done->new_project_mounting;
				$material_sum = $material_sum + $check_done->new_material_sum;
			}
			// Attempt to save the data.
            $return = $model->done($project_id, $new_value, $mouting_sum, $material_sum, $check, $mouting_sum_itog );
			//Gm_ceilingHelpersGm_ceiling::notify($data, 2);
			Gm_ceilingHelpersGm_ceiling::notify($data, 3);
			// Check for errors.


			if ($return === false)
			{
                throw new Exception('Save failed:'. $model->getError());
                //$result = 'Save failed:'. $model->getError();
				//echo JText::sprintf('Save failed: %s', $model->getError());
			}
			 

			die($result);
		}
		catch(Exception $e)
		{
			Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());

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
	public function publish()
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
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());

        }
	}

	/**
	 * Remove data
	 *
	 * @return void
	 *
	 * @throws Exception
	 */
	public function remove()
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
					/*// Check in the profile.
					if ($return)
					{
						$model->checkin($return);
					}*/

					// Clear the profile id from the session.
					$app->setUserState('com_gm_ceiling.edit.project.id', null);

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
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());

        }
	}
    
    public function refusing()
	{
		try
		{	
			// Initialise variables.
			$app = JFactory::getApplication();

			// Checking if the user can remove object
			$user = JFactory::getUser();
			$model = $this->getModel('Project', 'Gm_ceilingModel');
			$callback_model = $this->getModel('callback', 'Gm_ceilingModel');
			$client_model = $this->getModel('client', 'Gm_ceilingModel');
			$jinput = JFactory::getApplication()->input;
			//$project_id = $jinput->get('jform[id]', '0', 'INT');
			$project_id = $jinput->get('id', '', 'int');
			$date = $jinput->get('date', '0000-00-00', 'DATE');
			$time = $jinput->get('time', '00:00', 'string');
			$comment = $jinput->get('comment', '', 'string');

			if($comment == "") $comment= "Отказ от производства";
			$data_time = $date." ".$time;
			$client_id = $model->refusing($project_id);
			$manager = $client_model->getClientById($client_id);
			$return = $callback_model->save($data_time, $comment, $client_id ,$manager->manager_id);
			
			if ($return)
			{
				$this->setMessage("Проект отправлен в отказы от производства!");
				$this->setRedirect(JRoute::_('index.php?option=com_gm_ceiling&view=mainpage&type=gmmanagermainpage', false));
			} else {
				$this->setMessage("Не удалось отправить проект в отказы от производства!");
			}
		}
		catch(Exception $e)
		{
			Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
		}
	}

    public function updateProjectStatus()
    {
        try
        {   
            $jinput = JFactory::getApplication()->input;
            $project_id = $jinput->get('project_id', 0, 'int');
            $status = $jinput->get('status', 0, 'int');

            $model = $this->getModel('Project', 'Gm_ceilingModel');
            $result = $model->newStatus($project_id, $status);
            die($result);
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function update_ready_time()
    {
        try
        {   
            $jinput = JFactory::getApplication()->input;
            $data['id'] = $jinput->get('project_id', null, 'int');
            $data['ready_time'] = $jinput->get('ready_time', null, 'string');

            $model = $this->getModel('Project', 'Gm_ceilingModel');
            $result = $model->save($data);
            die($result);
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function delete_by_user(){
        try{
            $jinput = JFactory::getApplication()->input;
            $data['id'] = $jinput->get('project_id', null, 'int');
            $data['deleted_by_user'] = 1;

            $model = $this->getModel('Project', 'Gm_ceilingModel');
            $result = $model->save($data);
            die($result);
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());

        }
    }

    public function save_advt(){
        try {
            $jinput = JFactory::getApplication()->input;
            $data['id'] = $jinput->get('project_id', null, 'int');
            $api_phone_id = $jinput->get('api_phone_id', null, 'int');
            $client_id = $jinput->get('client_id', null, 'int');

            $model_project = Gm_ceilingHelpersGm_ceiling::getModel('Project', 'Gm_ceilingModel');
            $model_repeat = Gm_ceilingHelpersGm_ceiling::getModel('repeatrequest', 'Gm_ceilingModel');
            $model_api_phones = Gm_ceilingHelpersGm_ceiling::getModel('api_phones');

            $user = JFactory::getUser();
            $reklama = $model_api_phones->getDataById($api_phone_id);

            if ($user->dealer_id != $reklama->dealer_id) {
                throw new Exception('403 forbidden');
            }
            $project = $model_project->getData($data['id']);
            if($project->api_phone_id != 10){
                $data['api_phone_id'] = $api_phone_id;
                 $result = $model_project->save($data);
            }
            else{                
                  $model_repeat->update($data['id'],$api_phone_id);
            }           
            die(json_encode($result));
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());

        }
    }

    function save_mount_data($project_id,$data = null){
        try{
            $jinput = JFactory::getApplication()->input;
            if(empty($project_id)){
                $project_id = $jinput->getInt('id');
            }
            if(!empty($project_id)){
                if(empty($data)){
                    $data = $jinput->get('mount_data','','STRING');
                }
                if(!empty($data)){
                    $data = json_decode($data);
                    $mounts_model = Gm_ceilingHelpersGm_ceiling::getModel('Projects_mounts');
                    $result = $mounts_model->save($project_id,$data);
                    die(json_encode($result));
                }
                else{
                    throw new Exception("Empty mounting data!");     
                }
            }
            else {
                throw new Exception("Empty project_id!");
            }

        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());

        }
    }

    function change_address(){
        try{
            $jinput = JFactory::getApplication()->input;
            $address = $jinput->get('address','',"STRING");
            $id = $jinput->get('id',null,"INT");
            if(!empty($id)){
                $model = Gm_ceilingHelpersGm_ceiling::getModel('Project');
                $result = $model->update_address($id,$address);
                die(json_encode($result));
            }
            else {
                throw new Exception("Empty project id");
                
            }
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());

        }
    }

    function change_project_data($data = null){
        try{
            $jinput = JFactory::getApplication()->input;
            if(empty($data)){
                $ajax = 1;
                $data = json_decode($jinput->get('new_data', '', 'STRING'));
                $data = get_object_vars($data);
                foreach ($data as $key => $value) {
                    if(empty($value)){
                        unset($data[$key]);
                    }
                }
            }
            $model = $this->getModel('Project', 'Gm_ceilingModel');
            $result = $model->save($data);
            if($ajax){
                die(json_encode($result));
            }
            else{
                return true;
            }
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());

        }
    }

    function check_mount_for_service($mount_data){
        try{
            $mount = [];
            if(!empty($mount_data)){
                foreach ($mount_data as $value) {
                    $groups = JFactory::getUser($value->mounter)->groups;
                    if(in_array(26, $groups) && !in_array($value,$mount)){
                        array_push($mount, $value);
                    }
                }
            }
            return $mount;
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    function calcServiceMount(){
        try{ 
            $jinput = JFactory::getApplication()->input;
            $project_id = $jinput->get("project_id",null,"INT");
            $include_calculations = $jinput->get("calcs",array(),"ARRAY");
            $mount_data = json_decode($jinput->get("mount","","STRING"));
            $service_mount = $this->check_mount_for_service($mount_data);
            if(empty($service_mount)){
                foreach ($include_calculations as $calc) {
                    $result[$calc] = Gm_ceilingHelpersGm_ceiling::calculate_mount(0,$calc);
                }
                die(json_encode($result));
            }
            else{
                $mount_sum = [];
                foreach ($include_calculations as $calc) {
                    $result[$calc] = Gm_ceilingHelpersGm_ceiling::calculate_mount(0,$calc,null,"service");
                    $transport = Gm_ceilingHelpersGm_ceiling::calculate_transport($project_id,"service")['mounter_sum'];
                    $mount_sum[$calc] =  $result[$calc]['total_gm_mounting'];
                    $result['transport'] = $transport;        
                }
                die(json_encode($result));
            }

        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }   
    }

    function saveService(){
        try{
            $jinput = JFactory::getApplication()->input;
            $project_id = $jinput->get('project_id',null,'INT');
            $dealer_id = $jinput->get('dealer_id',null,'INT');
            $dealer = JFactory::getUser($dealer_id);
            $email = $dealer->email;
            $projects_mounts_model = $this->getModel('projects_mounts','Gm_ceilingModel');
            $model = $this->getModel('Project', 'Gm_ceilingModel');
            $mount_data = json_decode($jinput->get('mount','',"STRING"));
            if(!empty($mount_data) && !empty($project_id)){
                $mount_types = $projects_mounts_model->get_mount_types();
                foreach ($mount_data as $value) {
                    $value->stage_name = $mount_types[$value->stage];
                }
                $return = $projects_mounts_model->save($project_id,$mount_data);
            }
            if($return){
                $data = $model->getData($project_id);
                $model_for_mail = Gm_ceilingHelpersGm_ceiling::getModel('calculations');       
                // перимерт и зп бригаде
                $project_info_for_mail = $model_for_mail->InfoForMail($project_id);
                $perimeter = 0;
                $salary = 0;
                foreach ($project_info_for_mail as $value) {
                    $perimeter += $value->n5;
                    $salary += $value->mounting_sum;
                }
                $data->perimeter = $perimeter;
                $data->salary = $salary;
                Gm_ceilingHelpersGm_ceiling::notify($data,7);
                $appr_data['project_id'] = $project_id;
                $appr_data['dealer_id'] = $dealer_id;
                $appr_data['mount'] = $mount_data;
                Gm_ceilingHelpersGm_ceiling::notify((object)$appr_data,15);
                $this->setMessage("Монтаж назначен!");  
            }
            else{
                $this->setMessage("Произошла ошибка");
            }
             $this->setRedirect(JRoute::_('index.php?option=com_gm_ceiling&view=project&type=gmchief&subtype=service&id='.$project_id, false));  
            
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    function removeService(){
        try{
            $jinput = JFactory::getApplication()->input;
            $project_id = $jinput->get('project_id',null,'INT');
            $project_model = Gm_ceilingHelpersGm_ceiling::getModel('project');
            $projects_mounts_model = $this->getModel('projects_mounts','Gm_ceilingModel');
            $data['id'] = $project_id;
            $data['calcs_mounting_sum'] = '';
            $project_model->save($data);
            $projects_mounts_model->delete($project_id);
            die(json_encode(true));
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    function saveProjectMaterials(){
	    try{
            $jinput = JFactory::getApplication()->input;
            $project_id = $jinput->get('project_id',null,'INT');
            $components = $jinput->get('components',array(),'ARRAY');
            $canvases = $jinput->get('canvases',array(),'ARRAY');
            $components_sum = $jinput->get('components_sum',null,'INT');
            /*$data['project_id'] = $project_id;*/
            $data['canvases'] = $canvases;
            $data['components'] = $components;
            $data['components_sum'] = $components_sum;
            $materials_model = Gm_ceilingHelpersGm_ceiling::getModel('projects_materials');
            $result = $materials_model->save($project_id,json_encode($data,JSON_UNESCAPED_UNICODE));
            die(json_encode($result));

        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    function getProjectMaterials(){
	    try{
            $jinput = JFactory::getApplication()->input;
            $project_id = $jinput->get('project_id',null,'INT');
            $project_model = Gm_ceilingHelpersGm_ceiling::getModel('project');
            $data = json_encode($project_model->getMaterialsForEstimate($project_id));
            die(json_encode($data));
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    function saveStageMount(){
	    try{
            $jinput = JFactory::getApplication()->input;
            $project_id = $jinput->getInt('id');
            if(!empty($project_id)){
                if(empty($data)){
                    $data = $jinput->get('mount_data','','STRING');
                }
                if(!empty($data)){
                    $data = json_decode($data);
                    $mounts_model = Gm_ceilingHelpersGm_ceiling::getModel('Projects_mounts');
                    $result = $mounts_model->saveOrUpdateStage($project_id,$data);
                    die(json_encode($result));
                }
                else{
                    throw new Exception("Empty mounting data!");
                }
            }
            else {
                throw new Exception("Empty project_id!");
            }
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }
}
?>

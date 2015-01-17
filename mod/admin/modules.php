<?

//список модулей из репозитория
if (!empty($_GET['getModsListFromRepo'])) {
    $install = new InstallModule();
    $install->getHTMLModsListFromRepo($_GET['getModsListFromRepo']);
    exit();
}

//скачивание архива модуля
if (!empty($_GET['download_mod'])) {
    $install = new InstallModule();
    $install->downloadZip($_GET['download_mod']);
}

/* скачивание архива шаблона */
if (!empty($_GET['download_mod_tpl'])) {
    $install = new InstallModule();
    $install->downloadModTemplate($_GET['download_mod_tpl']);
}

$this->printJs("core2/mod/admin/mod.js");

$tab = new tabs('mod'); 
$tab->addTab("Установленные модули",    $app, 170);
$tab->addTab("Доступные модули",	    $app, 130);
$tab->addTab("Шаблоны модулей",	        $app, 130);
$tab->beginContainer("Модули");

$sid = session_id();
	if ($tab->activeTab == 1) {

        /* Обновление файлов модуля */
        if (!empty($_POST['refreshFilesModule'])) {
            $install = new InstallModule();
            echo $install->mRefreshFiles($_POST['refreshFilesModule'], $_POST['v']);
            exit();
        }

        //Деинсталяция модуля
		if (isset($_POST['uninstall'])) {
            $install = new InstallModule();
            echo $install->mUninstall($_POST['uninstall']);
            exit();
		}

		if (isset($_GET['edit']) && $_GET['edit'] != '') {	
			$edit = new editTable('mod'); 
			$selected_dep = array();
			$refid = (int)$_GET['edit'];
			$dep_list = "SELECT module_id, m_name FROM core_modules WHERE m_id != '$refid'";
			$field = '';
			if ($refid > 0) {
				$res = $this->dataModules->find($refid)->current()->dependencies;
				$dep = array();
				if ($res) {
					$dep = base64_decode($res);
					$dep = @unserialize($dep);
					
					if (is_array($dep)) {
						$selected_dep = array();
						foreach ($dep as $variable) {
							$selected_dep[] = $variable['module_id'];
						}
					} else {
						$dep = array();
					}
				}
				
				$res = $this->db->fetchAll($dep_list);
				$availableModules = array();
				foreach ($res as $variable) {
					$availableModules[] = $variable['module_id'];
				}
				
				$dep_list = array();
				$dep = array_merge($dep, $res);
				foreach ($dep as $variable) {
					$edit->addParams("dep_" . $variable['module_id'], $variable['m_name']);
					if (!in_array($variable['module_id'], $availableModules)) {
						$variable['m_name'] .= " <font color=\"red\">(deleted)</font>";
					}
					$dep_list[$variable['module_id']] = $variable['m_name'];
				}
				
				
				$selected_dep = implode(",", $selected_dep);
				$field = "'$selected_dep' AS ";
				
				
			}

			$edit->SQL  = "SELECT  m_id,
								   m_name,
								   module_id,
								   is_system,
								   is_public,
								   $field dependencies,
								   seq,
								   access_default,
								   access_add								   
							  FROM core_modules
							 WHERE m_id = '$refid'";
			$edit->addControl("Модуль:", "TEXT", "maxlength=\"60\" size=\"60\"", "", "", true);
			if ($_GET['edit'] > 0) {
				$edit->addControl("Идентификатор:", "PROTECTED");
			} else {
				$edit->addControl("Идентификатор:", "TEXT", "maxlength=\"20\"", " маленикие латинские буквы или цифры", "", true);
			}
			$edit->selectSQL[] = array('Y' => 'да', 'N' => 'нет'); 
			$edit->addControl("Системный:", "RADIO", "", "", "N");
			$edit->selectSQL[] = array('Y' => 'да', 'N' => 'нет'); 
			$edit->addControl("Отображаемый:", "RADIO", "", "", "N");
			$edit->selectSQL[] = $dep_list; 			
			$edit->addControl("Зависит от модулей:", "CHECKBOX", "", "", $selected_dep);
			$seq = '';
			if ($refid == 0) {
				$seq = $this->db->fetchOne("SELECT MAX(seq) + 5 FROM core_modules LIMIT 1");
			}
			$edit->addControl("Позиция в меню:", "NUMBER", "size=\"2\"", "", $seq);
			$access_default 	= array();
			$custom_access 		= '';			
			if ($refid) {
				$access_default = unserialize(base64_decode($module->access_default));
				$access_add 	= unserialize(base64_decode($module->access_add));
				if (is_array($access_add) && count($access_add)) {
					foreach ($access_add as $key => $value) {
						$id = uniqid('', true);
						$custom_access .= '<input type="text" class="input" name="addRules[' . $id . ']" value="' . $key . '"/>'.
						'<input type="checkbox" onchange="checkToAll(this)" id="access_' . $id . '_all" name="value_all[' . $id . ']" value="all" ' . ($value == 'all' ? 'checked="checked"' : '') . '/><label>Все</label>'.
						'<input type="checkbox" name="value_owner[' . $id . ']" id="access_' . $id . '_owner" value="owner" ' . (($value == 'all' || $value == 'owner') ? ' checked="checked"' : '') . ($value == 'all' ? ' disabled="disabled"' : '') . '/><label>Владелец</label><br>';
					}
				}
				
			}
			$checked = 'checked="checked"';
			$disabled = 'disabled="disabled"';
			
			$tpl = new Templater();
			$tpl->loadTemplate('core2/mod/admin/html/access_default.tpl');
			$tpl->assign(array(
				'{preff}' => '',
			
				'{access}' => (!empty($access_default['access']) ? $checked : ''),
				
				'{list_all}' => (!empty($access_default['list_all']) ? $checked : ''),
				'{list_all_list_owner}' => (!empty($access_default['list_all']) || !empty($access_default['list_owner']) ? $checked : ''),
				'{list_all_disabled}' => (!empty($access_default['list_all']) ? $disabled : ''),
				
				'{read_all}' => (!empty($access_default['read_all']) ? $checked : ''),
				'{read_all_read_owner}' => (!empty($access_default['read_all']) || !empty($access_default['read_owner']) ?$checked : ''),
				'{read_all_disabled}' => (!empty($access_default['read_all']) ? $disabled : ''),

				'{edit_all}' => (!empty($access_default['edit_all']) ? $checked : ''),
				'{edit_all_edit_owner}' => (!empty($access_default['edit_all']) || !empty($access_default['edit_owner']) ?$checked : ''),
				'{edit_all_disabled}' => (!empty($access_default['edit_all']) ? $disabled : ''),
			
				'{delete_all}' => (!empty($access_default['delete_all']) ? $checked : ''),
				'{delete_all_delete_owner}' => (!empty($access_default['delete_all']) || !empty($access_default['delete_owner']) ?$checked : ''),
				'{delete_all_disabled}' => (!empty($access_default['delete_all']) ? $disabled : ''),
			));			
			$access = $tpl->parse();
			$edit->addControl("Доступ по умолчанию:", "CUSTOM", $access);
			
			//CUSTOM ACCESS
			$rules = '<div id="xxx">' . $custom_access . '</div>';
			$rules .= '<div><span id="new_attr" class="newRulesModule" onclick="newRule(\'xxx\')">Новое правило</span></div>';
			$edit->addControl("Дополнительные правила доступа:", "CUSTOM", $rules);
			$edit->addButtonSwitch('visible', 	$this->db->fetchOne("SELECT 1 FROM core_modules WHERE visible = 'Y' AND m_id=? LIMIT 1", $refid));
			/*if ($is_visible) {
				if (count($list_name_modules) > 0) {
					$get_param = base64_encode(serialize($list_id_modules));					
					$str = implode(',', $list_name_modules);
					$edit->addButtonCustom("<button type=\"button\" onclick=\"if(confirm('Для деактивации модуля необходимо отключить следующие модули: ".$str." .Выполнить отключение модулей?')) {document.location='?module=admin&action=modules&loc=core&edit=".$_GET['edit']."&module_off=".$get_param."'} else {return false}\"><img id=\"switch_on\" src=\"core/img/on.png\"/></button>");									
				} else {
					$edit->addButtonCustom("<button type=\"button\" onclick=\"if(confirm('Деактивировать модуль?')) {document.location='?module=admin&action=modules&loc=core&module_off=1&edit=".$_GET['edit']."'} else {return false}\"><img id=\"switch_on\" src=\"core/img/on.png\"/></button>");									
				}
			} else {
				$edit->addButtonCustom("<button type=\"button\" onclick=\"if(confirm('Активировать модуль?')) {document.location='?module=admin&action=modules&loc=core&module_on=1&edit=".$_GET['edit']."'} else {return false}\"><img id=\"switch_on\" src=\"core/img/off.png\"/></button>");				
			}*/
			
			
			$edit->back = $app;
			$edit->addButton("Вернуться к списку Модулей", "load('$app')");
			$edit->save("xajax_saveModule(xajax.getFormValues(this.id))");
			$edit->showTable();

			//----------------------------
			// Субмодули
			//---------------------------
			$tab = new tabs("submods");
			$tab->beginContainer('Субмодули');
			if (isset($_GET['editsub']) && $_GET['editsub'] != '') {
				$edit = new editTable('submod'); 
				$edit->SQL  = "SELECT  sm_id,
									   sm_name,
									   sm_key,
									   sm_path,
									   seq,
									   visible,
									   access_default,
									   access_add
								  FROM core_submodules
								 WHERE m_id = '$refid'
								   AND sm_id = '" . $_GET['editsub'] . "'";
				$res = $this->db->fetchRow($edit->SQL);
				
				$edit->addControl("Субмодуль:", "TEXT", "maxlength=\"60\" size=\"60\"", "", "", true);
				
				if ($_GET['editsub'] > 0) {
					$edit->addControl("Идентификатор:", "PROTECTED");
				} else {
					$edit->addControl("Идентификатор:", "TEXT", "maxlength=\"20\"", " маленикие латинские буквы или цифры", "", true);
				}
				$edit->addControl("Адрес внешнего ресурса:", "TEXT");
				$seq = '1';
				if (empty($_GET['editsub'])) {
					$seq = $this->db->fetchOne("SELECT MAX(seq) + 5 FROM core_submodules WHERE m_id = ? LIMIT 1", $refid);
					if (!$seq) $seq = '1';
				}
				$edit->addControl("Позиция в меню:", "NUMBER", "size=\"2\"", "", $seq, true);
				$edit->selectSQL[] = array('Y' => 'вкл.', 'N' => 'выкл.'); 
				$edit->addControl("Статус:", "RADIO", "", "", "Y");

				$access_default 	= array();
				$custom_access 		= '';
				if ($_GET['editsub']) {
					$res = $this->dataSubModules->find($_GET['editsub'])->current();
					$access_default = unserialize(base64_decode($res->access_default));
					$access_add 	= unserialize(base64_decode($res->access_add));
					if (is_array($access_add) && count($access_add)) {
						foreach ($access_add as $key => $value) {
							$id = uniqid();
							$custom_access .= '<input type="text" class="input" name="addRules[' . $id . ']" value="' . $key . '"/>'.
							'<input type="checkbox" onchange="checkToAll(this)" id="access_' . $id . '_all" name="value_all[' . $id . ']" value="all" ' . ($value == 'all' ? 'checked="checked"' : '') . '/><label>Все</label>'.
							'<input type="checkbox" name="value_owner[' . $id . ']" id="access_' . $id . '_owner" value="owner" ' . (($value == 'all' || $value == 'owner') ? ' checked="checked"' : '') . ($value == 'all' ? ' disabled="disabled"' : '') . '/><label>Владелец</label><br>';
						}
					}
				}
				$tpl = new Templater();
				$tpl->loadTemplate('core2/mod/admin/html/access_default.tpl');
				$tpl->assign(array(
					'{preff}' => 'sub',
					
					'{access}' => (!empty($access_default['access']) ? $checked : ''),
					
					'{list_all}' => (!empty($access_default['list_all']) ? $checked : ''),
					'{list_all_list_owner}' => (!empty($access_default['list_all']) || !empty($access_default['list_owner']) ? $checked : ''),
					'{list_all_disabled}' => (!empty($access_default['list_all']) ? $disabled : ''),
					
					'{read_all}' => (!empty($access_default['read_all']) ? $checked : ''),
					'{read_all_read_owner}' => (!empty($access_default['read_all']) || !empty($access_default['read_owner']) ?  : ''),
					'{read_all_disabled}' => (!empty($access_default['read_all']) ? $disabled : ''),
	
					'{edit_all}' => (!empty($access_default['edit_all']) ? $checked : ''),
					'{edit_all_edit_owner}' => (!empty($access_default['edit_all']) || !empty($access_default['edit_owner']) ? $checked : ''),
					'{edit_all_disabled}' => (!empty($access_default['edit_all']) ? $disabled : ''),
				
					'{delete_all}' => (!empty($access_default['delete_all']) ? $checked : ''),
					'{delete_all_delete_owner}' => (!empty($access_default['delete_all']) || !empty($access_default['delete_owner']) ? $checked : ''),
					'{delete_all_disabled}' => (!empty($access_default['delete_all']) ? $disabled : ''),
				));		
					
				$access = $tpl->parse();
				$edit->addControl("Доступ по умолчанию:", "CUSTOM", $access);
				
				$rules = '<div id="xxxsub">' . $custom_access . '</div>';
				$rules .= '<div><span id="new_attr" class="newRulesSubModule" onclick="newRule(\'xxxsub\')">Новое правило</span></div>';
				$edit->addControl("Дополнительные правила доступа:", "CUSTOM", $rules);

				if (!$_GET['editsub']) $edit->setSessFormField('m_id', $refid);
				$edit->back = $app . "&edit=" . $refid;
				$edit->addButton("Отменить", "load('{$app}&edit={$refid}')");
				$edit->save("xajax_saveModuleSub(xajax.getFormValues(this.id))");
				
				$edit->showTable();
			}
			
			$list = new listTable('submod'); 
		
			$list->SQL = "SELECT sm_id,
								 sm_name,
								 sm_path,
								 seq,
								 visible
							FROM core_submodules
							WHERE m_id = '$refid'
						   ORDER BY seq, sm_name";
			$list->addColumn("Субмодуль", "", "TEXT");
			$list->addColumn("Путь", "", "TEXT");
			$list->addColumn("Позиция", "", "TEXT");
			$list->addColumn("", "1%", "STATUS_INLINE", "core_submodules.visible");
			
			$list->paintCondition	= "'TCOL_05' == 'N'";
			$list->paintColor		= "ffffee";
			
			$list->addURL 			= $app . "&edit={$refid}&editsub=0";
			$list->editURL 			= $app . "&edit={$refid}&editsub=TCOL_00";
			$list->deleteKey		= "core_submodules.sm_id";
			
			$list->showTable();
			$tab->endContainer();
		}
		else {

			$list = new listTable('mod');
		
			$list->SQL = "SELECT m_id,
								 m_name,
								 module_id,
								 version,
								 is_system,
								 is_public,
								 seq,	
								 '',							 								 
								 visible
							FROM core_modules
							WHERE m_id > 0
						   ORDER BY seq";
			$list->addColumn("Модуль", "", "TEXT");
			$list->addColumn("Идентификатор", "", "TEXT");
			$list->addColumn("Версия", "", "TEXT");
			$list->addColumn("Системный", "", "TEXT");
			$list->addColumn("Отображаемый", "", "TEXT");
			$list->addColumn("Позиция", "1%", "TEXT");
			$list->addColumn("Действие", "1%", "BLOCK", "align=\"center\"");
			$list->addColumn("", "1%", "STATUS_INLINE", "core_modules.visible");


			$data = $list->getData();
			foreach ($data as $key => $val) {
				$data[$key][7] = "<div style=\"display: inline-block;\" onclick=\"uninstallModule('" . $val[1] . "', '".$val[3]."', '".$val[0]."');\"><img src=\"core2/html/".THEME."/img/box_uninstall.png\" border=\"0\" title=\"Разинсталировать\" /></div>
				                  <div style=\"display: inline-block;\" onclick=\"modules.refreshFiles('" . $val[1] . "', '".$val[3]."', '".$val[2]."');\"><img src=\"core2/html/".THEME."/img/box_refresh.png\" border=\"0\" title=\"Перезаписать файлы\" /></div>";

			}
			$list->data = $data;
			$list->paintCondition	= "'TCOL_07' == 'N'";
			$list->paintColor		= "ffffee";
			
			$list->addURL 			= $app . "&edit=0";
			$list->editURL 			= $app . "&edit=TCOL_00";			
			$list->noCheckboxes = "yes";
			
			$list->showTable();
		}
		
	}
	
	if ($tab->activeTab == 2) { //ДОСТУПНЫЕ МОДУЛИ

        $edit = new editTable('mod_available');

		/* Добавление нового модуля */
		if (isset($_GET['add_mod']) && !$_GET['add_mod']) {

			$edit->SQL = "SELECT id,
							     name
						    FROM core_available_modules
						   WHERE id = 0";
			$edit->addControl("Файл архива(.zip)", "XFILE", "", "", "");
			$edit->classText['SAVE'] = "Загрузить";
			$edit->back              = $app . "&tab_mod=" . $tab->activeTab;
			$edit->save("xajax_saveAvailModule(xajax.getFormValues(this.id))");
			$edit->showTable();

		}
		elseif (!empty($_GET['add_mod'])) { // Инфа о модуле
				
            $edit = new editTable('modules_install');
            $edit->SQL = "SELECT 1";

            $res = $this->db->fetchRow("SELECT name, version, readme, install_info
                                        FROM core_available_modules
                                        WHERE id=?", $_GET['add_mod']);
            $title = "<h2><b>Инструкция по установке модуля</b></h2>";
            $content = $res['readme'];
            $inf = unserialize($res['install_info']);

            $modId = $inf['install']['module_id'];
            $modVers = $inf['install']['version'];
            $modName = $inf['install']['name'];
            $is_module = $this->db->fetchRow("SELECT m_id FROM core_modules WHERE module_id=? and version=?",
					array($modId, $modVers)
			);

            if (empty($content)) {
                $content = $title . "<br>Информация по установке отсутствует";
            } else {
                $content = $title . $content;
            }

            echo $content;
            if (!is_array($is_module)) {
                $tpl = new Templater("core2/html/" . THEME . "/buttons.tpl");
                $tpl->touchBlock("install_button");
                $tpl->assign("modName", $modName);
                $tpl->assign("modVers", $modVers);
                $tpl->assign("modInstall", $_GET['add_mod']);
                $edit->addButtonCustom($tpl->parse());
                $edit->readOnly = true;
            }

            $edit->addButton("Вернуться к скиску модулей", "load('$app&tab_mod=2')");

            $edit->addButtonCustom('<input class="button" type="button" value="Скачать файлы модуля" onclick="loadPDF(\'index.php?module=admin&action=modules&tab_mod=2&download_mod=' . $_GET['add_mod'] . '\')">');

            $edit->showTable();

            die;
        }


        // Инсталяция модуля
        if (!empty($_POST['install'])) {
            $install = new InstallModule();
            echo $install->mInstall($_POST['install']);
            exit();
        }


        // Инсталяция модуля из репозитория
        if (!empty($_POST['install_from_repo'])) {
            $install = new InstallModule();
            echo $install->mInstallFromRepo($_POST['repo'], $_POST['install_from_repo']);
            exit();
        }


       //список доступных одулей
		$list = new listTable('mod_available');
        $list->SQL = "SELECT 1";
        $list->addColumn("Имя модуля", "200px", "TEXT");
        $list->addColumn("Идентификатор", "200px", "TEXT");
        $list->addColumn("Описание", "", "TEXT");
        $list->addColumn("Версия", "150px", "BLOCK");
        $list->addColumn("Автор", "150px", "TEXT");
        $list->addColumn("Системный", "50px", "TEXT");
        $list->addColumn("Действие", "66", "BLOCK", 'align=center');

        $list->getData();

        $list->data = array();

        $copy_list = $this->db->fetchAll(
            "SELECT id,
                 `name`,
                 module_id,
                 descr,
                 version,
                 install_info
            FROM core_available_modules
        ORDER BY name"
        );

        $tmp = array();
        $listAllModules = $this->db->fetchAll("SELECT module_id, version FROM core_modules");
        $_GET['_page_mod_available'] = !empty($_GET['_page_mod_available']) ? (int)$_GET['_page_mod_available'] : 0;
        foreach ($copy_list as $val) {
            $arr = array();
            $arr[0] = $val['id'];
            $arr[1] = $val['name'];
            $arr[2] = $val['module_id'];
            $arr[3] = $val['descr'];
            $arr[4] = $val['version'];
            $mData = unserialize(htmlspecialchars_decode($val['install_info']));
            $arr[5] = $mData['install']['author'];
            $arr[6] = $mData['install']['module_system'] == 'Y' ? "Да" : "Нет";
            $mVersion = $arr[4];
            $mId = $mData['install']['module_id'];
            $mName = $arr[1];
            $arr[7] = "<div onclick=\"installModule('$mName', 'v$mVersion', '{$arr[0]}', {$_GET['_page_mod_available']})\"><img src=\"core2/html/".THEME."/img/box_out.png\" border=\"0\" title=\"Установить\"/></div>";
            foreach ($listAllModules as $allval) {
                if ($mId == $allval['module_id']) {
                    if ($mVersion == $allval['version']) {
                        $arr[7] = "<img src=\"core2/html/".THEME."/img/box_out_disable.png\" title=\"Уже установлен\" border=\"0\"/></a>";
                    }
                }
            }
            $tmp[$mId][$mVersion] = $arr;
        }
        //смотрим есть-ли разные версии одного мода
        //если есть, показываем последнюю, осатльные в спойлер
        $copy_list = array();
        foreach ($tmp as $module_id=>$val) {
            ksort($val);
            $max_ver = (max(array_keys($val)));
            $copy_list[$module_id] = $val[$max_ver];
            unset($val[$max_ver]);
            if (!empty($val)) {
                $copy_list[$module_id][4] .= " <a href=\"\" onclick=\"$('.mod_available_{$module_id}').toggle(); return false;\">Предыдущие версии</a><br>";
                $copy_list[$module_id][4] .= "<table width=\"100%\" class=\"mod_available_{$module_id}\" style=\"display: none;\"><tbody>";
                foreach ($val as $version=>$val) {
                    $copy_list[$module_id][4] .= "
                        <tr>
                            <td style=\"border: 0px; padding: 0px;\">{$version}</td>
                            <td style=\"border: 0px; text-align: right; padding: 0px;\">{$val[6]}</td>
                        </tr>
                    ";
                }
                $copy_list[$module_id][4] .= "</tbody></table>";
            }
        }
        //пагинация
        $per_page = 25;
        $list->recordsPerPage = $per_page;
        $list->setRecordCount(count($copy_list));
        $page = empty($_GET['_page_mod_available']) ? 1 : (int)$_GET['_page_mod_available'];
        $from = ($page - 1) * $per_page;
        $to = $page * $per_page;
        $i = 0;
        $tmp = array();
        foreach ($copy_list as $val) {
            $i++;
            if ($i > $from && $i <= $to) {
                $tmp[] = $val;
            }
        }

        $list->data 		= $tmp;
        $list->addURL 		= $app . "&add_mod=0&tab_mod=2";
        $list->editURL 		= $app . "&tab_mod=2&add_mod=TCOL_00";
        $list->deleteKey	= "core_available_modules.id";
        $list->showTable();


        //проверяем заданы ли ссылки на репозитории
        $mod_repos = $this->getSetting('repo');
        if (empty($mod_repos)){
            echo "<div class=\"im-msg-yellow\">Устоновка модулей из репозитория недоступна<br><span>Создайте дополнительный параметр 'repo' с адресами репозиториев через ';'  (адреса вида http://REPOSITORY/api/webservice?reg_apikey=YOUR_KEY)</span></div>";
        }
        $mod_repos = explode(";", $mod_repos);



        //готовим аякс запросы к репозиториям
        foreach($mod_repos as $i => $repo){
            $repo = trim($repo);
            if (!empty($repo)){

                echo "<h3>Доступные модули из репозитория - " . $repo . ":</h3>";

                echo "<div id=\"repo_{$i}\"> Подключаемся...</div>";
                echo "<script type=\"text\/javascript\" language=\"javascript\">";
                echo    "$(document).ready(function () {";
                echo        "modules.repo('{$repo}', {$i});";
                echo    "});";
                echo "</script>";
                echo "<br><br><br>";
            }
        }

	}
	
	if ($tab->activeTab == 3) {

		if (isset($_GET['file_mod']) && $_GET['file_mod'] != ""){
			$readme = "core2/mod_tpl/".$_GET['file_mod']."/Readme.txt";
			$file = "<h2><b>Краткое описание шаблона ".$_GET['file_mod']."</b></h2>";
			
			if (file_exists($readme))
				$handle = fopen ($readme, "r");
				echo $file;
				while (!feof ($handle)) {
    			$buffer = fgets($handle, 100);
    			echo $buffer."<br>";
                }
			
		}


		$list = new listTable('mod_tamplates');
        $list->extOrder = true;
		$list->SQL = "SELECT 1";
		$list->addColumn("Имя шаблона", "", "TEXT");
		$list->addColumn("Описание", "", "TEXT");
		$list->addColumn("Загрузить", "5%", "BLOCK",'align=center', false);
		$list->getData();
		$dir = opendir("core2/mod_tpl");
		$folder = array();
		$i = 0;
		while ($file = readdir($dir))
		{		
			$i++;				
			if ($file != "." && $file != ".." && !strpos($file, "svn"))
				if(is_dir("core2/mod_tpl/".$file))
				{					
					
					$folder[$i][] = $i;
					$folder[$i][] = $file;
					$folder[$i][] = "";					 
					$folder[$i][] = "<a href=\"?module=admin&action=modules&loc=core&tab_mod=3&download_mod_tpl=".$file."\"><img src=\"core2/html/".THEME."/img/templates_button.png\" border=\"0\"/></a>";
					
				}
		}
		
		closedir($dir);
		$list->data = $folder;
		$list->editURL = $app."&tab_mod=3&file_mod=TCOL_01";
		$list->showTable();
	}


$tab->endContainer();


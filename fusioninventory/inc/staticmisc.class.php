<?php

/*
   ------------------------------------------------------------------------
   FusionInventory
   Copyright (C) 2010-2013 by the FusionInventory Development Team.

   http://www.fusioninventory.org/   http://forge.fusioninventory.org/
   ------------------------------------------------------------------------

   LICENSE

   This file is part of FusionInventory project.

   FusionInventory is free software: you can redistribute it and/or modify
   it under the terms of the GNU Affero General Public License as published by
   the Free Software Foundation, either version 3 of the License, or
   (at your option) any later version.

   FusionInventory is distributed in the hope that it will be useful,
   but WITHOUT ANY WARRANTY; without even the implied warranty of
   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
   GNU Affero General Public License for more details.

   You should have received a copy of the GNU Affero General Public License
   along with FusionInventory. If not, see <http://www.gnu.org/licenses/>.

   ------------------------------------------------------------------------

   @package   FusionInventory
   @author    David Durieux
   @co-author
   @copyright Copyright (c) 2010-2013 FusionInventory team
   @license   AGPL License 3.0 or (at your option) any later version
              http://www.gnu.org/licenses/agpl-3.0-standalone.html
   @link      http://www.fusioninventory.org/
   @link      http://forge.fusioninventory.org/projects/fusioninventory-for-glpi/
   @since     2010

   ------------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

class PluginFusioninventoryStaticmisc {

   /**
   * Get task methods of this plugin fusioninventory
   *
   * @return array ('module'=>'value', 'method'=>'value')
   *   module value name of plugin
   *   method value name of method
   **/
   static function task_methods() {

      $a_tasks = array();
      $a_tasks[] = array('module'          => 'fusioninventory',
                         'method'          => 'wakeonlan',
                         'name'            => __('Wake On LAN', 'fusioninventory'),
                         'use_rest'        => false);

      $a_tasks[] =  array('module'         => 'fusioninventory',
                          'method'         => 'inventory',
                          'selection_type' => 'devices',
                          'hidetask'       => 1,
                          'name'           => __('Computer Inventory', 'fusioninventory'),
                          'use_rest'       => false);

      $a_tasks[] = array('module'         => 'fusioninventory',
                         'method'         => 'ESX',
                         'selection_type' => 'devices',
                         'name'           => __('VMware host remote inventory', 'fusioninventory'),
                         'use_rest'       => true);

      $a_tasks[] = array('module'         => 'fusioninventory',
                         'method'         => 'networkdiscovery',
                         'name'           => __('Network discovery', 'fusioninventory'));


      $a_tasks[] = array('module'         => 'fusioninventory',
                         'method'         => 'networkinventory',
                         'name'           => __('Network inventory (SNMP)', 'fusioninventory'));
      
      return $a_tasks;
   }



   /**
   * Get types of datas available to select for taskjob definition for WakeOnLan method
   *
   * @param $a_itemtype array types yet added for definitions
   *
   * @return array ('itemtype'=>'value','itemtype'=>'value'...)
   *   itemtype itemtype of object
   *   value name of the itemtype
   **/
   static function task_definitiontype_wakeonlan($a_itemtype) {

      $a_itemtype['Computer'] = Computer::getTypeName();

      return $a_itemtype;
   }



   /**
   * Get all devices of definition type 'Computer' defined in task_definitiontype_wakeonlan
   *
   * @param $title value ???(not used I think)
   *
   * @return dropdown list of computers
   *
   **/
   static function task_definitionselection_Computer_wakeonlan($title) {

      $options = array();
      $options['entity'] = $_SESSION['glpiactive_entity'];
      $options['entity_sons'] = 1;
      $options['name'] = 'definitionselectiontoadd';
      $rand = Dropdown::show("Computer", $options);
      return $rand;
   }



   /**
   * Get all methods of this plugin
   *
   * @return array ('module'=>'value', 'method'=>'value')
   *   module value name of plugin
   *   method value name of method
   *
   **/
   static function getmethods() {
      $a_methods = call_user_func(array('PluginFusioninventoryStaticmisc', 'task_methods'));
      $a_modules = PluginFusioninventoryModule::getAll();
      foreach ($a_modules as $data) {
         $class = $class= PluginFusioninventoryStaticmisc::getStaticmiscClass($data['directory']);
         if (is_callable(array($class, 'task_methods'))) {
            $a_methods = array_merge($a_methods,
               call_user_func(array($class, 'task_methods')));
         }
      }
      return $a_methods;
   }



   /**
   * Get all profiles defined for this plugin
   *
   * @return array [integer] array('profile'=>'value', 'name'=>'value')
   *   profile value profile name
   *   name value description name (LANG) of the profile
   *
   **/
   static function profiles() {

      return array(array('profil'  => 'agent',
                         'name'    => __('Agents', 'fusioninventory')),

                   array('profil'  => 'remotecontrol',
                         'name'    => __('Agent remote control', 'fusioninventory')),

                   array('profil'  => 'configuration',
                         'name'    => __('Configuration', 'fusioninventory')),

                   array('profil'  => 'wol',
                         'name'    => __('Wake On LAN', 'fusioninventory')),

                   array('profil'  => 'unknowndevice',
                         'name'    => __('Unknown devices', 'fusioninventory')),

                   array('profil'  => 'task',
                         'name'    => _n('Task', 'Tasks', 2)),

                   array('profil'  => 'iprange',
                         'name'    => __('IP range configuration', 'fusioninventory')),

                   array('profil'  => 'credential',
                         'name'    => __('Authentication for remote devices (VMware)', 'fusioninventory')),

                   array('profil'  => 'credentialip',
                         'name'    => __('Remote devices to inventory (VMware)', 'fusioninventory')),

                   array('profil'  => 'existantrule',
                         'name'    => __('Existance criteria', 'fusioninventory')),

                   array('profil'  => 'importxml',
                         'name'    => __('computer XML manual import', 'fusioninventory')),

                   array('profil'  => 'blacklist',
                         'name'    => __('Fields blacklist', 'fusioninventory')),

                   array('profil'  => 'ESX',
                         'name'    => __('VMware host', 'fusioninventory')),
          
                   array('profil'  => 'configsecurity',
                          'name'    => __('SNMP authentication', 'fusioninventory')),

                   array('profil'  => 'networkequipment',
                          'name'    => __('Network equipment SNMP', 'fusioninventory')),

                   array('profil'  => 'printer',
                          'name'    => __('Printer SNMP', 'fusioninventory')),

                   array('profil'  => 'model',
                          'name'    => __('SNMP model', 'fusioninventory')),

                   array('profil'  => 'reportprinter',
                          'name'    => __('Printers report', 'fusioninventory')),

                   array('profil'  => 'reportnetworkequipment',
                          'name'    => __('Network report')),

                   array('profil'  => 'collect',
                          'name'    => __('Additional computer information finder', 'fusioninventory'))
      );


   }



   /**
    * Get name of the staticmisc class for a module
    * @param module the module name
    *
    * @return the name of the staticmisc class associated with it
    */
   static function getStaticMiscClass($module) {
      return "Plugin".ucfirst($module)."Staticmisc";
   }



   /**
   * Get types of datas available to select for taskjob definition for ESX method
   *
   * @param $a_itemtype array types yet added for definitions
   *
   * @return array ('itemtype'=>'value','itemtype'=>'value'...)
   *   itemtype itemtype of object
   *   value name of the itemtype
   **/
   static function task_definitiontype_ESX($a_itemtype) {
      return array ('' => Dropdown::EMPTY_VALUE ,
                    'PluginFusioninventoryCredentialIp' => PluginFusioninventoryCredentialIp::getTypeName());
   }



   /**
   * Get all devices of definition type 'Computer' defined in task_definitiontype_wakeonlan
   *
   * @param $title value ???(not used I think)
   *
   * @return dropdown list of computers
   *
   **/
   static function task_definitionselection_PluginFusioninventoryCredentialIp_ESX($title) {
      global $DB;

      $query = "SELECT `a`.`id`, `a`.`name`
                FROM `glpi_plugin_fusioninventory_credentialips` as `a`
                LEFT JOIN `glpi_plugin_fusioninventory_credentials` as `c`
                   ON `c`.`id` = `a`.`plugin_fusioninventory_credentials_id`
                WHERE `c`.`itemtype`='PluginFusinvinventoryVmwareESX'";
      $query.= getEntitiesRestrictRequest(' AND','a');
      $results = $DB->query($query);

      $agents = array();
      //$agents['.1'] = __('All');

      while ($data = $DB->fetch_array($results)) {
         $agents[$data['id']] = $data['name'];
      }
      if (!empty($agents)) {
         return Dropdown::showFromArray('definitionselectiontoadd',$agents);
      }
   }


   //------------------------------------------ Actions-------------------------------------//

   static function task_actiontype_ESX($a_itemtype) {
      return array ('' => Dropdown::EMPTY_VALUE ,
                    'PluginFusioninventoryAgent' => __('Agents', 'fusioninventory'));

   }



   /**
   * Get all devices of definition type 'Computer' defined in task_definitiontype_wakeonlan
   *
   * @return dropdown list of computers
   *
   **/
   static function task_actionselection_PluginFusioninventoryCredentialIp_ESX() {
      global $DB;

      $options = array();
      $options['name'] = 'definitionactiontoadd';

      $module = new PluginFusioninventoryAgentmodule();
      $module_infos = $module->getActivationExceptions('esx');
      $exceptions = json_decode($module_infos['exceptions'],true);

      $in = "";
      if (!empty($exceptions)) {
         $in = " AND `a`.`id` NOT IN (".implode($exceptions,',').")";
      }

      $query = "SELECT `a`.`id`, `a`.`name`
                FROM `glpi_plugin_fusioninventory_credentialips` as `a`
                LEFT JOIN `glpi_plugin_fusioninventory_credentials` as `c`
                   ON `c`.`id` = `a`.`plugin_fusioninventory_credentials_id`
                WHERE `c`.`itemtype`='PluginFusioninventoryVmwareESX'";
      $query.= getEntitiesRestrictRequest(' AND','glpi_plugin_fusioninventory_credentialips');

      $results = $DB->query($query);
      $credentialips = array();
      while ($data = $DB->fetch_array($results)) {
         $credentialips[$data['id']] = $data['name'];
      }
      return Dropdown::showFromArray('actionselectiontoadd',$credentialips);
   }



   static function task_actionselection_PluginFusioninventoryAgent_ESX() {

      $array = array();
      $PluginFusioninventoryAgentmodule = new PluginFusioninventoryAgentmodule();
      $array1 = $PluginFusioninventoryAgentmodule->getAgentsCanDo(strtoupper("ESX"));
      foreach ($array1 as $id => $data) {
         $array[$id] = $data['name'];
      }
      asort($array);
      return Dropdown::showFromArray('actionselectiontoadd', $array);
   }

   //------------------------------------------ ---------------------------------------------//
   //------------------------------------------ REST PARAMS---------------------------------//
   //------------------------------------------ -------------------------------------------//

   /**
    * Get ESX task parameters to send to the agent
    * For the moment it's hardcoded, but in a future release it may be in DB
    * @return an array of parameters
    */
   static function task_ESX_getParameters() {

      return array ('periodicity' => 3600, 'delayStartup' => 3600, 'task' => 'ESX',
                    'remote' => PluginFusioninventoryAgentmodule::getUrlForModule('ESX'));
   }
   
   
   //------------------------------- Network tools ------------------------------------//

   // *** NETWORKDISCOVERY ***
   static function task_definitiontype_networkdiscovery($a_itemtype) {

      $a_itemtype['PluginFusioninventoryIPRange'] = __('IP Ranges', 'fusioninventory');


      return $a_itemtype;
   }



   static function task_definitionselection_PluginFusioninventoryIPRange_networkdiscovery($title) {

      $options = array();
      $options['entity'] = $_SESSION['glpiactive_entity'];
      $options['entity_sons'] = 1;
      $options['name'] = 'definitionselectiontoadd';
      $rand = Dropdown::show("PluginFusioninventoryIPRange", $options);
      return $rand;
   }



   // *** NETWORKINVENTORY ***
   static function task_definitiontype_networkinventory($a_itemtype) {

      $a_itemtype['PluginFusioninventoryIPRange'] = __('IP Ranges', 'fusioninventory');

      $a_itemtype['NetworkEquipment'] = NetworkEquipment::getTypeName();
      $a_itemtype['Printer'] = Printer::getTypeName();

      return $a_itemtype;
   }



   static function task_definitionselection_PluginFusioninventoryIPRange_networkinventory($title) {
      $rand = PluginFusioninventoryStaticmisc::task_definitionselection_PluginFusioninventoryIPRange_networkdiscovery($title);
      return $rand;
   }



   static function task_definitionselection_NetworkEquipment_networkinventory($title) {

      $options = array();
      $options['entity'] = $_SESSION['glpiactive_entity'];
      $options['entity_sons'] = 1;
      $options['name'] = 'definitionselectiontoadd';
      $rand = Dropdown::show("NetworkEquipment", $options);
      return $rand;
   }



   static function task_definitionselection_Printer_networkinventory($title) {

      $options = array();
      $options['entity'] = $_SESSION['glpiactive_entity'];
      $options['entity_sons'] = 1;
      $options['name'] = 'definitionselectiontoadd';
      $rand = Dropdown::show("Printer", $options);
      return $rand;
   }




   static function task_networkdiscovery_agents() {

      $array = array();
      $array["-.1"] = __('Auto managenement dynamic of agents', 'fusioninventory');

      $pfAgentmodule = new PluginFusioninventoryAgentmodule();
      $array1 = $pfAgentmodule->getAgentsCanDo('NETWORKDISCOVERY');
      foreach ($array1 as $id => $data) {
         $array["PluginFusioninventoryAgent-".$id] = __('Auto managenement dynamic of agents', 'fusioninventory')." - ".$data['name'];
      }
      return $array;
   }

   # Actions with itemtype autorized
   static function task_action_networkinventory() {
      $a_itemtype = array();
      $a_itemtype[] = "Printer";
      $a_itemtype[] = "NetworkEquipment";
      $a_itemtype[] = 'PluginFusioninventoryIPRange';

      return $a_itemtype;
   }



   # Selection type for actions
   static function task_selection_type_networkinventory($itemtype) {
      $selection_type = '';
      switch ($itemtype) {

         case 'PluginFusioninventoryIPRange':
            $selection_type = 'iprange';
            break;

         case "Printer";
         case "NetworkEquipment";
            $selection_type = 'devices';
            break;

      }
      return $selection_type;
   }



   static function task_selection_type_networkdiscovery($itemtype) {
      $selection_type = '';
      switch ($itemtype) {

         case 'PluginFusioninventoryIPRange':
            $selection_type = 'iprange';
            break;

         // __('Auto managenement dynamic of agents', 'fusioninventory')


      }

      return $selection_type;
   }




}

?>

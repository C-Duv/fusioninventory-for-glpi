<?php

/*
   ------------------------------------------------------------------------
   FusionInventory
   Copyright (C) 2010-2012 by the FusionInventory Development Team.

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
   along with Behaviors. If not, see <http://www.gnu.org/licenses/>.

   ------------------------------------------------------------------------

   @package   FusionInventory
   @author    Anthony Hébert
   @co-author 
   @copyright Copyright (c) 2010-2012 FusionInventory team
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

class PluginFusioninventoryInventoryComputerCollect extends CommonDBTM {

   // From CommonDBChild
   public $dohistory = true;



   /**
    * Display name of itemtype
    * 
    * @return value name of this itemtype
    */
   static function getTypeName($nb=0) {

      return __('Collect computer information', 'fusioninventory');
   }

   
   static function canCreate() {
      return PluginFusioninventoryProfile::haveRight("collect", "w");
   }


   static function canView() {
      return PluginFusioninventoryProfile::haveRight("collect", "r");
   }


   function getSearchOptions() {

      $tab = array();
    
      $tab['common'] = __('Additional computer information', 'fusioninventory');

      $tab[1]['table']     = $this->getTable();
      $tab[1]['field']     = 'name';
      $tab[1]['linkfield'] = 'name';
      $tab[1]['name']      = __("Name");
      $tab[1]['datatype']  = 'itemlink';

      $tab[2]['table']     = $this->getTable();
      $tab[2]['field']     = 'is_active';
      $tab[2]['linkfield'] = 'is_active';
      $tab[2]['name']      = __("Active");
      $tab[2]['datatype']  = 'bool';
 
      $tab[3]['table']     = 'glpi_plugin_fusioninventory_inventorycomputercollecttypes';
      $tab[3]['field']     = 'name';
      $tab[3]['name']      = __("Type");
 
      return $tab;
   }

   function showForm($ID, $options=array()) {
      global $CFG_GLPI;

      // Show ConsumableItem or blank form
      if (!Session::haveRight("consumable","r")) {
         return false;
      }

      if ($ID > 0) {
         $this->check($ID,'r');
         $this->getFromDB($ID);
      } else {
         // Create item
         $this->check(-1,'w');
      }
      
      $this->showTabs($options);
      $this->showFormHeader($options);


      echo "<tr class='tab_bg_1'>";
      echo "<td>".__('Name')."&nbsp;:</td>";
      echo "<td><input type='hidden' name='id' value='{$this->fields['id']}'/>";
      Html::autocompletionTextField($this, "name");
      echo "</td>";
      echo "<td>";
      echo __('Collect type', 'fusioninventory')."&nbsp;:";
      echo "</td>";
      echo "<td>";
      Dropdown::show('PluginFusioninventoryInventoryComputerCollectType', array(
               'value'     => $this->fields['plugin_fusioninventory_inventorycomputercollecttypes_id'],
               'name'      => "plugin_fusioninventory_inventorycomputercollecttypes_id"
      ));
      echo "</td>";
      echo "</tr>";
      echo "<tr class='tab_bg_1'>";
      echo "<td>";
      echo __('Active')."&nbsp;:";
      echo "</td>";
      echo "<td>";
      Dropdown::showYesNo("is_active", 
                          $this->fields['is_active']);
      echo "</td>";
      echo "<td>".__('Comments')."&nbsp;:</td>";
      echo "<td class='middle'>";
      echo "<textarea cols='45' rows='3' name='comment' >".$this->fields["comment"]."</textarea>";
      echo "</td></tr>";

      $this->showFormButtons($options);
      $this->addDivForTabs();

      // TODO GLB: Why I need to add that here in 0.84.
      $pfInventoryComputerCollectContent = new PluginFusioninventoryInventoryComputerCollectContent();
      $pfInventoryComputerCollectContent->showAssociated($this);

      return true;
   }

/* TODO GLB: For what?
   function defineTabs($options=array()) {
      global $CFG_GLPI;

      $ong = array();
      $this->addStandardTab('PluginFusioninventoryInventoryComputerCollectcontent', $ong, $options);
      return $ong;
   }
*/

   /**
    * Unserialize a string that may contains dirty contents (/,:, etc..)
    * 
    * 
    * @return an unserialized content
    */
   static function debugSerializedContent($string){
      $string = preg_replace('!s:(\d+):"(.*?)";!se', "'s:'.strlen('$2').':\"$2\";'", $string ); 
      return unserialize($string);
   }


   /**
    * Get the list of jobs from all active collections
    * 
    * 
    * @return an array of jobs, extracted from each collection
    */
   static function getAllCollects(){
      global $DB;

      $jobs    = array();
      $obj     = new self;
      $content = new PluginFusioninventoryInventoryComputerCollectcontent;

      //get active collections
      $collects = $obj->find("is_active = 1");
      $i = 0;
      foreach($collects as $collect){
         $contents = $content->find("plugin_fusioninventory_inventorycomputercollects_id = {$collect['id']}");
         foreach($contents as $job){
            $jobs[$i] = array(
            'name'      => $job['name'],
            'function'  => PluginFusioninventoryInventoryComputerCollecttype::getCollectTypeName(
            $collect['plugin_fusioninventory_inventorycomputercollecttypes_id']));
            $detail = self::debugSerializedContent($job['details']);
            foreach($detail as $k => $v){
               $jobs[$i][$k] = $v;
            }
            $i++;
         }
      }
      return $jobs;
   }
   
}

?>

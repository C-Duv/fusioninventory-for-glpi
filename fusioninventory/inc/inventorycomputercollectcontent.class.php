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
   @author    David Durieux
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

class PluginFusioninventoryInventoryComputerCollectContent extends CommonDBTM {

   // From CommonDBChild
   public $dohistory = true;



   
   static function getTypeName($nb=0) {

      return __('TODOComputerCollectContent', 'fusioninventory');
   }

   
   static function canCreate() {
      return PluginFusioninventoryProfile::haveRight("fusioninventory", "collect", "w");
   }


   static function canView() {
      return PluginFusioninventoryProfile::haveRight("fusioninventory", "collect", "r");
   }

   
   function getTabNameForItem(CommonGLPI $item, $withtemplate=0) {
      global $LANG;
      
      if ($item->getType() == 'PluginFusioninventoryInventoryComputerCollect') {
            return $LANG['plugin_fusioninventory']['collect'][1];
      }
      return '';
   }

   static function displayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate=0) {

      switch ($item->getType()) {
         case 'PluginFusioninventoryInventoryComputerCollect' :
            self::showAssociated($item);
         break;
      }
      return true;
   }


   private function showAssociatedRegistryKeys($content){
      global $DB, $CFG_GLPI, $LANG;

      $hives = array(
         "HKEY_CLASSES_ROOT",
         "HKEY_CURRENT_USER",
         "HKEY_LOCAL_MACHINE",
         "HKEY_USERS",
         "HKEY_CURRENT_CONFIG",
         "HKEY_DYN_DATA"
      );

      echo "<div class='spaced'><table class='tab_cadre_fixe'>";
      echo "<tr><th colspan=5>".__('Content')."</th></tr>";
      echo "<tr>
      <th>".__("Name")."</th>
      <th>".__("Hive", "fusioninventory")."</th>
      <th>".__("Path", "fusioninventory")."</th>
      <th>".__("Key", "fusioninventory")."</th>
      <th>".__("Action")."</th>
      </tr>";
      foreach($content as $data){

         //hack on unserialize bug
         $properties = PluginFusioninventoryInventoryComputerCollect::debugSerializedContent($data['details']);
         
         echo "<td align='center'>{$data['name']}</td>";
         echo "<td align='center'>
         {$hives[$properties['hives_id']]}</td>";
         echo "<td align='center'>{$properties['path']}</td>";
         echo "<td align='center'>{$properties['key']}</td>";
         echo "<td align='center'>
         <form name='form_bundle_item' action='".Toolbox::getItemTypeFormURL(__CLASS__).
                "' method='post'>
         <input type='hidden' name='id' value='{$data['id']}'>
         <input type='image' name='delete' src='../pics/drop.png'>";
         Html::closeForm(true);
         echo "</td></tr>";
      }
      echo "</table></div>";

   }

   private function showAssociatedWmiProperties($content){
      global $DB, $CFG_GLPI, $LANG;

      echo "<div class='spaced'><table class='tab_cadre_fixe'>";
      echo "<tr><th colspan=4>{$LANG['plugin_fusioninventory']['collect'][2]}</th></tr>";
      echo "<tr>
      <th>{$LANG['plugin_fusioninventory']['collect']['fields'][2]}</th>
      <th>{$LANG['plugin_fusioninventory']['collect']['fields'][4]}</th>
      <th>{$LANG['plugin_fusioninventory']['collect']['fields'][5]}</th>
      <th>{$LANG['rulesengine'][30]}</th>
      </tr>";
      foreach($content as $data){
        
         $properties = unserialize($data['details']);
         
         echo "<td align='center'>{$data['name']}</td>";
         echo "<td align='center'>{$properties['class']}</td>";
         echo "<td align='center'>{$properties['property']}</td>";
         echo "<td align='center'>
         <form name='form_bundle_item' action='".Toolbox::getItemTypeFormURL(__CLASS__).
                "' method='post'>
         <input type='hidden' name='id' value='{$data['id']}'>
         <input type='image' name='delete' src='../pics/drop.png'>";
         Html::closeForm(true);
         echo "</td></tr>";
      }
      echo "</table></div>";

   }

   private function showAssociatedFiles($content){
      global $DB, $CFG_GLPI, $LANG;

      echo "<div class='spaced'><table class='tab_cadre_fixe'>";
      echo "<tr><th colspan=5>{$LANG['plugin_fusioninventory']['collect'][2]}</th></tr>";
      echo "<tr>
      <th>{$LANG['plugin_fusioninventory']['collect']['fields'][2]}</th>
      <th>{$LANG['plugin_fusioninventory']['collect']['fields'][1]}</th>
      <th>{$LANG['plugin_fusioninventory']['collect']['fields'][6]}</th>
      <th>{$LANG['plugin_fusioninventory']['collect']['fields'][7]}</th>
      <th>{$LANG['rulesengine'][30]}</th>
      </tr>";
      foreach($content as $data){
        
         $properties = PluginFusioninventoryInventoryComputerCollect::debugSerializedContent($data['details']);
         
         echo "<td align='center'>{$data['name']}</td>";
         echo "<td align='center'>{$properties['path']}</td>";
         echo "<td align='center'>{$properties['filename']}</td>";
         echo "<td align='center'>{$LANG['choice'][$properties['getcontent']]}</td>";
         echo "<td align='center'>
         <form name='form_bundle_item' action='".Toolbox::getItemTypeFormURL(__CLASS__).
                "' method='post'>
         <input type='hidden' name='id' value='{$data['id']}'>
         <input type='image' name='delete' src='../pics/drop.png'>";
         Html::closeForm(true);
         echo "</td></tr>";
      }
      echo "</table></div>";

   }

   private function showAssociatedCommands($content){
      global $DB, $CFG_GLPI, $LANG;

      echo "<div class='spaced'><table class='tab_cadre_fixe'>";
      echo "<tr><th colspan=4>{$LANG['plugin_fusioninventory']['collect'][2]}</th></tr>";
      echo "<tr>
      <th>{$LANG['plugin_fusioninventory']['collect']['fields'][2]}</th>
      <th>{$LANG['plugin_fusioninventory']['collect']['fields'][1]}</th>
      <th>{$LANG['plugin_fusioninventory']['collect']['fields'][8]}</th>
      <th>{$LANG['rulesengine'][30]}</th>
      </tr>";
      foreach($content as $data){
        
         $properties = unserialize($data['details']);
         
         echo "<td align='center'>{$data['name']}</td>";
         echo "<td align='center'>{$properties['path']}</td>";
         echo "<td align='center'>{$properties['command']}</td>";
         echo "<td align='center'>
         <form name='form_bundle_item' action='".Toolbox::getItemTypeFormURL(__CLASS__).
                "' method='post'>
         <input type='hidden' name='id' value='{$data['id']}'>
         <input type='image' name='delete' src='../pics/drop.png'>";
         Html::closeForm(true);
         echo "</td></tr>";
      }
      echo "</table></div>";

   }


   static function showAssociated(CommonDBTM $item, $withtemplate='') {
      global $DB, $CFG_GLPI, $LANG;

      $is_template   = 0;
      $obj           = new PluginFusioninventoryInventoryComputerCollectContent;
      $ID            = $item->fields['id'];

      $content = $obj->find("plugin_fusioninventory_inventorycomputercollects_id = {$ID}");



      //List the content (switched per type)

      switch($item->fields['plugin_fusioninventory_inventorycomputercollecttypes_id']){
         //getFromRegistry
         case 1:
            $obj->showAssociatedRegistryKeys($content);
         break;

         //getFromWMI
         case 2:
            $obj->showAssociatedWmiProperties($content);
         break;

         //findFile
         case 3:
            $obj->showAssociatedFiles($content);
         break;

         //runCommand
         case 4:
            $obj->showAssociatedCommands($content);
         break;
      }

      //Form

      echo "<form name='form_bundle_item' action='".Toolbox::getItemTypeFormURL(__CLASS__).
                "' method='post'>";
      echo "<input type='hidden' name='plugin_fusioninventory_inventorycomputercollects_id' value='$ID'>";
      echo "<input type='hidden' name='plugin_fusioninventory_inventorycomputercollecttypes_id' 
      value='{$item->fields['plugin_fusioninventory_inventorycomputercollecttypes_id']}'>";

      echo "<div class='spaced'><table class='tab_cadre_fixe'>";
      echo "<tr><th colspan=6>".__("Path", "fusioninventory")."</th></tr>";

      //output the form depending on the type of collect
      //Note : No edition, we drop/add to edit
      $type = $item->fields['plugin_fusioninventory_inventorycomputercollecttypes_id'];

      //always ask for a name
      echo "<tr class='tab_bg_1'>";
            echo "<td>".__("Name")."&nbsp;:</td>";
            echo "<td><input type='text' name='name' value=''/></td>";
      switch($type){
         //getFromRegistry
         case 1:
            //Hive
            echo "<td>".__("Hive", "fusioninventory")."&nbsp;:</td>";
            echo "<td>";
            $hives = array(0 => 'HKEY_CLASSES_ROOT',1 => 'HKEY_CURRENT_USER',
                           2 => 'HKEY_LOCAL_MACHINE',3 => 'HKEY_USERS',
                           4 => 'HKEY_CURRENT_CONFIG', 5 => 'HKEY_DYN_DATA' );

            Dropdown::showFromArray("hives_id", $hives);
            echo "</td></tr>";
            //Path
            echo "<tr class='tab_bg_1'>";
            echo "<td>".__("Path", "fusioninventory")."&nbsp;:</td>";
            echo "<td><input type='text' name='path' value=''/></td>";
            //key name
            echo "<td>".__("Key", "fusioninventory")."&nbsp;:</td>";
            echo "<td><input type='text' name='key' value=''/></td>";
            echo "</tr>";
            echo "<tr class='tab_bg_1'><td colspan=6 class='center'>";
            echo "<input type='submit' name='add' value=\"".__("Add")."\" 
            class='submit'/></td>"; 
            echo "</table>";
         break;

         //getFromWMI
         case 2:
            //Class
            echo "<td>{$LANG['plugin_fusioninventory']['collect']['fields'][4]}&nbsp;:</td>";
            echo "<td><input type='text' name='class' value=''/></td>";
            //key name
            echo "<td>{$LANG['plugin_fusioninventory']['collect']['fields'][5]}&nbsp;:</td>";
            echo "<td><input type='text' name='property' value=''/></td>";
            echo "</tr>";
            echo "<tr class='tab_bg_1'><td colspan=6 class='center'>";
            echo "<input type='submit' name='add' value=\"".$LANG['buttons'][8]."\" 
            class='submit'/></td>"; 
            echo "</table>";
         break;

         //findFile
         case 3:
            //Class
            echo "<td>{$LANG['plugin_fusioninventory']['collect']['fields'][1]}&nbsp;:</td>";
            echo "<td><input type='text' name='path' value=''/></td>";
            //key name
            echo "</tr>";
            echo "<tr class='tab_bg_1'>";
            echo "<td>{$LANG['plugin_fusioninventory']['collect']['fields'][6]}&nbsp;:</td>";
            echo "<td><input type='text' name='filename' value=''/></td>";
            echo "<td>{$LANG['plugin_fusioninventory']['collect']['fields'][7]}&nbsp;:</td>";
            echo "<td>";
            Dropdown::showYesNo("getcontent");
            echo "</td></tr>";
            echo "<tr class='tab_bg_1'><td colspan=6 class='center'>";
            echo "<input type='submit' name='add' value=\"".$LANG['buttons'][8]."\" 
            class='submit'/></td>"; 
            echo "</table>";
         break;
         
         //runCommand
         case 4:
            //Class
            echo "<td>{$LANG['plugin_fusioninventory']['collect']['fields'][1]}&nbsp;:</td>";
            echo "<td><input type='text' name='path' value=''/></td>";
            //key name
            echo "<td>{$LANG['plugin_fusioninventory']['collect']['fields'][8]}&nbsp;:</td>";
            echo "<td><input type='text' name='command' value=''/></td>";
            echo "</tr>";
            echo "<tr class='tab_bg_1'><td colspan=6 class='center'>";
            echo "<input type='submit' name='add' value=\"".$LANG['buttons'][8]."\" 
            class='submit'/></td>"; 
            echo "</table>";
         break;
      }

      

      echo "</div>";
      Html::closeForm(true);

      
   }
   
}

?>

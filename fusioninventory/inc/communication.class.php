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
   @author    Vincent Mazzoni
   @co-author David Durieux
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

/**
 * Class to communicate with agents using XML
 **/
class PluginFusioninventoryCommunication {
   protected $message;


   function __construct() {
      $this->message = new SimpleXMLElement("<?xml version='1.0' encoding='UTF-8'?><REPLY></REPLY>");
      PluginFusioninventoryToolbox::logIfExtradebug(
         'pluginFusioninventory-communication',
         'New PluginFusioninventoryCommunication object.'
      );
   }



   /**
    * Get readable XML message (add carriage returns)
    *
    * @return readable XML message
    **/
   function getMessage() {
      return $this->message;
   }



   /**
    * Set XML message
    *
    * @param $message XML message
    *
    * @return nothing
    **/
   function setMessage($message) {
      // avoid xml warnings
      $this->message = @simplexml_load_string(
         $message,'SimpleXMLElement',
         LIBXML_NOCDATA
      );
   }

   /**
    * Send data, using given compression algorithm
    *
    **/
   function sendMessage($compressmode = 'none') {

      if (!$this->message) {
         return;
      }

      switch($compressmode) {
         case 'none':
            header("Content-Type: application/xml");
            echo PluginFusioninventoryToolbox::formatXML($this->message);
            break;

         case 'zlib':
            # rfc 1950
            header("Content-Type: application/x-compress-zlib");
            echo gzcompress(
               PluginFusioninventoryToolbox::formatXML($this->message)
            );
            break;

         case 'deflate':
            # rfc 1951
            header("Content-Type: application/x-compress-deflate");
            echo gzdeflate(
               PluginFusioninventoryToolbox::formatXML($this->message)
            );
            break;

         case 'gzip':
            # rfc 1952
            header("Content-Type: application/x-compress-gzip");
            echo gzencode(
               PluginFusioninventoryToolbox::formatXML($this->message)
            );
            break;

      }
   }



   /**
    * Add logs
    *
    * @param $p_logs logs to write
    *
    * @return nothing (write text in log file)
    **/
   static function addLog($p_logs) {

      if ($_SESSION['glpi_use_mode']==Session::DEBUG_MODE) {
         if (PluginFusioninventoryConfig::isExtradebugActive()) {
            file_put_contents(GLPI_LOG_DIR.'/pluginFusioninventory-communication.log',
                              "\n".time().' : '.$p_logs,
                              FILE_APPEND);
         }
      }
   }



   /**
    * Import data
    *
    * @param $arrayinventory array to import
    *
    * @return true (import ok) / false (import ko)
    **/
   function import($arrayinventory) {

      $pfAgentmodule = new PluginFusioninventoryAgentmodule();
      $pfAgent = new PluginFusioninventoryAgent();

      PluginFusioninventoryToolbox::logIfExtradebug(
         'pluginFusioninventory-communication',
         'Function import().'
      );

      $this->message = $arrayinventory;
      $errors = '';

      $xmltag = $this->message['QUERY'];
      if ($xmltag == "NETDISCOVERY") {
         $xmltag = "NETWORKDISCOVERY";
      }
      if ($xmltag == "SNMPQUERY"
              OR $xmltag == "SNMPINVENTORY") {
         $xmltag = "NETWORKINVENTORY";
      }

      
      $agent = $pfAgent->InfosByKey($this->message['DEVICEID']);
      if ($xmltag == "PROLOG") {
         return false;
      }

      if (isset($this->message['CONTENT']['MODULEVERSION'])) {
         $pfAgent->setAgentVersions($agent['id'], $xmltag, $this->message['CONTENT']['MODULEVERSION']);
      } else if (isset($this->message['CONTENT']['VERSIONCLIENT'])) {
         $version = str_replace("FusionInventory-Agent_", "", $this->message['CONTENT']['VERSIONCLIENT']);
         $pfAgent->setAgentVersions($agent['id'], $xmltag, $version);
      }

      if (isset($this->message->CONTENT->MODULEVERSION)) {
         $pfAgent->setAgentVersions($agent['id'], $xmltag, (string)$this->message->CONTENT->MODULEVERSION);
      } else if (isset($this->message->CONTENT->VERSIONCLIENT)) {
         $version = str_replace("FusionInventory-Agent_", "", (string)$this->message->CONTENT->VERSIONCLIENT);
         $pfAgent->setAgentVersions($agent['id'], $xmltag, $version);
      }


      if (isset($_SESSION['glpi_plugin_fusioninventory']['xmltags']["$xmltag"])) {
         $moduleClass = $_SESSION['glpi_plugin_fusioninventory']['xmltags']["$xmltag"];
         $moduleCommunication = new $moduleClass();
         $errors.=$moduleCommunication->import($this->message['DEVICEID'],
                 $this->message['CONTENT'],
                 $arrayinventory);
      } else {
         $errors.=__('Unattended element in', 'fusioninventory').' QUERY : *'.$xmltag."*\n";
      }
      $result=true;
      // TODO manage this error ( = delete it)
      if ($errors != '') {
         echo $errors;
         if (isset($_SESSION['glpi_plugin_fusioninventory_processnumber'])) {
            $result=true;
         } else {
            // It's PROLOG
            $result=false;
         }
      }
      return $result;
   }



   /**
    * Get all tasks prepared for this agent
    *
    * @param $agent_id interger id of agent
    *
    **/
   function getTaskAgent($agent_id) {

      $pfTaskjobstate = new PluginFusioninventoryTaskjobstate();
      $moduleRun = $pfTaskjobstate->getTaskjobsAgent($agent_id);
      foreach ($moduleRun as $className => $array) {
         if (class_exists($className)) {
            if ($className != "PluginFusioninventoryInventoryComputerESX") {
               $class = new $className();
               $sxml_temp = $class->run($array);
               PluginFusioninventoryToolbox::append_simplexml(
                  $this->message, $sxml_temp
               );
            }
         }
      }
   }



   /**
    * Set prolog for agent
    *
    **/
   function addProlog() {
      $pfConfig = new PluginFusioninventoryConfig();
      $plugins_id = PluginFusioninventoryModule::getModuleId('fusioninventory');
      $this->message->addChild('PROLOG_FREQ', $pfConfig->getValue("inventory_frequence"));
   }



   /**
    * order to agent to do inventory if module inventory is activated for this agent
    *
    * @param $items_id interger Id of this agent
    *
    **/
   function addInventory($items_id) {
      $pfAgentmodule = new PluginFusioninventoryAgentmodule();
      if ($pfAgentmodule->getAgentCanDo('INVENTORY', $items_id)) {
         $this->message->addChild('RESPONSE', "SEND");
      }
   }

   /**
    * Add all asked registry keys in XML File.
    *
    * @param $xml SimpleXMLElement object
    * @return nothing
    */   
   function addRegistry($items_id) {


      $pfAgentmodule = new PluginFusioninventoryAgentmodule();
#      if (!$pfAgentmodule->getAgentCanDo('COLLECT', $items_id)) {
#         return;
#      }

      // Get getFromRegistry ID
      $collectTypeObject = new PluginFusioninventoryInventoryComputerCollectType();
      $resultCollectType = $collectTypeObject->find("name = 'getFromRegistry'");

      if(count($resultCollectType) === 0) return false;

      $collecttypeArray = reset($resultCollectType);
      $collecttypeId = $collecttypeArray['id'];

      // Look for active getFromRegistry campaign.
      $collectObject = new PluginFusioninventoryInventoryComputerCollect();
      $sqlCollectCampaign  = "plugin_fusioninventory_inventorycomputercollecttypes_id = {$collecttypeId}";
      $sqlCollectCampaign .= " AND is_active = 1";
      $resultCollectCampaign = $collectObject->find($sqlCollectCampaign);

      if(count($resultCollectCampaign) === 0) return false;
      // Get all active campaign Id
      $activeCampaignsId = array();
      foreach($resultCollectCampaign as $campaign) {
         $activeCampaignsId[] = $campaign['id'];
      }

      // Get all collect content datas
      $collectContentObject = new PluginFusioninventoryInventoryComputerCollectcontent();
      $sqlActiveRegistry  = "plugin_fusioninventory_inventorycomputercollects_id IN (";
      $sqlActiveRegistry .= implode(',', $activeCampaignsId).")";
      $resultCollectContent = $collectContentObject->find($sqlActiveRegistry);

      if(count($resultCollectContent) === 0) return false;

      // Get all registry key wanted.
      $registryKeys = array();
      foreach($resultCollectContent as $row) {
         
         $row['details'] =
         preg_replace('!s:(\d+):"(.*?)";!se', "'s:'.strlen('$2').':\"$2\";'", $row['details'] );
         $details = unserialize($row['details']);

         if(!$details) return false;

         if(!empty($row['name'])
            && !empty($details['hives_id'])
            && !empty($details['path'])
            && !empty($details['key'])) {

            $registryKeys[] = array(
               'GLPI_NAME'    => $row['name'],
               'REGKEY'       => $details['path'],
               'REGTREE'      => $details['hives_id'],
               'REGISTRY_KEY' => $details['key']);

         }
      }

      if(!count($registryKeys) === 0) return false;

      // Add to xml.
      $xmlOption = $this->message->addChild('OPTION');
      $xmlOption->addChild('NAME', 'REGISTRY');

      foreach($registryKeys as $registryKey) {
         $xmlParam = $xmlOption->addChild('PARAM',$registryKey['REGISTRY_KEY']);
         $xmlParam->addAttribute('NAME', $registryKey['GLPI_NAME']);
         $xmlParam->addAttribute('REGKEY', $registryKey['REGKEY']);
         $xmlParam->addAttribute('REGTREE', $registryKey['REGTREE']);
      }

   }


   // new REST protocol
   function handleFusionCommunication() {
      $response = PluginFusioninventoryRestCommunication::communicate($_GET);
      if ($response) {
         echo json_encode($response);
      } else {
         PluginFusioninventoryRestCommunication::sendError();
      }
   }



// old POST protocol
   function handleOCSCommunication($xml='') {

      // ***** For debug only ***** //
      //$GLOBALS["HTTP_RAW_POST_DATA"] = gzcompress('');
      // ********** End ********** //

      $config = new PluginFusioninventoryConfig();
      $plugin = new Plugin();
      $user   = new User();

      ob_start();
      if (!isset($_SESSION['glpiID'])) {
         $users_id  = $config->getValue('users_id');
         $_SESSION['glpiID'] = $users_id;
         $user->getFromDB($users_id);
         Session::changeActiveEntities();
         $_SESSION["glpiname"] = $user->getField('name');
         $_SESSION['glpiactiveprofile'] = array();
         $_SESSION['glpiactiveprofile']['interface'] = '';
         $_SESSION['glpiactiveprofile']['internet'] = 'w';
         $_SESSION['glpiactiveprofile']['computer'] = 'w';
         $_SESSION['glpiactiveprofile']['monitor'] = 'w';
         $_SESSION['glpiactiveprofile']['printer'] = 'w';
         $_SESSION['glpiactiveprofile']['peripheral'] = 'w';
         $plugin->init();
      }
      if (isset($_SESSION["glpi_plugins"]) && is_array($_SESSION["glpi_plugins"])) {
         //Plugin::doHook("config");
         if (count($_SESSION["glpi_plugins"])) {
            foreach ($_SESSION["glpi_plugins"] as $name) {
               Plugin::load($name);
            }
         }
         // For plugins which require action after all plugin init
         Plugin::doHook("post_init");
      }
      ob_end_clean();

      $communication  = new PluginFusioninventoryCommunication();

      // identify message compression algorithm
      $taskjob = new PluginFusioninventoryTaskjob();
      $taskjob->disableDebug();
      $compressmode = '';
      if (!empty($xml)) {
            $compressmode = 'none';
      } else if ($_SERVER['CONTENT_TYPE'] == "application/x-compress-zlib") {
            $xml = gzuncompress($GLOBALS["HTTP_RAW_POST_DATA"]);
            $compressmode = "zlib";
      } else if ($_SERVER['CONTENT_TYPE'] == "application/x-compress-gzip") {
            $xml = $communication->gzdecode($GLOBALS["HTTP_RAW_POST_DATA"]);
            $compressmode = "gzip";
      } else if ($_SERVER['CONTENT_TYPE'] == "application/xml") {
            $xml = $GLOBALS["HTTP_RAW_POST_DATA"];
            $compressmode = 'none';
      } else {
         # try each algorithm successively
         if ($xml = gzuncompress($GLOBALS["HTTP_RAW_POST_DATA"])) {
            $compressmode = "zlib";
         } else if ($xml = $communication->gzdecode($GLOBALS["HTTP_RAW_POST_DATA"])) {
            $compressmode = "gzip";
         } else if ($xml = gzinflate (substr($GLOBALS["HTTP_RAW_POST_DATA"], 2))) {
            // accept deflate for OCS agent 2.0 compatibility,
            // but use zlib for answer
            if (strstr($xml, "<QUERY>PROLOG</QUERY>")
                    AND !strstr($xml, "<TOKEN>")) {
               $compressmode = "zlib";
            } else {
               $compressmode = "deflate";
            }
         } else {
            $xml = $GLOBALS["HTTP_RAW_POST_DATA"];
            $compressmode = 'none';
         }
      }
      $taskjob->reenableusemode();

      // check if we are in ssl only mode
      $ssl = $config->getValue('ssl_only');
      if (
         $ssl == "1"
            AND
         (!isset($_SERVER["HTTPS"]) OR $_SERVER["HTTPS"] != "on")
      ) {
         $communication->setMessage("<?xml version='1.0' encoding='UTF-8'?>
   <REPLY>
   <ERROR>SSL REQUIRED BY SERVER</ERROR>
   </REPLY>");
         $communication->sendMessage($compressmode);
         return;
      }

      PluginFusioninventoryConfig::logIfExtradebug(
         'pluginFusioninventory-dial' . uniqid(),
         $xml
      );

      // Check XML integrity
      $pxml = '';
      if ($pxml = @simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)) {

      } else if ($pxml = @simplexml_load_string(utf8_encode($xml), 'SimpleXMLElement', LIBXML_NOCDATA)) {
         $xml = utf8_encode($xml);
      } else {
         $xml = preg_replace ('/<FOLDER>.*?<\/SOURCE>/', '', $xml);
         $pxml = @simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);

         if (!$pxml) {
            $communication->setMessage("<?xml version='1.0' encoding='UTF-8'?>
   <REPLY>
   <ERROR>XML not well formed!</ERROR>
   </REPLY>");
            $communication->sendMessage($compressmode);
            return;
         }
      }
      
      $_SESSION['plugin_fusioninventory_compressmode'] = $compressmode;
      
      // Convert XML into PHP array
      $arrayinventory = array();
      $arrayinventory = PluginFusioninventoryFormatconvert::XMLtoArray($pxml);
      unset($pxml);
      $deviceid = '';
      if (isset($arrayinventory['DEVICEID'])) {
         $deviceid = $arrayinventory['DEVICEID'];
      }
      
      $agent = new PluginFusioninventoryAgent();
      $agents_id = $agent->importToken($arrayinventory);
      $_SESSION['plugin_fusioninventory_agents_id'] = $agents_id;
      
      if (!$communication->import($arrayinventory)) {

         if ($deviceid != '') {

            $communication->setMessage("<?xml version='1.0' encoding='UTF-8'?>
<REPLY>
</REPLY>");

            $a_agent = $agent->InfosByKey($deviceid);

            // Get taskjob in waiting
            $communication->getTaskAgent($a_agent['id']);
            // ******** Send XML

            $communication->addInventory($a_agent['id']);
            $communication->addRegistry($a_agent['id']);
            $communication->addProlog();
            $communication->sendMessage($compressmode);
         }
      } else {
         $communication->setMessage("<?xml version='1.0' encoding='UTF-8'?>
<REPLY>
</REPLY>");
         $communication->sendMessage($compressmode);
      }
   }
}


?>

<?php

/**
 * Copyright 2013 François Kooman <francois.kooman@surfnet.nl>
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

require_once 'vendor/autoload.php';

echo date("Y-m-d H:i:s") . " :: starting Janus entity data conversion\n";


try {
    $configFile = __DIR__ . DIRECTORY_SEPARATOR . "config" . DIRECTORY_SEPARATOR . "config.ini";
    $config = \fkooman\Config\Config::fromIniFile($configFile);

    // data directory
    $exportDir      = $config->s('output')->l('exportDir', true); // REQ
    $aclDir         = $config->s('output')->l('aclDir', true); // REQ
    $convertDir     = $config->s('output')->l('convertDir', true); // REQ
    $timezone       = $config->l('timezone', false, "Europe/Amsterdam");
    date_default_timezone_set($timezone);

    $inputFile = $exportDir . DIRECTORY_SEPARATOR . "export.json";
    $exportData = @file_get_contents($inputFile);
    if (false === $exportData) {
        throw new Exception(sprintf("unable to read JSON file '%s' from disk", $inputFile));
    }
    $entities = json_decode($exportData, true);

    $inputFile = $aclDir . DIRECTORY_SEPARATOR . "acl.json";
    $aclData = @file_get_contents($inputFile);
    if (false === $aclData) {
        throw new Exception(sprintf("unable to read JSON file '%s' from disk", $inputFile));
    }
    $acl = json_decode($aclData, true);

    $convert = new SURFnet\janus\convert\Ssp($entities, $acl);
    $idpConfig = $convert->getIdps();
    $spConfig = $convert->getSps();

    $outputFile = $convertDir . DIRECTORY_SEPARATOR . "saml20-idp-remote.json";
    if (false === @file_put_contents($outputFile, json_encode($idpConfig))) {
        throw new Exception(sprintf("unable to write JSON file '%s' to disk", $outputFile));
    }

    $outputFile = $convertDir . DIRECTORY_SEPARATOR . "saml20-sp-remote.json";
    if (false === @file_put_contents($outputFile, json_encode($spConfig))) {
        throw new Exception(sprintf("unable to write JSON file '%s' to disk", $outputFile));
    }

} catch (Exception $e) {
    echo sprintf("ERROR: %s", $e->getMessage());
    die(PHP_EOL);
}

echo date("Y-m-d H:i:s") . " :: Janus entity data conversion done\n";

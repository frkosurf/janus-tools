<?php

namespace fkooman\janus\validate\validators;

use fkooman\janus\validate\Validate;
use fkooman\janus\validate\ValidateInterface;

class CheckAcs extends Validate implements ValidateInterface
{

    public function idp($entityData, $metadata, $allowedEntities, $blockedEntities, $disableConsent)
    {
    }

    public function sp($entityData, $metadata, $allowedEntities, $blockedEntities, $arp)
    {
        if (!isset($metadata['AssertionConsumerService'])) {
            $this->logWarn("no AssertionConsumerService");

            return;
        }
        foreach ($metadata['AssertionConsumerService'] as $k => $v) {
            $this->validateEndpoint('AssertionConsumerService', $k, $v);
        }
    }

    private function validateEndpoint($type, $k, array $v)
    {
        if (!isset($v['Location'])) {
            $this->logWarn(sprintf("%s Location field missing [%s]", $type, $k));

            return;
        }
        if (false === filter_var($v['Location'], FILTER_VALIDATE_URL, FILTER_FLAG_PATH_REQUIRED)) {
            $this->logWarn(sprintf("%s invalid Location [%s]", $type, $k));

            return;
        }
        if (0 !== strpos($v['Location'], "https://")) {
            $this->logWarn(sprintf("%s non SSL Location specified [%s]", $type, $k));

            return;
        }

        if (!isset($v['Binding'])) {
            $this->logWarn(sprintf("%s Binding field missing [%s]", $type, $k));

            return;
        }
    }
}

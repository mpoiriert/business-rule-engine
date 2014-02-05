<?php

namespace Nucleus\BusinessRuleEngine;

class RuleNotFoundException extends \InvalidArgumentException
{
    public static function formatMessage($ruleName)
    {
        return 'The rule named [' . $ruleName . '] was not found';
    }
}
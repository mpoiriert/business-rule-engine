<?php

namespace Nucleus\BusinessRuleEngine;


class InvalidRuleException extends \InvalidArgumentException
{
    public static function formatMessage($ruleName)
    {
        return 'The callable set for rule [' . $ruleName . '] is not valid';
    }
}
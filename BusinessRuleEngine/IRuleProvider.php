<?php

namespace Nucleus\BusinessRuleEngine;

interface IRuleProvider
{
    /**
     * Return a rule base on it's name
     *
     * @param $ruleName
     *
     * @throws RuleNotFoundException
     *
     * @return callable
     */
    public function getRule($ruleName);

    /**
     * Return if a rule is available or not
     *
     * @param $ruleName
     *
     * @return boolean
     */
    public function hasRule($ruleName);

    /**
     * Set the rule by it's name.
     *
     * @param $ruleName
     * @param $callable
     *
     * @throws InvalidRuleException
     *
     * @return void
     */
    public function setRule($ruleName, $callable);
}
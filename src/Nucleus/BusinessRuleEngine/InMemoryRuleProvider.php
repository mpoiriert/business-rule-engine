<?php

namespace Nucleus\BusinessRuleEngine;


class InMemoryRuleProvider implements IRuleProvider
{
    /**
     * @var callable[]
     */
    protected $rules;

    /**
     * Return a rule base on it's name
     *
     * @param $ruleName
     *
     * @throws RuleNotFoundException
     *
     * @return callable
     */
    public function getRule($ruleName)
    {
        if(!$this->hasRule($ruleName)) {
            throw new RuleNotFoundException(RuleNotFoundException::formatMessage($ruleName));
        }

        return $this->rules[$ruleName];
    }

    /**
     * Return if a rule is available or not
     *
     * @param $ruleName
     *
     * @return boolean
     */
    public function hasRule($ruleName)
    {
        return array_key_exists($ruleName,$this->rules);
    }

    /**
     * Set the rule by it's name.
     *
     * @param $ruleName
     * @param $callable
     *
     * @return void
     */
    public function setRule($ruleName, $callable)
    {
        if(!is_callable($callable)) {
            throw new InvalidRuleException(InvalidRuleException::formatMessage($ruleName));
        }
        $this->rules[$ruleName] = $callable;
    }
}
Business Rule Engine
====================

[![Build Status](https://api.travis-ci.org/mpoiriert/business-rule-engine.png?branch=master)](http://travis-ci.org/mpoiriert/business-rule-engine)

Engine that implement the specification pattern

To use the business engine you need to need to instantiate a IBusinessRuleEngine class and assign a IRuleProvider to it.
The IRuleProvider is responsible to provide rule base on their name. A rule is simply a callable with a name attach to it.

```PHP

$businessRuleEngine = new \Nucleus\BusinessRuleEngine\BusinessRuleEngine();

$ruleProvider = new \Nucleus\BusinessRuleEngine\InMemoryRuleProvider();

$businessRuleEngine->setRuleProvider($ruleProvider);

$ruleProvider->setRule('ruleTrue',function(){ return true; });
$ruleProvider->setRule('ruleFalse',function(){ return false; });

```

*A easy way to make a rule implement in a class that have this as it's only concern is to use the __invoke magic method.*

From there you can call the business rule engine methods with a rule specification

```PHP

$businessRuleEngine->check("ruleTrue");//true
$businessRuleEngine->check("ruleFalse");//false

$result = $businessRuleEngine->getFirstMatch(
  array(
    "check1" => "ruleFalse",
    "check2" => "ruleTrue",
  )
);

echo $result;//check2

$result = $businessRuleEngine->getFirstMatch(
  array(
    "check1" => "ruleFalse",
    "check2" => "ruleTrue",
    "check3" => "ruleTrue",
  )
);

var_export($result);// array('check2','check3')

```

### Rule Specification ###


A rule specification is a string concatenation of the rule name and the default parameter in Yaml. If you want to call the
rule toto with default parameter param1 = 1, param2 = array(1,2) it would look like this:

    "toto{param1:1,param2[1,2]}"

You can add a ! before the rule if you want to make it false so you don't have to pass a parameter to the rule to say
not match:

    "!toto{param1:1,param2[1,2]}"

If you want to check that 2 or more rules must validate you can pass a array of rule:

    array("toto{param1:1,param2[1,2]}","!toto{param1:1,param2[1,2]}")

Obviously the previous example will not pass since we are using the same rule with a not (!) in the second test.
This will result in (true && !true) or (false && !false) evaluation.

If you want to do a __OR__ check you can put this in a second array:

    array(array("toto{param1:1,param2[1,2]}","!toto{param1:1,param2[1,2]}"))

In this case the ending result will always result in a true result since the evaluation will be ((true || !true) or
    ((false || !false))

You can mix those:

    array(rule1{},array(rule2{},rule3{}))

This will result in ($rule1Result && ($rule2Result || $rule3Result))

This system is inspire from Symfony 1 credentials checks http://symfony.com/legacy/doc/reference/1_4/en/08-Security
and the specification pattern http://en.wikipedia.org/wiki/Specification_pattern

Check the unit test for more example of how to use it.


### 'Real' life usage ###

Here is some example of usage that could occur in real live. The implementation of class a rule are not available
within this library, this library is just the engine itself.


#### Security ####

Check some complex permission rule base on a object that can provide a permission list

```PHP

$businessRuleEngine->check(array("permission{name:user}","!permission{name:newUser}"),array($user));//user && !newUser

$businessRuleEngine->check(array(array("permission{name:admin}","permission{name:moderator}")),array($user));// admin || moderator

```

#### Payment Method ####

Check witch payment method to offer base on some order attribute

```PHP

$paymentMethodName = $businessRuleEngine->getFirstMatch(
    array(
        "cheapCharge" => array("orderCountry{countries:[CA,US]}","maxOrderPrice{max:9.99}"),
        "digitalProduct" => array("!orderContainPhysical"),
        "fallback" => array()
    ),
    $order
)

```

#### Poker Card Game ####

Base on a hand witch have the biggest value. This is not complete but show how it could be use

```PHP

$rules = array(
    array("cardsValues{values:[10,11,12,13,A]}","cardsSameColor"),
    array("cardsStraight","cardsSameColor"),
    array("cardsSameValue{amountOfCards:[4]}"),
    array("cardsSameValue{amountOfCards:[3,2]"),
    array("cardsSameColor"),
    array("cardsStraight"),
    array("cardsSameValue{amountOfCards:[3]"),
    array("cardsSameValue{amountOfCards:[2,2]"),
    array("cardsSameValue{amountOfCards:[2]"),
    array(),
);

$positionHand1 = $businessRuleEngine->getFirstMatch($rules,$hand1);
$positionHand2 = $businessRuleEngine->getFirstMatch($rules,$hand2);

if($positionHand1 == $positionHand2) {
    if($businessRuleEngine->check('firstHandHighestCardValue',array('hand1'=>$hand1,'hand2'=>$hand2))) {
        $winner = 'hand1';
    } else {
        $winner = 'hand2';
    }
} else {
    $winner = $positionHand1 < $positionHand2 ? 'hand1' : 'hand2';
}

```

#### Promotion Display ####

If you display a promotion base on a user. Completely invented rules...

```PHP

$businessRuleEngine->check(
   array('lastTransaction{delay: - 1 months}','lastVisit{delay: -5 days}',array('totalPurchaseLessThan{amount:20.95}','fromCountry{countries[CA,US]}')),
   $user
);

```

This would mean that you display the promotion if

the last transaction of the user have been done more than 1 months ago

AND

the last visit of the user have been done more thant 5 days ago

AND

(the user have purchase for less the 20.95 in total

OR

in coming from country in the list CA and US)

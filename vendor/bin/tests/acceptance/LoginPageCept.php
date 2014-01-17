<?php

$I = new WebGuy($scenario);
$I->wantTo('Ensure that login page works');
$I->amOnPage('/login');
$I->see('Uren');
$I->fillField('Username', 'student');
$I->fillField('Password', 'student');
$I->click('Login');
$I->amOnPage('/student/1');
$I->see('Student Page');
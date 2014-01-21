<?php

class StudentCest {

	/**
	 * Test for requirement 10 of student
	 * @param  WebGuy $I [description]
	 * @return [type]    [description]
	 */
	public function urenInvullen(\WebGuy $I) {
		$I->amOnPage('/login');
		$I->see('Uren');
		$I->fillField('Username', 'student');
		$I->fillField('Password', 'student');
		$I->click('Login');
		$I->amOnPage('/student/1');
		$I->see('Student Page');
		$I->amOnPage('/student/1/uren/add');
		$I->submitForm('#ureninvullen', array(
			'cursus' => 'SOP',
			'onderdeel' => '1',
			'date' => '19-01-2014',
			'studielast', '1000',
			));
	}

	/**
	 * Test for requirement 20 of student
	 * @param  WebGuy $I [description]
	 * @return [type]    [description]
	 */
	public function gemiddeldeOverzichtInzien(\WebGuy $I) {
		$I->amOnPage('/login');
		$I->see('Uren');
		$I->fillField('Username', 'student');
		$I->fillField('Password', 'student');
		$I->click('Login');
		$I->amOnPage('/student/1');
		$I->see('Student Page');
		$I->click('Overzicht');
		$I->sendPOST('/student/1/overzicht', array(
			'week' => '51-2013',
			));
		$I->see('Cursus');
		$I->see('Uren voor week 51');
	}

	/**
	 * Test for requirement 30 of student TODO
	 * @param  WebGuy $I [description]
	 * @return [type]    [description]
	 */
	public function gemiddeldeOverzichtAfdrukken(\WebGuy $I) {
		$I->amOnPage('/login');
		$I->see('Uren');
		$I->fillField('Username', 'student');
		$I->fillField('Password', 'student');
		$I->click('Login');
		$I->amOnPage('/student/1');
		$I->see('Student Page');
		$I->click('Overzicht');
		// POST not working, if I can't get this to work then this test will always fails
		$I->sendPOST('/student/1/overzicht', array(
			'week' => '51-2013',
			));
		$I->see('Cursus');
		$I->see('Uren voor week 51');
	}

	/**
	 * Test for requirement 40 of student
	 * @param  WebGuy $I [description]
	 */
	public function feedbackInzien(\WebGuy $I) {
		$I->amOnPage('/login');
		$I->see('Uren');
		$I->fillField('Username', 'student');
		$I->fillField('Password', 'student');
		$I->click('Login');
		$I->amOnPage('/student/1');
		$I->see('Student Page');
		$I->click('Feedback');
		$I->see('Goed gewerkt');
		$I->see('Goed gewerkt2');
	}

	/**
	 * Test for requirement 50 of student
	 * @param  WebGuy $I [description]
	 * @return [type]    [description]
	 */
	public function profielInzienStudent(\WebGuy $I) {
		$I->amOnPage('/login');
		$I->see('Uren');
		$I->fillField('Username', 'student');
		$I->fillField('Password', 'student');
		$I->click('Login');
		$I->amOnPage('/student/1');
		$I->see('Student Page');
		$I->wantTo('Profiel Inzien');
		$I->click('Profiel');
		$I->amOnPage('/student/1/profiel');
		$I->see('student');
		$I->see('I12R');
	}
}
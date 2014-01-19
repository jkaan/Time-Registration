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
		$I->selectOption('cursus', 'SOP');
		// Doesn't work becuase after you choose cursus the onderdeel part is populated but the test doesn't wait for that.
		$I->selectOption('onderdeel', 'Urenregistratie');
		$I->fillField('Datum', '18-01-2014');
		$I->fillField('Studielast', '2500');
		$I->click('Opslaan');
		$I->seeInDatabase('Uren', array(
			'uren_Date' => '2014-01-08',
			'uren_Studielast' => '2500',
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
		// POST not working, if I can't get this to work then this test will always fails
		$I->sendPOST('/student/1/overzicht', array(
			'week' => '51-2013',
			));
		$I->see('Cursus');
		$I->see('Uren voor week 51');
	}

	/**
	 * Test for requirement 30 of student
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
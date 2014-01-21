<?php

class DocentCest {

	/**
	 * Test for requirement 60 of docent
	 * @param  WebGuy $I [description]
	 * @return [type]    [description]
	 */
	public function feedbackGeven(\WebGuy $I) {
		$I->wantTo('Feedback geven aan student');
		$I->amOnPage('/login');
		$I->see('Uren');
		$I->fillField('Username', 'docent');
		$I->fillField('Password', 'docent');
		$I->click('Login');
		$I->amOnPage('/docent/2');
		$I->see('Docent Page');
		$I->sendPOST('/docent/2/overzicht', array(
			'week' => '51-2013',
			));
		$I->see('student');
		$I->amOnPage('/docent/2/overzicht/feedback/1-51-1');
		$I->fillField('feedback', 'Goed gedaan man!');
		$I->click('Opslaan');
	}

	/**
	 * Test for requirement 70 of docent
	 */
	public function gemiddeldeOverzichtGenereren(\WebGuy $I) {
		$I->wantTo('Gemiddelde overzicht genereren');
		$I->amOnPage('/login');
		$I->see('Uren');
		$I->fillField('Username', 'docent');
		$I->fillField('Password', 'docent');
		$I->click('Login');
		$I->amOnPage('/docent/2');
		$I->see('Docent Page');
		$I->sendPOST('/docent/2/overzicht', array(
			'week' => '51-2013',
			));
		$I->see('student');
		$I->see('1:07');
	}

	/**
	 * Test for requirement 75 of docent
	 * @param  WebGuy $I [description]
	 * @return [type]    [description]
	 */
	public function gemiddeldeOverzichtAfdrukken(\WebGuy $I) {
		$I->wantTo('Gemiddelde overzicht afdrukken');
		$I->amOnPage('/login');
		$I->see('Uren');
		$I->fillField('Username', 'docent');
		$I->fillField('Password', 'docent');
		$I->click('Login');
		$I->amOnPage('/docent/2');
		$I->see('Docent Page');
		// Will not work because need to submit info through POST
	}

	/**
	 * Test for requirement 80 of docent
	 * @param  WebGuy $I [description]
	 * @return [type]    [description]
	 */
	public function onderdeelBeheren(\WebGuy $I) {
		$I->wantTo('Onderdeel beheren');
		$I->amOnPage('/login');
		$I->see('Uren');
		$I->fillField('Username', 'docent');
		$I->fillField('Password', 'docent');
		$I->click('Login');
		$I->amOnPage('/docent/2');
		$I->see('Docent Page');
		$I->click('Cursus beheer');
		// Toevoegen
		$I->amOnPage('/docent/2/cursus/2/onderdelen');
		$I->fillField('onderdeelNaam', 'Test');
		$I->fillField('onderdeelNorm', '100');
		$I->click('Voeg toe');
		$I->see('Test');
		$I->see('100');
		// Wijzigen
		$I->amOnPage('docent/2/cursus/2/onderdelen/10/edit');
		$I->fillField('onderdeelNaam', 'Test1234');
		$I->fillField('onderdeelNorm', '111');
		$I->click('Opslaan');
		$I->see('Test1234');
		$I->see('111');
	}

	/**
	 * Test for requirement 90 of docent
	 * @param  WebGuy $I [description]
	 * @return [type]    [description]
	 */
	public function gebruikersProfielInzien(\WebGuy $I) {
		$I->wantTo('Gebruikers profiel inzien');
		$I->amOnPage('/login');
		$I->see('Uren');
		$I->fillField('Username', 'docent');
		$I->fillField('Password', 'docent');
		$I->click('Login');
		$I->amOnPage('/docent/2');
		$I->see('Docent Page');
		$I->click('Gebruikersbeheer');
		$I->amOnPage('/docent/2/gebruikers');
		$I->amOnPage('/docent/2/gebruikers/1');
		$I->see('Student');
		$I->see('10');
		$I->see('student@student.nl');
		$I->see('I12R');
	}
}
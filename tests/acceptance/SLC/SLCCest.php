<?php

class SLCCest {

	/**
	 * Test for requirement 100 of SLC
	 * @param  WebGuy $I [description]
	 * @return [type]    [description]
	 */
	public function gebruikerActiefInactiefZetten(\WebGuy $I) {
		$I->amOnPage('/login');
		$I->see('Uren');
		$I->fillField('Username', 'slc');
		$I->fillField('Password', 'slc');
		$I->click('Login');
		$I->amOnPage('/slc/3');
		$I->see('SLC');
		$I->amOnPage('/slc/3/student/edit/1');
		$I->click('Opslaan');
	}

	/**
	 * Test for requirement 110 of student
	 * @param  WebGuy $I [description]
	 * @return [type]    [description]
	 */
	public function cursusBeheren(\WebGuy $I) {
		$I->amOnPage('/login');
		$I->see('Uren');
		$I->fillField('Username', 'slc');
		$I->fillField('Password', 'slc');
		$I->click('Login');
		$I->amOnPage('/slc/3');
		$I->see('SLC');
		$I->amOnPage('/slc/3/course/add');
		$I->fillField('Naam', 'TestCursus');
		$I->fillFIeld('Cursuscode', '123456789');
		$I->click('Opslaan');
		$I->amOnPage('/slc/3');
	}

	/**
	 * Test for requirement 120 of SLC
	 * @param  WebGuy $I [description]
	 * @return [type]    [description]
	 */
	public function gebruikerBeheren(\WebGuy $I) {
		$I->amOnPage('/login');
		$I->see('Uren');
		$I->fillField('Username', 'slc');
		$I->fillField('Password', 'slc');
		$I->click('Login');
		$I->amOnPage('/slc/3');
		$I->see('SLC');
		$I->amOnPage('/slc/3/student/add');
		$I->fillField('Student Naam', 'TestStudent');
		$I->fillField('Student Code', '1234');
		$I->fillField('Student Email', 'test@test.nl');
		$I->fillField('Student Paswoord', '123123');
		$I->fillFIeld('Student Klas', 'I12R');
		$I->click('Opslaan');
		$I->amOnPage('/slc/3');
	}

	/**
	 * Test for requirement 130 of SLC
	 * @param  WebGuy $I [description]
	 */
	public function gebruikerKoppelenAanCursus(\WebGuy $I) {
		$I->amOnPage('/login');
		$I->see('Uren');
		$I->fillField('Username', 'slc');
		$I->fillField('Password', 'slc');
		$I->click('Login');
		$I->amOnPage('/slc/3');
		$I->amOnPage('/slc/3/course/students/1');
		$I->click('Voeg toe');
		$I->amOnPage('/slc/3/course/students/1');
		$I->see('student');
	}
}
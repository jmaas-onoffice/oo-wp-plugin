<?php

/**
 *
 *    Copyright (C) 2021 onOffice GmbH
 *
 *    This program is free software: you can redistribute it and/or modify
 *    it under the terms of the GNU Affero General Public License as published by
 *    the Free Software Foundation, either version 3 of the License, or
 *    (at your option) any later version.
 *
 *    This program is distributed in the hope that it will be useful,
 *    but WITHOUT ANY WARRANTY; without even the implied warranty of
 *    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *    GNU Affero General Public License for more details.
 *
 *    You should have received a copy of the GNU Affero General Public License
 *    along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

declare (strict_types=1);

namespace onOffice\tests;

use onOffice\WPlugin\Renderer\InputFieldCheckboxRenderer;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class TestClassInputFieldCheckboxRenderer
	extends \WP_UnitTestCase
{
	/**
	 *
	 */
	public function testRenderHintText()
	{
		$pSubject = new InputFieldCheckboxRenderer('oopluginforms-createaddress', '1');
		$txtHint = 'Test Content Hint';
		$pSubject->setHint($txtHint);
		ob_start();
		$pSubject->render();
		$output = ob_get_clean();
		$this->assertEquals('<input type="checkbox" name="oopluginforms-createaddress" value="1" class="onoffice-input" id="checkbox_8"><p class="hint-text">Test Content Hint</p>',
			$output);
	}
}
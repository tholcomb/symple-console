<?php
/**
 * This file is part of the Symple framework
 *
 * Copyright (c) Tyler Holcomb <tyler@tholcomb.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tholcomb\Symple\Console;

class FrozenConsoleException extends \LogicException {
	public function __construct()
	{
		parent::__construct('The console has already been instantiated');
	}
}
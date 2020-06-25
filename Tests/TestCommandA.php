<?php
/**
 * This file is part of the Symple framework
 *
 * Copyright (c) Tyler Holcomb <tyler@tholcomb.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tholcomb\Symple\Console\Tests;

use Tholcomb\Symple\Console\Commands\AbstractCommand;

class TestCommandA extends AbstractCommand {
	protected const NAME = 'test.cmd.a';
}
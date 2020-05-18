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

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Tholcomb\Symple\Console\LazyCommandInterface;

class TestCommandA extends Command implements LazyCommandInterface {
	protected const NAME = 'test.cmd.a';

	public static function getLazyName(): string
	{
		return static::NAME;
	}

	public function __construct()
	{
		parent::__construct(static::NAME);
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		// Blank
	}
}
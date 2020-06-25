<?php
/**
 * This file is part of the Symple framework
 *
 * Copyright (c) Tyler Holcomb <tyler@tholcomb.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tholcomb\Symple\Console\Commands;

use Symfony\Component\Console\Command\Command;
use Tholcomb\Symple\Console\LazyCommandInterface;

abstract class AbstractCommand extends Command implements LazyCommandInterface {
	protected const NAME = 'symple:command';

	public function __construct()
	{
		parent::__construct(static::NAME);
	}

	public static function getLazyName(): string
	{
		return static::NAME;
	}
}
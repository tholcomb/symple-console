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

use PHPUnit\Framework\TestCase;
use Pimple\Container;
use Symfony\Component\Console\Exception\CommandNotFoundException;
use Tholcomb\Symple\Console\Commands\CacheCommand;
use Tholcomb\Symple\Console\Commands\ContainerDebugCommand;
use Tholcomb\Symple\Console\ConsoleProvider;
use Tholcomb\Symple\Console\FrozenConsoleException;

class ConsoleTest extends TestCase {
	private function getContainer(): Container
	{
		$c = new Container();
		$c->register(new ConsoleProvider('Test Console', date('c')));

		return $c;
	}

	public function testLazyCommands() {
		$c = $this->getContainer();
		$arr = [];
		ConsoleProvider::addCommand($c, TestCommandA::class, function () use (&$arr) {
			$arr[TestCommandA::getLazyName()] = true;
			return new TestCommandA();
		});
		ConsoleProvider::addCommand($c, TestCommandB::class, function () use (&$arr) {
			$arr[TestCommandB::getLazyName()] = true;
			return new TestCommandB();
		});
		$app = ConsoleProvider::getConsole($c);
		$app->get(TestCommandA::getLazyName());
		$this->assertTrue(isset($arr[TestCommandA::getLazyName()]), 'cmd A not loaded');
		$this->assertFalse(isset($arr[TestCommandB::getLazyName()]), 'cmd B loaded early');
		$app->get(TestCommandB::getLazyName());
		$this->assertTrue(isset($arr[TestCommandB::getLazyName()]), 'cmd B not loaded');
	}

	public function testPreventDoubleCommand() {
		$this->expectException(\LogicException::class);

		$c = $this->getContainer();
		ConsoleProvider::addCommand($c, TestCommandA::class, function () {});
		ConsoleProvider::addCommand($c, TestCommandA::class, function () {});
	}

	public function testFrozenConsole() {
		$this->expectException(FrozenConsoleException::class);

		$c = $this->getContainer();
		ConsoleProvider::getConsole($c);
		ConsoleProvider::addCommand($c, TestCommandA::class, function () {});
	}

	public function testAddBuiltins() {
		$c = $this->getContainer();
		ConsoleProvider::addBuiltinCommands($c);
		$app = ConsoleProvider::getConsole($c);
		$builtins = [
			ContainerDebugCommand::class,
			CacheCommand::class,
		];
		foreach ($builtins as $b) {
			try {
				$a = $app->get(call_user_func([$b, 'getLazyName']));
			} catch (CommandNotFoundException $e) {
				$a = null;
			}
			$this->assertInstanceOf($b, $a, "Did not get command: '$b'");
		}
	}
}
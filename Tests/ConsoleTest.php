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
use Symfony\Component\Console\Application;
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
		$app = $c[ConsoleProvider::KEY_CONSOLE];
		if (!$app instanceof Application) {
			throw new \LogicException(sprintf("Did not get '%s'", Application::class));
		}
		$app->get(TestCommandA::getLazyName());
		$this->assertTrue(isset($arr[TestCommandA::getLazyName()]), 'cmd A not loaded');
		$this->assertFalse(isset($arr[TestCommandB::getLazyName()]), 'cmd B loaded early');
		$app->get(TestCommandB::getLazyName());
		$this->assertTrue(isset($arr[TestCommandB::getLazyName()]), 'cmd B not loaded');
	}

	public function testPreventDoubleCommand() {
		$c = $this->getContainer();
		ConsoleProvider::addCommand($c, TestCommandA::class, function () {});
		$caught = false;
		try {
			ConsoleProvider::addCommand($c, TestCommandA::class, function () {});
		} catch (\Throwable|\Exception $e) {
			$caught = true;
			$this->assertTrue($e instanceof \LogicException, 'Exception not LogicException');
		}
		$this->assertTrue($caught, 'Did not catch exception');
	}

	public function testFrozenConsole() {
		$c = $this->getContainer();
		$app = $c[ConsoleProvider::KEY_CONSOLE];
		$caught = false;
		try {
			ConsoleProvider::addCommand($c, TestCommandA::class, function () {});
		} catch (FrozenConsoleException $e) {
			$caught = true;
		}
		$this->assertTrue($caught, 'Did not catch exception');
	}

	public function testContainerDebugAdd() {
		$c = $this->getContainer();
		ConsoleProvider::addContainerDebugCommand($c);
		$app = ConsoleProvider::getConsole($c);
		$a = $app->get(ContainerDebugCommand::getLazyName());
		$this->assertTrue($a instanceof ContainerDebugCommand, 'Did not get command');
	}
}
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

use Pimple\Container;
use Pimple\Psr11\ServiceLocator;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\CommandLoader\FactoryCommandLoader;
use Tholcomb\Symple\Console\Commands\ContainerDebugCommand;
use Tholcomb\Symple\Core\AbstractProvider;
use Tholcomb\Symple\Core\UnregisteredProviderException;
use function Tholcomb\Symple\Core\class_implements_interface;

class ConsoleProvider extends AbstractProvider {
	public const KEY_CONSOLE = 'console.app';
	protected const PREFIX_CONSOLE_COMMANDS = 'console.command.';
	protected const NAME = 'console';

	private $name;
	private $version;

	public function __construct(string $name = '', string $version = '')
	{
		$this->name = $name;
		$this->version = $version;
	}

	public function register(Container $c)
	{
		parent::register($c);
		$c['console.app.name'] = $this->name;
		$c['console.app.version'] = $this->version;
		$c['console.isFrozen'] = false;
		$c['console.commands'] = [];
		$c[static::KEY_CONSOLE] = function ($c) {
			$c['console.isFrozen'] = true;
			$app = new Application($c['console.app.name'], $c['console.app.version']);
			$app->setCatchExceptions(true);
			$app->setCommandLoader(new FactoryCommandLoader($c['console.commands']));

			return $app;
		};
	}

	public static function getConsole(Container $c): Application
	{
		if (!isset($c[static::KEY_CONSOLE])) {
			throw new UnregisteredProviderException(static::class);
		}
		return $c[static::KEY_CONSOLE];
	}

	public static function addCommand(Container $c, string $class, callable $definition, ?string $forceName = null): void
	{
		if ($c['console.isFrozen'] !== false) throw new FrozenConsoleException();
		if (!class_implements_interface($class, LazyCommandInterface::class)) {
			throw new \InvalidArgumentException(sprintf('Class "%s" does not implement interface "%s"', $class, LazyCommandInterface::class));
		}
		if (!is_subclass_of($class, Command::class)) {
			throw new \InvalidArgumentException(sprintf('Class "%s" is not subclass of "%s"', $class, Command::class));
		}
		$name = ($forceName !== null) ? $forceName : call_user_func([$class, 'getLazyName']); // LazyCommandInterface
		if (isset($c['console.commands'][$name])) throw new \InvalidArgumentException("Command '{$name}' already defined");
		$key = static::PREFIX_CONSOLE_COMMANDS . $name;
		$c[$key] = $definition;
		$loc = new ServiceLocator($c, ['cmd' => $key]);
		$arr = [$name => function () use ($loc) { return $loc->get('cmd'); }];
		$c['console.commands'] = array_merge($c['console.commands'], $arr);
	}

	public static function addContainerDebugCommand(Container $c): void
	{
		self::addCommand($c, ContainerDebugCommand::class, function ($c) {
			return new ContainerDebugCommand($c);
		});
	}
}
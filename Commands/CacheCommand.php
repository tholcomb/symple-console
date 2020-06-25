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

use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Tholcomb\Symple\Core\Cache\SympleCacheContainer;

class CacheCommand extends AbstractCommand {
	protected const NAME = 'symple:cache';

	private $caches;
	private $errors = [];

	public function __construct(SympleCacheContainer $caches)
	{
		parent::__construct();
		$this->caches = $caches;
	}

	protected function configure()
	{
		$this->setDescription('Perform cache operations');
		$this->addArgument('action', InputArgument::REQUIRED, implode(' | ', ['list','warm','clear']));
		$this->addArgument('indices', InputArgument::OPTIONAL | InputArgument::IS_ARRAY, 'Optionally specify caches');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$cmd = $input->getArgument('action');
		$indices = [];
		foreach ($input->getArgument('indices') as $i) {
			$indices[] = (int)$i;
		}
		switch ($cmd) {
			case 'list':
				$this->listCaches($output);
				return 0;
			case 'clear':
				$verb = 'Cleared';
				$num = $this->clearCaches($indices);
				break;
			case 'warm':
				$verb = 'Warmed';
				$num = $this->warmCaches($indices);
				break;
			default:
				throw new \InvalidArgumentException("Invalid action '{$cmd}'");
		}
		$this->printErrors($output);
		$output->writeln("$verb $num caches.");

		return 0;
	}

	private function clearCaches(array $indices): int
	{
		$i = 0;
		foreach ($this->caches->getCaches() as $k=>$c) {
			if (!empty($indices) && !in_array($k, $indices)) continue;
			try {
				$c->clearCache();
				$i++;
			} catch (\Exception $e) {
				$this->handleException($e);
			}
		}

		return $i;
	}

	private function warmCaches(array $indices): int
	{
		$i = 0;
		foreach ($this->caches->getCaches() as $k=>$c) {
			if (!empty($indices) && !in_array($k, $indices)) continue;
			try {
				$c->warmCache();
				$i++;
			} catch (\Exception $e) {
				$this->handleException($e);
			}
		}

		return $i;
	}

	private function listCaches(OutputInterface $out): void
	{
		$table = new Table($out);
		$table->setHeaders(['index', 'name', 'location']);
		foreach ($this->caches->getCaches() as $k=>$c) {
			$table->addRow([$k, get_class($c), $c->getCacheLocation() ?: 'null']);
		}
		$table->render();
	}

	private function handleException(\Exception $e): void
	{
		$this->errors[] = substr($e->__toString(), 0, strpos($e->__toString(), "\n"));
	}

	private function printErrors(OutputInterface $out): void
	{
		foreach ($this->errors as $e) {
			if ($out instanceof ConsoleOutput) {
				$out->getErrorOutput()->writeln($e);
			} else {
				$out->writeln($e);
			}
		}
		$this->errors = [];
	}
}
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

use Pimple\Container;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ContainerDebugCommand extends AbstractCommand {
	protected const NAME = 'pimple.debug';

	private $c;

	public function __construct(Container $c)
	{
		parent::__construct();
		$this->c = $c;
	}

	protected function configure()
	{
		$this->setDescription('Outputs the container');
		$this->addArgument('component', InputArgument::OPTIONAL, 'Limit results');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$component = $input->getArgument('component');
		$rows = [];
		foreach ($this->c->keys() as $k) {
			if ($component !== null && !preg_match("/^{$component}\./", $k)) continue;
			$res = $this->c[$k];
			$type = gettype($res);
			switch ($type) {
				case 'object':
					$val = get_class($res);
					break;
				case 'array':
					$val = json_encode($res);
					break;
				default:
					$val = $res;
			}
			$rows[$k] = [$k, $type, $val];
		}
		ksort($rows);
		$table = new Table($output);
		$table->setHeaders(['key', 'type', 'val'])->setRows($rows);
		$table->render();

		return 0;
	}
}
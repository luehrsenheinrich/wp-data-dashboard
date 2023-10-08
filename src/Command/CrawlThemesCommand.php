<?php
/**
 * The class that registers a cli command to crawl the WordPress.org themes API.
 */

declare(strict_types=1);

namespace App\Command;

use App\Service\WpOrgApiCrawlService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:crawl:themes')]
class CrawlThemesCommand extends Command
{
	public function __construct(
		private WpOrgApiCrawlService $crawlService,
	) {
		parent::__construct();
	}

	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		try {
			$themes = $this->crawlService->crawlThemes();
			if ($themes) {
				$output->writeln("Crawled themes from page {$themes['info']['page']} of {$themes['info']['pages']}.");
			} else {
				$output->writeln('No themes crawled.');
			}
		} catch (\Exception $e) {
			$output->writeln($e->getMessage());
			return Command::FAILURE;
		}

		return Command::SUCCESS;
	}
}

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
		$this->crawlService->maybeCrawlThemeTags();
		$this->crawlService->maybeCrawlThemeInfos();
		$this->crawlService->maybeCrawlThemeStats();

		return Command::SUCCESS;
	}
}

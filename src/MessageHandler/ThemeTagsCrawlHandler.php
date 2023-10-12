<?php
/**
 * The handler that handles the theme tags crawl.
 */

declare(strict_types=1);

namespace App\MessageHandler;

use App\Message\ThemeTagsCrawl;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class ThemeTagsCrawlHandler
{
	public function __construct()
	{
	}

	/**
	 * Handle the message.
	 *
	 * @param ThemeTagsCrawl $message The message.
	 *
	 * @return void
	 */
	public function __invoke(ThemeTagsCrawl $message): void
	{
	}
}

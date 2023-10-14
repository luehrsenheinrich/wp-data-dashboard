<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
	name: 'app:update-version',
	description: 'Updates the version of the application',
)]
class UpdateVersionCommand extends Command
{
	protected function execute(InputInterface $input, OutputInterface $output): int
	{

		/**
		 * Read the services.yaml file.
		 */
		$servicesYaml = file_get_contents(__DIR__.'/../../config/services.yaml');

		/**
		 * Read the package.json file.
		 */
		$packageJson = file_get_contents(__DIR__.'/../../package.json');

		/**
		 * Parse the package.json file.
		 */
		$packageJson = json_decode($packageJson, true);

		/**
		 * Get the version from the package.json file.
		 */
		$version = $packageJson['version'];

		/**
		 * Update the version configuration.
		 * The config is called 'app.version' and is a semver string.
		 */
		$servicesYaml = preg_replace('/app\.version: \'[0-9]+\.[0-9]+\.[0-9]+\'/', 'app.version: \''.$version.'\'', $servicesYaml);

		/**
		 * Write the services.yaml file.
		 */
		file_put_contents(__DIR__.'/../../config/services.yaml', $servicesYaml);

		$output->writeln('Version updated!');

		return Command::SUCCESS;
	}
}

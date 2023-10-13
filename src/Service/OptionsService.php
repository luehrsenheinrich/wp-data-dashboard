<?php
/**
 * A class that handles our mutable options.
 */

declare(strict_types=1);

namespace App\Service;

use App\Entity\Options;
use App\Options\ThemeInfosCrawlerStateOption;
use App\Options\ThemeTagsCrawlerStateOption;
use App\Options\ThemeStatsCrawlerStateOption;
use App\Repository\OptionsRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class OptionsService
{

	/**
	 * The serializer.
	 *
	 * @var Serializer
	 */
	private Serializer $serializer;

	/**
	 * Constructor.
	 */
	public function __construct(
		private ManagerRegistry $doctrine
	) {
		$normalizers = [new DateTimeNormalizer(), new ObjectNormalizer()];
		$encoders = [];
		$this->serializer = new Serializer($normalizers, $encoders);
	}

	/**
	 * The options map.
	 *
	 * @var array
	 */
	private array $optionsMap = [
		'theme_info_crawler_state' => ThemeInfosCrawlerStateOption::class,
		'theme_tags_crawler_state' => ThemeTagsCrawlerStateOption::class,
		'theme_stats_crawler_state' => ThemeStatsCrawlerStateOption::class,
	];

	/**
	 * Get the option class by the option key.
	 *
	 * @param string $key The option key.
	 *
	 * @return string|null
	 */
	public function getOptionClass(string $key): ?string
	{
		return $this->optionsMap[$key] ?? null;
	}

	/**
	 * Get all available option classes.
	 *
	 * @return array
	 */
	public function getOptionClasses(): array
	{
		return array_keys($this->optionsMap);
	}

	/**
	 * Get the option key by the option class.
	 *
	 * @param string $class The option class.
	 *
	 * @return string|null
	 */
	public function getOptionKey(string $class): ?string
	{
		return array_search($class, $this->optionsMap, true) ?: null;
	}

	/**
	 * Get an option from the database by the option key.
	 */
	public function get(string $key): ?object
	{
		$optionClass = $this->getOptionClass($key);

		/**
		 * @var OptionsRepository
		 */
		$optionsRepository = $this->doctrine->getRepository(Options::class);

		if ($optionClass === null) {
			return null;
		}

		$option = $optionsRepository->getOptionByKey($key);

		// The repository returns an object, but we need an instance of the option class. Normalize it.
		$optionObject = $this->serializer->denormalize($option?->getValue() ?? null, $optionClass);

		return $optionObject;
	}

	/**
	 * Set an option in the database.
	 *
	 * @param OptionsInterface $option The option to set.
	 */
	public function set(object $option): void
	{
		$optionKey = $this->getOptionKey(get_class($option));

		if ($optionKey === null) {
			return;
		}

		/**
		 * @var OptionsRepository
		 */
		$optionsRepository = $this->doctrine->getRepository(Options::class);

		/**
		 * The option entity.
		 */
		$optionEntity = $optionsRepository->getOptionByKey($optionKey);

		if ($optionEntity === null) {
			$optionEntity = new Options();
		}

		$value = $this->serializer->normalize($option);

		$optionEntity->setType($optionKey);
		$optionEntity->setValue($value);

		$entityManager = $this->doctrine->getManager();
		$entityManager->persist($optionEntity);
		$entityManager->flush();
	}
}

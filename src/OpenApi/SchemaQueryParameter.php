<?php

namespace App\OpenApi;

use OpenApi\Analysis;
use OpenApi\Annotations\Operation;
use OpenApi\Annotations as OA;
use OpenApi\Generator;
use Nelmio\ApiDocBundle\OpenApiPhp\Util;

/**
 * Custom processor to translate the vendor tag `` into query parameter annotations.
 *
 * Details for the parameters are taken from the referenced schema.
 */
class SchemaQueryParameter
{
	const X_QUERY_AGS_REF = 'query-args-explode';

	/**
	 * @param Analysis $analysis
	 */
	protected $analysis;

	public function __invoke(Analysis $analysis)
	{
		$this->analysis = $analysis;

		/** @var OA\Parameter[] $schemas */
		$parameters = $analysis->getAnnotationsOfType(OA\Parameter::class);

		/** @var OA\Operation[] $operations */
		$operations = $analysis->getAnnotationsOfType(OA\Operation::class);


		foreach ($operations as $operation) {
			if ($operation->x !== GENERATOR::UNDEFINED && array_key_exists(self::X_QUERY_AGS_REF, $operation->x)) {
				// Check if the ref exists and is a string.
				if (is_string($operation->x[self::X_QUERY_AGS_REF]->ref)) {
					$schema = $this->schemaForRef($operation->x[self::X_QUERY_AGS_REF]->ref);

					// Check if the schema is of type 'OA\Schema' and if it has properties.
					if ($schema && $schema instanceof OA\Schema && $schema->properties !== GENERATOR::UNDEFINED) {
						$this->expandQueryArgs($operation, $schema);
						$this->cleanUp($operation, $schema);
					}
				}
			}
		}
	}

	/**
	 * Find schema for the given ref.
	 *
	 * @param Schema[] $schemas
	 * @param string $ref
	 */
	protected function schemaForRef(string $ref)
	{
		$name = str_replace(OA\Components::SCHEMA_REF, '', $ref);
		$schema = Util::getSchema($this->analysis->openapi, $name);

		if ($schema) {
			return $schema;
		}

		return null;
	}

	/**
	 * Expand the given operation by injecting parameters for all properties of the given schema.
	 */
	protected function expandQueryArgs(Operation $operation, OA\Schema $schema)
	{

		$operation->parameters = $operation->parameters === GENERATOR::UNDEFINED ? [] : $operation->parameters;

		// Extract the properties from the schema.
		$properties = $schema->properties;

		// Loop through the properties and create a parameter for each.
		foreach ($properties as $property) {
			if (!($property instanceof OA\Property)) {
				continue;
			}

			$parameterName = $property->property;

			// If the property is an array, we need to add the [] to the name.
			if ($property->type === 'array') {
				$parameterName .= '[]';
			}

			$parameter = new OA\Parameter([
				'name' => $parameterName,
				'in' => 'query',
				'required' => $property->required,
				'description' => $property->description,
				'schema' => $property,
			]);

			$operation->parameters[] = $parameter;
		}
	}

	/**
	 * Clean up.
	 */
	protected function cleanUp(OA\Operation $operation, OA\Schema $schema)
	{

		/** @var OA\OpenApi */
		$api = $this->analysis->openapi;

		// Find the key for the schema.
		$key = null;
		foreach ($api->components->schemas as $k => $v) {
			if ($v === $schema) {
				$key = $k;
				break;
			}
		}

		// Remove the schema from the components.
		if ($key !== null) {
			unset($api->components->schemas[$key]);
		}

		unset($operation->x[self::X_QUERY_AGS_REF]);
		if (!$operation->x) {
			$operation->x = GENERATOR::UNDEFINED;
		}
	}

	/**
	 * Helper function to check if a given values is "undefined" in the context of the OpenApiPhp library.
	 */
	protected function isUndefined($value)
	{
		return $value === GENERATOR::UNDEFINED;
	}
}

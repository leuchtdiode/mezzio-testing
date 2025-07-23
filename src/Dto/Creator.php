<?php
namespace Testing\Dto;

use Doctrine\ORM\EntityManager;
use Psr\Container\ContainerInterface;
use Throwable;

abstract class Creator
{
	protected static ContainerInterface $serviceManager;

	private array $data = [];

	public static function setServiceManager(ContainerInterface $serviceManager): void
	{
		self::$serviceManager = $serviceManager;
	}

	abstract public static function getInstance();

	abstract protected function getDto(array $data): mixed;

	abstract protected function getEntityClass(): string;

	abstract protected function getDefaultData(): array;

	abstract protected function createDto(mixed $entity): mixed;

	public function setData(array $data): void
	{
		$this->data = $data;
	}

	/**
	 * @param $name
	 * @param $arguments
	 * @return static
	 */
	public function __call($name, $arguments)
	{
		$property = lcfirst(
			str_replace(
				'set',
				'',
				$name
			)
		);

		$this->data[$property] = $arguments[0];

		return $this;
	}

	/**
	 * @throws Throwable
	 */
	public function create(): CreationResult
	{
		$result = new CreationResult();

		$data = array_replace_recursive(
			$this->getDefaultData(),
			$this->data
		);

		$dto = $this->getDto($data);

		if ($dto)
		{
			$result->setDto($dto);
			$result->setEntity($dto->getEntity());

			return $result;
		}

		$entityClass = $this->getEntityClass();

		$entity = new $entityClass;

		foreach ($data as $property => $value)
		{
			$setter = 'set' . ucfirst($property);

			if (method_exists($entity, $setter))
			{
				$entity->{$setter}($value);
			}
		}

		self::$serviceManager
			->get(EntityManager::class)
			->persist($entity);

		$result->setDto(
			$this->createDto($entity)
		);
		$result->setEntity($entity);

		return $result;
	}
}
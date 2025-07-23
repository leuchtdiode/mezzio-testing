<?php
namespace Testing;

use Psr\Container\ContainerInterface;
use ReflectionClass;
use Exception;
use Throwable;

class DefaultFactory
{
	private ContainerInterface $container;

	private string $requestedName;

	public function canCreate(
		ContainerInterface $container,
		$requestedName
	): bool
	{
		return str_starts_with($requestedName, __NAMESPACE__ . '\\');
	}

	/**
	 * @throws Throwable
	 */
	public function __invoke(
		Containerinterface $container,
		$requestedName,
		array $options = null
	)
	{
		$this->container     = $container;
		$this->requestedName = $requestedName;

		$factoryClassName = $requestedName . 'Factory';

		if (class_exists($factoryClassName))
		{
			return (new $factoryClassName())->__invoke($container, $requestedName, $options);
		}

		if (($object = $this->tryToLoadWithReflection()))
		{
			return $object;
		}

		return new $requestedName;
	}

	/**
	 * @throws Throwable
	 */
	private function tryToLoadWithReflection(): ?object
	{
		$class = new ReflectionClass($this->requestedName);

		if (!($constructor = $class->getConstructor()))
		{
			return null;
		}

		if (!($params = $constructor->getParameters()))
		{
			return null;
		}

		$parameterInstances = [];

		foreach ($params as $p)
		{
			$type = $p->getType();

			if ($p->getName() === 'container')
			{
				$parameterInstances[] = $this->container;
			}
			else
			{
				if ($type && !$type->isBuiltin())
				{
					try
					{
						$parameterInstances[] = $this->container->get(
							$type->getName()
						);
					}
					catch (Exception $ex)
					{
						error_log($ex->getMessage());

						throw $ex;
					}
				}
				else
				{
					if ($type && $type->getName() === 'array' && $p->getName() === 'config')
					{
						$parameterInstances[] = $this->container->get('Config');
					}
				}
			}
		}

		return $class->newInstanceArgs($parameterInstances);
	}
}
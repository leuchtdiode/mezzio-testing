<?php
namespace Testing\Module;

use Throwable;

trait ServiceManagerTrait
{
	/**
	 * @throws Throwable
	 */
	protected function getInstance(string $className): mixed
	{
		return $this
			->getApplicationServiceLocator()
			->get($className);
	}
}
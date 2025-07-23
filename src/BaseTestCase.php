<?php
namespace Testing;

use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Testing\Dto\Creator;
use Testing\Module\DynamicFixturesTrait;
use Testing\Module\MockTrait;
use Testing\Module\ServiceManagerTrait;
use Throwable;
use Trinet\MezzioTest\MezzioTestEnvironment;

class BaseTestCase extends TestCase
{
	use ServiceManagerTrait;
	use DynamicFixturesTrait;
	use MockTrait;

	protected MezzioTestEnvironment $app;

	protected function isDatabaseNecessary(): bool
	{
		return true;
	}

	/**
	 * @throws Throwable
	 */
	protected function setUp(): void
	{
		$this->app = new MezzioTestEnvironment();

		Creator::setServiceManager(
			$this->getApplicationServiceLocator()
		);

		if ($this->isDatabaseNecessary())
		{
			$this->createEmptyDb();
		}
	}

	protected function tearDown(): void
	{
		if ($this->isDatabaseNecessary())
		{
			$this->restoreEmptyDb();
		}

		parent::tearDown();
	}

	protected function dispatch(
		$uri,
		string|null $method = null,
		array $params = [],
		array $headers = []
	): ResponseInterface
	{
		return $this->app->dispatch($uri, $method, $params, $headers);
	}

	protected function getApplicationServiceLocator(): ContainerInterface
	{
		return $this->app->container();
	}

	/**
	 * @throws Throwable
	 */
	protected function getService(string $class): mixed
	{
		return $this
			->getApplicationServiceLocator()
			->get($class);
	}
}

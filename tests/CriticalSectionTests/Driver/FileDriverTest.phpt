<?php

declare(strict_types=1);

/**
 * @testCase
 */

namespace stekycz\CriticalSection\tests\Driver;

use stekycz\CriticalSection\Driver\FileDriver;
use stekycz\CriticalSection\Exception\CriticalSectionException;
use TestCase;
use Tester\Assert;

require_once __DIR__ . '/../bootstrap.php';

class FileDriverTest extends TestCase
{

	public const TEST_LABEL = 'test';

	/** @var FileDriver */
	private $driver;

	/** @var string */
	private $filesDir;


	protected function setUp()
	{
		parent::setUp();
		$this->filesDir = TEMP_DIR . '/critical-section';
		mkdir($this->filesDir, 0777, true);
		$this->driver = new FileDriver($this->filesDir);
	}


	protected function tearDown()
	{
		system('rm -rf ' . escapeshellarg($this->filesDir));
		parent::tearDown();
	}


	public function testCanAcquireOnce()
	{
		Assert::true($this->driver->acquireLock(self::TEST_LABEL));
	}


	public function testCanReleaseOnceAndOnlyOnce()
	{
		Assert::true($this->driver->acquireLock(self::TEST_LABEL));
		Assert::true($this->driver->releaseLock(self::TEST_LABEL));
		Assert::false($this->driver->releaseLock(self::TEST_LABEL));
	}


	public function testReleaseWithoutAcquire()
	{
		Assert::false($this->driver->releaseLock(self::TEST_LABEL));
	}


	public function testCanAcquireAndReleaseMultipleTimes()
	{
		Assert::true($this->driver->acquireLock(self::TEST_LABEL));
		Assert::true($this->driver->releaseLock(self::TEST_LABEL));
		Assert::true($this->driver->acquireLock(self::TEST_LABEL));
		Assert::true($this->driver->releaseLock(self::TEST_LABEL));
		Assert::true($this->driver->acquireLock(self::TEST_LABEL));
		Assert::true($this->driver->releaseLock(self::TEST_LABEL));
	}


	public function testCannotCreateDirectory()
	{
		$path = TEMP_DIR . '/file';
		touch($path);
		Assert::exception(function () use ($path) {
			new FileDriver($path);
		}, CriticalSectionException::class);
		unlink($path);
	}
}

(new FileDriverTest)->run();

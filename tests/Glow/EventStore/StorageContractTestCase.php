<?php

namespace LidskaSila\Glow\EventStore;

use LidskaSila\Glow\EventStore\OptimisticLocking\Storage;
use LidskaSila\Glow\EventStore\OptimisticLocking\StreamData;
use LidskaSila\Glow\Serializer\Serializer;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

abstract class StorageContractTestCase extends TestCase
{

	/**
	 * @test
	 */
	public function it_merges_new_stream_data_with_old_when_committing_to_existing_stream()
	{
		$uuid = Uuid::uuid4();

		$firstStreamDataWithOneEvent = $this->givenFixtureStreamDataWith($uuid, 1000);
		$this->givenStorageContains($firstStreamDataWithOneEvent);

		$secondStreamDataWithOneEvent = $this->givenFixtureStreamDataWith($uuid, 1001);
		$this->whenStoringNewStreamData($secondStreamDataWithOneEvent);

		$streamDataOfTwoEvents = $this->givenFixtureStreamDataOfTwoEventsWith($uuid, 1002);
		$this->thenStorageContainsStreamData($streamDataOfTwoEvents);
	}

	protected function givenFixtureStreamDataWith(UuidInterface $uuid, int $version)
	{
		$testEvent       = new EventStoreTestEvent();
		$serializedEvent = $this->getStorageSerializer()->toArray($testEvent);
		$streamData      = new StreamData((string) $uuid, EventSourcedAggregate::class, [ $serializedEvent ], $version);

		return $streamData;
	}

	abstract protected function getStorageSerializer(): Serializer;

	protected function givenStorageContains(StreamData $fixtureStorage)
	{
		$this->getStorage()->store(
			$fixtureStorage->getId(),
			$fixtureStorage->getClassName(),
			$fixtureStorage->getEventData(),
			$fixtureStorage->getVersion() + 1,
			$fixtureStorage->getVersion()
		);
	}

	abstract protected function getStorage(): Storage;

	protected function whenStoringNewStreamData(StreamData $streamData)
	{
		$this->getStorage()->store(
			$streamData->getId(),
			$streamData->getClassName(),
			$streamData->getEventData(),

			$streamData->getVersion() + 1,
			$streamData->getVersion()
		);
	}

	protected function givenFixtureStreamDataOfTwoEventsWith(UuidInterface $uuid, $version)
	{
		$testEvent       = new EventStoreTestEvent();
		$serializedEvent = $this->getStorageSerializer()->toArray($testEvent);
		$streamData      = new StreamData((string) $uuid, EventSourcedAggregate::class, [ $serializedEvent, $serializedEvent ], $version);

		return $streamData;
	}

	protected function thenStorageContainsStreamData(StreamData $streamData)
	{
		self::assertEquals($streamData, $this->getStorage()->load((string) $streamData->getId()));
	}
}

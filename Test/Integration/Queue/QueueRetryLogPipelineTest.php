<?php
declare(strict_types=1);

namespace MaGuru\Core\Test\Integration\Queue;

use Dev\TestCore\Test\Integration\AbstractIntegrationTest;
use MaGuru\Core\Api\Data\QueueRetryLogInterface;
use MaGuru\Core\Api\QueueRetryLogRepositoryInterface;
use MaGuru\Core\Api\QueueRetryPolicyProviderInterface;
use MaGuru\Core\Model\Data\QueueRetryLogFactory;
use MaGuru\Core\Model\Queue\QueueRetryLogger;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Psr\Log\LoggerInterface;

/**
 * QueueRetryLogger writes a row, QueueRetryLogRepository::getDueForRetry() reads it back, against a
 * real database.
 */
class QueueRetryLogPipelineTest extends AbstractIntegrationTest
{
    private const MODULE = 'ITest_QueueRetryLogPipeline';

    private QueueRetryLogRepositoryInterface $repository;
    private EncryptorInterface $encryptor;
    private SerializerInterface $serializer;
    private DateTime $dateTime;
    private QueueRetryLogger $unencryptedLogger;
    private QueueRetryLogger $encryptedLogger;

    /** @var int[] */
    private array $cleanupIds = [];

    protected function setUp(): void
    {
        parent::setUp();

        $om = ObjectManager::getInstance();

        $this->repository = $om->get(QueueRetryLogRepositoryInterface::class);
        $this->encryptor  = $om->get(EncryptorInterface::class);
        $this->dateTime   = $om->get(DateTime::class);

        $logFactory  = $om->get(QueueRetryLogFactory::class);
        $this->serializer = $om->get(SerializerInterface::class);
        $logger      = $om->get(LoggerInterface::class);
        $retryPolicy = $om->get(QueueRetryPolicyProviderInterface::class);

        $this->unencryptedLogger = new QueueRetryLogger(
            $logFactory,
            $this->repository,
            $this->serializer,
            $this->encryptor,
            $logger,
            $retryPolicy,
            false,
        );

        $this->encryptedLogger = new QueueRetryLogger(
            $logFactory,
            $this->repository,
            $this->serializer,
            $this->encryptor,
            $logger,
            $retryPolicy,
            true,
        );
    }

    protected function tearDown(): void
    {
        if ($this->cleanupIds !== []) {
            $connection = ObjectManager::getInstance()->get(ResourceConnection::class)->getConnection();
            $table      = ObjectManager::getInstance()->get(ResourceConnection::class)
                ->getTableName('maguru_core_queue_retry_log');
            $connection->delete($table, ['entity_id IN (?)' => $this->cleanupIds]);
            $this->cleanupIds = [];
        }

        parent::tearDown();
    }

    public function testFreshFailureHasNullNextRetryAtAndIsImmediatelyDue(): void
    {
        $topic = $this->uniqueTopic();

        $this->unencryptedLogger->recordFailure(self::MODULE, $topic, ['order_id' => 1], new \RuntimeException('boom'));

        $row = $this->findRowByTopic($topic);
        $this->assertNotNull($row, 'A freshly recorded failure must be returned by getDueForRetry() - its next_retry_at is unset (NULL), which the reader\'s filter must treat as "due now", not "never due".');
        $this->assertNull($row->getNextRetryAt());
        $this->assertSame(QueueRetryLogInterface::STATUS_FAILED, $row->getStatus());
        $this->assertSame(0, $row->getAttemptCount());
    }

    public function testRowScheduledInTheFutureIsExcludedFromDueForRetry(): void
    {
        $topic = $this->uniqueTopic();
        $this->unencryptedLogger->recordFailure(self::MODULE, $topic, ['order_id' => 2], new \RuntimeException('boom'));
        $row = $this->findRowByTopic($topic);
        $this->assertNotNull($row);

        $row->setStatus(QueueRetryLogInterface::STATUS_RETRYING);
        $row->setAttemptCount(1);
        $row->setNextRetryAt($this->dateTime->gmtDate(null, time() + 3600));
        $this->repository->save($row);

        $this->assertNull(
            $this->findRowByTopic($topic),
            'A row whose next_retry_at is an hour in the future must not be returned as due yet.'
        );
    }

    public function testRowBecomesDueAgainOncePastNextRetryAt(): void
    {
        $topic = $this->uniqueTopic();
        $this->unencryptedLogger->recordFailure(self::MODULE, $topic, ['order_id' => 3], new \RuntimeException('boom'));
        $row = $this->findRowByTopic($topic);
        $this->assertNotNull($row);

        $row->setStatus(QueueRetryLogInterface::STATUS_RETRYING);
        $row->setAttemptCount(1);
        $row->setNextRetryAt($this->dateTime->gmtDate(null, time() - 60));
        $this->repository->save($row);

        $dueAgain = $this->findRowByTopic($topic);
        $this->assertNotNull($dueAgain, 'A row whose next_retry_at has already passed must be due again.');
        $this->assertSame(1, $dueAgain->getAttemptCount());
    }

    public function testExhaustedRowIsNeverReturnedRegardlessOfNextRetryAt(): void
    {
        $topic = $this->uniqueTopic();
        $this->unencryptedLogger->recordFailure(self::MODULE, $topic, ['order_id' => 4], new \RuntimeException('boom'));
        $row = $this->findRowByTopic($topic);
        $this->assertNotNull($row);

        $row->setStatus(QueueRetryLogInterface::STATUS_EXHAUSTED);
        $row->setNextRetryAt(null); // NULL would otherwise satisfy the "due now" branch
        $this->repository->save($row);

        $this->assertNull(
            $this->findRowByTopic($topic),
            'STATUS_EXHAUSTED must be excluded by the status filter even though next_retry_at is NULL.'
        );
    }

    public function testSucceededRowIsNeverReturned(): void
    {
        $topic = $this->uniqueTopic();
        $this->unencryptedLogger->recordFailure(self::MODULE, $topic, ['order_id' => 5], new \RuntimeException('boom'));
        $row = $this->findRowByTopic($topic);
        $this->assertNotNull($row);

        $row->setStatus(QueueRetryLogInterface::STATUS_SUCCEEDED);
        $this->repository->save($row);

        $this->assertNull($this->findRowByTopic($topic));
    }

    public function testUnencryptedPayloadIsStoredAsReadablePlaintext(): void
    {
        $topic = $this->uniqueTopic();
        $this->unencryptedLogger->recordFailure(
            self::MODULE,
            $topic,
            ['marker' => 'PLAINTEXT-MARKER'],
            new \RuntimeException('boom'),
        );

        $row = $this->findRowByTopic($topic);
        $this->assertNotNull($row);
        $this->assertFalse($row->getIsEncrypted());
        $this->assertStringContainsString('PLAINTEXT-MARKER', (string) $row->getPayload());
    }

    public function testEncryptedPayloadRoundTripsThroughTheRealEncryptor(): void
    {
        $topic   = $this->uniqueTopic();
        $payload = ['email' => 'customer@example.com', 'invoice_id' => 'inv-123'];

        $this->encryptedLogger->recordFailure(self::MODULE, $topic, $payload, new \RuntimeException('boom'));

        $row = $this->findRowByTopic($topic);
        $this->assertNotNull($row);
        $this->assertTrue($row->getIsEncrypted());

        $rawPayload = (string) $row->getPayload();
        $this->assertStringNotContainsString(
            'customer@example.com',
            $rawPayload,
            'An encrypted row must never contain the plaintext PII on disk.'
        );

        $decrypted = $this->serializer->unserialize($this->encryptor->decrypt($rawPayload));
        $this->assertSame($payload, $decrypted, 'The real encryptor must round-trip the payload exactly.');
    }

    /**
     * @param string $topic
     * @return QueueRetryLogInterface|null
     */
    private function findRowByTopic(string $topic): ?QueueRetryLogInterface
    {
        foreach ($this->repository->getDueForRetry(1000) as $row) {
            if ($row->getTopic() === $topic) {
                if ($row->getEntityId() !== null) {
                    $this->cleanupIds[] = $row->getEntityId();
                }
                return $row;
            }
        }
        return null;
    }

    private function uniqueTopic(): string
    {
        return 'itest.queue.retry.pipeline.' . uniqid();
    }
}

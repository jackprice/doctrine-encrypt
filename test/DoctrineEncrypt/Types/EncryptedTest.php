<?php

namespace DoctrineEncrypt\Types;


use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\ORM\Tools\Setup;
use DoctrineEncrypt\Encryptors\AES256Encryptor;
use Example\ExampleEntity;
use Doctrine\DBAL\Types;

class EncryptedTest extends \PHPUnit_Framework_TestCase
{
    /**
     * A configured entity manager.
     *
     * @var EntityManager
     */
    protected $entityManager;

    public function testDataIsPersisted()
    {
        $entity = new ExampleEntity();

        $entity->setUnencrypted("Hello, World!");
        $entity->setEncrypted("This is secret");

        $this->entityManager->persist($entity);
        $this->entityManager->flush();
    }

    public function testDataCanBeRetrieved()
    {
        $entity = new ExampleEntity();

        $entity->setUnencrypted("Hello, World!");
        $entity->setEncrypted("This is secret");

        $this->entityManager->persist($entity);
        $this->entityManager->flush();

        $id = $entity->getId();

        $this->entityManager->clear();

        /** @var ExampleEntity $retrieved */
        $retrieved = $this->entityManager->getRepository(ExampleEntity::class)->find($id);

        $this->assertEquals("This is secret", $retrieved->getEncrypted());
    }

    public function testAlteringUnencryptedField()
    {
        $entity = new ExampleEntity();

        $entity->setUnencrypted("Hello, World!");
        $entity->setEncrypted("This is secret");

        $this->entityManager->persist($entity);
        $this->entityManager->flush();

        $id = $entity->getId();

        $this->entityManager->clear();

        /** @var ExampleEntity $retrieved */
        $retrieved = $this->entityManager->getRepository(ExampleEntity::class)->find($id);

        $this->assertEquals("This is secret", $retrieved->getEncrypted());

        $retrieved->setUnencrypted("New value");

        $this->entityManager->flush();

        $this->entityManager->clear();

        /** @var ExampleEntity $retrieved */
        $retrieved = $this->entityManager->getRepository(ExampleEntity::class)->find($id);

        $this->assertEquals("This is secret", $retrieved->getEncrypted());
    }

    protected function setUp()
    {
        $entityDirectory = __DIR__.'/../../../example';
        $config = Setup::createAnnotationMetadataConfiguration([$entityDirectory], false, null, null, false);

        $conn = [
            'driver' => 'pdo_sqlite',
            'path' => ':memory:',
        ];

        $this->entityManager = EntityManager::create($conn, $config);

        $encryptor = new AES256Encryptor(str_repeat('abc', AES256Encryptor::MINIMUM_KEY_LENGTH));

        Encrypted::setEncryptor($encryptor);

        if (!Types\Type::hasType(Encrypted::ENCRYPTED)) {
            Types\Type::addType(Encrypted::ENCRYPTED, Encrypted::class);
        }

        $schemaTool = new SchemaTool($this->entityManager);

        $schemaTool->createSchema([$this->entityManager->getClassMetadata(ExampleEntity::class)]);
    }
}

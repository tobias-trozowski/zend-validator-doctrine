<?php
declare(strict_types=1);

namespace TobiasTest\Zend\Validator\Doctrine;

use DateTime;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\ObjectRepository;
use PHPUnit\Framework\TestCase;
use stdClass;
use Tobias\Zend\Validator\Doctrine\UniqueObject;
use Zend\Validator\Exception\InvalidArgumentException;
use Zend\Validator\Exception\RuntimeException;
use function str_replace;

final class UniqueObjectTest extends TestCase
{
    public function testCanValidateWithNotAvailableObjectInRepository(): void
    {
        $repository = $this->createMock(ObjectRepository::class);
        $repository
            ->expects($this->once())
            ->method('findOneBy')
            ->with(['matchKey' => 'matchValue'])
            ->willReturn(null);
        $objectManager = $this->createMock(ObjectManager::class);
        $validator = new UniqueObject([
            'object_repository' => $repository,
            'object_manager'    => $objectManager,
            'fields'            => 'matchKey',
        ]);
        $this->assertTrue($validator->isValid('matchValue'));
    }

    public function testCanValidateIfThereIsTheSameObjectInTheRepository(): void
    {
        $match = new stdClass();
        $classMetadata = $this->createMock(ClassMetadata::class);
        $classMetadata
            ->expects($this->once())
            ->method('getIdentifierFieldNames')
            ->willReturn(['id']);
        $classMetadata
            ->expects($this->once())
            ->method('getIdentifierValues')
            ->with($match)
            ->willReturn(['id' => 'identifier']);
        $objectManager = $this->createMock(ObjectManager::class);
        $objectManager->expects($this->any())
            ->method('getClassMetadata')
            ->with('stdClass')
            ->willReturn($classMetadata);
        $repository = $this->createMock(ObjectRepository::class);
        $repository
            ->expects($this->any())
            ->method('getClassName')
            ->willReturn('stdClass');
        $repository
            ->expects($this->once())
            ->method('findOneBy')
            ->with(['matchKey' => 'matchValue'])
            ->willReturn($match);
        $validator = new UniqueObject([
            'object_repository' => $repository,
            'object_manager'    => $objectManager,
            'fields'            => 'matchKey',
        ]);
        $this->assertTrue($validator->isValid(['matchKey' => 'matchValue', 'id' => 'identifier']));
    }

    public function testCannotValidateIfThereIsAnotherObjectWithTheSameValueInTheRepository(): void
    {
        $match = new stdClass();
        $classMetadata = $this->createMock(ClassMetadata::class);
        $classMetadata
            ->expects($this->once())
            ->method('getIdentifierFieldNames')
            ->willReturn(['id']);
        $classMetadata
            ->expects($this->once())
            ->method('getIdentifierValues')
            ->with($match)
            ->willReturn(['id' => 'identifier']);
        $objectManager = $this->createMock(ObjectManager::class);
        $objectManager->expects($this->any())
            ->method('getClassMetadata')
            ->with('stdClass')
            ->willReturn($classMetadata);
        $repository = $this->createMock(ObjectRepository::class);
        $repository
            ->expects($this->any())
            ->method('getClassName')
            ->willReturn('stdClass');
        $repository
            ->expects($this->once())
            ->method('findOneBy')
            ->with(['matchKey' => 'matchValue'])
            ->willReturn($match);
        $validator = new UniqueObject([
            'object_repository' => $repository,
            'object_manager'    => $objectManager,
            'fields'            => 'matchKey',
        ]);
        $this->assertFalse($validator->isValid(['matchKey' => 'matchValue', 'id' => 'another identifier']));
    }

    public function testCanFetchIdentifierFromContext(): void
    {
        $match = new stdClass();
        $classMetadata = $this->createMock(ClassMetadata::class);
        $classMetadata
            ->expects($this->once())
            ->method('getIdentifierFieldNames')
            ->willReturn(['id']);
        $classMetadata
            ->expects($this->once())
            ->method('getIdentifierValues')
            ->with($match)
            ->willReturn(['id' => 'identifier']);
        $objectManager = $this->createMock(ObjectManager::class);
        $objectManager->expects($this->any())
            ->method('getClassMetadata')
            ->with('stdClass')
            ->willReturn($classMetadata);
        $repository = $this->createMock(ObjectRepository::class);
        $repository
            ->expects($this->any())
            ->method('getClassName')
            ->willReturn('stdClass');
        $repository
            ->expects($this->once())
            ->method('findOneBy')
            ->with(['matchKey' => 'matchValue'])
            ->willReturn($match);
        $validator = new UniqueObject([
            'object_repository' => $repository,
            'object_manager'    => $objectManager,
            'fields'            => 'matchKey',
            'use_context'       => true,
        ]);
        $this->assertTrue($validator->isValid('matchValue', ['id' => 'identifier']));
    }

    public function testThrowsAnExceptionOnUsedButMissingContext(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Expected context to be an array but is null');
        $match = new stdClass();
        $repository = $this->createMock(ObjectRepository::class);
        $repository
            ->expects($this->once())
            ->method('findOneBy')
            ->with(['matchKey' => 'matchValue'])
            ->willReturn($match);
        $objectManager = $this->createMock(ObjectManager::class);
        $validator = new UniqueObject([
            'object_repository' => $repository,
            'object_manager'    => $objectManager,
            'fields'            => 'matchKey',
            'use_context'       => true,
        ]);
        $validator->isValid('matchValue');
    }

    public function testThrowsAnExceptionOnMissingIdentifier(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Expected context to contain id');
        $match = new stdClass();
        $classMetadata = $this->createMock(ClassMetadata::class);
        $classMetadata
            ->expects($this->once())
            ->method('getIdentifierFieldNames')
            ->willReturn(['id']);
        $objectManager = $this->createMock(ObjectManager::class);
        $objectManager->expects($this->any())
            ->method('getClassMetadata')
            ->with('stdClass')
            ->willReturn($classMetadata);
        $repository = $this->createMock(ObjectRepository::class);
        $repository
            ->expects($this->any())
            ->method('getClassName')
            ->willReturn('stdClass');
        $repository
            ->expects($this->once())
            ->method('findOneBy')
            ->with(['matchKey' => 'matchValue'])
            ->willReturn($match);
        $validator = new UniqueObject([
            'object_repository' => $repository,
            'object_manager'    => $objectManager,
            'fields'            => 'matchKey',
        ]);
        $validator->isValid('matchValue');
    }

    public function testThrowsAnExceptionOnMissingIdentifierInContext(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Expected context to contain id');
        $match = new stdClass();
        $classMetadata = $this->createMock(ClassMetadata::class);
        $classMetadata
            ->expects($this->once())
            ->method('getIdentifierFieldNames')
            ->willReturn(['id']);
        $objectManager = $this->createMock(ObjectManager::class);
        $objectManager->expects($this->any())
            ->method('getClassMetadata')
            ->with('stdClass')
            ->willReturn($classMetadata);
        $repository = $this->createMock(ObjectRepository::class);
        $repository
            ->expects($this->any())
            ->method('getClassName')
            ->willReturn('stdClass');
        $repository
            ->expects($this->once())
            ->method('findOneBy')
            ->with(['matchKey' => 'matchValue'])
            ->willReturn($match);
        $validator = new UniqueObject([
            'object_repository' => $repository,
            'object_manager'    => $objectManager,
            'fields'            => 'matchKey',
            'use_context'       => true,
        ]);
        $validator->isValid('matchValue', []);
    }

    public function testThrowsAnExceptionOnMissingObjectManager(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Option "object_manager" is required and must be an instance of ' .
            'Doctrine\\Common\\Persistence\\ObjectManager, nothing given'
        );
        $repository = $this->createMock(ObjectRepository::class);
        new UniqueObject([
            'object_repository' => $repository,
            'fields'            => 'matchKey',
        ]);
    }

    public function testThrowsAnExceptionOnWrongObjectManager(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Option "object_manager" is required and must be an instance of ' .
            'Doctrine\\Common\\Persistence\\ObjectManager, stdClass given'
        );
        $objectManager = new stdClass();
        $repository = $this->createMock(ObjectRepository::class);
        new UniqueObject([
            'object_repository' => $repository,
            'object_manager'    => $objectManager,
            'fields'            => 'matchKey',
        ]);
    }

    public function testCanValidateWithNotAvailableObjectInRepositoryByDateTimeObject(): void
    {
        $date = new DateTime('17 March 2014');
        $repository = $this->createMock(ObjectRepository::class);
        $repository
            ->expects($this->once())
            ->method('findOneBy')
            ->with(['date' => $date])
            ->willReturn(null);
        $objectManager = $this->createMock(ObjectManager::class);
        $validator = new UniqueObject([
            'object_repository' => $repository,
            'object_manager'    => $objectManager,
            'fields'            => 'date',
        ]);
        $this->assertTrue($validator->isValid($date));
    }

    public function testCanFetchIdentifierFromObjectContext(): void
    {
        $context = new stdClass();
        $context->id = 'identifier';
        $match = new stdClass();
        $classMetadata = $this->createMock(ClassMetadata::class);
        $classMetadata
            ->expects($this->at(0))
            ->method('getIdentifierValues')
            ->with($context)
            ->willReturn(['id' => 'identifier']);
        $classMetadata
            ->expects($this->at(1))
            ->method('getIdentifierValues')
            ->with($match)
            ->willReturn(['id' => 'identifier']);
        $objectManager = $this->createMock(ObjectManager::class);
        $objectManager->expects($this->any())
            ->method('getClassMetadata')
            ->with('stdClass')
            ->willReturn($classMetadata);
        $repository = $this->createMock(ObjectRepository::class);
        $repository
            ->expects($this->any())
            ->method('getClassName')
            ->willReturn('stdClass');
        $repository
            ->expects($this->once())
            ->method('findOneBy')
            ->with(['matchKey' => 'matchValue'])
            ->willReturn($match);
        $validator = new UniqueObject([
            'object_repository' => $repository,
            'object_manager'    => $objectManager,
            'fields'            => 'matchKey',
            'use_context'       => true,
        ]);
        $this->assertTrue($validator->isValid('matchValue', $context));
    }

    public function testErrorMessageIsStringInsteadArray(): void
    {
        $match = new stdClass();
        $classMetadata = $this->createMock(ClassMetadata::class);
        $classMetadata
            ->expects($this->once())
            ->method('getIdentifierFieldNames')
            ->willReturn(['id']);
        $classMetadata
            ->expects($this->once())
            ->method('getIdentifierValues')
            ->with($match)
            ->willReturn(['id' => 'identifier']);
        $objectManager = $this->createMock(ObjectManager::class);
        $objectManager->expects($this->any())
            ->method('getClassMetadata')
            ->with('stdClass')
            ->willReturn($classMetadata);
        $repository = $this->createMock(ObjectRepository::class);
        $repository
            ->expects($this->any())
            ->method('getClassName')
            ->willReturn('stdClass');
        $repository
            ->expects($this->once())
            ->method('findOneBy')
            ->with(['matchKey' => 'matchValue'])
            ->willReturn($match);
        $validator = new UniqueObject([
            'object_repository' => $repository,
            'object_manager'    => $objectManager,
            'fields'            => 'matchKey',
            'use_context'       => true,
        ]);
        $this->assertFalse(
            $validator->isValid(
                'matchValue',
                ['matchKey' => 'matchValue', 'id' => 'another identifier']
            )
        );
        $messageTemplates = $validator->getMessageTemplates();
        $expectedMessage = str_replace(
            '%value%',
            'matchValue',
            $messageTemplates[UniqueObject::ERROR_OBJECT_NOT_UNIQUE]
        );
        $messages = $validator->getMessages();
        $receivedMessage = $messages[UniqueObject::ERROR_OBJECT_NOT_UNIQUE];
        $this->assertSame($expectedMessage, $receivedMessage);
    }
}

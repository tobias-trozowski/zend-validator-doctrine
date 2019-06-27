<?php
declare(strict_types=1);

namespace TobiasTest\Zend\Validator\Doctrine;

use Doctrine\Common\Persistence\ObjectRepository;
use PHPUnit\Framework\TestCase;
use stdClass;
use Tobias\Zend\Validator\Doctrine\NoObjectExists;
use function str_replace;

final class NoObjectExistsTest extends TestCase
{
    public function testCanValidateWithNoAvailableObjectInRepository(): void
    {
        $repository = $this->createMock(ObjectRepository::class);
        $repository
            ->expects($this->once())
            ->method('findOneBy')
            ->willReturn(null);
        $validator = new NoObjectExists(['object_repository' => $repository, 'fields' => 'matchKey']);
        $this->assertTrue($validator->isValid('matchValue'));
    }

    public function testCannotValidateWithAvailableObjectInRepository(): void
    {
        $repository = $this->createMock(ObjectRepository::class);
        $repository
            ->expects($this->once())
            ->method('findOneBy')
            ->willReturn(new stdClass());
        $validator = new NoObjectExists(['object_repository' => $repository, 'fields' => 'matchKey']);
        $this->assertFalse($validator->isValid('matchValue'));
    }

    public function testErrorMessageIsStringInsteadArray(): void
    {
        $repository = $this->createMock(ObjectRepository::class);
        $repository
            ->expects($this->once())
            ->method('findOneBy')
            ->willReturn(new stdClass());
        $validator = new NoObjectExists(['object_repository' => $repository, 'fields' => 'matchKey']);
        $this->assertFalse($validator->isValid('matchValue'));
        $messageTemplates = $validator->getMessageTemplates();
        $expectedMessage = str_replace(
            '%value%',
            'matchValue',
            $messageTemplates[NoObjectExists::ERROR_OBJECT_FOUND]
        );
        $messages = $validator->getMessages();
        $receivedMessage = $messages[NoObjectExists::ERROR_OBJECT_FOUND];
        $this->assertSame($expectedMessage, $receivedMessage);
    }
}

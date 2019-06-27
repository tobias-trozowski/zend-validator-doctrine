<?php
declare(strict_types=1);

namespace TobiasTest\Zend\Validator\Doctrine\Service;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\ObjectRepository;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Tobias\Zend\Validator\Doctrine\Service\UniqueObjectFactory;
use Tobias\Zend\Validator\Doctrine\UniqueObject;

/**
 * @coversDefaultClass \Tobias\Zend\Validator\Doctrine\Service\UniqueObjectFactory
 */
final class UniqueObjectFactoryTest extends TestCase
{
    /**
     * @var UniqueObjectFactory
     */
    private $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp(): void
    {
        $this->object = new UniqueObjectFactory();
    }

    /**
     * @covers ::__invoke
     */
    public function testInvoke(): void
    {
        $options = [
            'target_class' => 'Foo\Bar',
            'fields'       => ['test'],
        ];
        $repository = $this->prophesize(ObjectRepository::class);
        $objectManager = $this->prophesize(ObjectManager::class);
        $objectManager->getRepository('Foo\Bar')
            ->shouldBeCalled()
            ->willReturn($repository->reveal());
        $container = $this->prophesize(ContainerInterface::class);
        $container->get('doctrine.entitymanager.orm_default')
            ->shouldBeCalled()
            ->willReturn($objectManager->reveal());
        $instance = $this->object->__invoke(
            $container->reveal(),
            UniqueObject::class,
            $options
        );
        $this->assertInstanceOf(UniqueObject::class, $instance);
    }
}

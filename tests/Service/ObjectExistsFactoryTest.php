<?php
declare(strict_types=1);

namespace TobiasTest\Zend\Validator\Doctrine\Service;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\ObjectRepository;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Tobias\Zend\Validator\Doctrine\ObjectExists;
use Tobias\Zend\Validator\Doctrine\Service\ObjectExistsFactory;

/**
 * @coversDefaultClass \Tobias\Zend\Validator\Doctrine\Service\ObjectExistsFactory
 */
final class ObjectExistsFactoryTest extends TestCase
{
    /**
     * @var ObjectExistsFactory
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp(): void
    {
        $this->object = new ObjectExistsFactory();
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
            ObjectExists::class,
            $options
        );
        $this->assertInstanceOf(ObjectExists::class, $instance);
    }
}

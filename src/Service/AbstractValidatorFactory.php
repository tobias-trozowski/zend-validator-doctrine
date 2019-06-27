<?php
declare(strict_types=1);

namespace Tobias\Zend\Validator\Doctrine\Service;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\ObjectRepository;
use Psr\Container\ContainerInterface;
use Zend\Stdlib\ArrayUtils;
use function is_string;

abstract class AbstractValidatorFactory
{
    public const DEFAULT_OBJECTMANAGER_KEY = 'doctrine.entitymanager.orm_default';

    protected $validatorClass;

    /**
     * @param ContainerInterface $container
     * @param array              $options
     *
     * @return ObjectRepository
     * @throws Exception\ServiceCreationException
     */
    protected function getRepository(ContainerInterface $container, array $options = null): ObjectRepository
    {
        if (empty($options['target_class'])) {
            throw new Exception\ServiceCreationException(sprintf(
                "Option 'target_class' is missing when creating validator %s",
                __CLASS__
            ));
        }
        $objectManager = $this->getObjectManager($container, $options);
        return $objectManager->getRepository($options['target_class']);
    }

    /**
     * @param ContainerInterface $container
     * @param array              $options
     *
     * @return ObjectManager
     */
    protected function getObjectManager(ContainerInterface $container, array $options = null): ObjectManager
    {
        $objectManager = $options['object_manager'] ?? self::DEFAULT_OBJECTMANAGER_KEY;
        if (is_string($objectManager)) {
            $objectManager = $container->get($objectManager);
        }
        return $objectManager;
    }

    /**
     * @param array $options
     *
     * @return array
     */
    protected function getFields(array $options): array
    {
        if (isset($options['fields'])) {
            return (array)$options['fields'];
        }
        return [];
    }

    /**
     * Helper to merge options array passed to `__invoke`
     * together with the options array created based on the above
     * helper methods.
     *
     * @param array $previousOptions
     * @param array $newOptions
     *
     * @return array
     */
    protected function merge(array $previousOptions, array $newOptions): array
    {
        return ArrayUtils::merge($previousOptions, $newOptions, true);
    }
}

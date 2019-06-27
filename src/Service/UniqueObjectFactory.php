<?php
declare(strict_types=1);

namespace Tobias\Zend\Validator\Doctrine\Service;

use Psr\Container\ContainerInterface;
use Tobias\Zend\Validator\Doctrine\UniqueObject;

final class UniqueObjectFactory extends AbstractValidatorFactory
{
    protected $validatorClass = UniqueObject::class;

    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new UniqueObject($this->merge($options, [
            'object_manager'    => $this->getObjectManager($container, $options),
            'use_context'       => isset($options['use_context']) ? (bool)$options['use_context'] : false,
            'object_repository' => $this->getRepository($container, $options),
            'fields'            => $this->getFields($options),
        ]));
    }
}

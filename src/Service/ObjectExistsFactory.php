<?php
declare(strict_types=1);

namespace Tobias\Zend\Validator\Doctrine\Service;

use Psr\Container\ContainerInterface;
use Tobias\Zend\Validator\Doctrine\ObjectExists;

final class ObjectExistsFactory extends AbstractValidatorFactory
{
    protected $validatorClass = ObjectExists::class;

    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new ObjectExists($this->merge($options, [
            'object_repository' => $this->getRepository($container, $options),
            'fields'            => $this->getFields($options),
        ]));
    }
}

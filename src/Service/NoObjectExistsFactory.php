<?php
declare(strict_types=1);

namespace Tobias\Zend\Validator\Doctrine\Service;

use Psr\Container\ContainerInterface;
use Tobias\Zend\Validator\Doctrine\NoObjectExists;

final class NoObjectExistsFactory extends AbstractValidatorFactory
{
    protected $validatorClass = NoObjectExists::class;

    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new NoObjectExists($this->merge($options, [
            'object_repository' => $this->getRepository($container, $options),
            'fields'            => $this->getFields($options),
        ]));
    }
}

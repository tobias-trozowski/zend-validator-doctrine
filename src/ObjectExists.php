<?php
declare(strict_types=1);

namespace Tobias\Zend\Validator\Doctrine;

use Doctrine\Common\Persistence\ObjectRepository;
use Zend\Validator\AbstractValidator;
use function is_object;

/**
 * Class that validates if objects exist in a given repository with a given list of matched fields
 */
final class ObjectExists extends AbstractValidator
{
    use DoctrineValidatorTrait;
    /**
     * Error constants
     */
    public const ERROR_NO_OBJECT_FOUND = 'noObjectFound';

    /**
     * @var array Message templates
     */
    protected $messageTemplates = [
        self::ERROR_NO_OBJECT_FOUND => "No object matching '%value%' was found",
    ];

    /**
     * Fields to be checked
     *
     * @var array
     */
    private $fields;

    /**
     * ObjectRepository from which to search for entities
     *
     * @var ObjectRepository
     */
    protected $objectRepository;

    /**
     * Constructor
     *
     * @param array $options            required keys are `object_repository`, which must be an instance of
     *                                  Doctrine\Common\Persistence\ObjectRepository, and `fields`, with either
     *                                  a string or an array of strings representing the fields to be matched by the
     *                                  validator.
     */
    public function __construct(array $options)
    {
        $this->objectRepository = $this->getFromOptions($options, 'object_repository', ObjectRepository::class);
        $this->fields = (static function (string ...$fields) {
            return $fields;
        })(...$this->getFieldsFromOptions($options));

        parent::__construct($options);
    }

    /**
     * {@inheritDoc}
     */
    public function isValid($value): bool
    {
        $cleanedValue = $this->cleanSearchValue($this->fields, $value);
        $match = $this->objectRepository->findOneBy($cleanedValue);
        if (is_object($match)) {
            return true;
        }
        $this->error(self::ERROR_NO_OBJECT_FOUND, $value);
        return false;
    }
}

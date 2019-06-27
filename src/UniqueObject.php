<?php
declare(strict_types=1);

namespace Tobias\Zend\Validator\Doctrine;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\ObjectRepository;
use Zend\Validator\AbstractValidator;
use Zend\Validator\Exception;
use function array_diff_assoc;
use function count;
use function sprintf;

/**
 * Class that validates if objects exist in a given repository with a given list of matched fields only once.
 */
final class UniqueObject extends AbstractValidator
{
    use DoctrineValidatorTrait;
    /**
     * Error constants
     */
    public const ERROR_OBJECT_NOT_UNIQUE = 'objectNotUnique';

    /**
     * @var array Message templates
     */
    protected $messageTemplates = [
        self::ERROR_OBJECT_NOT_UNIQUE => "There is already another object matching '%value%'",
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
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var boolean
     */
    protected $useContext;

    /***
     * Constructor
     *
     * @param array $options            required keys are `object_repository`, which must be an instance of
     *                                  Doctrine\Common\Persistence\ObjectRepository, `object_manager`, which
     *                                  must be an instance of Doctrine\Common\Persistence\ObjectManager,
     *                                  and `fields`, with either a string or an array of strings representing
     *                                  the fields to be matched by the validator.
     */
    public function __construct(array $options)
    {
        $this->objectRepository = $this->getFromOptions($options, 'object_repository', ObjectRepository::class);
        $this->objectManager = $this->getFromOptions($options, 'object_manager', ObjectManager::class);

        $this->fields = (static function (string ...$fields) {
            return $fields;
        })(...$this->getFieldsFromOptions($options));
        $this->useContext = isset($options['use_context']) ? (bool)$options['use_context'] : false;

        parent::__construct($options);
    }

    /**
     * Returns false if there is another object with the same field values but other identifiers.
     *
     * @param mixed $value
     * @param array $context
     *
     * @return boolean
     */
    public function isValid($value, $context = null): bool
    {
        if (!$this->useContext) {
            $context = (array)$value;
        }
        $cleanedValue = $this->cleanSearchValue($this->fields, $value);
        $match = $this->objectRepository->findOneBy($cleanedValue);
        if (!is_object($match)) {
            return true;
        }
        $expectedIdentifiers = $this->getExpectedIdentifiers($context);
        $foundIdentifiers = $this->getFoundIdentifiers($match);
        if (count(array_diff_assoc($expectedIdentifiers, $foundIdentifiers)) === 0) {
            return true;
        }
        $this->error(self::ERROR_OBJECT_NOT_UNIQUE, $value);
        return false;
    }

    /**
     * Gets the identifiers from the matched object.
     *
     * @param object $match
     *
     * @return array
     * @throws Exception\RuntimeException
     */
    protected function getFoundIdentifiers($match): array
    {
        return $this->objectManager
            ->getClassMetadata($this->objectRepository->getClassName())
            ->getIdentifierValues($match);
    }

    /**
     * Gets the identifiers from the context.
     *
     * @param array|object $context
     *
     * @return array
     * @throws Exception\RuntimeException
     */
    protected function getExpectedIdentifiers($context = null): array
    {
        if ($context === null) {
            throw new Exception\RuntimeException(
                'Expected context to be an array but is null'
            );
        }
        $className = $this->objectRepository->getClassName();
        if ($context instanceof $className) {
            return $this->objectManager
                ->getClassMetadata($this->objectRepository->getClassName())
                ->getIdentifierValues($context);
        }
        $result = [];
        foreach ($this->getIdentifiers() as $identifierField) {
            if (!array_key_exists($identifierField, $context)) {
                throw new Exception\RuntimeException(sprintf('Expected context to contain %s', $identifierField));
            }
            $result[$identifierField] = $context[$identifierField];
        }
        return $result;
    }

    /**
     * @return array the names of the identifiers
     */
    protected function getIdentifiers(): array
    {
        return $this->objectManager
            ->getClassMetadata($this->objectRepository->getClassName())
            ->getIdentifierFieldNames();
    }
}

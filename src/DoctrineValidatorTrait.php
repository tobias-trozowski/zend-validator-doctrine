<?php
declare(strict_types=1);

namespace Tobias\Zend\Validator\Doctrine;

use Zend\Stdlib\ArrayUtils;
use Zend\Validator\Exception;
use function array_combine;
use function array_key_exists;
use function count;
use function get_class;
use function gettype;
use function is_object;
use function sprintf;

trait DoctrineValidatorTrait
{
    /**
     * @param string[]            $fields
     * @param string|array|object $value a field value or an array of field values if more fields have been configured
     *                                   to be matched
     *
     * @return array
     */
    protected function cleanSearchValue(array $fields, $value): array
    {
        $value = is_object($value) ? [$value] : (array)$value;
        if (ArrayUtils::isHashTable($value)) {
            $matchedFieldsValues = [];
            foreach ($fields as $field) {
                if (!array_key_exists($field, $value)) {
                    throw new Exception\RuntimeException(
                        sprintf(
                            'Field "%s" was not provided, but was expected since the configured field lists needs'
                            . ' it for validation',
                            $field
                        )
                    );
                }
                $matchedFieldsValues[$field] = $value[$field];
            }
        } else {
            $matchedFieldsValues = @array_combine($fields, $value);
            if (false === $matchedFieldsValues) {
                throw new Exception\RuntimeException(
                    sprintf(
                        'Provided values count is %s, while expected number of fields to be matched is %s',
                        count($value),
                        count($this->fields)
                    )
                );
            }
        }
        return $matchedFieldsValues;
    }

    private function getFromOptions(array $options, string $key, string $type)
    {
        if (!isset($options[$key]) || !$options[$key] instanceof $type) {
            if (!array_key_exists($key, $options)) {
                $provided = 'nothing';
            } elseif (is_object($options[$key])) {
                $provided = get_class($options[$key]);
            } else {
                $provided = gettype($options[$key]);
            }
            throw new Exception\InvalidArgumentException(
                sprintf(
                    'Option "%s" is required and must be an instance of'
                    . ' %s, %s given',
                    $key,
                    $type,
                    $provided
                )
            );
        }
        return $options[$key];
    }

    private function getFieldsFromOptions(array $options): array
    {
        if (!isset($options['fields']) || empty($options['fields'])) {
            throw new Exception\InvalidArgumentException(
                'Key `fields` must be provided and be a field or a list of fields to be used when searching for'
                . ' existing instances'
            );
        }
        return (array)$options['fields'];
    }
}

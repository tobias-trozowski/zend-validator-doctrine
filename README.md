# Zend Validator Doctrine

[![Build Status](https://travis-ci.com/tobias-trozowski/zend-validator-doctrine.svg)](https://travis-ci.com/tobias-trozowski/zend-validator-doctrine)

Inspired and based on the famous [DoctrineModule](https://github.com/doctrine/DoctrineModule).

This package provides three validators that work out the box: `Tobias\Zend\Validator\Doctrine\ObjectExists` and `Tobias\Zend\Validator\Doctrine\NoObjectExists` which implements a check if an entity exists or does not exists in the database, respectively, and `Tobias\Zend\Validator\Doctrine\UniqueObject` which implements a check if a value is only used in one object.  They behave like any other standard Zend validator.

All three validators accept the following options :

* `object_repository` : an instance of an object repository.
* `fields` : an array that contains all the fields that are used to check if the entity exists (or does not).

The `Tobias\Zend\Validator\Doctrine\UniqueObject` also needs the following option:

* `object_manager` : an instance of an object manager.

For the `use_context` option and other specifics to `Tobias\Zend\Validator\Doctrine\UniqueObject` see [below](#uniqueobject).

> Tip : to get an object repository from an object manager you call the `getRepository` function of any valid object manager instance, passing it the FQCN of the class. For instance, in the context of Doctrine 2 ORM, here is how you get the `object_repository` of the 'Application\Entity\User' entity:

```php
$repository = $entityManager->getRepository('Application\Entity\User');
```

### Simple usage

You can directly instantiate a validator the following way:

```php
$validator = new \Tobias\Zend\Validator\Doctrine\ObjectExists([
    'object_repository' => $objectManager->getRepository('Application\Entity\User'),
    'fields' => ['email'],
]);

var_dump($validator->isValid('test@example.com')); // dumps 'true' if an entity matches
var_dump($validator->isValid(['email' => 'test@example.com'])); // dumps 'true' if an entity matches
```


### Use together with Zend Framework 2 forms

Of course, validators are especially useful when paired with forms.  To add a `NoObjectExists` validator to a Zend Framework form element:

```php
namespace Application\Form;

use Tobias\Zend\Validator\Doctrine\NoObjectExists as NoObjectExistsValidator;
use Zend\Form\Form;
use Zend\ServiceManager\ServiceManager;
use Application\Entity;

class User extends Form
{
    public function __construct(ServiceManager $serviceManager)
    {
        parent::__construct('my-form');

        // Add an element
        $this->add([
            'type'    => 'Zend\Form\Element\Email',
            'name'    => 'email',
            'options' => [
                'label' => 'Email',
            ],
            'attributes' => [
                'required' => 'required',
            ],
        ]);

        // add other elements (submit, CSRF…)

        // Fetch any valid object manager from the Service manager
        $entityManager = $serviceManager->get('doctrine.entitymanager.orm_default');

        // Now get the input filter of the form, and add the validator to the email input
        $emailInput = $this->getInputFilter()->get('email');

        $noObjectExistsValidator = new NoObjectExistsValidator([
            'object_repository' => $entityManager->getRepository(Entity\User::class),
            'fields'            => 'email',
        ]);

        $emailInput
            ->getValidatorChain()
            ->attach($noObjectExistsValidator);
    }
}
```

If you are using fieldset's you can directly add the validator using the array notation.  For instance in the `getInputFilterSpecification` function, as shown here:

```php
namespace Application\Form;

use Zend\Form\Fieldset;
use Zend\InputFilter\InputFilterProviderInterface;
use Zend\ServiceManager\ServiceManager;
use Application\Entity;

class UserFieldset extends Fieldset implements InputFilterProviderInterface
{
    protected $serviceManager;

    public function __construct(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;

        parent::__construct('my-fieldset');

        // Add an element
        $this->add([
            'type'    => 'Zend\Form\Element\Email',
            'name'    => 'email',
            'options' => [
                'label' => 'Email',
            ],
            'attributes' => [
                'required' => 'required',
            ],
        ]);
    }

    public function getInputFilterSpecification()
    {
        $entityManager = $this->serviceManager->get('doctrine.entitymanager.orm_default');

        return [
            'email' => [
                'validators' => [
                    [
                        'name' => 'Tobias\Zend\Validator\Doctrine\NoObjectExists',
                        'options' => [
                            'object_repository' => $entityManager->getRepository(Entity\User::class),
                            'fields' => 'email',
                        ],
                    ],
                ],
            ],
        ];
    }
}
```

You can change the default message of the validators like this:

```php
// For NoObjectExists validator (using array notation) :
'validators' => [
    [
        'name' => 'Tobias\Zend\Validator\Doctrine\NoObjectExists',
        'options' => [
            'object_repository' => $this->getEntityManager()->getRepository('Application\Entity\User'),
            'fields' => 'email',
            'messages' => [
                'objectFound' => 'A user with this email already exists.',
            ],
        ],
    ],
],

// For ObjectExists validator (using object notation) :
$objectExistsValidator = new \Tobias\Zend\Validator\Doctrine\ObjectExists([
    'object_repository' => $entityManager->getRepository('Application\Entity\User'),
    'fields'            => 'email',
]);

**$objectExistsValidator->setMessage('noObjectFound', 'Email was not found.');**
```


### UniqueObject

There are two things you have to think about when using `Tobias\Zend\Validator\Doctrine\UniqueObject`;  As mentioned above you have to pass an ObjectManager as `object_manager` option and second you have to pass a value for every identifier your entity has.

* If you leave out the `use_context` option or set it to `false` you have to pass an array containing the `fields`- and `identifier`-values into `isValid()`. When using `Zend\Form` this behaviour is needed if you're using fieldset's.
* If you set the `use_context` option to `true` you have to pass the `fields`-values as first argument and an array containing the `identifier`-values as second argument into `isValid()`. When using `Zend\Form` without fieldset's, this behaviour would be needed.

__Important:__ Whatever you choose, please ensure that the `identifier`-values are named by the field-names, not by the database-column.

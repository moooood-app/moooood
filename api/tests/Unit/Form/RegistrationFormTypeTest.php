<?php

declare(strict_types=1);

namespace App\Tests\Unit\Form;

use App\Entity\User;
use App\Form\RegistrationFormType;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\Validator\Validation;

/**
 * @internal
 */
#[CoversClass(RegistrationFormType::class)]
#[CoversClass(User::class)]
final class RegistrationFormTypeTest extends TypeTestCase
{
    public function testSubmitValidData(): void
    {
        $formData = [
            'firstName' => 'John',
            'lastName' => 'Doe',
            'plainPassword' => 'SecureP@ss123',
        ];

        $user = new User();

        $form = $this->factory->create(RegistrationFormType::class, $user);

        $form->submit($formData);

        // Assert form submission success
        self::assertTrue($form->isSynchronized());

        // Check data transformation
        self::assertSame('John', $user->getFirstName());
        self::assertSame('Doe', $user->getLastName());
    }

    public function testInvalidData(): void
    {
        $formData = [
            'firstName' => 'a',
            'lastName' => 'b',
            'plainPassword' => 'c',
        ];

        $form = $this->factory->create(RegistrationFormType::class);
        $form->submit($formData);

        $firstNameErrors = $form->get('firstName')->getErrors();
        $lastNameErrors = $form->get('lastName')->getErrors();

        self::assertCount(2, $firstNameErrors);
        self::assertSame('This value is too short. It should have 2 characters or more.', $firstNameErrors[0]->getMessage());
        self::assertCount(2, $lastNameErrors);
        self::assertSame('This value is too short. It should have 2 characters or more.', $lastNameErrors[0]->getMessage());
    }

    public function testGetDefaultOptions(): void
    {
        $form = $this->factory->create(RegistrationFormType::class);

        $config = $form->getConfig();
        self::assertSame(User::class, $config->getOption('data_class'));
    }

    /**
     * @return array<ValidatorExtension>
     */
    protected function getExtensions(): array
    {
        $validator = Validation::createValidatorBuilder()
            ->enableAttributeMapping()
            ->getValidator()
        ;

        return [
            new ValidatorExtension($validator),
        ];
    }
}

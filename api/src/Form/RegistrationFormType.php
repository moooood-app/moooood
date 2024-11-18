<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\PasswordStrength;

/**
 * @extends AbstractType<User>
 */
class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstName', TextType::class, [
                'label' => 'First Name',
                'constraints' => [
                    new NotBlank(['message' => 'First name is required']),
                    new Length([
                        'min' => 2,
                        'minMessage' => 'First name must be at least {{ limit }} characters',
                        'max' => 50,
                        'maxMessage' => 'First name cannot exceed {{ limit }} characters',
                    ]),
                ],
            ])
            ->add('lastName', TextType::class, [
                'label' => 'Last Name',
                'constraints' => [
                    new NotBlank(['message' => 'Last name is required']),
                    new Length([
                        'min' => 2,
                        'minMessage' => 'Last name must be at least {{ limit }} characters',
                        'max' => 50,
                        'maxMessage' => 'Last name cannot exceed {{ limit }} characters',
                    ]),
                ],
            ])
            ->add('plainPassword', PasswordType::class, [
                // instead of being set onto the object directly,
                // this is read and encoded in the handler
                'mapped' => false,
                'attr' => ['autocomplete' => 'new-password'],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter a password',
                    ]),
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Your password should be at least {{ limit }} characters',
                        // max length allowed by Symfony for security reasons
                        'max' => 4096,
                    ]),
                    new PasswordStrength(
                        minScore: PasswordStrength::STRENGTH_WEAK,
                        message: 'Your password is too easy to guess.'
                    ),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class, // Bind the form to your User entity
        ]);
    }
}

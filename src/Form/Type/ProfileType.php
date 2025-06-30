<?php

declare(strict_types=1);

namespace App\Form\Type;

use App\Entity\User;
use App\Form\Type\Type\AvatarType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class ProfileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'username',
                TextType::class,
                [
                    'label' => 'Login',
                    'trim' => true,
                    'required' => true,
                    'disabled' => true,
                ]
            )
            ->add(
                'oldPassword',
                PasswordType::class,
                [
                    'label' => 'Obecne hasło',
                    'required' => false,
                ]
            )
            ->add(
                'newPassword',
                RepeatedType::class,
                [
                    'type' => PasswordType::class,
                    'invalid_message' => 'Hasła muszą się zgadzać.',
                    'first_options' => [
                        'label' => 'Nowe hasło',
                        'attr' => ['class' => 'pwstrength', 'placeholder' => 'minimum 10 znaków'],
                    ],
                    'second_options' => ['label' => 'Potwórz nowe hasło'],
                    'required' => false,
                ]
            )
            ->add(
                'email',
                EmailType::class,
                [
                    'label' => 'E-mail',
                    'trim' => true,
                    'required' => true,
                ]
            )
            ->add(
                'file',
                AvatarType::class
            )
            ->add(
                'save',
                SubmitType::class,
                [
                    'attr' => ['class' => 'btn-primary'],
                    'label' => 'Zapisz',
                ]
            );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'data_class' => User::class,
                'validation_groups' => static function (FormInterface $form) {
                    $validationGroup = ['profile'];

                    $data = $form->getData();

                    if ($data->getOldPassword() || $data->getNewPassword()) {
                        $validationGroup[] = 'profile_password';
                    }

                    return $validationGroup;
                },
            ]
        );
    }
}

<?php

declare(strict_types=1);

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

final class PasswordRecoverType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'username',
                TextType::class,
                [
                    'label' => false,
                    'required' => true,
                    'trim' => true,
                    'attr' => [
                        'placeholder' => 'Login lub adres e-mail',
                        'class' => 'mb-10',
                        'autofocus' => 'autofocus'
                    ]
                ]
            );
    }
}

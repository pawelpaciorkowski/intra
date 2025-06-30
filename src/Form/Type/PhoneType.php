<?php

declare(strict_types=1);

namespace App\Form\Type;

use App\Entity\Phone;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class PhoneType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'number',
                TelType::class,
                [
                    'label' => 'Numer',
                    'required' => true,
                    'trim' => true,
                ]
            )
            ->add(
                'isVisible',
                CheckboxType::class,
                [
                    'label' => 'Widoczny publicznie',
                    'required' => false,
                ]
            );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Phone::class]);
    }
}

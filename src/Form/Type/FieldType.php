<?php

declare(strict_types=1);

namespace App\Form\Type;

use App\Entity\Field;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class FieldType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'isActive',
                CheckboxType::class,
                [
                    'label' => 'Aktywny',
                    'required' => false,
                ]
            )
            ->add(
                'name',
                TextType::class,
                [
                    'label' => 'Nazwa',
                    'trim' => true,
                    'attr' => ['autofocus' => 'autofocus'],
                    'required' => true,
                ]
            )
            ->add(
                'langType',
                TextType::class,
                [
                    'label' => 'Typ danych (wg kryteriów PHP)',
                    'trim' => true,
                    'required' => true,
                ]
            )
            ->add(
                'type',
                TextType::class,
                [
                    'label' => 'Rodzaj typu pola',
                    'trim' => true,
                    'required' => true,
                ]
            )
            ->add(
                'className',
                TextType::class,
                [
                    'label' => 'Klasa CSS',
                    'trim' => true,
                    'required' => false,
                ]
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
        $resolver->setDefaults(['data_class' => Field::class]);
    }
}

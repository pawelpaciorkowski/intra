<?php

declare(strict_types=1);

namespace App\Form\Type;

use App\Entity\SpecialTemplate;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class SpecialTemplateType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
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
                'template',
                TextareaType::class,
                [
                    'label' => 'Szablon',
                    'trim' => true,
                    'required' => true,
                    'attr' => ['rows' => 20],
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
        $resolver->setDefaults(['data_class' => SpecialTemplate::class]);
    }
}

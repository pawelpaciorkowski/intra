<?php

declare(strict_types=1);

namespace App\Form\Type;

use App\Entity\EmployeeImport;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Form\Extension\Core\Type\FileType;

final class EmployeeImportType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'mode',
                ChoiceType::class,
                [
                    'label' => 'Sposób importu',
                    'choices' => EmployeeImport::MODES,
                    'required' => true,
                ]
            )
            ->add(
                'file',
                FileType::class,
                [
                    'label' => 'Plik z danymi',
                    'required' => true,
                    'constraints' => [
                        new File([
                            'maxSize' => '128M',
                        ]),
                    ],
                ]
            )
            ->add(
                'save',
                SubmitType::class,
                [
                    'attr' => ['class' => 'btn-primary'],
                    'label' => 'Importuj',
                ]
            );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => EmployeeImport::class]);
    }
}

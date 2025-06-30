<?php

declare(strict_types=1);

namespace App\Form\Type;

use App\Entity\FileSection;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class FileSectionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'title',
                TextType::class,
                [
                    'label' => 'Tytuł',
                    'required' => true,
                    'trim' => true,
                ]
            )
            ->add(
                'shortText',
                TextareaType::class,
                [
                    'purify_html' => true,
                    'label' => 'Tekst wstępny',
                    'trim' => true,
                    'required' => false,
                    'attr' => ['class' => 'tinymce'],
                ]
            )
            ->add(
                'files',
                CollectionType::class,
                [
                    'entry_type' => FileType::class,
                    'allow_add' => true,
                    'allow_delete' => true,
                    'delete_empty' => true,
                    'error_bubbling' => false,
                    'by_reference' => false,
                ]
            )
            ->add(
                'order',
                HiddenType::class,
                [
                    'label' => 'Kolejność',
                ]
            );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => FileSection::class]);
    }
}

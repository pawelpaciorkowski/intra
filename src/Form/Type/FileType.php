<?php

declare(strict_types=1);

namespace App\Form\Type;

use App\Entity\File;
use App\Form\Type\Type\AttachmentType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class FileType extends AbstractType
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
                'file',
                AttachmentType::class
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
        $resolver->setDefaults(['data_class' => File::class]);
    }
}

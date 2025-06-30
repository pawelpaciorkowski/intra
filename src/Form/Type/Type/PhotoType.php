<?php

declare(strict_types=1);

namespace App\Form\Type\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Image;

final class PhotoType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'label' => 'Zdjęcie',
            'required' => false,
            'constraints' => [
                new File([
                    'maxSize' => '16M',
                ]),
                new Image([
                    'detectCorrupted' => true,
                    'minWidth' => 32,
                    'maxWidth' => 2048,
                    'minHeight' => 32,
                    'maxHeight' => 2048,
                ]),
            ],
        ]);
    }

    public function getParent(): ?string
    {
        return FileType::class;
    }
}

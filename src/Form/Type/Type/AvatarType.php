<?php

declare(strict_types=1);

namespace App\Form\Type\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Image;

final class AvatarType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'label' => 'Avatar',
            'required' => false,
            'validation_groups' => ['avatar'],
            'constraints' => [
                new File([
                    'groups' => ['avatar'],
                    'maxSize' => '16M',
                ]),
                new Image([
                    'groups' => ['avatar'],
                    'detectCorrupted' => true,
                    'minWidth' => 32,
                    'maxWidth' => 1024,
                    'minHeight' => 32,
                    'maxHeight' => 1024,
                ]),
            ],
        ]);
    }

    public function getParent(): ?string
    {
        return FileType::class;
    }
}

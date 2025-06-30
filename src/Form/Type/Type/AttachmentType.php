<?php

declare(strict_types=1);

namespace App\Form\Type\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

final class AttachmentType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'label' => 'Załącznik',
            'required' => false,
            'constraints' => [
                new File([
                    'maxSize' => '128M',
                ]),
            ],
        ]);
    }

    public function getParent(): ?string
    {
        return FileType::class;
    }
}

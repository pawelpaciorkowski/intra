<?php

declare(strict_types=1);

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Range;

use function unserialize;

final class UserSettingType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        foreach ($options['settings'] as $setting) {
            if ($setting->getIsHidden()) {
                continue;
            }

            $constraints = [];
            if (!$setting->getIsEmpty()) {
                $constraints[] = new NotBlank();
            }
            if ($setting->getMinValue() || $setting->getMaxValue()) {
                $constraints[] = new Range(
                    [
                        'min' => unserialize($setting->getMinValue(), ['allowed_classes' => false]),
                        'max' => unserialize($setting->getMaxValue(), ['allowed_classes' => false]),
                    ]
                );
            }

            $builder->add(
                $setting->getKey(),
                $setting->getField()->getType(),
                [
                    'label' => $setting->getName(),
                    'constraints' => $constraints,
                    'required' => !$setting->getIsEmpty(),
                    'attr' => [
                        'class' => $setting->getField()->getClassName(),
                        'data-section' => $setting->getSection()->getName(),
                    ],
                ]
            );
        }

        $builder->add(
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
        $resolver->setDefaults(['settings' => null]);
    }
}

<?php

declare(strict_types=1);

namespace App\Form\Type;

use App\Entity\Feedback;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class FeedbackType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'subject',
                TextType::class,
                [
                    'label' => 'Tytuł',
                    'trim' => true,
                    'required' => true,
                ]
            )
            ->add(
                'body',
                TextareaType::class,
                [
                    'purify_html' => true,
                    'label' => 'Treść',
                    'attr' => ['rows' => '5'],
                    'trim' => true,
                    'required' => true,
                ]
            )
            ->add(
                'save',
                SubmitType::class,
                [
                    'attr' => ['class' => 'btn-primary'],
                    'label' => 'Wyślij',
                ]
            );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Feedback::class]);
    }
}

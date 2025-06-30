<?php

declare(strict_types=1);

namespace App\Form\Type;

use App\Entity\Psajdak;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class PsajdakType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'type',
                ChoiceType::class,
                [
                    'label' => 'Typ komunikatu',
                    'choices' => [
                        'SMS' => 'sms',
                        'Wiadomość email' => 'email',
                        'Wiadomość na teams' => 'teams',
                    ],
                    'required' => true,
                    'attr' => [
                        'placeholder' => 'sms',
                    ],
                ]
            )
            ->add(
                'fromEmail',
                TextType::class,
                [
                    'label' => 'Nadawca',
                    'trim' => true,
                    'required' => false,
                    'help' => 'adres email',
                    'attr' => [
                        'placeholder' => 'jan.kowalski@gmail.com',
                    ],
                ]
            )
            ->add(
                'fromName',
                TextType::class,
                [
                    'label' => 'Nadawca',
                    'trim' => true,
                    'required' => false,
                    'help' => 'imię i nazwisko',
                    'attr' => [
                        'placeholder' => 'Jan Kowalski',
                    ],
                ]
            )
            ->add(
                'email',
                TextType::class,
                [
                    'label' => 'Adresat(ci)',
                    'trim' => true,
                    'required' => false,
                    'help' => 'lista adresów email rozdzielona przecinkiem',
                    'attr' => [
                        'placeholder' => 'Jan Kowalski <jan.kowalski@gmail.com>',
                    ],
                ]
            )
            ->add(
                'phone',
                TextType::class,
                [
                    'label' => 'Nu telefonu',
                    'trim' => true,
                    'required' => false,
                    'attr' => [
                        'placeholder' => '501 608 276',
                    ],
                ]
            )
            ->add(
                'subject',
                TextType::class,
                [
                    'label' => 'Tytuł',
                    'required' => false,
                    'trim' => true,
                ]
            )
            ->add(
                'htmlBody',
                TextareaType::class,
                [
                    'purify_html' => true,
                    'label' => 'Treść html',
                    'attr' => [
                        'class' => 'tinymce',
                        'rows' => '5',
                    ],
                    'trim' => true,
                    'required' => false,
                ]
            )
            ->add(
                'textBody',
                TextareaType::class,
                [
                    'purify_html' => true,
                    'label' => 'Treść tekstowa',
                    'attr' => ['rows' => '5'],
                    'trim' => true,
                    'required' => false,
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
        $resolver->setDefaults(['data_class' => Psajdak::class]);
    }
}

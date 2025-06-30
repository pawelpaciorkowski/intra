<?php

declare(strict_types=1);

namespace App\Form\Type;

use App\Entity\Template;
use App\Entity\TemplateTag;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class TemplateType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'isActive',
                CheckboxType::class,
                [
                    'label' => 'Aktywny',
                    'required' => false,
                ]
            )
            ->add(
                'templateTag',
                EntityType::class,
                [
                    'class' => TemplateTag::class,
                    'query_builder' => static function (EntityRepository $er) {
                        return $er->createQueryBuilder('m')->where('m.isActive = 1')->orderBy('m.name', 'ASC');
                    },
                    'choice_attr' => function ($choice) {
                        if (!$choice->getIsActive()) {
                            return ['class' => 'inactive'];
                        }

                        return [];
                    },
                    'label' => 'Wyzwalacz',
                    'choice_label' => 'name',
                    'placeholder' => '-- wybierz --',
                    'empty_data' => [],
                    'attr' => ['data-live-search' => 'true'],
                    'required' => true,
                ]
            )
            ->add(
                'senderAddress',
                EmailType::class,
                [
                    'label' => 'Adres email nadawcy',
                    'help' => sprintf(
                        'Jeżeli nie wypełniony, zostanie użyty domyślny adres \'%s\'',
                        $options['sender']['email']
                    ),
                    'trim' => true,
                    'required' => false,
                ]
            )
            ->add(
                'senderName',
                TextType::class,
                [
                    'label' => 'Nazwa nadawcy',
                    'help' => sprintf(
                        'Jeżeli nie wypełniona, zostanie użyta domyślna nazwa \'%s\'',
                        $options['sender']['name']
                    ),
                    'trim' => true,
                    'required' => false,
                ]
            )
            ->add(
                'recipient',
                TextType::class,
                [
                    'label' => 'Adresaci (lista rozdzielona przecinkami)',
                    'trim' => true,
                    'required' => true,
                ]
            )
            ->add(
                'attachment',
                TextareaType::class,
                [
                    'purify_html' => true,
                    'label' => 'Załączniki (JSON)',
                    'attr' => ['rows' => '10'],
                    'trim' => true,
                    'required' => false,
                ]
            )
            ->add(
                'subject',
                TextType::class,
                [
                    'label' => 'Tytuł',
                    'trim' => true,
                    'required' => true,
                    'attr' => ['autofocus' => 'autofocus'],
                ]
            )
            ->add(
                'body',
                TextareaType::class,
                [
                    'label' => 'Treść',
                    'trim' => true,
                    'required' => false,
                    'attr' => ['rows' => '10'],
//                    'attr' => ['class' => 'tinymce'],
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
        $resolver->setDefaults([
            'data_class' => Template::class,
            'sender' => [
                'email' => '',
                'name' => '',
            ],
        ]);
    }
}

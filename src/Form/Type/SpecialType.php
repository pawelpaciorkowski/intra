<?php

declare(strict_types=1);

namespace App\Form\Type;

use App\Entity\Special;
use App\Entity\SpecialTemplate;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class SpecialType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'title',
                TextType::class,
                [
                    'label' => 'Tytuł',
                    'trim' => true,
                    'attr' => ['autofocus' => 'autofocus'],
                    'required' => true,
                ]
            )
            ->add(
                'specialTemplate',
                EntityType::class,
                [
                    'class' => SpecialTemplate::class,
                    'query_builder' => static function (EntityRepository $er) {
                        return $er->createQueryBuilder('st')->orderBy('st.name', 'ASC');
                    },
                    'label' => 'Szablon strony specjalnej',
                    'choice_label' => 'name',
                    'placeholder' => '-- brak --',
                    'attr' => ['title' => '-- brak --', 'data-live-search' => 'true'],
                    'choice_attr' => static function ($category) {
                        return ['class' => 'fa ' . $category->getName() . ' fa-choice'];
                    },
                    'required' => false,
                ]
            )
            ->add(
                'longText',
                TextareaType::class,
                [
                    'purify_html' => true,
                    'purify_html_profile' => 'iframe',
                    'label' => 'Tekst główny',
                    'trim' => true,
                    'required' => false,
                    'attr' => ['class' => 'tinymce'],
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
        $resolver->setDefaults(['data_class' => Special::class]);
    }
}

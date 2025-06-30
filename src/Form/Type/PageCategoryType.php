<?php

declare(strict_types=1);

namespace App\Form\Type;

use App\Entity\Page;
use App\Entity\PageCategory;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class PageCategoryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'isActive',
                CheckboxType::class,
                [
                    'label' => 'Aktywna',
                    'required' => false,
                ]
            )
            ->add(
                'page',
                EntityType::class,
                [
                    'class' => Page::class,
                    'query_builder' => static function (EntityRepository $er) {
                        return $er->createQueryBuilder('p');
                    },
                    'choice_label' => function (Page $page) {
                        return $page->getTitle();
                    },
                    'label' => 'Strona',
                    'placeholder' => '-- brak --',
                    'attr' => ['title' => '-- brak --', 'data-live-search' => 'true'],
                    'required' => true,
                    'empty_data' => [],
                    'disabled' => true,
                ]
            )
            ->add(
                'name',
                TextType::class,
                [
                    'label' => 'Nazwa',
                    'trim' => true,
                    'required' => true,
                    'attr' => ['autofocus' => 'autofocus'],
                ]
            )
            ->add(
                'description',
                TextareaType::class,
                [
                    'purify_html' => true,
                    'label' => 'Opis kategorii',
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
        $resolver->setDefaults(['data_class' => PageCategory::class]);
    }
}

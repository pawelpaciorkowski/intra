<?php

declare(strict_types=1);

namespace App\Form\Type;

use App\Entity\Category;
use App\Entity\Page;
use App\Entity\PageTemplate;
use App\Services\UserService;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class PageType extends AbstractType
{
    public function __construct(private readonly Security $security)
    {
    }

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
                'categories',
                EntityType::class,
                [
                    'class' => Category::class,
                    'query_builder' => static function (EntityRepository $er) {
                        return $er->createQueryBuilder('c')->orderBy('c.lft', 'ASC');
                    },
                    'label' => 'Kategorie',
                    'choice_label' => function (Category $category) {
                        return str_repeat(' · ', $category->getDepth()) . $category->getName();
                    },
                    'choice_attr' => function ($category): array {
                        if (
                            array_intersect($this->security->getUser()->getRoles(), UserService::ROLES_EDITOR)
                            && !$this->security->getUser()->getCategories()->contains($category)) {
                            return ['disabled' => true];
                        }

                        return [];
                    },
                    'multiple' => true,
                    'placeholder' => '-- wybierz --',
                    'attr' => ['title' => '-- wybierz --', 'data-live-search' => 'true'],
                    'required' => false,
                ]
            )
            ->add(
                'publishedAt',
                DateTimeType::class,
                [
                    'label' => 'Data publikacji',
                    'help' => 'Po tej dacie sortowane są strony (malejąco) w ramach kategorii',
                    'required' => true,
                    'widget' => 'single_text',
                    'format' => 'dd.MM.yyyy HH:mm',
                    'html5' => false,
                ]
            )
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
                'pageTemplate',
                EntityType::class,
                [
                    'class' => PageTemplate::class,
                    'query_builder' => static function (EntityRepository $er) {
                        return $er->createQueryBuilder('pt')->orderBy('pt.name', 'ASC');
                    },
                    'label' => 'Szablon strony',
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
                'shortText',
                TextareaType::class,
                [
                    'purify_html' => true,
                    'purify_html_profile' => 'iframe',
                    'label' => 'Tekst wstępny',
                    'trim' => true,
                    'required' => false,
                    'attr' => ['class' => 'tinymce'],
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
                'fileSections',
                CollectionType::class,
                [
                    'entry_type' => FileSectionType::class,
                    'allow_add' => true,
                    'allow_delete' => true,
                    'delete_empty' => true,
                    'error_bubbling' => false,
                    'by_reference' => false,
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
        $resolver->setDefaults(['data_class' => Page::class]);
    }
}

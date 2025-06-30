<?php

declare(strict_types=1);

namespace App\Form\Type;

use App\Entity\Category;
use App\Entity\CategoryTemplate;
use App\Entity\Icon;
use App\Entity\Link;
use App\Entity\Page;
use App\Entity\Team;
use App\Entity\User;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class CategoryType extends AbstractType
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
                'isShowCategories',
                CheckboxType::class,
                [
                    'label' => 'Wyświetlaj listę podkategorii',
                    'required' => false,
                ]
            )
            ->add(
                'isShowPagesInSubcategories',
                CheckboxType::class,
                [
                    'label' => 'Wyświetlaj strony także z podkategorii',
                    'required' => false,
                ]
            )
            ->add(
                'isShowChildren',
                CheckboxType::class,
                [
                    'label' => 'Wyświetlaj kategorie podrzędne w menu',
                    'required' => false,
                ]
            )
            ->add(
                'users',
                EntityType::class,
                [
                    'class' => User::class,
                    'query_builder' => static function (EntityRepository $er) {
                        return $er
                            ->createQueryBuilder('u')
                            ->leftjoin('u.team', 't')
                            ->where('t.id = :teamId')
                            ->setParameter('teamId', Team::EDITOR_ID)
                            ->orderBy('u.name', 'ASC')
                            ->addOrderBy('u.surname', 'ASC');
                    },
                    'label' => 'Edytorzy',
                    'help' => 'Lista edytorów którzy mają dostęp do danej kategorii',
                    'choice_label' => 'fullname',
                    'multiple' => true,
                    'placeholder' => '-- wybierz --',
                    'attr' => ['title' => '-- wybierz --', 'data-live-search' => 'true'],
                    'required' => false,
                ]
            )
            ->add(
                'icon',
                EntityType::class,
                [
                    'class' => Icon::class,
                    'query_builder' => static function (EntityRepository $er) {
                        return $er->createQueryBuilder('i')->orderBy('i.name', 'ASC');
                    },
                    'label' => 'Ikonka',
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
                'additionalIcon',
                EntityType::class,
                [
                    'class' => Icon::class,
                    'query_builder' => static function (EntityRepository $er) {
                        return $er->createQueryBuilder('i')->orderBy('i.name', 'ASC');
                    },
                    'label' => 'Ikonka dodatkowa',
                    'help' => 'Ikona pojawia się jako dodatkowa ikona z prawej stron pozycji w menu',
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
                'link',
                EntityType::class,
                [
                    'class' => Link::class,
                    'query_builder' => static function (EntityRepository $er) {
                        return $er->createQueryBuilder('l')->orderBy('l.name', 'ASC');
                    },
                    'label' => 'Link',
                    'choice_label' => 'name',
                    'placeholder' => '-- wybierz --',
                    'attr' => ['title' => '-- wybierz --', 'data-live-search' => 'true'],
                    'required' => false,
                ]
            )
            ->add(
                'page',
                EntityType::class,
                [
                    'class' => Page::class,
                    'query_builder' => static function (EntityRepository $er) {
                        return $er->createQueryBuilder('p')->orderBy('p.title', 'ASC');
                    },
                    'label' => 'Strona',
                    'choice_label' => 'title',
                    'placeholder' => '-- wybierz --',
                    'attr' => ['title' => '-- wybierz --', 'data-live-search' => 'true'],
                    'help' => 'Wybrana strona powoduje zignorowanie wartości wskazanych w polu link',
                    'required' => false,
                ]
            )
            ->add(
                'url',
                TextType::class,
                [
                    'label' => 'Url',
                    'trim' => true,
                    'required' => false,
                    'help' => 'Wpisany adres URL nadpisuje wartości wybrane w polach link i strona',
                ]
            )
            ->add(
                'highlights',
                EntityType::class,
                [
                    'class' => Link::class,
                    'query_builder' => static function (EntityRepository $er) {
                        return $er->createQueryBuilder('l')->orderBy('l.name', 'ASC');
                    },
                    'label' => 'Powiązane linki',
                    'help' => 'Lista linków, które powodują podświetlenie tej kategorii (dotyczy gdy wskazany jest link)',
                    'choice_label' => 'name',
                    'multiple' => true,
                    'placeholder' => '-- wybierz --',
                    'attr' => ['title' => '-- wybierz --', 'data-live-search' => 'true'],
                    'required' => false,
                ]
            )
            ->add(
                'categoryTemplate',
                EntityType::class,
                [
                    'class' => CategoryTemplate::class,
                    'query_builder' => static function (EntityRepository $er) {
                        return $er->createQueryBuilder('ct')->orderBy('ct.name', 'ASC');
                    },
                    'label' => 'Szablon kategorii',
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
        $resolver->setDefaults(['data_class' => Category::class]);
    }
}

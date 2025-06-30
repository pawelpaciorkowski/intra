<?php

declare(strict_types=1);

namespace App\Form\Type;

use App\Entity\Field;
use App\Entity\Link;
use App\Entity\Role;
use App\Entity\Section;
use App\Entity\Setting;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use App\Services\Component\ParameterBag;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class SettingType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'isActive',
                CheckboxType::class,
                [
                    'label' => 'Aktywne',
                    'required' => false,
                ]
            )
            ->add(
                'isHidden',
                CheckboxType::class,
                [
                    'label' => 'Ukryte',
                    'required' => false,
                    'help' => 'Ustawienie nie będzie widoczne na liście ustawień dla użytkownika',
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
                'key',
                TextType::class,
                [
                    'label' => 'Klucz',
                    'trim' => true,
                ]
            )
            ->add(
                'field',
                EntityType::class,
                [
                    'class' => Field::class,
                    'query_builder' => static function (EntityRepository $er) {
                        return $er->createQueryBuilder('f')->where('f.isActive = 1')->orderBy('f.name', 'ASC');
                    },
                    'choice_attr' => function ($choice) {
                        if (!$choice->getIsActive()) {
                            return ['class' => 'inactive'];
                        }

                        return [];
                    },
                    'label' => 'Typ pola',
                    'choice_label' => 'name',
                    'placeholder' => '-- wybierz --',
                    'empty_data' => [],
                    'attr' => ['data-live-search' => 'true'],
                    'required' => true,
                ]
            )
            ->add(
                'section',
                EntityType::class,
                [
                    'class' => Section::class,
                    'query_builder' => static function (EntityRepository $er) {
                        return $er->findAllByParams(
                            new ParameterBag([
                                'return_query' => true,
                                'orderBy' => 's.order asc',
                            ])
                        );
                    },
                    'label' => 'Sekcja',
                    'choice_label' => 'name',
                    'placeholder' => '-- wybierz --',
                    'empty_data' => [],
                    'attr' => ['data-live-search' => 'true'],
                    'required' => true,
                ]
            )
            ->add(
                'link',
                EntityType::class,
                [
                    'class' => Link::class,
                    'query_builder' => static function (EntityRepository $er) {
                        return $er->findAllByParams(
                            new ParameterBag([
                                'return_query' => true,
                                'orderBy' => 'l.name asc',
                            ])
                        );
                    },
                    'label' => 'Link',
                    'choice_label' => 'name',
                    'placeholder' => '-- wybierz --',
                    'attr' => ['data-live-search' => 'true'],
                    'required' => false,
                    'help' => 'Ustawienie będzie widoczne tylko takim użytkownikom, którzy mają uprawniania dostępu do wskazanego linku',
                ]
            )
            ->add(
                'roles',
                EntityType::class,
                [
                    'class' => Role::class,
                    'query_builder' => static function (EntityRepository $er) {
                        return $er->createQueryBuilder('r')->orderBy('r.name', 'ASC');
                    },
                    'label' => 'Role',
                    'choice_label' => 'name',
                    'multiple' => true,
                    'placeholder' => '-- wybierz --',
                    'attr' => ['title' => '-- wybierz --', 'data-live-search' => 'true'],
                    'required' => false,
                    'help' => 'Ustawienie będzie widoczne tylko użytkownikom z wskazaną rangą',
                ]
            )
            ->add(
                'default',
                TextType::class,
                [
                    'label' => 'Wartość domyślna',
                    'trim' => true,
                    'required' => true,
                ]
            )
            ->add(
                'min_value',
                TextType::class,
                [
                    'label' => 'Wartość minimalna',
                    'trim' => true,
                    'required' => false,
                ]
            )
            ->add(
                'max_value',
                TextType::class,
                [
                    'label' => 'Wartość maksymalna',
                    'trim' => true,
                    'required' => false,
                ]
            )
            ->add(
                'isEmpty',
                CheckboxType::class,
                [
                    'label' => 'Czy może być puste',
                    'required' => false,
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
        $resolver->setDefaults(['data_class' => Setting::class]);
    }
}

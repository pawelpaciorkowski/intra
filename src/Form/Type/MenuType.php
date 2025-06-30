<?php

declare(strict_types=1);

namespace App\Form\Type;

use App\Entity\Icon;
use App\Entity\Link;
use App\Entity\Menu;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class MenuType extends AbstractType
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
                    'choice_attr' => static function ($menu) {
                        return ['class' => 'fa ' . $menu->getName() . ' fa-choice'];
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
                    'choice_attr' => static function ($menu) {
                        return ['class' => 'fa ' . $menu->getName() . ' fa-choice'];
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
                'highlights',
                EntityType::class,
                [
                    'class' => Link::class,
                    'query_builder' => static function (EntityRepository $er) {
                        return $er->createQueryBuilder('l')->orderBy('l.name', 'ASC');
                    },
                    'label' => 'Powiązane linki',
                    'choice_label' => 'name',
                    'multiple' => true,
                    'placeholder' => '-- wybierz --',
                    'attr' => ['title' => '-- wybierz --', 'data-live-search' => 'true'],
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
        $resolver->setDefaults(['data_class' => Menu::class]);
    }
}

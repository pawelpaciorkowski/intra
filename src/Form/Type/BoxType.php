<?php

declare(strict_types=1);

namespace App\Form\Type;

use App\Entity\Box;
use App\Entity\Category;
use App\Entity\Enum\TargetType;
use App\Entity\Link;
use App\Entity\Page;
use App\Form\Type\Type\PhotoType;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class BoxType extends AbstractType
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
                'file',
                PhotoType::class,
                [
                    'help' => 'Docelowa rozdzielczość 792x446. Zdjęcie przesłane w innych proporcjach zostanie przycięte do rozdzielczości docelowej.',
                ]
            )
            ->add(
                'title',
                TextType::class,
                [
                    'label' => 'Tytuł',
                    'trim' => true,
                    'required' => true,
                    'attr' => ['autofocus' => 'autofocus'],
                ]
            )
            ->add(
                'shortText',
                TextareaType::class,
                [
                    'purify_html' => true,
                    'label' => 'Krótki opis',
                    'trim' => true,
                    'required' => false,
                    'attr' => ['class' => 'tinymce'],
                ]
            )
            ->add(
                'date',
                DateTimeType::class,
                [
                    'label' => 'Data',
                    'required' => false,
                    'widget' => 'single_text',
                    'format' => 'dd.MM.yyyy HH:mm',
                    'html5' => false,
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
                'category',
                EntityType::class,
                [
                    'class' => Category::class,
                    'query_builder' => static function (EntityRepository $er) {
                        return $er->createQueryBuilder('c')->orderBy('c.lft', 'ASC');
                    },
                    'label' => 'Kategoria',
                    'choice_label' => function (Category $category) {
                        return str_repeat(' · ', $category->getDepth()) . $category->getName();
                    },
                    'multiple' => false,
                    'placeholder' => '-- wybierz --',
                    'attr' => ['title' => '-- wybierz --', 'data-live-search' => 'true'],
                    'help' => 'Wybrana kategoria powoduje zignorowanie wartości wskazanych w polu strona',
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
                'targetType',
                EnumType::class,
                [
                    'class' => TargetType::class,
                    'choice_label' => static fn ($item) => $item->getLabel($item),
                    'label' => 'Target',
                    'required' => true,
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
        $resolver->setDefaults(['data_class' => Box::class]);
    }
}

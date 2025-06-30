<?php

declare(strict_types=1);

namespace App\Form\Type;

use App\Entity\PageCategory;
use App\Entity\PageFile;
use App\Form\Type\Type\AttachmentType;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class PageFileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $tagNames = $options['data']->getTags()->map(fn ($tag) => $tag->getName())->toArray();

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
                'pageCategory',
                EntityType::class,
                [
                    'class' => PageCategory::class,
                    'query_builder' => static function (EntityRepository $er) use ($options) {
                        return $er
                            ->createQueryBuilder('ic')
                            ->where('ic.page = :page')
                            ->setParameter('page', $options['page'])
                            ->orderBy('ic.lft', 'ASC');
                    },
                    'choice_label' => function (PageCategory $category) {
                        return str_repeat(' · ', $category->getDepth()) . $category->getName();
                    },
                    'label' => 'Kategoria',
                    'placeholder' => '-- brak --',
                    'attr' => ['title' => '-- brak --', 'data-live-search' => 'true'],
                    'required' => true,
                    'empty_data' => [],
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
                    'label' => 'Opis',
                    'trim' => true,
                    'required' => false,
                    'attr' => ['class' => 'tinymce'],
                ]
            )
            ->add(
                'currentFile',
                AttachmentType::class,
                [
                    'label' => 'Plik wyświetlany',
                    'help' => 'Maksymalna wielkość pliku 128MB, tylko pliki w formacie PDF, DOC(X), XLS(X), PPTX, ODT, JP(E)G, GIF, PNG, HEIC, BMP, TIFF, SVG, WEBP',
                ]
            )
            ->add(
                'originalFile',
                AttachmentType::class,
                [
                    'label' => 'Plik źródłowy',
                    'help' => "Plik nie jest widoczny publicznie na stronie. Maksymalna wielkość pliku 128MB, tylko pliki w formacie PDF, DOC(X), XLS(X), PPTX, ODT, JP(E)G, GIF, PNG, HEIC, BMP, TIFF, SVG, WEBP",
                ]
            )
            ->add(
                'tags',
                TextType::class,
                [
                    'required' => false,
                    'mapped' => false,
                    'label' => 'Tagi',
                    'attr' => [
                        'class' => 'tag-input'
                    ],
                    'data' => implode(',', $tagNames),
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
            'data_class' => PageFile::class,
            'page' => null,
        ]);
    }
}

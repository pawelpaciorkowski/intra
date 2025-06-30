<?php

declare(strict_types=1);

namespace App\Form\Type;

use App\Entity\Department;
use App\Entity\Employee;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class EmployeeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'departments',
                EntityType::class,
                [
                    'class' => Department::class,
                    'query_builder' => static function (EntityRepository $er) {
                        return $er->createQueryBuilder('d')->orderBy('d.lft', 'ASC');
                    },
                    'label' => 'Departamenty',
                    'choice_label' => function (Department $department) {
                        return str_repeat(' · ', $department->getDepth()) . $department->getName();
                    },
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
                    'label' => 'Imię',
                    'trim' => true,
                    'required' => true,
                    'attr' => ['autofocus' => 'autofocus'],
                ]
            )
            ->add(
                'surname',
                TextType::class,
                [
                    'label' => 'Nazwisko',
                    'trim' => true,
                    'required' => true,
                ]
            )
            ->add(
                'email',
                EmailType::class,
                [
                    'label' => 'Email',
                    'trim' => true,
                    'required' => false,
                ]
            )
            ->add(
                'position',
                TextType::class,
                [
                    'label' => 'Stanowisko',
                    'trim' => true,
                    'required' => false,
                ]
            )
            ->add(
                'phones',
                CollectionType::class,
                [
                    'entry_type' => PhoneType::class,
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
        $resolver->setDefaults(['data_class' => Employee::class]);
    }
}

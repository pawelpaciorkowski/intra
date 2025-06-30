<?php

declare(strict_types=1);

namespace App\Form\Type;

use App\Entity\Category;
use App\Entity\Position;
use App\Entity\Team;
use App\Entity\User;
use App\Form\Type\Type\AvatarType;
use App\Services\Component\ParameterBag;
use App\Services\PositionService;
use App\Services\TeamService;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class UserType extends AbstractType
{
    private $teamService;
    private $positionService;

    public function __construct(
        TeamService $teamService,
        PositionService $positionService
    ) {
        $this->teamService = $teamService;
        $this->positionService = $positionService;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) {
                $event
                    ->getForm()
                    ->add(
                        'team',
                        EntityType::class,
                        [
                            'class' => Team::class,
                            'query_builder' => $this->teamService->findAllByParams(
                                new ParameterBag([
                                    'return_query' => true,
                                    'is-active' => 1,
                                ])
                            ),
                            'choice_attr' => function ($choice) {
                                if (!$choice->getIsActive()) {
                                    return ['class' => 'inactive'];
                                }

                                return [];
                            },
                            'label' => 'Grupa',
                            'choice_label' => 'name',
                            'placeholder' => '-- wybierz --',
                            'empty_data' => [],
                            'attr' => ['data-live-search' => 'true'],
                            'required' => true,
                            'disabled' => (bool)$event->getData()->getId(),
                        ]
                    );
            }
        );

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
                    'multiple' => true,
                    'placeholder' => '-- wybierz --',
                    'attr' => ['title' => '-- wybierz --', 'data-live-search' => 'true'],
                    'required' => false,
                    'help' => 'W przypadku użytkownika z grupy "Redaktor", kategorie w których będzie można edytować strony'
                ]
            )
            ->add(
                'isPasswordChangeRequired',
                CheckboxType::class,
                [
                    'label' => 'Wymagana okresowa zmiana hasła',
                    'required' => false,
                ]
            )
            ->add(
                'position',
                EntityType::class,
                [
                    'class' => Position::class,
                    'query_builder' => $this->positionService->findAllByParams(
                        new ParameterBag([
                            'return_query' => true,
                        ])
                    ),
                    'label' => 'Stanowisko',
                    'choice_label' => 'name',
                    'placeholder' => '-- wybierz --',
                    'empty_data' => [],
                    'attr' => ['data-live-search' => 'true'],
                    'required' => true,
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
                'username',
                TextType::class,
                [
                    'label' => 'Login',
                    'trim' => true,
                    'required' => true,
                ]
            )
            ->add(
                'password',
                RepeatedType::class,
                [
                    'type' => PasswordType::class,
                    'invalid_message' => 'Hasła muszą się zgadzać.',
                    'options' => ['attr' => ['class' => 'password-field']],
                    'first_options' => [
                        'label' => 'Hasło',
                        'attr' => ['class' => 'pwstrength', 'placeholder' => 'minimum 10 znaków'],
                    ],
                    'second_options' => ['label' => 'Potwórz hasło'],
                    'required' => false,
                ]
            )
            ->add(
                'email',
                EmailType::class,
                [
                    'label' => 'E-mail',
                    'trim' => true,
                    'required' => true,
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
                    'row_attr' => ['class' => 'public'],
                ]
            )
            ->add(
                'file',
                AvatarType::class
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
        $resolver->setDefaults(
            [
                'data_class' => User::class,
                'validation_groups' => static function (FormInterface $form) {
                    $validationGroup = ['user_crud', 'phone'];

                    $data = $form->getData();

                    if (!$data->getId()) {
                        $validationGroup[] = 'create_password';
                    } else {
                        $validationGroup[] = 'edit_password';
                    }

                    if ($data->getTeam() && Team::REGION_MANAGER_ID === $data->getTeam()->getId()) {
                        $validationGroup[] = 'region';
                    }

                    return $validationGroup;
                },
            ]
        );
    }
}

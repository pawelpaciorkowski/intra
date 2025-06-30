<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Team;
use App\Entity\User;
use App\Repository\Traits\QueryBuilderExtension;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bridge\Doctrine\Security\User\UserLoaderInterface;
use App\Services\Component\ParameterBagInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\UserInterface;

use function count;
use function get_class;
use function is_subclass_of;
use function sprintf;

final class UserRepository extends ServiceEntityRepository implements UserLoaderInterface
{
    use QueryBuilderExtension;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function findAllByParams(?ParameterBagInterface $parameterBag = null)
    {
        $this->parameterBag = $parameterBag;

        $this->query = $this
            ->getEntityManager()
            ->createQueryBuilder()
            ->select(['u', 't', 'po', 'cp', 'cp2', 'ph'])
            ->from(User::class, 'u')
            ->join('u.team', 't')
            ->leftJoin('t.role', 'r')
            ->leftJoin('u.position', 'po')
            ->leftJoin('u.collectionPoints', 'cp')
            ->leftJoin('u.collectionPoints2', 'cp2')
            ->leftJoin('u.phones', 'ph');

        if ($parameterBag && $parameterBag->has('roles')) {
            $this->query->andWhere('r.name IN (:roles)')->setParameter('roles', $parameterBag->get('roles'));
        }

        if ($parameterBag && $parameterBag->has('restrictForApi')) {
            $this
                ->query
                ->andWhere('t.uuid IN (:restrictForApi)')
                ->setParameter('restrictForApi', Team::ALLOWED_ENTITIES);
        }

        return $this
            ->includeFilterQueryData(
                [
                    'name' => ['u.name'],
                    'surname' => ['u.surname'],
                    'team' => ['t.name'],
                    'login' => ['u.username'],
                    'email' => ['u.email'],
                    'phone' => ['ph.number'],
                    'position' => ['po.name'],
                    'collection-point-coordinator' => ['cp.name'],
                    'collection-point-region' => ['cp2.name'],
                    'query' => [
                        'u.name',
                        'u.surname',
                        't.name',
                        'u.username',
                        'u.email',
                        'po.name',
                        'ph.number',
                        'cp.name',
                        'cp2.name',
                    ],
                ]
            )
            ->where(
                [
                    'is-active' => ['t.isActive', 'u.isActive'],
                    'id' => 'u.id',
                    'user-id' => 'u.id',
                    'team-id' => 't.id',
                    'username' => 'u.username',
                    'uuid' => 'u.uuid',
                ]
            )
            ->order('u.name')
            ->group()
            ->return();
    }

    public function loadUserByUsername($username): User
    {
        $user = $this->createQueryBuilder('u')
            ->where('u.username = :username OR u.email = :email')
            ->setParameter('username', $username)
            ->setParameter('email', $username)
            ->getQuery()
            ->getOneOrNullResult();

        if (null === $user) {
            $message = sprintf('Unable to find an active admin User object identified by "%s".', $username);

            throw new UsernameNotFoundException($message);
        }

        return $user;
    }

    public function refreshUser(UserInterface $user)
    {
        $class = get_class($user);
        if (!$this->supportsClass($class)) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $class));
        }

        return $this->find($user->getId());
    }

    public function supportsClass(string $class): bool
    {
        return $this->getEntityName() === $class || is_subclass_of($class, $this->getEntityName());
    }

    public function findUserByUsernameOrEmail(string $usernameOrEmail): ?User
    {
        $this->query = $this
            ->getEntityManager()
            ->createQueryBuilder()
            ->select('u')
            ->from(User::class, 'u')
            ->where('u.username = :usernameOrEmail or u.email = :usernameOrEmail')
            ->setParameter('usernameOrEmail', $usernameOrEmail);

        $user = $this->return();

        if (1 === count($user)) {
            return $user[0];
        }

        return null;
    }

    public function isAccessableForApi(User $user): bool
    {
        return $user->getTeam() && in_array($user->getTeam()->getUuid(), Team::ALLOWED_ENTITIES, true);
    }

    public function loadUserByIdentifier(string $identifier): ?UserInterface
    {
        return $this->findUserByUsernameOrEmail($identifier);
    }
}

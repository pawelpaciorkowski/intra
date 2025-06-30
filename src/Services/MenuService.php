<?php

declare(strict_types=1);

namespace App\Services;

use App\Entity\Menu;
use App\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Config\Definition\Exception\Exception;
use App\Services\Component\ParameterBag;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\User\UserInterface;

use function count;
use function in_array;
use function is_array;

final class MenuService
{
    private $selectedMenu;

    private $selectedPath;

    private $loopDetector;

    private $entityManager;
    private $requestStack;
    private $securityService;
    private $entityService;

    public function __construct(
        EntityManagerInterface $entityManager,
        RequestStack $requestStack,
        SecurityService $securityService,
        EntityService $entityService
    ) {
        $this->entityManager = $entityManager;
        $this->requestStack = $requestStack;
        $this->securityService = $securityService;
        $this->entityService = $entityService;
    }

    public function setOrder(array $data): bool
    {
        $this->entityManager->getConnection()->beginTransaction();

        try {
            foreach ($data as $row) {
                if ($row->id) {
                    $category = $this->entityManager->getRepository(Menu::class)->find($row->id);

                    $category->setLft($row->left - 2);
                    $category->setRgt($row->right - 2);
                    $category->setDepth($row->depth);

                    if ($row->parent_id) {
                        $category->setParent($this->entityManager->getReference(Menu::class, $row->parent_id));
                    } else {
                        $category->setParent();
                    }
                }
            }
            $this->entityManager->flush();
            $this->loopDetection();
            $this->entityManager->getConnection()->commit();

            $this->updateMenu();
            $this->rebuildRoles();
        } catch (Exception) {
            $this->entityManager->getConnection()->rollBack();

            return false;
        }

        return true;
    }

    private function loopDetection(): void
    {
        $this->loopDetector = [];
        $this->loopDetectionWorker();

        $count = $this
            ->entityManager
            ->getRepository(Menu::class)
            ->fetchAllCount();

        // if worker founds less categories then exists in database, throw Exception
        if ((int)$count !== count($this->loopDetector)) {
            throw new Exception('loop detected!');
        }
    }

    private function loopDetectionWorker($parent = null): void
    {
        $categories = $this
            ->entityManager
            ->getRepository(Menu::class)
            ->findAllByParams(new ParameterBag(['parent' => $parent]));

        foreach ($categories as $category) {
            if (in_array($category->getId(), $this->loopDetector, true)) {
                throw new Exception('loop detected!');
            }
            $this->loopDetector[] = $category->getId();

            $this->loopDetectionWorker($category);
        }
    }

    public function findAllByParams($parameterBag = null)
    {
        if (is_array($parameterBag)) {
            $parameterBag = new ParameterBag($parameterBag);
        } elseif (null === $parameterBag) {
            $parameterBag = new ParameterBag();
        }

        if ($parameterBag->has('withAuthUser') && $parameterBag->has('user')) {
            $parameterBag->add(['roles' => $this->securityService->getUserInheritedRoles($parameterBag->get('user'))]);
        }

        return $this->entityService->findAllByParams(Menu::class, $parameterBag);
    }

    public function updateMenu(): self
    {
        $this->updateBranch();
        $this->entityManager->flush();

        return $this;
    }

    private function updateBranch(?Menu $parent = null, $depth = 0, $lft = 1): array
    {
        $categories = $this
            ->entityManager
            ->getRepository(Menu::class)
            ->findAllByParams(new ParameterBag(['parent' => $parent]));

        $start = $rgt = $lft;
        $allRoles = new ArrayCollection();

        foreach ($categories as $category) {
            $currentRoles = new ArrayCollection();
            if ($category->getLink() && $category->getLink()->getRoles()) {
                $currentRoles = $category->getLink()->getRoles();
            }

            [$count, $pathRoles] = $this->updateBranch($category, $depth + 1, $lft + 1);

            $currentRoles = $this->mergeRoles($currentRoles, $pathRoles);
            $allRoles = $this->mergeRoles($allRoles, $currentRoles);

            $rgt = $lft + $count + 1;
            if ($count) {
                ++$rgt;
            }

            $category
                ->setDepth($depth)
                ->setLft($lft)
                ->setRgt($rgt);

            $this->entityManager->persist($category);

            $lft = $rgt + 1;
        }

        return [$rgt - $start, $allRoles];
    }

    private function mergeRoles(Collection $targets, Collection $sources): Collection
    {
        $return = $targets;

        foreach ($sources as $source) {
            foreach ($targets as $t) {
                if ($t->getId() === $source->getId()) {
                    continue 2;
                }
            }
            $return->add($source);
        }

        return $return;
    }

    public function rebuildRoles(): void
    {
        $menus = $this->entityManager->getRepository(Menu::class)->findBy(['depth' => 0]);

        foreach ($menus as $menu) {
            $this->updateRolesForLeaf($menu);
        }

        $this->entityManager->flush();
    }

    public function updateRolesForLeaf(Menu $menu): array
    {
        $return = [];

        if (1 === $menu->getRgt() - $menu->getLft()) {
            if ($menu->getLink()) {
                foreach ($menu->getLink()->getRoles() as $role) {
                    $return[$role->getId()] = $role;
                }
            }
        } else {
            $children = $this
                ->entityManager
                ->getRepository(Menu::class)
                ->fetchChildren(
                    $menu->getDepth(),
                    $menu->getLft(),
                    $menu->getRgt()
                );

            foreach ($children as $child) {
                $return += $this->updateRolesForLeaf($child);
            }
        }

        if ($menu->getRoles()) {
            foreach ($menu->getRoles() as $role) {
                $menu->removeRole($role);
            }
        }

        foreach ($return as $role) {
            $menu->addRole($role);
        }

        return $return;
    }

    /**
     * Get selectedPath.
     */
    public function getSelectedPath(?UserInterface $user)
    {
        if (!$this->selectedPath) {
            $this->readSelectedPath($user);
        }

        return $this->selectedPath;
    }

    private function readSelectedPath(?UserInterface $user): void
    {
        if (!$this->selectedMenu) {
            $this->readSelectedMenu($user);
        }

        if ($this->selectedMenu) {
            $this->selectedPath = $this->entityManager->getRepository(Menu::class)->fetchPath($this->selectedMenu);
        }
    }

    private function readSelectedMenu(?UserInterface $user): void
    {
        $request = $this->requestStack->getCurrentRequest();

        if (!$request) {
            return;
        }

        $this->selectedMenu = $this
            ->entityManager
            ->getRepository(Menu::class)
            ->fetchMenuByRoute(
                $request->attributes->get('_route'),
                $this->securityService->getUserInheritedRoles($user)
            );
    }

    public function getSelectedLeaf(?User $user)
    {
        if (!$this->selectedMenu) {
            $this->readSelectedMenu($user);
        }

        return $this->selectedMenu;
    }
}

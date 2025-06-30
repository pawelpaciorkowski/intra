<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Entity\Table;
use App\Entity\TableColumn;
use App\Entity\UserTableColumnVisible;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations\Post;
use FOS\RestBundle\View\View;
use App\Services\Component\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Security\Core\User\UserInterface;

final class ApiUserTableColumnVisibleController extends AbstractFOSRestController
{
    #[Post("/api/user-table-column-visible", name: "api-post-user-column", options: ["method_prefix" => false])]
    public function postAction(Request $request, UserInterface $user, EntityManagerInterface $entityManager): View
    {
        $columnVisible = $entityManager->getRepository(UserTableColumnVisible::class)->findRowByParams(
            new ParameterBag([
                'tableName' => $request->request->get('table'),
                'columnName' => $request->request->get('column'),
                'user' => $user,
            ])
        );

        if (!$columnVisible) {
            $columnVisible = new UserTableColumnVisible();
        }

        $table = $entityManager
            ->getRepository(Table::class)
            ->findOneBy(['table' => $request->request->get('table')]);

        if (!$table) {
            throw new HttpException(404, 'Table not found');
        }

        $column = $entityManager
            ->getRepository(TableColumn::class)
            ->findOneBy(['column' => $request->request->get('column'), 'table' => $table]);

        if (!$column) {
            throw new HttpException(404, 'Column not found');
        }

        $columnVisible
            ->setTableColumn($column)
            ->setIsVisible((bool)$request->request->get('status'))
            ->setUser($user);

        $entityManager->persist($columnVisible);
        $entityManager->flush();

        return View::create(['status' => true]);
    }
}

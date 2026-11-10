<?php

namespace App\Controller;

use App\Repository\ClientRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\UX\Pagination\PaginatorInterface;

final class ClientController extends AbstractController
{
    private const PER_PAGE = 10;

    #[Route('/client', name: 'client')]
    public function index(ClientRepository $clientRepository, PaginatorInterface $paginator): Response
    {
        // The current page is read from ?page= automatically; an out-of-range page returns a 404.
        $clients = $paginator
            ->query($clientRepository->createWithProjectsQueryBuilder())
            ->perPage(self::PER_PAGE)
            ->throwOnOutOfRange()
            ->paginate();

        return $this->render('client/index.html.twig', [
            'clients' => $clients,
        ]);
    }
}

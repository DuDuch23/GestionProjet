<?php

namespace App\Controller;

use App\Repository\ProjectRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\UX\Pagination\PaginatorInterface;

final class ProjectController extends AbstractController
{
    private const PER_PAGE = 10;

    #[Route('/project', name: 'project')]
    public function index(ProjectRepository $projectRepository, PaginatorInterface $paginator): Response
    {
        // The current page is read from ?page= automatically; an out-of-range page returns a 404.
        $projects = $paginator
            ->query($projectRepository->createActiveQueryBuilder())
            ->perPage(self::PER_PAGE)
            ->throwOnOutOfRange()
            ->paginate();

        return $this->render('project/index.html.twig', [
            'projects' => $projects,
        ]);
    }
}

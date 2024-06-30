<?php

namespace App\Controller;

use App\Entity\Country;
use App\Form\CountryType;
use App\Service\CountryService;
use Knp\Component\Pager\PaginatorInterface;
use App\Repository\CountryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

use Symfony\Component\HttpFoundation\JsonResponse;

#[Route('/country')]
class CountryController extends AbstractController
{
    private $countryService;
    private $paginator;
    private $countryRepository;

    public function __construct(CountryService $countryService, PaginatorInterface $paginator, CountryRepository $countryRepository)
    {
        $this->countryService = $countryService;
        $this->paginator = $paginator;
        $this->countryRepository = $countryRepository;
    }

    #[Route('/', name: 'app_country_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        try {
            // Llamar al servicio para obtener y almacenar los datos
            $this->countryService->fetchAndStoreCountries();

            $page = $request->query->getInt('page', 1);
            $pageSize = 10; // num de elementos por pagina

            $query = $this->countryRepository->createQueryBuilder('c')->getQuery();

            // Paginar los resultados
            $pagination = $this->paginator->paginate(
                $query,
                $page,
                $pageSize
            );

            return $this->render('country/index.html.twig', [
                'pagination' => $pagination,
            ]);
        } catch (\Exception $e) {
           
            $errorMessage = 'Error al cargar los países: ' . $e->getMessage();
            $this->addFlash('error', $errorMessage);

            return $this->redirectToRoute('app_country_index');
        }
    }


    #[Route('/new', name: 'app_country_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        try {
            $country = new Country();
            $form = $this->createForm(CountryType::class, $country);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $entityManager->persist($country);
                $entityManager->flush();

                $this->addFlash('success', 'Country created successfully!');

                return $this->redirectToRoute('app_country_index', [], Response::HTTP_SEE_OTHER);
            }

            return $this->render('country/new.html.twig', [
                'country' => $country,
                'form' => $form->createView(),
            ]);
        } catch (\Exception $e) {
            $this->addFlash('error', 'Failed to create country: ' . $e->getMessage());
            return $this->redirectToRoute('app_country_new');
        }
    }

    #[Route('/{id}', name: 'app_country_show', methods: ['GET'])]
    public function show(Country $country): Response
    {
        return $this->render('country/show.html.twig', [
            'country' => $country,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_country_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Country $country, EntityManagerInterface $entityManager): Response
    {
        try {
            $form = $this->createForm(CountryType::class, $country);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $entityManager->flush();

                $this->addFlash('success', 'Country updated successfully!');

                return $this->redirectToRoute('app_country_index', [], Response::HTTP_SEE_OTHER);
            }

            return $this->render('country/edit.html.twig', [
                'country' => $country,
                'form' => $form->createView(),
            ]);
        } catch (\Exception $e) {
            $this->addFlash('error', 'Failed to update country: ' . $e->getMessage());
            return $this->redirectToRoute('app_country_edit', ['id' => $country->getId()]);
        }
    }

    #[Route('/{id}', name: 'app_country_delete', methods: ['POST'])]
    public function delete(Request $request, Country $country, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$country->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($country);
            $entityManager->flush();

            $this->addFlash('success', 'Country deleted successfully!');
        }

        return $this->redirectToRoute('app_country_index', [], Response::HTTP_SEE_OTHER);
    }


    #[Route('/country/search', name: 'app_country_search', methods: ['GET'])]
    public function search(Request $request): Response
    {
        $query = $request->query->get('query', '');

       
        $countries = $this->countryRepository->createQueryBuilder('c')
            ->where('c.name LIKE :query OR c.capital LIKE :query OR c.region LIKE :query')
            ->setParameter('query', '%' . $query . '%')
            ->getQuery()
            ->getResult();

        //dd($countries); 

        return $this->render('country/_country_list.html.twig', [
            'countries' => $countries,
        ]);
    }


    #[Route('/country/truncate-and-reload', name: 'app_country_truncate_and_reload', methods: ['POST'])]
    public function truncateAndReload(Request $request): JsonResponse
    {
        if ($this->isCsrfTokenValid('truncate_and_reload', $request->request->get('_token'))) {
            try {
                // Truncate the country table
                $this->countryService->truncateCountryTable();

                // Fetch and store countries again
                $this->countryService->fetchAndStoreCountries();

                return new JsonResponse(['status' => 'success', 'message' => 'Countries truncated and reloaded successfully!']);
            } catch (\Exception $e) {
                return new JsonResponse(['status' => 'error', 'message' => 'An error occurred: ' . $e->getMessage()]);
            }
        }

        return new JsonResponse(['status' => 'error', 'message' => 'Invalid CSRF token.']);
    }
}

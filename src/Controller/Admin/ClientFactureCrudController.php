<?php

namespace App\Controller\Admin;

use App\Entity\ClientContact;
use App\Entity\ClientFacture;
use App\Form\ClientFactureType;
use App\Form\ServiceType;
use App\Repository\ClientContactRepository;
use App\Service\PdfGeneratorService;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Config\KeyValueStore;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\BatchActionDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Doctrine\ORM\EntityRepository;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\ChoiceList\Loader\CallbackChoiceLoader;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

class ClientFactureCrudController extends AbstractCrudController
{
    private PdfGeneratorService $pdfGeneratorService;
    private AdminUrlGenerator $adminUrlGenerator;
    private RequestStack $requestStack;
    private ClientContactRepository $contactRepository;

    public function __construct(
        PdfGeneratorService $pdfGeneratorService,
        AdminUrlGenerator $adminUrlGenerator,
        RequestStack $requestStack,
        ClientContactRepository $contactRepository
    ) {
        $this->pdfGeneratorService = $pdfGeneratorService;
        $this->adminUrlGenerator = $adminUrlGenerator;
        $this->requestStack = $requestStack;
        $this->contactRepository = $contactRepository;
    }

    public static function getEntityFqcn(): string
    {
        return ClientFacture::class;
    }

    public function configureActions(Actions $actions): Actions
    {
        $generatePdfAction = Action::new('generatePdf', 'Generate PDF')
            ->linkToCrudAction('generatePdf')
            ->setCssClass('btn btn-primary')
            ->displayIf(static function ($entity) {
                return !$entity->isPdf();
            });

        $customNewAction = Action::new('customNew', 'Create')
        ->linkToUrl($this->adminUrlGenerator->setController(
            'App\Controller\Admin\ClientFactureCrudController', // Use the full class name
            'customNewAction'
        )->generateUrl());
   
        return $actions
            ->add(Crud::PAGE_INDEX, $generatePdfAction)
            ->add(Crud::PAGE_NEW, $customNewAction);
    }

    public function generatePdf(AdminContext $context, EntityManagerInterface $manager): Response
    {
        $clientFacture = $context->getEntity()->getInstance();
    
        if (!$clientFacture instanceof ClientFacture) {
            $this->addFlash('danger', 'Invalid Client Facture.');
            return $this->redirect($this->generateUrl('admin')); // Fallback route
        }
    
        $fileName = sprintf('facture_%d.pdf', $clientFacture->getId());
        $outputDir = $this->getParameter('kernel.project_dir') . '/public/factures_Client';
        $template = 'pdf/invoice_client.html.twig';
    
        try {
            $filePath = $this->pdfGeneratorService->generatePdf(
                $template,
                ['facture' => $clientFacture],
                $outputDir,
                $fileName
            );
            $clientFacture->setPdf(true);
            $manager->persist($clientFacture);
            $manager->flush();
    
            $this->addFlash('success', 'Facture générée avec succès.');
            
        } catch (\Exception $e) {
            $this->addFlash('danger', 'Une erreur s\'est produite lors de la génération de la facture : ' . $e->getMessage());
        }
    
        $referrer = $context->getReferrer() ?: $this->generateUrl('admin'); // Fallback to dashboard if referrer is null
        return $this->redirect($referrer);
    }
    
    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Facture de Client')
            ->setEntityLabelInPlural('Factures de Client')
       
        ;
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add('isPaid')
            ->add('createdAt');
    }

    
    public function configureFields(string $pageName): iterable
    {
       
          return [
            IdField::new('id')->hideOnForm(),
            AssociationField::new('client', 'Client'),
        
            AssociationField::new('contact', 'Contact')
            ->setFormTypeOptions([
                'attr' => [
                    'id' => 'ClientFacture_contact',
                    'data-client-id' => 'ClientFacture_client',
                ],
            ])
            ->setRequired(false)
       
            ->hideWhenUpdating(),
       
    
          
            AssociationField::new('service', 'Services'),
            MoneyField::new('totalPrice', 'Total Price')
                ->setCurrency('EUR')
                ->setStoredAsCents(false),
            BooleanField::new('isPdf', 'Créer facture')->hideOnForm(),
            BooleanField::new('isPaid', 'Facture Payée')->hideOnForm(),
            DateTimeField::new('createdAt', 'Created At')
                ->setSortable(true)
                ->hideOnForm(),  
                
        ];
        

    }

    #[Route('/admin/client_facture/new', name: 'admin_client_facture_new')]
    public function customNewAction(Request $request, EntityManagerInterface $entityManager): Response
    {

        $clientFacture = new ClientFacture();
        $form = $this->createForm(ClientFactureType::class, $clientFacture);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($clientFacture);
            $entityManager->flush();

            $this->addFlash('success', 'Client Facture created successfully!');

            return $this->redirectToRoute('admin_index'); 
        }

        return $this->render('admin/client_facture/new.html.twig', [
            'form' => $form,
          
        ]);
    }

    #[Route('/admin/client-facture/contacts/{id}', name: 'app_clientFacture_contacts', methods: ['GET'])]
    public function getContacts(int $id, ClientContactRepository $repository): JsonResponse
    {
        $contacts = $repository->findBy(['client' => $id]);

        $response = [];
        foreach ($contacts as $contact) {
            $response[] = [
                'id' => $contact->getId(),
                'title' => $contact->getTitle(),
            ];
        }

        return new JsonResponse($response);
    }

   


 
    /*
    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id'),
            TextField::new('title'),
            TextEditorField::new('description'),
        ];
    }
    */
}

<?php declare(strict_types=1);

namespace Frosh\ViewExporter\Controller;

use Frosh\ViewExporter\Export\Exporter;
use Frosh\ViewExporter\Message\FroshExportMessage;
use Shopware\Core\Framework\DataAbstractionLayer\DefinitionInstanceRegistry;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\RequestCriteriaBuilder;
use Shopware\Core\Framework\Routing\Annotation\RouteScope;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @RouteScope(scopes={"api", "administration"})
 */
class ListingExportController extends AbstractController
{
    protected DefinitionInstanceRegistry $definitionRegistry;
    protected RequestCriteriaBuilder     $criteriaBuilder;
    protected EntityRepositoryInterface  $entityRepository;
    protected MessageBusInterface        $messageBus;
    protected Exporter                   $exporter;

    public function __construct(
        DefinitionInstanceRegistry $definitionRegistry,
        RequestCriteriaBuilder     $criteriaBuilder,
        EntityRepositoryInterface  $entityRepository,
        MessageBusInterface        $messageBus,
        Exporter                   $exporter
    ) {
        $this->definitionRegistry = $definitionRegistry;
        $this->criteriaBuilder    = $criteriaBuilder;
        $this->entityRepository   = $entityRepository;
        $this->messageBus         = $messageBus;
        $this->exporter           = $exporter;
    }

    /**
     * @Route("/api/frosh/export/trigger/{id}", name="api.frosh.export.trigger", methods={"GET"})
     */
    public function triggerExport(string $id): Response
    {
        $this->messageBus->dispatch(new FroshExportMessage($id));

        return new Response($id);
    }

    /**
     * @Route("/api/frosh/export/{id}", name="api.frosh.export", methods={"GET"})
     */
    public function triggerExportDebug(Request $request, string $id): Response
    {
        $file = $this->exporter->export($id);

        return new RedirectResponse($request->getSchemeAndHttpHost() . '/' . $file);
    }
}

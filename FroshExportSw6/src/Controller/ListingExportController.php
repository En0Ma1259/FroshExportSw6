<?php declare(strict_types=1);

namespace Frosh\Exporter\Controller;

use Doctrine\DBAL\Connection;
use Frosh\Exporter\Export\Exporter;
use Frosh\Exporter\Message\FroshExportMessage;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\DefinitionInstanceRegistry;
use Shopware\Core\Framework\DataAbstractionLayer\Doctrine\RetryableQuery;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
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
    protected Connection                 $connection;
    protected MessageBusInterface        $messageBus;
    protected Exporter                   $exporter;

    public function __construct(
        DefinitionInstanceRegistry $definitionRegistry,
        RequestCriteriaBuilder     $criteriaBuilder,
        Connection                 $connection,
        MessageBusInterface        $messageBus,
        Exporter                   $exporter
    ) {
        $this->definitionRegistry = $definitionRegistry;
        $this->criteriaBuilder    = $criteriaBuilder;
        $this->connection         = $connection;
        $this->messageBus         = $messageBus;
        $this->exporter           = $exporter;
    }

    /**
     * @Route("/api/frosh/export/criteria/{id}/{entity}", name="api.frosh.export.criteria", methods={"POST"})
     */
    public function updateCustomCriteria(Request $request, Context $context, string $id, string $entity): Response
    {
        $criteria         = new Criteria();
        $entityDefinition = $this->definitionRegistry->getByEntityName($entity);
        $this->criteriaBuilder->handleRequest($request, $criteria, $entityDefinition, $context);

        $query = new RetryableQuery(
            $this->connection,
            $this->connection->prepare('UPDATE `frosh_export` SET `criteria` = :criteria WHERE `id` = UNHEX(:id)')
        );
        $query->execute(['id' => $id, 'criteria' => serialize($criteria)]);

        return new Response();
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

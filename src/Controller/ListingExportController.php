<?php declare(strict_types=1);

namespace Frosh\Exporter\Controller;

use Frosh\Exporter\Export\CriteriaBuilder;
use Frosh\Exporter\Export\Exporter;
use Frosh\Exporter\Message\FroshExportMessage;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Log\Package;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route(defaults: ['_routeScope' => ['api']])]
#[Package('administration')]
class ListingExportController extends AbstractController
{
    public function __construct(
        protected CriteriaBuilder     $criteriaBuilder,
        protected MessageBusInterface $messageBus,
        protected Exporter            $exporter
    ) {
    }

    /**
     * @Route(
     *     "/api/frosh/export/{id}/criteria",
     *     name="api.frosh.export.update.criteria",
     *     methods={"POST"}
     * )
     */
    public function updateCustomCriteria(Request $request, Context $context, string $id): Response
    {
        $criteria = $this->criteriaBuilder->buildCriteria($id, $request, $context);
        $this->criteriaBuilder->updateCriteria($id, $criteria);

        return new Response();
    }

    /**
     * @Route(
     *     "/api/frosh/export/{id}/criteria",
     *     name="api.frosh.export.load.criteria",
     *     methods={"GET"}
     * )
     */
    public function loadCriteria(string $id): Response
    {
        return new JsonResponse($this->criteriaBuilder->loadCriteria($id, true));
    }

    /**
     * @Route(
     *     "/api/frosh/export/{id}/trigger",
     *     name="api.frosh.export.trigger",
     *     methods={"GET"}
     * )
     */
    public function triggerExport(string $id): Response
    {
        $this->messageBus->dispatch(new FroshExportMessage($id));

        return new Response($id);
    }

    /**
     * @Route(
     *     "/api/frosh/export/{id}",
     *     name="api.frosh.export",
     *     methods={"POST"}
     * )
     */
    public function export(Request $request, Context $context, string $id): Response
    {
        $criteria = null;
        if (!empty($request->request->all())) {
            $criteria = $this->criteriaBuilder->buildCriteria($id, $request, $context);
        }

        $export = $this->exporter->export($id, $context, $criteria);
        json_decode($export);
        if (json_last_error() === JSON_ERROR_NONE) {
            return new JsonResponse($export, json: true);
        }

        return new Response($export);
    }
}

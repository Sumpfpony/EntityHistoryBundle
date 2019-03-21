<?php

namespace Sumpfpony\EntityHistoryBundle\Controller;

use Sumpfpony\EntityHistoryBundle\Model\BaseLog;
use Sumpfpony\EntityHistoryBundle\Registry\Catalogue;
use Sumpfpony\EntityHistoryBundle\StoreAdapter\StoreAdapterInterface;
use Sumpfpony\EntityHistoryBundle\Util\Dumper;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class HistoryController extends Controller
{

    /**
     * @var StoreAdapterInterface
     */
    private $storeAdapter;

    /**
     * @param StoreAdapterInterface $storeAdapter
     */
    public function setStoreAdapter(StoreAdapterInterface $storeAdapter)
    {
        $this->storeAdapter = $storeAdapter;
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function apiShowAction(Request $request)
    {
        $className = $request->get('className');
        $classId = $request->get('classId');
        $limit = $request->get('limit');
        $offset = $request->get('offset', null);

        $histories = ($className && (int)$classId > 0) ? $this->storeAdapter->getHistories($className, $classId, $limit, $offset) : [];
        $historiesArray = array_map(function (BaseLog $baseLog) {
            return [
                'classId' => $baseLog->getClassId(),
                'className' => $baseLog->getClassName(),
                'user' => $baseLog->getUser(),
                'changeSet' => $baseLog->getChangeSet(),
                'dateTime' => $baseLog->getDateTime()->format('c'),
            ];
        }, $histories);

        return new JsonResponse($historiesArray);
    }

    /**
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showAction(Request $request)
    {
        $className = $request->get('className');
        $classId = $request->get('classId');
        $limit = $request->get('limit');
        $offset = $request->get('offset', null);

        $logs = ($className && (int)$classId > 0) ? $this->storeAdapter->getHistories($className, $classId, $limit, $offset) : [];

        $dumper = new Dumper();

        return $this->render('@EntityHistory/history/entity_history_table.html.twig',
            [
                'logs' => $logs,
                'dumper' => $dumper
            ]);
    }
}

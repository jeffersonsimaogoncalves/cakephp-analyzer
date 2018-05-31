<?php
/**
 * Created by PhpStorm.
 * User: Jefferson Simão Gonçalves
 * Email: gerson.simao.92@gmail.com
 * Date: 31/05/2018
 * Time: 19:34
 */

namespace Analyzer\Routing\Middleware;

use Analyzer\Model\Entity\Visitor;
use Cake\Core\Configure;
use Cake\ORM\TableRegistry;

/**
 * Class AnalyzerMiddleware
 *
 * @author  Jefferson Simão Gonçalves <gerson.simao.92@gmail.com>
 *
 * @package Analyzer\Routing\Middleware
 */
class AnalyzerMiddleware
{
    /**
     * @var \Analyzer\Model\Table\VisitorsTable
     */
    private $Visitors;
    /**
     * @var \Analyzer\Model\Table\RequestsTable
     */
    private $Requests;
    /**
     * @var \Cake\Http\ServerRequest
     */
    private $request;

    public function __construct()
    {
        $this->Visitors = TableRegistry::getTableLocator()->get('Analyzer.Visitors');
        $this->Requests = TableRegistry::getTableLocator()->get('Analyzer.Requests');
    }

    /**
     * Serve assets if the path matches one.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request  The request.
     * @param \Psr\Http\Message\ResponseInterface      $response The response.
     * @param callable                                 $next     Callback to invoke the next middleware.
     *
     * @return \Psr\Http\Message\ResponseInterface A response
     */
    public function __invoke($request, $response, $next)
    {
        $this->request = $request;

        $this->registerRequest($this->getVisitor());

        return $next($request, $response);
    }

    /**
     * @param \Analyzer\Model\Entity\Visitor $visitor
     *
     * @return bool
     */
    private function registerRequest(Visitor $visitor)
    {
        if ($this->shouldBeIgnored()) {
            return false;
        }
        $data = [
            'visitor_id' => $visitor->get('id'),
            'url' => $this->request->getRequestTarget(),
            'plugin' => $this->request->getParam('plugin'),
            'controller' => $this->request->getParam('controller'),
            'action' => $this->request->getParam('action'),
            'ext' => $this->request->getParam('ext'),
            'prefix' => $this->request->getParam('prefix'),
            'pass' => $this->request->getParam('pass'),
            'query' => $this->request->getQueryParams()
        ];

        $entity = $this->Requests->newEntity($data);

        return $this->Requests->save($entity);
    }

    /**
     * @return bool
     */
    private function shouldBeIgnored()
    {
        $list = Configure::read('Analyzer.Ignore');

        $_rule = [
            'plugin' => '*',
            'controller' => '*',
            'action' => '*',
            'prefix' => '*'
        ];

        foreach ($list as $key => $rule) {
            $rule = array_merge($_rule, $rule);

            if ($this->__paramIgnored($rule, 'plugin') &&
                $this->__paramIgnored($rule, 'controller') &&
                $this->__paramIgnored($rule, 'action') &&
                $this->__paramIgnored($rule, 'prefix')) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param $rule
     * @param $key
     *
     * @return bool
     */
    private function __paramIgnored($rule, $key)
    {
        if ($rule[$key] === '*') {
            return true;
        }
        if (is_array($rule[$key])) {
            if (in_array($this->request->getParam($key), $rule[$key])) {
                return true;
            }
        }
        if ($this->request->getParam($key) === $rule[$key]) {
            return true;
        }

        return false;
    }

    /**
     * @return \Analyzer\Model\Entity\Visitor|array|\Cake\Datasource\EntityInterface
     */
    private function getVisitor()
    {
        $clientIp = $this->request->clientIp();
        $query = $this->Visitors->find()->where(['Visitors.client_ip' => $clientIp]);
        $exists = (bool)$query->count();

        if (!$exists) {
            $entity = $this->Visitors->newEntity();
            $entity->client_id = $clientIp;
            $this->Visitors->save($entity);
        }

        return $query->first();
    }
}
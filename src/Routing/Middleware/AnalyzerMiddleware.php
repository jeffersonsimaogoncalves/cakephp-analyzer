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
use Cake\Datasource\ConnectionManager;
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
     * @var \Cake\Http\ServerRequest
     */
    private $request;

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
        if ($this->_tableExists()) {
            $this->request = $request;
            $this->registerRequest($this->getVisitor());
        }

        return $next($request, $response);
    }

    /**
     * @return bool
     */
    protected function _tableExists()
    {
        $db = ConnectionManager::get('default');
        $tables = $db->getSchemaCollection()->listTables();

        if (in_array('analyzer_requests', $tables) && in_array('analyzer_visitors', $tables)) {
            return true;
        }

        return false;
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
            'query' => $this->request->getQuery(),
        ];

        /** @var \Analyzer\Model\Table\RequestsTable $Requests */
        $Requests = TableRegistry::getTableLocator()->get('Analyzer.Requests');
        $entity = $Requests->newEntity($data);

        $Requests->save($entity);
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
            'prefix' => '*',
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
     * @return \Analyzer\Model\Entity\Visitor
     */
    private function getVisitor()
    {
        $clientIp = $this->request->clientIp();
        /** @var \Analyzer\Model\Table\VisitorsTable $Visitors */
        $Visitors = TableRegistry::getTableLocator()->get('Analyzer.Visitors');
        /** @var \Analyzer\Model\Entity\Visitor $visitor */
        $visitor = $Visitors->find()->where(['Visitors.client_ip' => $clientIp])->first();

        if (is_null($visitor)) {
            $visitor = $Visitors->newEntity(['client_ip' => $clientIp]);
            $Visitors->save($visitor);
        }

        return $visitor;
    }
}
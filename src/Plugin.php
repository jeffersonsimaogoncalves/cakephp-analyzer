<?php
/**
 * Created by PhpStorm.
 * User: Jefferson Simão Gonçalves
 * Email: gerson.simao.92@gmail.com
 * Date: 08/06/2018
 * Time: 16:50
 */

namespace Analyzer;

use Analyzer\Routing\Middleware\AnalyzerMiddleware;
use Cake\Core\BasePlugin;

/**
 * Class Plugin
 *
 * @author Jefferson Simão Gonçalves <gerson.simao.92@gmail.com>
 *
 * @package Analyzer
 */
class Plugin extends BasePlugin
{
    protected $routesEnabled = false;
    protected $consoleEnabled = false;

    /**
     * @param \Cake\Http\MiddlewareQueue $middleware
     *
     * @return \Cake\Http\MiddlewareQueue
     */
    public function middleware($middleware)
    {
        $middleware->add(new AnalyzerMiddleware());

        return $middleware;
    }
}
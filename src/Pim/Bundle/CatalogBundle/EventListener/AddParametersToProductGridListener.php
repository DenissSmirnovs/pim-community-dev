<?php

namespace Pim\Bundle\CatalogBundle\EventListener;

use Doctrine\ORM\QueryBuilder;
use Oro\Bundle\DataGridBundle\Datagrid\RequestParameters;
use Oro\Bundle\DataGridBundle\Event\BuildAfter;
use Oro\Bundle\DataGridBundle\Datasource\Orm\OrmDatasource;
use Pim\Bundle\CatalogBundle\Manager\ProductManager;

/**
 * Get parameters from request and bind then to query builder
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AddParametersToProductGridListener
{
    /** @var array */
    protected $paramNames;

    /** @var RequestParameters */
    protected $requestParams;

    /**
     * @var ProductManager
     */
    protected $productManager;

    /**
     * @param array             $paramNames    Parameter name that should be binded to query
     * @param RequestParameters $requestParams Request params
     * @param ProductManager    $productManager
     */
    public function __construct($paramNames, RequestParameters $requestParams, ProductManager $productManager)
    {
        $this->paramNames      = $paramNames;
        $this->requestParams   = $requestParams;
        $this-> productManager = $productManager;
    }

    /**
     * Bound parameters in query builder
    *
     * @param BuildAfter $event
     */
    public function onBuildAfter(BuildAfter $event)
    {
        $datasource = $event->getDatagrid()->getDatasource();
        if ($datasource instanceof OrmDatasource) {
            /** @var QueryBuilder $query */
            $queryBuilder = $datasource->getQueryBuilder();
            $queryParameters = array();
            foreach ($this->paramNames as $paramName) {
                $queryParameters[$paramName]= $this->requestParams->get($paramName, null);
            }
            // TODO : how to avoid this inject
            if (isset($queryParameters['dataLocale'])) {
                $this->productManager->setLocale($queryParameters['dataLocale']);
            }
            if (isset($queryParameters['dataScope'])) {
                $this->productManager->setScope($queryParameters['dataScope']);
                unset($queryParameters['dataScope']);
            }

            $queryBuilder->setParameters($queryParameters);
        }
    }
}

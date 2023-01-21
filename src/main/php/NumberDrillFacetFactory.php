<?php

namespace SUBHH\VuFind\Facets;

use Laminas\ServiceManager\ServiceManager;

final class NumberDrillFacetFactory
{
    public static function newInstance (ServiceManager $sm) : NumberDrillFacet
    {
        return new NumberDrillFacet(
            $sm->get('VuFind\Config\PluginManager'),
            $sm->get('VuFind\Search\Solr\HierarchicalFacetHelper')
        );
    }
}

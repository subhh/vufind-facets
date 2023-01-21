<?php

namespace SUBHH\VuFind\Facets;

use VuFind\Recommend\SideFacets;

final class NumberDrillFacet extends SideFacets
{
    private $numberDrillFacets = [];

    public function setConfig($settings)
    {
        parent::setConfig($settings);
        $config = $this->configLoader->get('facets');
        if (isset($config->SpecialFacets->numberDrillFacets)) {
            $this->numberDrillFacets = $config->SpecialFacets->numberDrillFacets->toArray();
        }
    }

    public function getNumberDrillFacets($oldFacetList, $label)
    {
        array_multisort($oldFacetList, SORT_DESC);
        $minYear = $oldFacetList[count($oldFacetList)-1]['value'];
        $maxYear = $oldFacetList[0]['value'];
        $facetListAssoc = array();
        foreach ($oldFacetList as $oldFacetListItem) {
            $facetListAssoc[$oldFacetListItem['value']] = $oldFacetListItem['count'];
        }
        $newFacetList = array();

        $filters = $this->results->getParams()->getFilterList();
        if (isset($filters[$label])) {
            $lastYearFilter = array_pop($filters[$label]);
            list($filteredMinYear,$filteredMaxYear) = explode(' TO ',str_replace(array('[', ']'), '', $lastYearFilter['value']));
            $displayText = ($filteredMaxYear <= date('Y')) ? $filteredMinYear.'-'.$filteredMaxYear : $filteredMinYear.'-';
            $filteredYearFacet = array('value' => '['.$filteredMinYear.' TO '.$filteredMaxYear.']', 'displayText' => $displayText, 'count' => 1, 'operator' => 'AND', 'isApplied' => true);
            if ($minYear < $filteredMinYear) {
                $minYear = $filteredMinYear;
            }
            if ($maxYear > $filteredMaxYear) {
                $maxYear = $filteredMaxYear;
            }
        }

        foreach (array(100, 10, 1) as $scale) {
            if (floor($minYear/$scale) != floor($maxYear/$scale)) {
                for ($year = $scale*floor($minYear/$scale); $year <= $scale*floor($maxYear/$scale); $year += $scale) {
                    $newCount = 0;
                    for ($y=$year; $y < $year + $scale; $y++) {
                        if (isset($facetListAssoc[$y])) {
                            $newCount += $facetListAssoc[$y];
                        }
                    }
                    if ($newCount > 0) {
                        if ($scale == 1) {
                            $displayText = $year;
                        } else {
                            $displayText = ($year + $scale - 1 <= date('Y')) ? $year.'-'.($year + $scale - 1) : $year.'-';
                        }
                        $newFacetList[] = array('value' => '['.$year.' TO '.($year + $scale - 1).']', 'displayText' => $displayText, 'count' => $newCount, 'operator' => 'AND', 'isApplied' => false);
                    }
                }
                krsort($newFacetList);
                $newFacetList = array_values($newFacetList);
                if (isset($filteredYearFacet)) {
                    array_unshift($newFacetList, $filteredYearFacet);
                }
                return $newFacetList;
            }
        }
        if (isset($filteredYearFacet)) {
            array_unshift($newFacetList, $filteredYearFacet);
        }
        return $newFacetList;
    }

    public function getFacetSet ()
    {
        $facetSet = parent::getFacetSet();
        foreach ($this->numberDrillFacets as $facetName) {
            if (isset($facetSet[$facetName])) {
                $facetSet[$facetName]['list'] = $this->getNumberDrillFacets($facetSet[$facetName]['list'], $facetSet[$facetName]['label']);
            }
        }
        return $facetSet;
    }
}

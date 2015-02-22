<?php

/**
 * Extension for Contao Open Source CMS
 *
 * Copyright (c) 2015 Craffft
 *
 * @package CssSelector
 * @link    https://github.com/craffft/contao-css-selector
 * @license http://www.gnu.org/licenses/lgpl-3.0.html LGPL
 */

namespace Craffft\CSSSelector;

class SelectorHelper
{
    /**
     * @param $varValue
     * @param \DataContainer $dc
     * @return mixed
     */
    public function saveCallback($varValue, \DataContainer $dc)
    {
        if (!$dc->activeRecord) {
            return false;
        }

        $arrClasses = $this->getClassesFromCssIDAsArray(\Input::post('cssID'));

        // Remove all known cssSelector classes from cssID classes
        $arrClasses = array_diff($arrClasses, $this->getAllCssSelectorClasses());

        // Add all selected classes of cssSelector to the classes of cssID
        $arrCssClassesSelectorIds = $this->convertSerializedCssSelectorToArray($varValue);
        $arrClasses = array_merge($arrClasses, $this->getCssSelectorClassesByIds($arrCssClassesSelectorIds));

        $arrClasses = array_unique($arrClasses);

        $this->saveClassesToCssID($arrClasses, $dc);

        return $varValue;
    }

    /**
     * @param string $strValue
     * @return array
     */
    protected function convertSerializedCssSelectorToArray($strValue)
    {
        $arrCssClassesSelectorIds = deserialize($strValue);

        if (!is_array($arrCssClassesSelectorIds)) {
            $arrCssClassesSelectorIds = array();
        }

        return $arrCssClassesSelectorIds;
    }

    /**
     * @param array $arrClasses
     * @param \DataContainer $dc
     */
    protected function saveClassesToCssID(array $arrClasses, \DataContainer $dc)
    {
        $arrPostedCssID = deserialize(\Input::post('cssID'));
        $arrPostedCssID[1] = implode(' ', $arrClasses);
        $arrPostedCssID[1] = str_replace('  ', ' ', $arrPostedCssID[1]);
        $arrPostedCssID[1] = trim($arrPostedCssID[1]);

        \Input::setPost('cssID', serialize($arrPostedCssID));
        $dc->activeRecord->cssID = serialize($arrPostedCssID);

        ContentModel::updateCssIDById($dc->id, $arrPostedCssID);
    }

    /**
     * @param array $arrCssID
     * @return array
     */
    protected function getClassesFromCssIDAsArray(array $arrCssID)
    {
        list($strId, $strClasses) = $arrCssID;

        $arrClasses = $this->convertClassesStringToArray($strClasses);

        return $arrClasses;
    }

    /**
     * @param array $arrIds
     * @return array
     */
    protected function getCssSelectorClassesByIds(array $arrIds)
    {
        if (empty($arrIds)) {
            return array();
        }

        $arrClasses = CssSelectorModel::findCssClassesByIds($arrIds);

        return $this->convertCombinedClassesToSingleClasses($arrClasses);
    }

    /**
     * @return array
     */
    protected function getAllCssSelectorClasses()
    {
        $arrClasses = CssSelectorModel::findAllCssClasses();
        $arrClasses = $this->convertCombinedClassesToSingleClasses($arrClasses);

        return $arrClasses;
    }

    /**
     * @param array $arrClasses
     * @return array
     */
    protected function convertCombinedClassesToSingleClasses(array $arrClasses)
    {
        $arrSingleClasses = array();

        if (is_array($arrClasses)) {
            foreach ($arrClasses as $k => $v) {
                $arrSingleClasses = array_merge($arrSingleClasses, $this->convertClassesStringToArray($v));
            }
        }

        $arrSingleClasses = array_unique($arrSingleClasses);

        return $arrSingleClasses;
    }

    /**
     * @param string $strClasses
     * @return array
     */
    protected function convertClassesStringToArray($strClasses)
    {
        $arrClasses = explode(' ', $strClasses);

        if (empty($arrClasses)) {
            $arrClasses = array();
        }

        return $arrClasses;
    }
}

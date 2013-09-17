<?php
/**
 * @author: Mike Henry
 */

namespace RtfmConvert\Analyzers;


use RtfmConvert\PageStatistics;
use RtfmConvert\ProcessorOperationInterface;

/**
 * Class MixedNestedListAnalyzer
 * Searches for ul > ol and ol > ul which are invalid and which QueryPath
 * corrupts.
 * See http://www.whatwg.org/specs/web-apps/current-work/multipage/tree-construction.html#parsing-main-inbody
 *
 * @package RtfmConvert\Analyzers
 */
class MixedNestedListAnalyzer implements ProcessorOperationInterface {
    protected $emptyElements = array('area', 'base', 'basefont', 'br',
        'col', 'frame', 'hr', 'img', 'input', 'isindex', 'link', 'meta',
        'param'); // HTML 4
    protected $specialElements = array(
        'address', 'applet', 'area', 'article', 'aside', 'base', 'basefont', 'bgsound', 'blockquote', 'body', 'br', 'button', 'caption', 'center', 'col', 'colgroup', 'dd', 'details', 'dir', 'div', 'dl', 'dt', 'embed', 'fieldset', 'figcaption', 'figure', 'footer', 'form', 'frame', 'frameset', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'head', 'header', 'hgroup', 'hr', 'html', 'iframe', 'img', 'input', 'isindex', 'li', 'link', 'listing', 'main', 'marquee', 'menu', 'menuitem', 'meta', 'nav', 'noembed', 'noframes', 'noscript', 'object', 'ol', 'p', 'param', 'plaintext', 'pre', 'script', 'section', 'select', 'source', 'style', 'summary', 'table', 'tbody', 'td', 'template', 'textarea', 'tfoot', 'th', 'thead', 'title', 'tr', 'track', 'ul', 'wbr', 'xmp');
    protected $impliedEndTags = array('dd', 'dt', 'li', 'option', 'optgroup', 'p'); // rp, rt
    protected $closesP = array(
        'address', 'article', 'aside', 'blockquote', 'center', 'details', 'dialog', 'dir', 'div', 'dl', 'fieldset', 'figcaption', 'figure', 'footer', 'header', 'hgroup', 'main', 'menu', 'nav', 'ol', 'p', 'section', 'summary', 'ul',
        'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
        'pre', 'listing',
        'form',
        'li',
        'dd', 'dt',
        'plaintext',
        'table',
        'hr'
    );
    protected $mainEndTags = array(
        'address', 'article', 'aside', 'blockquote', 'button', 'center', 'details', 'dialog', 'dir', 'div', 'dl', 'fieldset', 'figcaption', 'figure', 'footer', 'header', 'hgroup', 'listing', 'main', 'menu', 'nav', 'ol', 'pre', 'section', 'summary', 'ul');
    protected $scopeElementTypes = array(
        'applet', 'caption', 'html', 'table', 'td', 'th', 'marquee', 'object', 'template');
    protected $listItemScopeElementTypes = array('ol', 'ul');
    protected $buttonScopeElementTypes = array('button');
    protected $headings = array('h1', 'h2', 'h3', 'h4', 'h5', 'h6');

    /**
     * @param \RtfmConvert\PageData $pageData
     * @return \RtfmConvert\PageData
     */
    public function process($pageData) {
        $mixedNestedLists = array();
        $html = $pageData->getHtmlString();
        $tags = $this->getTags($html);
        $isInList = false;
        $openElements = array();
        foreach ($tags as $tag) {
            preg_match('#^</?([a-z][a-z0-9]*)\b[^>]*>$#i', $tag, $matches);
            $tagName = strtolower($matches[1]);
            if (!$isInList && ($tagName == 'ul' || $tagName == 'ol'))
                $isInList = true;
            if ($isInList) {
                if ($this->isEndTag($tag)) {
                    if ($this->isMainEndTag($tagName)) {
                        $this->parseMainEndTag($openElements, $tagName);
                        if (count($openElements) == 0)
                            $isInList = false;
                        continue;
                    }
                    if ($tagName == 'form') {
                        if (!$this->hasElementInScope($openElements, $tagName))
                            continue; // parse error; ignore the token
                        $this->generateImpliedEndTags($openElements);
                        // if ($this->getCurrentNode($openElements) != $tagName) // parse error
                        $this->removeNode($openElements, $tagName);
                        continue;
                    }
                    if ($tagName == 'p') {
                        if (!$this->hasElementInButtonScope($openElements, $tagName))
                            array_push($openElements, $tagName); // parse error
                        $this->closePElement($openElements);
                    }
                    if (in_array($tagName, array('li', 'dd', 'dt'))) {
                        if (!$this->hasElementInListItemScope($openElements, $tagName))
                            continue; // parse error; ignore the token
                        $this->generateImpliedEndTags($openElements, $tagName);
                        // if ($this->getCurrentNode($openElements) != $tagName) // parse error
                        $this->popElementsUntil($openElements, $tagName);
                        continue;
                    }
                    if ($this->isHeading($tagName)) {
                        if (!$this->hasElementInScope($tagName, $this->headings))
                            continue; // parse error; ignore the token
                        $this->generateImpliedEndTags($openElements);
                        // if ($this->getCurrentNode($openElements) != $tagName) // parse error
                        $this->popElementsUntil($openElements, $this->headings);
                    }
                    if (in_array($tagName, array("a", "b", "big", "code", "em", "font", "i", "nobr", "s", "small", "strike", "strong", "tt", "u")))
                        $this->runAdoptionAgencyAlgorithm($openElements, $tagName);
                    // "applet", "marquee", "object"
                    if ($tagName = 'br')
                        continue; // parse error

//                    if ($tagName == $this->getCurrentNode($openElements)) {
//                        array_pop($openElements);
//                        continue;
//                    }

                    // Any other end tag
                    for ($i = count($openElements) - 1; $i >= 0; $i--) {
                        $node = $this->getCurrentNode($openElements);
                        if ($node == $tagName) {
                            $this->generateImpliedEndTags($openElements, $tagName);
                            // if ($this->getCurrentNode($openElements) != $tagName) // parse error
                            $this->popElementsUntil($openElements, $tagName);
                            continue;
                        }
                        if ($this->isSpecialElement($node)) {
                            continue; // parse error
                        }
                    }
                    continue;
                }

                // start tag

                // handle ol > ul
                if ($tagName == 'ul' && $this->getCurrentNode($openElements) == 'ol')
                    $mixedNestedLists[] = 'ol > ul';
                if ($tagName == 'ol' && $this->getCurrentNode($openElements) == 'ul')
                    $mixedNestedLists[] = 'ul > ol';


                if ($tagName == 'li')
                    $this->parseLiStartTag($openElements);
                if ($tagName == 'dd' || $tagName == 'dt')
                    $this->parseDdDtStartTag($openElements);

                if ($this->tagClosesPElement($openElements, $tagName))
                    $this->closePElement($openElements);

                if ($this->isHeading($tagName) &&
                    $this->isHeading($this->getCurrentNode($openElements))) {
                    // parse error; pop the current node off the stack of open elements
                    array_pop($openElements);
                }
                if ($tagName == 'form' && in_array('form', $openElements)) {
                    // parse error; ignore the token
                    continue;
                }
                if ($tagName == 'button') {
                    $this->parseButtonStartTag($openElements);
                }
                // formatting
                if ($tagName == 'a') {
                    // ignoring complex rules
                    $this->reconstructActiveFormattingElements($openElements);
                    $this->pushOntoListOfActiveFormattingElements($openElements, $tagName);
                }
                if (in_array($tagName, array('b', 'big', 'code', 'em', 'font', 'i', 's', 'small', 'strike', 'strong', 'tt', 'u'))) {
                    $this->reconstructActiveFormattingElements($openElements);
                    $this->pushOntoListOfActiveFormattingElements($openElements, $tagName);
                }
                // "applet", "marquee", "object"
                if ($tagName == 'nobr') {
                    $this->reconstructActiveFormattingElements($openElements);
                    if (!$this->hasElementInScope($openElements, $tagName)) {
                        // parse error
                        $this->runAdoptionAgencyAlgorithm($openElements, $tagName);
                        $this->reconstructActiveFormattingElements($openElements);
                    }
                    $this->pushOntoListOfActiveFormattingElements($openElements, $tagName);
                }
                // self-closing
                if (in_array($tagName, array("area", "br", "embed", "img", "keygen", "wbr"))) {
                    $this->reconstructActiveFormattingElements($openElements);
                    continue;
                }
                if ($tagName == 'input') {
                    $this->reconstructActiveFormattingElements($openElements);
                    continue;
                }
                if (in_array($tagName, array("menuitem", "param", "source", "track")))
                    continue;
                if ($tagName == 'hr')
                    continue;
                // textarea, iframe, noscript, select, optgroup, option
                // "caption", "col", "colgroup", "frame", "head", "tbody", "td", "tfoot", "th", "thead", "tr"

                // Any other start tag
                $this->reconstructActiveFormattingElements($openElements);
                array_push($openElements, $tagName);
            }
        }

        if (count($mixedNestedLists) > 0) {
            $pageData->addTransformStat('lists: mixed nested',
                count($mixedNestedLists));
            foreach ($mixedNestedLists as $entry)
                $pageData->incrementStat('lists: mixed nested',
                    PageStatistics::ERROR, 1, $entry);
        }

        return $pageData;
    }

    protected function getTags($html) {
        preg_match_all('#</?(?:[a-z][a-z0-9]*)\b[^>]*>#i', $html,
            $matches);
        return $matches[0];
    }

    protected function isEndTag($tag) {
        return strpos($tag, '</') === 0;
    }

    protected function isEmptyElement($tagName) {
        return in_array($tagName, $this->emptyElements);
    }

    protected function isSelfClosing($tag) {
        return strrpos($tag, '/>', -2) !== false;
    }

    protected function getCurrentNode(array $openElements) {
        return end($openElements);
    }

    protected function tagClosesPElement(array $openElements, $tagName) {
        return in_array($tagName, $this->closesP) &&
            $this->hasElementInButtonScope($openElements, 'p');
    }

    protected function closePElement(array &$openElements) {
        $this->generateImpliedEndTags($openElements, 'p');
        if ($this->getCurrentNode($openElements) == 'p')
            array_pop($openElements);
    }

    protected function generateImpliedEndTags(array &$openElements, $omit = null) {
        while ($this->hasImpliedEndTag(
            $this->getCurrentNode($openElements), $omit))
            array_pop($openElements);
    }

    protected function hasImpliedEndTag($tagName, $omit = null) {
        return in_array($tagName, $this->impliedEndTags) && $tagName !== $omit;
    }

    private function hasElementInButtonScope(array $openElements, $targetNode) {
        $scopeElementTypes = array_merge($this->scopeElementTypes,
            $this->listItemScopeElementTypes, $this->buttonScopeElementTypes);
        return $this->hasElementInScopeCore($openElements, $targetNode,
            $scopeElementTypes);
    }

    private function hasElementInListItemScope(array $openElements, $targetNode) {
        $scopeElementTypes = array_merge($this->scopeElementTypes,
            $this->listItemScopeElementTypes);
        return $this->hasElementInScopeCore($openElements, $targetNode,
            $scopeElementTypes);
    }

    private function hasElementInScope(array $openElements, $targetNode) {
        return $this->hasElementInScopeCore($openElements, $targetNode,
            $this->scopeElementTypes);
    }

    private function hasElementInScopeCore(array $openElements, $targetNode,
        $scopeElementTypes) {
        for ($i = count($openElements) - 1; $i >= 0; $i--) {
            $current = $openElements[$i];
            if (is_string($targetNode) && $current === $targetNode)
                return true;
            if (is_array($targetNode) && in_array($current, $targetNode))
                return true;
            if (in_array($current, $scopeElementTypes))
                return false;
        }
        return false;
    }

    protected function isHeading($tagName) {
        return in_array($tagName, $this->headings);
    }

    protected function isSpecialElement($tagName) {
        return in_array($tagName, $this->specialElements);
    }

    protected function parseLiStartTag(array &$openElements) {
        for ($i = count($openElements) - 1; $i >= 0; $i--) {
            $node = $this->getCurrentNode($openElements);
            if ($node == 'li') {
                $this->runListItemSubsteps($openElements, 'li');
                return;
            }
            if ($this->isSpecialElement($node) &&
                !in_array($node, array('address', 'div', 'p')))
                return;
        }
    }

    private function parseDdDtStartTag(array &$openElements) {
        for ($i = count($openElements) - 1; $i >= 0; $i--) {
            $node = $this->getCurrentNode($openElements);
            if ($node == 'dd') {
                $this->runListItemSubsteps($openElements, 'dd');
                return;
            }
            if ($node == 'dt') {
                $this->runListItemSubsteps($openElements, 'dt');
                return;
            }
            if ($this->isSpecialElement($node) &&
                !in_array($node, array('address', 'div', 'p')))
                return;
        }
    }

    protected function runListItemSubsteps(array &$openElements, $elementName) {
        $this->generateImpliedEndTags($openElements, $elementName);
        // parse error
//        if ($this->getCurrentNode($openElements) != $elementName)
//            throw new RtfmException('HTML parse error in MixedNestedListAnalyzer');
        $this->popElementsUntil($openElements, $elementName);
    }

    protected function popElementsUntil(array &$openElements, $elementName) {
        while (true) {
            $popped = array_pop($openElements);
            if (is_string($elementName) && $popped == $elementName)
                return;
            if (is_array($elementName) && in_array($popped, $elementName))
                return;
        }
    }

    protected function parseButtonStartTag(array &$openElements) {
        if ($this->hasElementInButtonScope($openElements, 'button')) {
            // parse error
            $this->generateImpliedEndTags($openElements);
            $this->popElementsUntil($openElements, 'button');
        }
        $this->reconstructActiveFormattingElements($openElements);
    }

    protected function reconstructActiveFormattingElements(array &$openElements) {
        // TODO: implement
    }

    protected function pushOntoListOfActiveFormattingElements($openElements, $tagName) {
        // TODO: implement
    }

    protected function runAdoptionAgencyAlgorithm(array &$openElements, $subject) {
        if ($this->getCurrentNode($openElements) == $subject) {
            $element = $this->getCurrentNode($openElements);
            array_pop($openElements);
            // If element is also in the list of active formatting elements, remove the element from the list.
        }
        // TODO: implement
    }

    protected function isMainEndTag($tagName) {
        return in_array($tagName, $this->mainEndTags);
    }

    protected function parseMainEndTag(array &$openElements, $tagName) {
        if (!$this->hasElementInScope($openElements, $tagName))
            return false; // parse error; ignore the token
        $this->generateImpliedEndTags($openElements);
//        if ($this->getCurrentNode($openElements) != $tagName) // parse error
        $this->popElementsUntil($openElements, $tagName);
    }

    protected function removeNode(array &$openElements, $tagName) {
        array_slice($openElements,
            array_search($tagName, $openElements), 1);
    }
}

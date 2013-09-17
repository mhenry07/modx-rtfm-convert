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
    protected $specialElements = array(
        'address', 'applet', 'area', 'article', 'aside', 'base', 'basefont', 'bgsound', 'blockquote', 'body', 'br', 'button', 'caption', 'center', 'col', 'colgroup', 'dd', 'details', 'dir', 'div', 'dl', 'dt', 'embed', 'fieldset', 'figcaption', 'figure', 'footer', 'form', 'frame', 'frameset', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'head', 'header', 'hgroup', 'hr', 'html', 'iframe', 'img', 'input', 'isindex', 'li', 'link', 'listing', 'main', 'marquee', 'menu', 'menuitem', 'meta', 'nav', 'noembed', 'noframes', 'noscript', 'object', 'ol', 'p', 'param', 'plaintext', 'pre', 'script', 'section', 'select', 'source', 'style', 'summary', 'table', 'tbody', 'td', 'template', 'textarea', 'tfoot', 'th', 'thead', 'title', 'tr', 'track', 'ul', 'wbr', 'xmp');
    protected $impliedEndTags = array(
        'dd', 'dt', 'li', 'option', 'optgroup', 'p'); // rp, rt
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

    /**
     * @param \RtfmConvert\PageData $pageData
     * @return \RtfmConvert\PageData
     */
    public function process($pageData) {
        $tags = $this->getTags($pageData->getHtmlString());
        $openElements = array();
        $mixedNestedLists = array();
        foreach ($tags as $tag) {
            preg_match('#^</?([a-z][a-z0-9]*)\b[^>]*>$#i', $tag, $matches);
            $tagName = strtolower($matches[1]);
            if ($this->isEndTag($tag)) {
                if ($this->isMainEndTag($tagName)) {
                    $this->parseMainEndTag($openElements, $tagName);
                    continue;
                }
                if ($tagName == 'p') {
                    if (!$this->hasElementInButtonScope($openElements, $tagName))
                        array_push($openElements, $tagName); // parse error
                    $this->closePElement($openElements);
                }
                if ($tagName == 'li') {
                    if (!$this->hasElementInListItemScope($openElements, $tagName))
                        continue; // parse error; ignore the token
                    $this->generateImpliedEndTags($openElements, $tagName);
                    // if ($this->getCurrentNode($openElements) != $tagName) // parse error
                    $this->popElementsUntil($openElements, $tagName);
                    continue;
                }

                // Any other end tag
                for ($i = count($openElements) - 1; $i >= 0; $i--) {
                    $node = $openElements[$i];
                    if ($node == $tagName) {
                        $this->generateImpliedEndTags($openElements, $tagName);
                        $this->popElementsUntil($openElements, $tagName);
                        continue;
                    }
                }
                continue;
            }

            $isStartTag = !$this->isEndTag($tag);
            if ($isStartTag) {
                $currentNode = $this->getCurrentNode($openElements);
                if (in_array($currentNode, array('ol', 'ul')) &&
                    $tagName != 'li' && $tagName != $currentNode)
                    $mixedNestedLists[] = "{$currentNode} > {$tagName}";

                if ($this->isTrackedElement($openElements, $tagName)) {
                    if ($tagName == 'li')
                        $this->parseLiStartTag($openElements);

                    if ($this->tagClosesPElement($openElements, $tagName) &&
                        $this->hasElementInButtonScope($openElements, 'p'))
                        $this->closePElement($openElements);

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

                    // Any other start tag
                    array_push($openElements, $tagName);
                }
            }
        }

        if (count($mixedNestedLists) > 0) {
            $pageData->addTransformStat('lists: mixed nested',
                count($mixedNestedLists));
            foreach ($mixedNestedLists as $entry) {
                $statType = in_array($entry, array('ol > ul', 'ul > ol')) ?
                    PageStatistics::ERROR : PageStatistics::WARNING;
                $pageData->incrementStat('lists: mixed nested', $statType, 1,
                    $entry);
            }
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

    protected function isTrackedElement($openElements, $tagName) {
        $isList = in_array($tagName, array('ol', 'ul'));
        $isChildOfList =
            in_array($this->getCurrentNode($openElements), array('ol', 'ul'));
        $isOpenElement = in_array($tagName, $openElements);
        return $isList || $isChildOfList || $isOpenElement;
    }

    protected function hasElementInButtonScope(array $openElements, $targetNode) {
        $scopeElementTypes = array_merge($this->scopeElementTypes,
            $this->listItemScopeElementTypes, $this->buttonScopeElementTypes);
        return $this->hasElementInScopeCore($openElements, $targetNode,
            $scopeElementTypes);
    }

    protected function hasElementInListItemScope(array $openElements, $targetNode) {
        $scopeElementTypes = array_merge($this->scopeElementTypes,
            $this->listItemScopeElementTypes);
        return $this->hasElementInScopeCore($openElements, $targetNode,
            $scopeElementTypes);
    }

    protected function hasElementInScope(array $openElements, $targetNode) {
        return $this->hasElementInScopeCore($openElements, $targetNode,
            $this->scopeElementTypes);
    }

    protected function hasElementInScopeCore(array $openElements, $targetNode,
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

    protected function runListItemSubsteps(array &$openElements, $elementName) {
        $this->generateImpliedEndTags($openElements, $elementName);
        // parse error
//        if ($this->getCurrentNode($openElements) != $elementName)
//            throw new RtfmException('HTML parse error in MixedNestedListAnalyzer');
        $this->popElementsUntil($openElements, $elementName);
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

    protected function popElementsUntil(array &$openElements, $elementName) {
        while (true) {
            $popped = array_pop($openElements);
            if (is_string($elementName) && $popped == $elementName)
                return;
            if (is_array($elementName) && in_array($popped, $elementName))
                return;
        }
    }
}

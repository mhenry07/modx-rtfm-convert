<?php
/**
 * Convert headings with a named anchor into a heading with an id. The name
 * attribute is obsolete for <a> elements.
 * E.g. <h2><a name="identifier"></a>Heading</h2> to
 * <h2 id="identifier">Heading</h2>
 *
 * @author: Mike Henry
 */

namespace RtfmConvert\HtmlTransformers;


use RtfmConvert\PageData;

class NamedAnchorHtmlTransformer extends AbstractHtmlTransformer {
    protected $namedAnchorInHeadingCount = 0;
    protected $namedAnchorExceptionsCount = 0;

    /**
     * @param \RtfmConvert\PageData $pageData
     * @return \QueryPath\DOMQuery
     */
    public function transform(PageData $pageData) {
        $this->resetStats();
        $qp = $pageData->getHtmlQuery();
        $qp->find('h1, h2, h3, h4, h5, h6')->has('a[name]')->each(
            function ($index, $item) {
                /** @var \DOMNode $item */
                $anchor = qp($item)->firstChild();
                if (!$anchor->is('a[name]'))
                    return;
                $this->namedAnchorInHeadingCount++;
                $anchorNode = $anchor->get(0);
                $name = $anchor->attr('name');
                $target = $anchor->parent();
                if ($anchorNode->hasChildNodes())
                    $target = $anchor;
                // skip if name does not match id of anchor or target
                if ($anchor->hasAttr('id') && $anchor->attr('id') !== $name ||
                    $target->hasAttr('id') && $target->attr('id') !== $name) {
                    $this->namedAnchorExceptionsCount++;
                    return;
                }
                if ($anchor->attr('id') === $name)
                    $anchor->removeAttr('id');
                $target->attr('id', $name);
                $anchor->removeAttr('name');
                if (!$anchorNode->hasAttributes() && !$anchorNode->hasChildNodes())
                    $anchor->remove();
            }
        );
        $this->generateStatistics(new PageData($qp, $pageData->getStats()));

        return $qp;
    }

    protected function generateStatistics(PageData $pageData) {
        $this->namedAnchorInHeadingCount -= $this->namedAnchorExceptionsCount;
        $pageData->addCountStat('named anchors: headings',
            $this->namedAnchorInHeadingCount, true);
        $pageData->addCountStat('named anchors: heading exceptions',
            $this->namedAnchorExceptionsCount, false, true);

        $otherNamedAnchors = $pageData->getHtmlQuery()->find('a[name]')->count() -
            $this->namedAnchorExceptionsCount;
        $pageData->addCountStat('named anchors: others', $otherNamedAnchors,
            false, true);
    }

    protected function resetStats() {
        $this->namedAnchorInHeadingCount = 0;
        $this->namedAnchorExceptionsCount = 0;
    }
}

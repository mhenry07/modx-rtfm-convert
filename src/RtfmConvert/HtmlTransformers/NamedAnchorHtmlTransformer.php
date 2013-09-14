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

    /**
     * @param \RtfmConvert\PageData $pageData
     * @return \QueryPath\DOMQuery
     */
    public function transform(PageData $pageData) {
        $this->generateStatistics($pageData);
        $qp = $pageData->getHtmlQuery();
        $matches = $qp->find('h1, h2, h3, h4, h5, h6');
        if ($matches->count() > 0)
            $matches = $matches->has('a[name]:first-child');
        $pageData->addQueryStat('named anchors: headings', $matches);
        $pageData->beginTransform($qp);
        $expectedDiff = 0;
        $matches->each(
            function ($index, $item) use ($pageData, &$expectedDiff) {
                /** @var \DOMNode $item */
                $anchor = qp($item)->find('a[name]:first-child');
                $anchorNode = $anchor->get(0);
                $name = $anchor->attr('name');
                $target = $anchor->parent();
                if ($anchorNode->hasChildNodes())
                    $target = $anchor;
                // skip if name does not match id of anchor or target
                if ($anchor->hasAttr('id') && $anchor->attr('id') !== $name ||
                    $target->hasAttr('id') && $target->attr('id') !== $name) {
                    $pageData->incrementStat('named anchors: headings',
                        self::WARNING, 1,
                        'anchor name does not match existing id of heading or anchor');
                    return;
                }
                if ($anchor->attr('id') === $name)
                    $anchor->removeAttr('id');
                $target->attr('id', $name);
                $anchor->removeAttr('name');
                if (!$anchorNode->hasAttributes() && !$anchorNode->hasChildNodes()) {
                    $pageData->incrementStat('named anchors: headings',
                        self::TRANSFORM, 1, 'converted named anchor to heading id');
                    $expectedDiff--;
                    $anchor->remove();
                } else {
                    $pageData->incrementStat('named anchors: headings',
                        self::TRANSFORM, 1,
                        'converted anchor name to ' . $target->tag() . ' id');
                }
            }
        );
        $pageData->checkTransform('named anchors: headings', $qp, $expectedDiff);

        return $qp;
    }

    protected function generateStatistics(PageData $pageData) {
        $qp = $pageData->getHtmlQuery();
        $headingAnchors = $qp->find('h1, h2, h3, h4, h5, h6')
            ->find('a[name]:first-child');
        $otherNamedAnchors = $qp->find('a[name]')->not($headingAnchors->get());
        if ($otherNamedAnchors->count() > 0)
            $pageData->addQueryStat('named anchors: others', $otherNamedAnchors,
                array(self::WARN_IF_FOUND => true,
                    self::WARNING_MESSAGES => 'unhandled named anchor(s)'));
    }
}

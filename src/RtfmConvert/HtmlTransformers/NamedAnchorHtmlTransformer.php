<?php
/**
 * @author: Mike Henry
 */

namespace RtfmConvert\HtmlTransformers;


use QueryPath\DOMQuery;
use RtfmConvert\PageData;

/**
 * Class NamedAnchorHtmlTransformer
 * Convert headings with a named anchor into a heading with an id. The name
 * attribute is obsolete for <a> elements.
 * E.g. <h2><a name="identifier"></a>Heading</h2> to
 * <h2 id="identifier">Heading</h2>
 * Also convert other anchor names to ids.
 *
 * @package RtfmConvert\HtmlTransformers
 */
class NamedAnchorHtmlTransformer extends AbstractHtmlTransformer {

    /**
     * @param \RtfmConvert\PageData $pageData
     * @return \QueryPath\DOMQuery
     */
    public function transform(PageData $pageData) {
        $qp = $pageData->getHtmlQuery();
        $this->transformNamedAnchorHeadings($qp, $pageData);
        $this->transformOtherNamedAnchors($qp, $pageData);

        return $qp;
    }

    protected function transformNamedAnchorHeadings(DOMQuery $qp,
                                                    PageData $pageData) {
        $namedAnchorHeadings = $this->getNamedAnchorHeadings($qp);
        $pageData->addQueryStat('named anchors: headings', $namedAnchorHeadings);
        $pageData->beginTransform($qp);
        $expectedDiff = 0;
        $namedAnchorHeadings->each(
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
                    $target->hasAttr('id') && $target->attr('id') !== $name
                ) {
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
    }

    // note: use multiple steps because if there are no matches,
    // has() throws an error
    protected function getNamedAnchorHeadings(DOMQuery $qp) {
        $namedAnchorHeadings = $qp->find('h1, h2, h3, h4, h5, h6');
        if ($namedAnchorHeadings->count() > 0)
            $namedAnchorHeadings = $namedAnchorHeadings
                ->has('a[name]:first-child');
        return $namedAnchorHeadings;
    }

    protected function transformOtherNamedAnchors(DOMQuery $qp,
                                                  PageData $pageData) {
        $headingAnchors = $qp->find('h1, h2, h3, h4, h5, h6')
            ->find('a[name]:first-child');
        $otherNamedAnchors = $qp->find('a[name]')->not($headingAnchors->get());
        $pageData->addQueryStat('named anchors: others', $otherNamedAnchors);
        $pageData->beginTransform($qp);
        $otherNamedAnchors->each(
            function ($index, $item) use ($pageData, &$expectedDiff) {
                $anchor = qp($item);
                $name = $anchor->attr('name');
                if ($anchor->hasAttr('id') && $anchor->attr('id') !== $name) {
                    $pageData->incrementStat('named anchors: others',
                        self::WARNING, 1,
                        'anchor name does not match existing id of anchor');
                    return;
                }
                $anchor->attr('id', $name)->removeAttr('name');
                $pageData->incrementStat('named anchors: others',
                    self::TRANSFORM, 1, 'converted name to id');
            }
        );
        $pageData->checkTransform('named anchors: others', $qp, 0);
    }
}

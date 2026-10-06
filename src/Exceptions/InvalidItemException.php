<?php

namespace Pharaonic\RSS\Exceptions;

/**
 * Raised when an RSS item is incomplete or receives conflicting values.
 */
class InvalidItemException extends RssException
{
    /**
     * @param int|null $position Zero-based position of the item inside the feed, when known.
     */
    public static function missingTitleOrDescription(?int $position = null): self
    {
        $subject = $position === null ? 'An RSS item' : sprintf('The RSS item at position %d', $position);

        return new self($subject . ' requires at least a title or a description.');
    }

    public static function ambiguousGuidPermalink(): self
    {
        return new self(
            'Item::guid() received a Guid object and an isPermaLink flag. '
            . 'Set the flag on the Guid object with Guid::permalink() instead.'
        );
    }
}

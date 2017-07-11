<?php
namespace Spatie\PartialCache;

trait Cacheable
{
    /**
     * Calculate a unique cache key for the model instance.
     */
    public function getCacheKey()
    {
        return implode(".", [
            get_class($this),
            $this->getKey(),
            $this->updated_at->timestamp
        ]);
    }
}
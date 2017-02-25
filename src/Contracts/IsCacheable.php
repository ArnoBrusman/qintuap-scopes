<?php

namespace Qintuap\Scopes\Contracts;

/**
 *
 * @author Premiums
 */
interface IsCacheable {

    // make a cache key for a method
    function makeCacheKey($method, $parameters);
    // make cache tags for a method
    function makeCacheTags($method, $parameters);
}

<?php

namespace Qintuap\Scopes\Contracts;

/**
 *
 * @author Premiums
 */
interface IsCacheable {

    function makeCacheKey($method, $parameters);
    function makeCacheTags($method, $parameters);
}

<?php

namespace Qintuap\Scopes;

use Exception;
use Qintuap\Repositories\Contracts\Repository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Scope as EloquentScope;
use Qintuap\Repositories\Repos;
use Qintuap\Scopes\Contracts\IsCacheable;

/**
 * Scope class that uses an other callable method as the scope.
 *
 * @author Premiums
 */
class ScopeCall extends Scope {
    
    /**
     * The unique cache key used to cache the results
     */
    var $cache_key = false;
    var $callable;
    var $parameters;
    /**
     * Tags that the results will be cached with
     * @var array
     */
    var $cache_tags = [];
    
    public function __construct($callable, $arguments = [], $cache_tags = [])
    {
        $this->callable = $callable;
        $this->parameters = $arguments;
        $this->tags = $cache_tags;
        $this->cache_key = $this->makeCacheKey($callable,$arguments);
    }
    
    public function apply(Builder $query, Model $model) {
        $parameters = array_merge([], [$query], $this->parameters);
        return call_user_func_array($this->callable,$parameters);
    }
    
    protected function makeCacheKey($callable) {
//        if(is_array($callable) && ($callable[0] instanceof Model || $callable[0] instanceof Repository)) {
        if(is_array($callable) && ($callable[0] instanceof IsCacheable)) {

            $this->cache_key = $callable[0]->makeCacheKey($callable[1], $this->parameters);
            $this->tags = $callable[0]->makeCacheTags($callable[1], $this->parameters);
            
            $classRepo = Repos::make($callable[0]);
            $method = $callable[1];
            if(isset($classRepo->cache_tags) && key_exists($method, $classRepo->cache_tags)) {
                $this->tags = array_merge($this->tags, $classRepo->cache_tags[$method]);
            }
            if(isset($classRepo->scopes_cache) && ($classRepo->scopes_cache === true || in_array($method, $classRepo->scopes_cache))) {
                foreach ($this->parameters as &$parameter) {
                    if($parameter instanceof Model) {
                        $parameter = $parameter->getKey();
                    }
                }
                $this->cache_key = md5(json_encode(array(
                        $this->callable,
                        $this->parameters
                    )));
            } else {
                $this->cache_key = false;
            }
        } elseif(is_string($callable)) {
            throw new Exception('string callable not yet supported');
//            $this->cache_key = md5(json_encode(array(
//                    $this->callable,
//                    $this->parameters
//                )));
        } else {
            \Debugbar::addMessage($callable, 'info');
            throw new Exception('no valid callable given');
        }
        return $this->cache_key;
    }
}

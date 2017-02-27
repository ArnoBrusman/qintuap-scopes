<?php

namespace Qintuap\Scopes;

use Exception;
use Qintuap\Repositories\Contracts\Repository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Scope as EloquentScope;
use Qintuap\Repositories\Repos;
use Qintuap\CacheDecorators\Contracts\CacheableMethods;
use Qintuap\CacheDecorators\Facades\CacheDecorator;

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
    }
    
    public function apply(Builder $query, Model $model) {
        $parameters = array_merge([], [$query], $this->parameters);
        return call_user_func_array($this->callable,$parameters);
    }
    
    public function useCache() {
        $callable = $this->callable;
        if((is_array($callable) && ($callable[0] instanceof HasCacheableMethods))
                || $this->cache_key
                || CacheDecorator::canDecorate($callable[0])) {
            return true;
        } else {
            return false;
        }
    }
    
    public function getCacheKey() {
        $callable = $this->callable;
//        if(is_array($callable) && ($callable[0] instanceof Model || $callable[0] instanceof Repository)) {
        if(is_array($callable) && ($callable[0] instanceof CacheableMethods || CacheDecorator::canDecorate($callable[0])) ) {
            if(CacheDecorator::canDecorate($callable[0])) {
                $cachable = CacheDecorator::decorate();
            } else {
                $cachable = $callable[0];
            }
            $cache_key = $cachable->makeMethodCacheKey($callable[1], $this->parameters);
        } elseif(is_string($callable)) {
            throw new Exception('string callable not yet supported');
//            $this->cache_key = md5(json_encode(array(
//                    $this->callable,
//                    $this->parameters
//                )));
        } elseif(!$this->cache_key) {
            \Debugbar::addMessage($callable, 'info');
            throw new Exception('no valid callable given');
        }
        return $this->cache_key;
    }
    
    public function getCacheTags() {
        
        $callable = $this->callable;
        $tags = $this->cache_tags;
//        if(is_array($callable) && ($callable[0] instanceof Model || $callable[0] instanceof Repository)) {
        if(is_array($callable) && ($callable[0] instanceof CacheableMethods || CacheDecorator::canDecorate($callable[0])) ) {
            if(CacheDecorator::canDecorate($callable[0])) {
                $cachable = CacheDecorator::decorate();
            } else {
                $cachable = $callable[0];
            }
            $tags = array_merge($tags, $cachable->makeMethodCacheTags($callable[1], $this->parameters));
            
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
        \Debugbar::addMessage($tags, 'info');
        return $tags;
    }
    
}

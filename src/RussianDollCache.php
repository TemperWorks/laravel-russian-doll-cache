<?php

namespace Temperworks\RussianDollCache;

use Illuminate\Cache\TaggableStore;
use Illuminate\Contracts\Cache\Factory as CacheManager;
use Illuminate\Contracts\Cache\Repository as Cache;
use Illuminate\Contracts\Config\Repository as Config;
use Illuminate\Contracts\View\Factory as View;
use Spatie\PartialCache\Exceptions\MethodNotSupportedException;

class RussianDollCache
{
    /**
     * @var \Illuminate\Contracts\View\Factory
     */
    protected $view;

    /**
     * @var \Illuminate\Contracts\Cache\Repository
     */
    protected $cache;

    /**
     * @var \Illuminate\Contracts\Cache\Factory
     */
    protected $cacheManager;

    /**
     * @var string
     */
    protected $cacheKey;

    /**
     * @var bool
     */
    protected $cacheIsTaggable;

    /**
     * @var bool
     */
    protected $enabled;

    /**
     * @param \Illuminate\Contracts\View\Factory      $view
     * @param \Illuminate\Contracts\Cache\Repository  $cache
     * @param \Illuminate\Contracts\Cache\Factory     $cacheManager
     * @param \Illuminate\Contracts\Config\Repository $config
     */
    public function __construct(View $view, Cache $cache, CacheManager $cacheManager, Config $config)
    {
        $this->view = $view;
        $this->cache = $cache;
        $this->cacheManager = $cacheManager;

        $this->cacheKey = $config->get('russian-doll-cache.key');
        $this->cacheIsTaggable = is_a($this->cacheManager->driver()->getStore(), TaggableStore::class);
        $this->enabled = $config->get('russian-doll-cache.enabled');
    }

    /**
     * Cache a view. If minutes are null, the view is cached forever.
     *
     * @param array        $data
     * @param string       $view
     * @param array        $mergeData
     * @param int          $minutes
     *
     * @return string
     */
    public function cache($view, array $mergeData = null, $minutes = null)
    {
        if (!$this->enabled) {
            return call_user_func($this->renderView($view, $data, $mergeData));
        }

        $viewKey = $this->getCacheKeyForView($view, $mergeData);

        $tags = [$this->cacheKey];

        if ($this->cacheIsTaggable && $minutes === null) {
            return $this->cache
                ->tags($tags)
                ->rememberForever($viewKey, $this->renderView($view, [], $mergeData));
        }

        if ($this->cacheIsTaggable) {
            return $this->cache
                ->tags($tags)
                ->remember($viewKey, $minutes, $this->renderView($view, [], $mergeData));
        }

        if ($minutes === null) {
            return $this->cache
                ->rememberForever($viewKey, $this->renderView($view, [], $mergeData));
        }

        return $this->cache
            ->remember($viewKey, $minutes, $this->renderView($view, [], $mergeData));
    }

    /**
     * Create a key name for the cached view.
     *
     * @param string $view
     * @param array $mergeData
     *
     * @return string
     */
    public function getCacheKeyForView($view, $mergeData)
    {
        $parts = array_merge(
                [$this->cacheKey, $view],
                $this->getCacheKeysForMergeData($mergeData)
        );

        return implode(':', $parts);
    }

    private function getCacheKeysForMergeData($mergeData)
    {
        return array_map(function($data) {
            if (method_exists($data, "getCacheKey")) {
                return $data->getCacheKey();
            }
            else {
                return md5(json_encode($data));
            }
        }, $mergeData);
    }

    /**
     * Empty all views linked to a tag or the complete partial cache.
     * Note: Only supported by Taggable cache drivers.
     *
     * @param string $tag
     *
     * @throws \Spatie\PartialCache\Exceptions\MethodNotSupportedException
     */
    public function flush($tag = null)
    {
        if (!$this->cacheIsTaggable) {
            throw new MethodNotSupportedException('The cache driver ('.
                get_class($this->cacheManager->driver()).') doesn\'t support the flush method.');
        }

        $tag = $tag ?: $this->cacheKey;
        $this->cache->tags($tag)->flush();
    }

    /**
     * Render a view.
     *
     * @param string $view
     * @param array  $data
     * @param array  $mergeData
     *
     * @return string
     */
    protected function renderView($view, $data, $mergeData)
    {
        $data = $data ?: [];
        $mergeData = $mergeData ?: [];

        return function () use ($view, $data, $mergeData) {
            return $this->view->make($view, $data, $mergeData)->render();
        };
    }
}

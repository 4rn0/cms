<?php

namespace Statamic\Data;

use Statamic\API\Arr;
use Statamic\API\Str;
use Statamic\Contracts\Auth\UserRepository;
use Statamic\Contracts\Assets\AssetRepository;
use Statamic\Contracts\Data\Repositories\EntryRepository;
use Statamic\Contracts\Data\Repositories\GlobalRepository;

class DataRepository
{
    protected $repositories = [
        'entry' => EntryRepository::class,
        'global' => GlobalRepository::class,
        'asset' => AssetRepository::class,
        'user' => UserRepository::class,
    ];

    public function setRepository($handle, $class)
    {
        $this->repositories[$handle] = $class;

        return $this;
    }

    public function find($reference)
    {
        list($handle, $id) = $this->splitReference($reference);

        if (! $handle) {
            return $this->attemptAllRepositories('find', $id);
        }

        if (! $class = Arr::get($this->repositories, $handle)) {
            return null;
        }

        return app($class)->find($id);
    }

    protected function attemptAllRepositories($method, ...$args)
    {
        foreach ($this->repositories as $class) {
            if ($result = app($class)->$method(...$args)) {
                return $result;
            }
        }
    }

    public function splitReference($reference)
    {
        $repo = null;
        $id = $reference;

        if (substr_count($id, '::')) {
            list($repo, $id) = explode('::', $id, 2);
        }

        return [$repo, $id];
    }
}
<?php

namespace Tacone\DataSource;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Collection;
use Tacone\DataSource\Relation\AbstractWrapper;
use Tacone\DataSource\Relation\BelongsToManyWrapper;
use Tacone\DataSource\Relation\BelongsToWrapper;
use Tacone\DataSource\Relation\HasManyWrapper;
use Tacone\DataSource\Relation\HasOneWrapper;

class RelationApi
{
    /**
     * @var Collection
     */
    public static $relations;
//    const BEFORE = 'BEFORE';
//    const AFTER = 'AFTER';

    protected static function registerRelations()
    {
        if (!static::$relations) {
            $supported = [
                HasOne::class => HasOneWrapper::class,
                BelongsTo::class => BelongsToWrapper::class,
                BelongsToMany::class => BelongsToManyWrapper::class,
                HasMany::class => HasManyWrapper::class,
            ];

            static::$relations = Collection::make([]);

            foreach ($supported as $rel => $wrapperName) {
                static::$relations[$rel] = [
                    'className' => $wrapperName,
                ];
            }
        }
    }

    /**
     * @param $relation
     *
     * @return AbstractWrapper
     */
    public static function make($relation)
    {
        if (static::isSupported($relation)) {
            $className = static::$relations[get_class($relation)]['className'];

            return new $className($relation);
        }
        throw new \LogicException('Unsupported relation: '.get_type_class($relation));
    }

    public static function isSupported($relation)
    {
        static::registerRelations();

        return isset(static::$relations[get_class($relation)]);
    }
}

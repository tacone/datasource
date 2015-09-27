<?php

namespace Tacone\DataSource;

/*
 * global namespace functions
 */
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Database\Eloquent\Model;


function to_array($array)
{
    switch (true) {
        case $array instanceof ArrayableInterface:
        case $array instanceof Model:
            return $array->toArray();

        case $array instanceof EloquentBuilder:
        case $array instanceof QueryBuilder:
            return $array->get()->toArray();

        case $array instanceof \ArrayIterator:
        case $array instanceof \ArrayObject:
            return $array->getArrayCopy();

        case is_null($array):
            return [];
    }

    throw new \LogicException(sprintf(
        'to_array() does not supports type: %s%s',
        gettype($array),
        is_object($array) ? ' - ' . get_class($array) : ''
    ));
}

function get_type_class($value)
{
    return gettype($value) . (is_object($value) ? '/' . get_class($value) : '');
}

function is_eloquent_object($object, $throw = false)
{
    $result = $object instanceof Model || $object instanceof Collection;
    if ($throw && !$result) {
        throw new \LogicException(
            'Expected Eloquent Model or Collection, got ' . get_type_class($object)
        );
    }

    return $result;
}

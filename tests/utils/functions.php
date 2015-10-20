<?php

function purgeUnwantedAttributes($array)
{
    unset($array['id']);
    unset($array['created_at']);
    unset($array['updated_at']);
    foreach ($array as $key => $value) {
        if (strpos($key, 'pivot') === 0) {
            unset($array[$key]);
            continue;
        }
        if (is_array($value)) {
            $array[$key] = purgeUnwantedAttributes($value);
        }
    }

    return $array;
}

function assertModelArrayEqual($expected, $actual, $message = '')
{
    $args = func_get_args();
    foreach (range(0, 1) as $a) {
        foreach ($args[$a] as $k => $v) {
            $args[$a][$k] = purgeUnwantedAttributes($v);
        }
    }

    return call_user_func_array(
        'PHPUnit_Framework_Assert::assertEquals',
        $args
    );
}

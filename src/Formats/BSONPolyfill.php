<?php

if ( ! function_exists('bson_decode')) {
    function bson_decode($payload)
    {
        return MongoDB\BSON\toPHP($payload, ['root' => 'array', 'document' => 'array']);
    }
}

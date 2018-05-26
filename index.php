<?php
require_once __DIR__ . '/vendor/autoload.php';

$data = collect(include __DIR__ . '/events.php');

$activities = $data->groupBy('id')->transform(function ($group, $id) {
    $items = collect($group);
    $firstItem = $items->pop();

    return data_set($firstItem, 'total_points', with($items, function ($items) use ($firstItem) {
        return $items->reduce(function ($carry, $item) {
            return $carry + data_get($item, 'bonus_points');
        }, with($firstItem, function ($firstItem) {
            return data_get($firstItem, 'points') + data_get($firstItem, 'bonus_points');
        }));
    }));
});

// removes the ID keys
die($activities->values()->toJson(JSON_PRETTY_PRINT));

// keeps the ID keys
// die($activities->toJson(JSON_PRETTY_PRINT));

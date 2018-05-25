<?php
require_once __DIR__ . '/vendor/autoload.php';

$data = collect(
    include __DIR__ . '/events.php'
);

// group by potentially-duplicated ID
$activities = $data->groupBy('id')->transform(function ($item, $id) {
    // collect all potential duplicate IDs
    $items = collect($item);

    // no duplicates for this guy
    // tally up the `total_points` and add to transformed object
    if ($items->count() <= 1) {
        $singularItem = $items->pop();
        $totalPoints = data_get($singularItem, 'points') + data_get($singularItem, 'bonus_points');
        data_set($singularItem, 'total_points', $totalPoints);

        // done
        return $singularItem;
    }

    // this guy does have duplicates
    $firstItem = $items->pop();

    // I think this first one is what you need...
    // use this if the first item's `bonus_points`
    // should be added to its initial `points` value
    $initialPoints = data_get($firstItem, 'points') + data_get($firstItem, 'bonus_points');

    // OR use this one if the first one's bonus points are excluded
    // $initialPoints = data_get($firstItem, 'points');

    // sum up the bonus points and return a single object
    $bonusPoints = $items->reduce(function ($carry, $item) {
        return $carry + data_get($item, 'bonus_points');
    }, $initialPoints);

    // data_set() works with arrays or objects
    data_set($firstItem, 'total_points', $bonusPoints);

    // return the transformed duplicate(s)
    return $firstItem;
});

// $activities is the return value
die($activities->toJson(JSON_PRETTY_PRINT));

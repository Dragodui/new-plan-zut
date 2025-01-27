<?php

namespace App\Utils;

function getSemesterDates($currentDate = null) {
    $currentDate = $currentDate ?: date('Y-m-d');
    $year = (int)date('Y', strtotime($currentDate));

    $firstSemesterStart = ($year - 1) . "-10-01T23%3A00%3A00.000Z";
    $firstSemesterEnd =  "$year-02-09T23%3A00%3A00.000Z";
    $secondSemesterStart = "$year-02-10T23%3A00%3A00.000Z";
    $secondSemesterEnd = "$year-09-30T23%3A00%3A00.000Z";

    if ($currentDate >= $firstSemesterStart && $currentDate <= $firstSemesterEnd) {
        return (object)[
            'start' => $firstSemesterStart,
            'end' => $firstSemesterEnd,
        ];
    } elseif ($currentDate >= $secondSemesterStart && $currentDate <= $secondSemesterEnd) {
        return (object)[
            'start' => $secondSemesterStart,
            'end' => $secondSemesterEnd,
        ];
    } else {
        return (object)[
            'start' => $secondSemesterEnd,
            'end' => $firstSemesterStart,
        ];
    }
}
<?php
include 'config.php';

function generateBookID($title, $pubDate, $addedDate, $category, $count) {
    $th = strtoupper(substr($title, 0, 2));
    $month = strtoupper(date('M', strtotime($pubDate)));
    $day = date('d', strtotime($addedDate));
    $year = date('Y', strtotime($pubDate));
    return "{$th}{$month}{$day}{$year}-{$category}" . str_pad($count, 5, '0', STR_PAD_LEFT);
}
?>

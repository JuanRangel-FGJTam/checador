<?php

namespace App\ViewModels;

class CalendarEvent
{
    public string $title;
    public string $start;
    public string $end;
    public string $color = "#27ae60"; //green
    public ?string $url;
    public ?string $backgroundColor;

    function __construct($title, $start, $end)
    {
        $this->title = $title;
        $this->start = $start;
        $this->end = $end;
    }
}
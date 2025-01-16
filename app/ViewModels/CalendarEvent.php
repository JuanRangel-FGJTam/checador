<?php

namespace App\ViewModels;

class CalendarEvent
{
    public string $id;
    public string $title;
    public string $start;
    public string $end;
    public string $color = "#27ae60"; //green
    public ?string $url;
    public ?string $backgroundColor;
    public string $type = "RECORD";

    function __construct($id, $title, $start, $end)
    {
        $this->id = $id;
        $this->title = $title;
        $this->start = $start;
        $this->end = $end;
    }
}
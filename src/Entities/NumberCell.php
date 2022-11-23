<?php

namespace Minesweeper\Entities;

class NumberCell extends Cell
{
    public function __construct(private readonly int $count)
    {
    }

    public function getCount()
    {
        return $this->count;
    }
}
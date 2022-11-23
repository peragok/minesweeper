<?php

namespace Minesweeper\Entities;

class ClosedCell extends Cell
{
    public function __construct($isSetFlag)
    {
        $this->isSetFlag = $isSetFlag;
    }
}
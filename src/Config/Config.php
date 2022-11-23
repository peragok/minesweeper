<?php

namespace Minesweeper\Config;

abstract class Config
{
    abstract public function getWidth(): int;

    abstract public function getHeight():int;

    abstract public function getMinesCount(): int;

    public function getOccupancyPercentage(): int|float
    {
        return ($this->getMinesCount() / ($this->getWidth() * $this->getHeight())) * 100;
    }
}
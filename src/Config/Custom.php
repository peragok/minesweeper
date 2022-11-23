<?php

namespace Minesweeper\Config;

class Custom extends Config
{
    public function __construct(private int $width, private int $height, private int $minesCount) {}

    public function getWidth(): int
    {
        return $this->width;
    }

    public function getHeight(): int
    {
        return $this->height;
    }

    public function getMinesCount(): int
    {
        return $this->minesCount;
    }
}
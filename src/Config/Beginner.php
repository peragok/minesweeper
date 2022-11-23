<?php

namespace Minesweeper\Config;

class Beginner extends Config
{

    public function getWidth(): int
    {
        return 8;
    }

    public function getHeight(): int
    {
        return 8;
    }

    public function getMinesCount(): int
    {
        return 10;
    }
}
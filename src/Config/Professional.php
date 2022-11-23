<?php

namespace Minesweeper\Config;

class Professional extends Config
{

    public function getWidth(): int
    {
        return 30;
    }

    public function getHeight(): int
    {
        return 16;
    }

    public function getMinesCount(): int
    {
        return 99;
    }
}
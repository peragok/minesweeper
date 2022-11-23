<?php

namespace Minesweeper\Config;

class Amateur extends Config
{

    public function getWidth(): int
    {
        return 16;
    }

    public function getHeight(): int
    {
        return 16;
    }

    public function getMinesCount(): int
    {
        return 40;
    }
}
<?php

namespace Minesweeper\Entities;

abstract class Cell
{
    public bool $isOpen = false;
    public bool $isSetFlag = false;

    public function setFlag(): bool
    {
        if (!$this->isOpen) {
            $this->isSetFlag = !$this->isSetFlag;
            return true;
        }
        return false;
    }
}
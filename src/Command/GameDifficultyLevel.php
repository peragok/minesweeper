<?php

namespace Minesweeper\Command;

use Minesweeper\Config\Amateur;
use Minesweeper\Config\Beginner;
use Minesweeper\Config\Config;
use Minesweeper\Config\Custom;
use Minesweeper\Config\Professional;

enum GameDifficultyLevel: string
{
    case BEGINNER = 'Начинающий (8*8 - 10 мин)';
    case AMATEUR = 'любитель (16*16 - 40 мин)';
    case PROFESSIONAL = 'профессионал (30*16 - 99 мин)';
    case CUSTOM = 'настроить свою';

    public function getConfig(?int $width = null, ?int $height = null, ?int $minesCount = null): Config
    {
        return match ($this) {
            self::BEGINNER => new Beginner(),
            self::AMATEUR => new Amateur(),
            self::PROFESSIONAL => new Professional(),
            self::CUSTOM => new Custom($width, $height, $minesCount),
        };
    }
}

<?php

namespace Minesweeper\Command;

enum GameCommand: string
{
    case DIG = 'копать';
    case FLAG = 'поставить флаг';
}

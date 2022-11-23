<?php

namespace Minesweeper;

enum GameStatus
{
    case IN_GAME;
    case GAME_LOST;
    case GAME_WON;
}

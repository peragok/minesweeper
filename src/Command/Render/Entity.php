<?php

namespace Minesweeper\Command\Render;

enum Entity
{
    case Number;
    case Mine;
    case Flag;
    case Empty;
    case Closed;

    public function getSymbol($number = '')
    {
        return match($this) {
            Entity::Number => (string) $number,
            Entity::Mine => '<bg=red>X</>',
            Entity::Flag => '<fg=yellow;bg=white>F</>',
            Entity::Empty => '<bg=gray> </>',
            Entity::Closed => '<bg=white>#</>'
        };
    }
}

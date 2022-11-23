<?php

namespace Minesweeper\Command\Render;

use Minesweeper\Command\GameCommand;
use Minesweeper\Entities\Cell;
use Minesweeper\Entities\ClosedCell;
use Minesweeper\Entities\EmptyCell;
use Minesweeper\Entities\MineCell;
use Minesweeper\Entities\NumberCell;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;

class Game
{
    private Table $gameTable;

    public function __construct(private InputInterface $input, private OutputInterface $output, private \Minesweeper\Game $game)
    {
        $this->gameTable = new Table($this->output->section());
    }

    public function render(): void
    {
        $this->renderGame($this->gameTable);
    }

    private function renderGame(Table $table): void
    {
        $table->addRow([
            $this->game->getRemainingFlagCounts()
        ]);
        $table->addRow(new TableSeparator());


        $i = 0;
        foreach ($this->prepareArea() as $row) {
            $table->addRow($row);
            $i++;
            if ($i < count($row)) {
                $table->addRow(new TableSeparator());
            }
        }

        $table->render();
    }

    private function prepareArea()
    {
        $area = $this->game->getArea();
        foreach ($area as &$height) {
            $height = array_map(
                function (Cell $cell) {
                    return match (true) {
                        $cell instanceof ClosedCell && $cell->isSetFlag => Entity::Flag->getSymbol(),
                        $cell instanceof ClosedCell => Entity::Closed->getSymbol(),
                        $cell instanceof MineCell => Entity::Mine->getSymbol(),
                        $cell instanceof MineCell && $cell->isSetFlag => Entity::Flag->getSymbol(),
                        $cell instanceof EmptyCell => Entity::Empty->getSymbol(),
                        $cell instanceof NumberCell => Entity::Number->getSymbol($cell->getCount()),
                    };
                },
                $height
            );
        }
        unset($height);
        return $area;
    }
}
<?php

namespace Minesweeper;

use Minesweeper\Config\Config;
use Minesweeper\Entities\Cell;
use Minesweeper\Entities\ClosedCell;
use Minesweeper\Entities\EmptyCell;
use Minesweeper\Entities\MineCell;
use Minesweeper\Entities\NumberCell;

class Game
{
    const SKIP_AREA_OFFSET = 1;
    private array $area = [];
    private int $remainingFlagCounts;
    private GameStatus $gameStatus = GameStatus::IN_GAME;

    public function __construct(private readonly Config $config)
    {
        $this->remainingFlagCounts = $this->config->getMinesCount();
    }

    public function view(int $width, int $height)
    {

    }

    public function dig(int $width, int $height): GameStatus
    {
        if (count($this->area) === 0) {
            $this->__init($width, $height);
        }

        $cell = $this->area[$height][$width];
        if($cell instanceof MineCell && !$cell->isSetFlag) {
            $this->gameStatus = GameStatus::GAME_LOST;
        }

        $this->openCells($width, $height);

        $this->checkForVictory();

        return $this->gameStatus;
    }

    public function flag(int $width, int $height): GameStatus
    {
        $cell = $this->area[$height][$width];
        if ($cell->setFlag()) {
            $this->remainingFlagCounts += (($cell->isSetFlag) ? -1 : 1);
        }

        if (count($this->area)) {
            $this->checkForVictory();
        }

        return $this->gameStatus;
    }

    public function getGameStatus(): GameStatus
    {
        return $this->gameStatus;
    }

    public function getGameWidth(): int
    {
        return $this->config->getWidth();
    }

    public function getGameHeight(): int
    {
        return $this->config->getHeight();
    }

    public function getArea(): array
    {
        if (count($this->area) === 0) {
            return array_fill(1, $this->config->getHeight(), array_fill(1, $this->config->getWidth(), new ClosedCell(false)));
        }
        $area = $this->area;
        foreach ($area as &$height) {
            $height = array_map(
                function (Cell $cell){
                    if ($this->gameStatus === GameStatus::GAME_WON || ($this->gameStatus === GameStatus::GAME_LOST && $cell instanceof MineCell)) {
                        $cell->isOpen = true;
                    }
                    return match ($this->gameStatus) {
                        GameStatus::IN_GAME => (!$cell->isOpen) ? new ClosedCell($cell->isSetFlag) : $cell,
                        default => $cell
                    };
                },
                $height
            );
        }
        unset($height);
        return $area;
    }

    public function getRemainingFlagCounts(): int
    {
        return $this->remainingFlagCounts;
    }

    public function getCellsAroundCell(int $width, int $height, int $offset): array
    {
        $cells = [];
        foreach (range($height - $offset, $height + $offset) as $newHeight) {
            foreach (range($width - $offset, $width + $offset) as $newWidth) {
                $cells[$newHeight . ':' . $newWidth] = $this->area[$newHeight][$newWidth] ?? null;
            }
        }
        return $cells;
    }

    public function getAreaAroundCell(int $width, int $height, int $offset): array
    {
        $cells = [];
        foreach (range($height - $offset, $height + $offset) as $newHeight) {
            foreach (range($width - $offset, $width + $offset) as $newWidth) {
                $cells[$newHeight][$newWidth] = $this->area[$newHeight][$newWidth] ?? null;
            }
        }
        return $cells;
    }

    private function __init(int $digWidth, int $digHeight): void
    {
        $this->setEmptyCellToArea();
        $this->setMineCellToArea($digWidth, $digHeight);
        $this->setNumberCellToArea();
    }

    private function skipArea(int $width, int $height): array
    {
        $area = [];
        for ($i = $height - self::SKIP_AREA_OFFSET; $i <= $height + self::SKIP_AREA_OFFSET; $i++) {
            for ($j = $width - self::SKIP_AREA_OFFSET; $j <= $width + self::SKIP_AREA_OFFSET; $j++) {
                $area[$i][$j] = true;
            }
        }
        return $area;
    }

    private function setEmptyCellToArea(): void
    {
        for ($height = 1; $height <= $this->config->getHeight(); $height++) {
            for ($width = 1; $width <= $this->config->getWidth(); $width++) {
                $this->area[$height][$width] = new EmptyCell();
            }
        }
    }

    private function setMineCellToArea(int $digWidth, int $digHeight): void
    {
        $skipArea = $this->skipArea($digWidth, $digHeight);
        $i = $this->config->getMinesCount();
        while ($i) {
            $height = rand(1, $this->config->getHeight());
            $width = rand(1, $this->config->getWidth());
            if (empty($skipArea[$height][$width]) && $this->area[$height][$width] instanceof EmptyCell) {
                $this->area[$height][$width] = new MineCell();
                $i--;
            }
        }
    }

    private function setNumberCellToArea(): void
    {
        for ($height = 1; $height <= $this->config->getHeight(); $height++) {
            for ($width = 1; $width <= $this->config->getWidth(); $width++) {
                $cell = $this->area[$height][$width];
                $count = $this->getMinesCountAroundCell($width, $height);
                if ($cell instanceof EmptyCell && $count > 0) {
                    $this->area[$height][$width] = new NumberCell($count);
                }
            }
        }
    }

    private function getMinesCountAroundCell(int $width, int $height): int
    {
        return array_reduce(
            $this->getCellsAroundCell($width, $height, 1),
            function ($carry, $item) {
                return $carry + (($item instanceof MineCell) ? 1 : 0);
            },
            0
        );
    }

    private function checkAllMinesAroundNumberCellIsSetFlag(int $width, int $height): bool
    {
        return $this->getMinesCountAroundCell($width, $height)
            ===
            array_reduce(
                $this->getCellsAroundCell($width, $height, 1),
                function ($carry, $cell) {
                    return $carry + (($cell instanceof MineCell && $cell->isSetFlag) ? 1 : 0);
                },
                0
            );
    }

    private function openCells(int $width, int $height, array &$checkedCells = []): GameStatus
    {
        $area = $this->getAreaAroundCell($width, $height, 1);
        foreach ($area as $heightNumber => $height) {
            foreach ($height as $widthNumber => $cell) {
                if (empty($this->area[$heightNumber][$widthNumber]) || !empty($checkedCells[$heightNumber][$widthNumber])) {
                    continue;
                }
                $checkedCells[$heightNumber][$widthNumber] = true;
                if ($cell->isSetFlag) {
                    continue;
                }
                if ($cell instanceof EmptyCell) {
                    $cell->isOpen = true;
                    $this->openCells($widthNumber, $heightNumber, $checkedCells);
                }
                if ($cell instanceof NumberCell ) {
                    $cell->isOpen = true;
                    if ($this->checkAllMinesAroundNumberCellIsSetFlag($widthNumber, $heightNumber)) {
                        $this->openCells($widthNumber, $heightNumber, $checkedCells);
                    }
                }
            }
        }
        return $this->gameStatus;
    }

    private function checkForVictory(): void
    {
        $countMinesWithFlag = 0;
        $countClosedCells = 0;
        foreach ($this->area as $height) {
            foreach ($height as $cell) {
                if (!$cell instanceof MineCell && $cell->isSetFlag) {
                    return;
                }
                if ($cell instanceof MineCell && $cell->isSetFlag) {
                    $countMinesWithFlag++;
                    continue;
                }
                if (!$cell->isOpen) {
                    $countClosedCells++;
                }
            }
        }

        $this->gameStatus = match (true) {
            $countMinesWithFlag == $this->config->getMinesCount(), $countClosedCells === $this->remainingFlagCounts => GameStatus::GAME_WON,
            default => $this->gameStatus
        };
    }
}
<?php

namespace Minesweeper\Command;

use Minesweeper\Command\Render\Game as GameRender;
use Minesweeper\Config\Beginner;
use Minesweeper\Config\Config;
use Minesweeper\Game;
use Minesweeper\GameStatus;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;

class PlayCommand extends Command
{
    protected static $defaultName = 'play';
    protected static $defaultDescription = 'Play in Minesweeper';
    private Game $game;

    public function execute(InputInterface $input, OutputInterface $output)
    {

        $config = $this->getGameConfig($input, $output);

        $this->game = new Game($config);

        while ($this->game->getGameStatus() === GameStatus::IN_GAME) {
            $this->renderGame($input, $output);
            list($command, $width, $height) = $this->renderCommand($input, $output);
            $output->writeln("Предыдущий действие. Клетка: $width:$height. Действие: {$command->value}");
            match ($command) {
                GameCommand::DIG => $this->game->dig($width, $height),
                GameCommand::FLAG => $this->game->flag($width, $height)
            };
        }

        $this->renderGame($input, $output);

        match ($this->game->getGameStatus()) {
            GameStatus::GAME_WON => $output->writeln('Вы выиграли'),
            GameStatus::GAME_LOST => $output->writeln('Вы проиграли'),
        };

        return Command::SUCCESS;
    }

    protected function configure()
    {
        $this->setDefinition(
            new InputDefinition([
                new InputOption('difficulty', 'd', InputOption::VALUE_OPTIONAL, 'Game difficulty', 'beginner')
            ])
        );
    }

    private function getGameConfig(InputInterface $input, OutputInterface $output): Config
    {
        $helper = $this->getHelper('question');
        $question = new ChoiceQuestion(
            'Выберите уровень сложности',
            [
                GameDifficultyLevel::BEGINNER->value,
                GameDifficultyLevel::AMATEUR->value,
                GameDifficultyLevel::PROFESSIONAL->value,
                GameDifficultyLevel::CUSTOM->value
            ],
            GameDifficultyLevel::BEGINNER->value
        );
        $question->setErrorMessage('Config %s is invalid.');

        $gameDifficultyLevel = GameDifficultyLevel::from($helper->ask($input, $output, $question));
        $output->writeln('Вы выбрали сложность: '.$gameDifficultyLevel->value);

        if ($gameDifficultyLevel === GameDifficultyLevel::CUSTOM) {
            $question = new Question('Укажите ширину арены: ', 8);
            $width = $helper->ask($input, $output, $question);
            $question = new Question('Укажите высоту арены: ', 8);
            $height = $helper->ask($input, $output, $question);
            $question = new Question('Укажите количество мин: ', 10);
            $minesCount = $helper->ask($input, $output, $question);
            return $gameDifficultyLevel->getConfig($width, $height, $minesCount);
        }
        return $gameDifficultyLevel->getConfig();
    }

    private function renderGame(InputInterface $input, OutputInterface $output): void
    {
        $render = new GameRender($input, $output, $this->game);
        $render->render();
    }

    private function renderCommand(InputInterface $input, OutputInterface $output): array
    {
        $helper = $this->getHelper('question');

        $coordinates = $helper->ask($input, $output, $this->getCellSelectionQuestion());
        list($width, $height) = explode(':', $coordinates);

        $command = $helper->ask($input, $output, $this->getActionQuestion());

        return [GameCommand::from($command), $width, $height];
    }

    private function getCellSelectionQuestion(): Question
    {
        $callback = function (string $userInput): array {
            $cellsCoordinates = [];
            foreach (range(1, $this->game->getGameWidth()) as $width) {
                foreach (range(1, $this->game->getGameHeight()) as $height) {
                    $cellsCoordinates[] = $width . ':' . $height;
                }
            }

            $inputPath = preg_replace('%(:|^)[^:]*$%', '$1', $userInput);

            return array_filter($cellsCoordinates, function ($cellCoordinate) use ($inputPath){
                return mb_strripos($cellCoordinate, $inputPath);
            });
        };
        $question = new Question('Выберите клетку (ширина:высота): ');
        $question->setAutocompleterCallback($callback);
        return $question;
    }

    private function getActionQuestion(): Question
    {
        $question = new ChoiceQuestion(
            'Выберите действие. По умолчанию "копать"',
            [GameCommand::DIG->value, GameCommand::FLAG->value],
            GameCommand::DIG->value
        );
        $question->setErrorMessage('Действие %s не существует');
        return $question;
    }
}
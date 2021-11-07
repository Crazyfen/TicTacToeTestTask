<?php

namespace App\Service;

use App\Entity\Game;
use Exception;
use Symfony\Component\HttpFoundation\RequestStack;

class GameService
{
    private RequestStack $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    /**
     * Получаем список неоконченных игр
     * @return array
     */
    public function getUnfinishedGames(): array
    {
        $session = $this->requestStack->getSession();

        return array_column($session->get('games', []), 'gameCode');
    }

    /**
     * @param string|null $gameCode
     * @return Game
     */
    public function getGame(?string $gameCode): Game
    {
        $session = $this->requestStack->getSession();
        $arGames = $session->get('games', []);

        if ($gameIndex = array_search($gameCode, array_column($arGames, 'gameCode'))) {
            $arGame = $arGames[$gameIndex];
            $game = new Game();
            $game->setGameCode($arGame['gameCode']);
            $game->setBoardSize($arGame['boardSize']);
            $game->setBoardState($arGame['boardState']);
        } else {
            $game = Game::generateNewGame();
            $arGames[] = $game->toArray();
            $session->set('games', $arGames);
        }

        return $game;
    }

    public function saveGame(Game $game): void
    {
        $session = $this->requestStack->getSession();
        $arGames = $session->get('games', []);

        if ($gameIndex = array_search($game->getGameCode(), array_column($arGames, 'gameCode'))) {
            $arGames[$gameIndex] = $game->toArray();
            $session->set('games', $arGames);
        }
    }

    /**
     * @param Game $game
     * @param int $row
     * @param int $column
     * @param string $mark
     * @return string|null
     * @throws Exception
     */
    public function setMark(Game $game, int $row, int $column, string $mark = 'X')
    {
        if ($row >= $game->getBoardSize() || $column >= $game->getBoardSize()) {
            throw new Exception('Ваш ход вне диапазона!');
        }

        $board = $game->getBoardState();

        if ($board[$row][$column]) {
            throw new Exception('Ячейка уже занята!');
        }

        $board[$row][$column] = $mark;

        $game->setBoardState($board);
        $this->saveGame($game);
        $boardSize = $game->getBoardSize();
        $AIMoves = [];

        //Проверка колонок
        $marksCount = $game->checkLines($row);
        if (!is_null($gameEnd = Game::parseMarksSequence($marksCount, $boardSize, $AIMoves))) {
            return $gameEnd;
        }

        //Проверка рядов
        $marksCount = $game->checkLines(null, $column);
        if (!is_null($gameEnd = Game::parseMarksSequence($marksCount, $boardSize, $AIMoves))) {
            return $gameEnd;
        }

        //Проверка главной диагонали
        if ($row === $column) {
            $marksCount = $game->checkDiagonal();
            if (!is_null($gameEnd = Game::parseMarksSequence($marksCount, $boardSize, $AIMoves))) {
                return $gameEnd;
            }
        }

        //Проверка обратной диагонали
        if ($row + $column === ($boardSize - 1)) {
            $marksCount = $game->checkDiagonal(false);
            if (!is_null($gameEnd = Game::parseMarksSequence($marksCount, $boardSize, $AIMoves))) {
                return $gameEnd;
            }
        }

        if ($mark === 'X') {
            if (!empty($AIMoves)) {
                $coords = $AIMoves[rand(0, count($AIMoves) - 1)];
                return $this->setMark($game, $coords[0], $coords[1], 'O');
            } else {
                foreach (array_reverse($board) as $row_key => $row) {
                    foreach (array_reverse($row) as $column_key => $column) {
                        if (is_null($column)) {
                            break 2;
                        }
                    }
                }
                return $this->setMark($game, ($boardSize - 1) - $row_key, ($boardSize - 1) - $column_key, 'O');
            }
        }
        return;
    }
}
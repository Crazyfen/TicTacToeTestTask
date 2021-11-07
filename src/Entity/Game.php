<?php

namespace App\Entity;

use Symfony\Component\Uid\Ulid;

class Game
{
    /**
     * Ulid Идентификатор игры
     * @var string
     */
    private ?string $gameCode = null;

    /**
     * @var int
     */
    private int $boardSize = 3;

    /**
     * Переменая для хранения состояния доски
     * @var array
     */
    private array $boardState;

    /**
     * @return string
     */
    public function getGameCode(): string
    {
        if (is_null($this->gameCode)) {
            $this->gameCode = new Ulid();
        }

        return $this->gameCode;
    }

    /**
     * @param string $gameCode
     * @return $this
     */
    public function setGameCode(string $gameCode): self
    {
        $this->gameCode = $gameCode;

        return $this;
    }

    /**
     * @return array
     */
    public function getBoardState(): array
    {
        return $this->boardState;
    }

    /**
     * @param array $boardState
     * @return $this
     */
    public function setBoardState(array $boardState): self
    {
        $this->boardState = $boardState;

        return $this;
    }

    /**
     * @return int
     */
    public function getBoardSize(): int
    {
        return $this->boardSize;
    }

    /**
     * @param int $boardSize
     */
    public function setBoardSize(int $boardSize): void
    {
        $this->boardSize = $boardSize;
    }

    public static function generateNewGame(): self
    {
        $game = new Game();
        $boardSize = $game->getBoardSize();
        $game->setBoardState(array_fill(0, $boardSize, array_fill(0, $boardSize, null)));

        return $game;
    }

    public function toArray(): array
    {
        return [
            'gameCode' => $this->getGameCode(),
            'boardSize' => $this->getBoardSize(),
            'boardState' => $this->getBoardState(),
        ];
    }

    /**
     * Получить кол-во отметок в строке
     * @param int|null $row
     * @param int|null $column
     * @return array
     */
    public function checkLines(?int $row = null, ?int $column = null)
    {
        if ((is_null($row) && is_null($column)) || ($row && $column)) {
            return [];
        }
        $marksCount = [
            'X' => 0,
            'O' => 0,
            'possibleCoords' => [],
            null => 0,
        ];
        $boardSize = $this->getBoardSize();
        $board = $this->getBoardState();

        for ($index = 0; $index < $boardSize; $index++) {
            if (!is_null($row)) {
                $marksCount[$board[$row][$index]]++;
                if (is_null($board[$row][$index])) {
                    $marksCount['possibleCoords'][] = [$row, $index];
                }
            } else {
                $marksCount[$board[$index][$column]]++;
                if (is_null($board[$index][$column])) {
                    $marksCount['possibleCoords'][] = [$index, $column];
                }
            }
        }

        return $marksCount;
    }

    /**
     * Проверяем диагональ (главную или обратную)
     * @param bool $main
     * @return array
     */
    public function checkDiagonal(bool $main = true)
    {
        $marksCount = [
            'X' => 0,
            'O' => 0,
            'possibleCoords' => [],
            null => 0,
        ];
        $boardSize = $this->getBoardSize();
        $board = $this->getBoardState();

        for ($index = 0; $index < $boardSize; $index++) {
            if ($main) {
                $marksCount[$board[$index][$index]]++;
                if (is_null($board[$index][$index])) {
                    $marksCount['possibleCoords'][] = [$index, $index];
                }
            } else {
                $marksCount[$board[$index][($boardSize - 1) - $index]]++;
                if (is_null($board[$index][($boardSize - 1) - $index])) {
                    $marksCount['possibleCoords'][] = [$index, ($boardSize - 1) - $index];
                }
            }
        }

        return $marksCount;
    }

    /**
     * Проверяем последовательность в ячейках на выигрышную комбинацию и наполняем возможные блокирующие ходы для ответа
     * @param array $marksSequence
     * @param int $boardSize
     * @param array $blockerMoves
     * @return string|null
     */
    public static function parseMarksSequence(array $marksSequence, int $boardSize, array &$blockerMoves): ?string
    {
        if ($marksSequence['X'] === $boardSize) {
            return 'win';
        } else if ($marksSequence['O'] === $boardSize) {
            return 'lose';
        } else if (($nullCount = count($marksSequence['possibleCoords'])) === 1 && ($nullCount + $marksSequence['X'] === $boardSize)) {
            $blockerMoves[] = $marksSequence['possibleCoords'][0];
        }

        return null;
    }
}

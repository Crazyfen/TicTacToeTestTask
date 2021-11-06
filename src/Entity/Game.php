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
}

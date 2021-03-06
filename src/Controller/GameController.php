<?php

namespace App\Controller;

use App\Service\GameService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class GameController extends AbstractController
{
    private GameService $gameService;

    public function __construct(GameService $gameService)
    {
        $this->gameService = $gameService;
    }

    public function index(): Response
    {
        return $this->render('game/index.html.twig', [
            'games' => $this->gameService->getUnfinishedGames() ?? []
        ]);
    }

    public function newGame(?string $game_code): Response
    {
        $game = $this->gameService->getGame($game_code);

        if (is_null($game_code)) {
            return $this->redirectToRoute('play_game', ['game_code' => $game->getGameCode()]);
        }

        return $this->render('game/game_area.html.twig', [
            'game_code' => $game->getGameCode(),
            'board' => $game->getBoardState()
        ]);
    }

    public function setMark(string $game_code, int $row, $column): Response
    {
        $game = $this->gameService->getGame($game_code);
        $winner_message = null;

        try {
            $winner_message = $this->gameService->setMark($game, $row, $column);
        } catch (\Exception $exception) {
            $this->addFlash('error', $exception->getMessage());
        }

        if (!is_null($winner_message)) {
            return $this->redirectToRoute('game_result', ['game_code' => $game->getGameCode(), 'message' => $winner_message === 'win' ? 'Ты победил' : 'Ты проиграл']);
        }

        return $this->redirectToRoute('play_game', ['game_code' => $game->getGameCode()]);
    }

    public function showResult(string $game_code, ?string $message)
    {
        $game = $this->gameService->getGame($game_code);

        return $this->render('game/game_result.html.twig', [
            'game_code' => $game->getGameCode(),
            'board' => $game->getBoardState(),
            'message' => $message
        ]);
    }
}

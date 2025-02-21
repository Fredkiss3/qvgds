<?php
declare(strict_types=1);

namespace QVGDS\Game\Service;

use QVGDS\Game\Domain\Game;
use QVGDS\Game\Domain\GameId;
use QVGDS\Game\Domain\GamesRepository;
use QVGDS\Game\Domain\UnknownGameException;
use QVGDS\Session\Domain\Question\Answer;
use QVGDS\Session\Domain\SessionId;
use QVGDS\Session\Domain\SessionNotFoundException;
use QVGDS\Session\Domain\SessionsRepository;

final class GamesService
{
    public function __construct(private readonly GamesRepository $games, private readonly SessionsRepository $sessions)
    {
    }

    public function start(GameId $id, SessionId $sessionId, string $player): Game
    {
        $session = $this->sessions->get($sessionId);
        if ($session === null) {
            throw new SessionNotFoundException();
        }

        $game = Game::start($id, $player, $session);

        $this->games->save($game);

        return $game;
    }

    public function get(GameId $id): Game
    {
        $game = $this->games->get($id);
        if ($game === null) {
            throw new UnknownGameException();
        }
        return $game;
    }

    public function guess(GameId $id, Answer $answer): bool
    {
        $game = $this->get($id);

        $guess = $game->guess($answer);

        $this->games->save($game);

        return $guess;
    }

    public function currentQuestion(GameId $gameId): string
    {
        $game = $this->get($gameId);
        return $game->currentQuestion();
    }

    /**
     * @return Game[]
     */
    public function list(): array
    {
        return $this->games->list();
    }
}

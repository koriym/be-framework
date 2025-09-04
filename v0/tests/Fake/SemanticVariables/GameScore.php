<?php

declare(strict_types=1);

namespace Be\Framework\SemanticVariables;

use Be\Framework\Attribute\Validate;
use Be\Framework\SemanticTag\HighScore;
use Be\Framework\SemanticTag\PersonalBest;
use DomainException;

final class GameScore
{
    #[Validate]
    public function validateGameScore(int $score): void
    {
        if ($score < 0) {
            throw new DomainException("Game score cannot be negative: {$score}");
        }
        
        if ($score > 1000000) {
            throw new DomainException("Game score cannot exceed 1000000: {$score}");
        }
    }

    #[Validate]
    public function validateHighScore(#[HighScore] int $score): void
    {
        // Base validation first
        $this->validateGameScore($score);
        
        if ($score < 10000) {
            throw new DomainException("High score must be at least 10000: {$score}");
        }
    }

    #[Validate]
    public function validatePersonalBest(#[PersonalBest] int $score): void
    {
        // Base validation first
        $this->validateGameScore($score);
        
        if ($score < 1000) {
            throw new DomainException("Personal best must be at least 1000: {$score}");
        }
    }
}
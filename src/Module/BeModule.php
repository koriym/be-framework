<?php

declare(strict_types=1);

namespace Be\Framework\Module;

use Be\Framework\Becoming;
use Be\Framework\BecomingArguments;
use Be\Framework\BecomingArgumentsInterface;
use Be\Framework\BecomingInterface;
use Be\Framework\SemanticLog\Logger;
use Be\Framework\SemanticLog\LoggerInterface;
use Be\Framework\SemanticVariable\SemanticValidator;
use Be\Framework\SemanticVariable\SemanticValidatorInterface;
use Ray\Di\AbstractModule;
use Ray\Di\Scope;

final class BeModule extends AbstractModule
{
    public function __construct(private string $namespace = 'Be\App\Semantic', AbstractModule|null $module = null)
    {
        parent::__construct($module);
    }

    public function configure(): void
    {
        $this->bind(BecomingArgumentsInterface::class)->to(BecomingArguments::class);
        $this->bind(BecomingInterface::class)->to(Becoming::class);
        $this->bind(LoggerInterface::class)->to(Logger::class)->in(Scope::SINGLETON);
        $this->bind(SemanticValidatorInterface::class)->to(SemanticValidator::class);
        $this->bind('')->annotatedWith('semantic_namespace')->toInstance($this->namespace);
    }
}

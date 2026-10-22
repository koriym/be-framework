<?php

declare(strict_types=1);

namespace Be\Framework\SemanticLog;

use Be\Framework\Exception\SemanticVariableException;
use Be\Framework\SemanticVariable\Errors;
use DomainException;
use PHPUnit\Framework\TestCase;

final class ValidationErrorExtractorTest extends TestCase
{
    public function testThrowSiteAcceptsErrorsWhoseKeysAreNotAList(): void
    {
        $validation = new DomainException('rejected');
        $exception = new SemanticVariableException(new Errors([5 => $validation, 7 => new DomainException('also rejected')]));

        $site = (new ValidationErrorExtractor())->throwSite($exception);

        $this->assertSame(['file' => $validation->getFile(), 'line' => $validation->getLine()], $site);
    }
}

<?php

namespace App\Psalm;

use PhpParser\Node\Expr\FuncCall;
use Psalm\Issue\ForbiddenCode;
use Psalm\IssueBuffer;
use Psalm\Plugin\EventHandler\AfterFunctionCallAnalysisInterface;
use Psalm\Plugin\EventHandler\Event\AfterFunctionCallAnalysisEvent;
use Psalm\Plugin\PluginEntryPointInterface;
use Psalm\Plugin\RegistrationInterface;
use SimpleXMLElement;

class ForbiddenFunctions implements PluginEntryPointInterface, AfterFunctionCallAnalysisInterface
{
    private static array $forbiddenFunctions = [
        'dump',
        'print_r',
        'var_dump',
    ];

    public function __invoke(RegistrationInterface $registration, ?SimpleXMLElement $config = null): void
    {
        $registration->registerHooksFromClass(self::class);
    }

    /**
     * Detecta llamadas a funciones prohibidas.
     */
    public static function afterFunctionCallAnalysis(AfterFunctionCallAnalysisEvent $event): void
    {
        $functionId = $event->getFunctionId();
        $expr = $event->getExpr();  // Obtener el nodo de la expresión

        if (in_array($functionId, self::$forbiddenFunctions, true) && $expr instanceof FuncCall) {
            $location = $event->getStatementsSource()->getCodebase()->getSourceLocationForNode($expr);

            if ($location !== null) {
                IssueBuffer::accepts(
                    new ForbiddenCode("Uso de la función prohibida '$functionId'.", $location),
                    $event->getStatementsSource()->getSuppressedIssues(),
                );
            }
        }
    }
}




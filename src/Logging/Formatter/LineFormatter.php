<?php

declare(strict_types=1);

namespace App\Logging\Formatter;

use JsonException;
use Monolog\Formatter\FormatterInterface;
use Monolog\LogRecord;

use function json_encode;

final class LineFormatter implements FormatterInterface
{
    /**
     * @throws JsonException
     */
    public function formatBatch(array $records): string
    {
        $return = '';
        foreach ($records as $record) {
            $return .= $this->format($record);
        }

        return $return;
    }

    /**
     * @throws JsonException
     */
    public function format(LogRecord $record): string
    {
        try {
            $context = json_encode($record['context'], JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            $context = serialize($record['context']);
        }

        return $record['datetime']->format('c') . ' ' .
            '[' . $record['context']['ip'] . '] ' .
            '[' . $record['context']['userId'] . '] ' .
            $record['channel'] . '.' .
            $record['level_name'] . ' ' .
            $record['message'] . ' ' .
            $context . "\n";
    }
}

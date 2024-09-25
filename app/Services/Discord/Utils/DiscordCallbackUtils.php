<?php

namespace App\Services\Discord\Utils;

use Illuminate\Support\Arr;

trait DiscordCallbackUtils
{
    /**
     * @param string $name
     * @return string
     */
    private function formatCustomId(
        string $commandName,
        string $componentName,
        array $args = []
    ): string {
        if (strpos($commandName, '|') !== false) {
            throw new \Exception('Command name can not contain pipelines.');
        }

        if (strpos($componentName, '|') !== false) {
            throw new \Exception('Component name can not contain pipelines.');
        }

        $args = http_build_query($args);

        return sprintf('%s|%s|%s|%s', $commandName, $componentName, $args, uniqid());
    }

    /**
     * @param array $payload
     * @return array
     */
    private function parseCustomId(
        array $payload,
        string $path = 'data.custom_id'
    ): array {
        $customId = explode(
            '|',
            Arr::get($payload, $path)
        );

        parse_str($customId[2], $args);

        return [
            'command'   => $customId[0],
            'component' => $customId[1],
            'args'      => $args,
            'uid'       => $customId[3],
        ];
    }
}

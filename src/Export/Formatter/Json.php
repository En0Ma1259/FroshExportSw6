<?php declare(strict_types=1);

namespace Frosh\ViewExporter\Export\Formatter;

class Json extends AbstractFormatter
{
    public static function fileExtension(): string
    {
        return 'json';
    }

    public function startFile(): void
    {
        parent::startFile();

        $this->filesystem->put($this->fileName, '[');
    }

    public function endFile(): void
    {
        // TODO: Remove last ',' and empty element '{}'
        $this->filesystem->put($this->fileName, '{}]');
    }

    public function writeItem($item): void
    {
        $this->filesystem->put($this->fileName, json_encode($item, JSON_THROW_ON_ERROR) . ',');
    }

    public function __toString(): string
    {
        return json_encode($this->data, JSON_THROW_ON_ERROR);
    }
}

<?php declare(strict_types=1);

namespace Frosh\Exporter\Export\Formatter;

use Frosh\Exporter\Entity\FroshExportEntity;
use Frosh\Exporter\Struct\ExportItem;

class Xml extends AbstractFormatter
{
    public static function fileExtension(): string
    {
        return 'xml';
    }

    public function startFile(FroshExportEntity $exportEntity): void
    {
        parent::startFile($exportEntity);

        $this->getFileSystem()->write($this->getFilename(true), '<?xml version="1.0"?><items>');
    }

    public function endFile(FroshExportEntity $exportEntity): void
    {
        $this->getFileSystem()->write($this->getFilename(true), '</items>');

        parent::endFile($exportEntity);
    }

    protected function writeItem(ExportItem $item, bool $lastItem = false): void
    {
        $array = json_decode(json_encode($item), true);

        $item = new \SimpleXMLElement('<item/>');
        $this->addDataToNode($item, $array);
        $xml = mb_strstr($item->asXML(), '<item>');

        $this->getFileSystem()->write($this->getFilename(true), $xml);
    }

    private function addDataToNode(\SimpleXMLElement $node, array $data): void
    {
        foreach ($data as $key => $value) {
            if (!is_array($value)) {
                $node->addChild($key, htmlspecialchars((string) $value, ENT_XML1));

                continue;
            }

            $child = $node->addChild(is_numeric($key) ? 'value' . $key : $key);
            $this->addDataToNode($child, $value);
        }
    }
}

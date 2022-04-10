<?php declare(strict_types=1);

namespace Frosh\ViewExporter\Entity;

use Shopware\Core\Content\ProductStream\ProductStreamEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\System\Language\LanguageEntity;
use Shopware\Core\System\User\UserEntity;

class FroshExportEntity extends Entity
{
    use EntityIdTrait;

    protected string  $entity;
    protected string  $formatter;
    protected ?string $name;
    protected ?string $criteria;
    protected ?string $userId;
    protected ?string $productStreamId;
    protected ?string $languageId;

    protected array $fields;

    protected ?UserEntity          $user          = null;
    protected ?ProductStreamEntity $productStream = null;
    protected ?LanguageEntity      $language      = null;
    protected Criteria             $realCriteria;

    public function getEntity(): string
    {
        return $this->entity;
    }

    public function getFormatter(): string
    {
        return $this->formatter;
    }

    public function setFormatter(string $formatter): void
    {
        $this->formatter = $formatter;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getCriteria(): ?string
    {
        return $this->criteria;
    }

    public function getLanguageId(): ?string
    {
        return $this->languageId;
    }

    public function getFields(): array
    {
        return $this->fields;
    }

    public function getUserId(): ?string
    {
        return $this->userId;
    }

    public function getUser(): ?UserEntity
    {
        return $this->user;
    }

    public function getProductStreamId(): ?string
    {
        return $this->productStreamId;
    }

    public function setProductStreamId(string $productStreamId): void
    {
        $this->productStreamId = $productStreamId;
    }

    public function getProductStream(): ?ProductStreamEntity
    {
        return $this->productStream;
    }

    public function setProductStream(?ProductStreamEntity $productStream): void
    {
        $this->productStream = $productStream;
    }

    public function getLanguage(): ?LanguageEntity
    {
        return $this->language;
    }

    public function setLanguage(?LanguageEntity $language): void
    {
        $this->language = $language;
    }

    public function getRealCriteria(): Criteria
    {
        if (!isset($this->realCriteria)) {
            $this->realCriteria = unserialize($this->criteria, [Criteria::class]);
        }

        return $this->realCriteria;
    }
}

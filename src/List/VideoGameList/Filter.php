<?php

namespace App\List\VideoGameList;

use App\Model\Entity\Tag;

final class Filter
{
    /**
     * @param Tag[] $tags
     */
    public function __construct(
        private ?string $search = null,
        private array $tags = [],
    ) {
    }

    public function getSearch(): ?string
    {
        return $this->search;
    }

    public function setSearch(?string $search): self
    {
        $this->search = $search;

        return $this;
    }

    /**
     * @return array<int, Tag>
     */
    public function getTags(): array
    {
        return $this->tags;
    }

    /**
     * @param array<int, Tag> $tags
     */
    public function setTags(array $tags): self
    {
        $this->tags = $tags;

        return $this;
    }
}

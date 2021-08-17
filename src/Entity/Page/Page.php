<?php

namespace TwinElements\PageBundle\Entity\Page;

use App\Model\IdTrait;
use App\Model\PositionInterface;
use App\Model\SeoInterface;
use App\Model\SeoTranslatableTrait;
use App\Model\TranslatableAttachmentsTrait;
use App\Model\TranslatableContentTrait;
use App\Model\TranslatableImageAlbumTrait;
use App\Model\TranslatableImageTrait;
use App\Model\TranslatableTitle;
use App\Model\TranslatableTitleSlug;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use App\Model\PositionTrait;
use Knp\DoctrineBehaviors\Contract\Entity\BlameableInterface;
use Knp\DoctrineBehaviors\Contract\Entity\LoggableInterface;
use Knp\DoctrineBehaviors\Contract\Entity\TimestampableInterface;
use Knp\DoctrineBehaviors\Contract\Entity\TranslatableInterface;
use Knp\DoctrineBehaviors\Model\Blameable\BlameableTrait;
use Knp\DoctrineBehaviors\Model\Loggable\LoggableTrait;
use Knp\DoctrineBehaviors\Model\Timestampable\TimestampableTrait;
use Knp\DoctrineBehaviors\Model\Translatable\TranslatableTrait;

/**
 * @ORM\Entity(repositoryClass="TwinElements\PageBundle\Repository\PageRepository")
 * @ORM\Table(name="page")
 */
class Page implements TranslatableInterface, BlameableInterface, TimestampableInterface, LoggableInterface, SeoInterface, PositionInterface
{
    use IdTrait,
        TranslatableTitleSlug,
        PositionTrait,
        TranslatableTrait,
        BlameableTrait,
        TimestampableTrait,
        LoggableTrait,
        SeoTranslatableTrait,
        TranslatableTitle,
        TranslatableImageTrait,
        TranslatableImageAlbumTrait,
        TranslatableAttachmentsTrait,
        TranslatableContentTrait;

    /**
     * @var string|null
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $code;

    /**
     * @var string|null
     * @ORM\Column(name="route", type="string", length=255, nullable=true)
     */
    private $route;

    /**
     * @ORM\ManyToOne(targetEntity="TwinElements\PageBundle\Entity\Page\Page", inversedBy="children")
     */
    private $parent;

    /**
     * @ORM\OneToMany(targetEntity="TwinElements\PageBundle\Entity\Page\Page", mappedBy="parent", fetch="EXTRA_LAZY")
     */
    private $children;

    /**
     * @var Page|null
     * @ORM\ManyToOne(targetEntity="TwinElements\PageBundle\Entity\Page\Page", inversedBy="childrenContents")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $isContentFor;

    /**
     * @ORM\OneToMany(targetEntity="TwinElements\PageBundle\Entity\Page\Page", mappedBy="isContentFor", fetch="EXTRA_LAZY")
     * @ORM\OrderBy(value={"position"="asc"})
     */
    private $childrenContents;

    public function __construct()
    {
        $this->children = new ArrayCollection();
        $this->childrenContents = new ArrayCollection();
    }

    public function __toString()
    {
        if (is_null($this->translate(null, false)->getTitle())) {
            return 'no translation';
        } else {
            return $this->translate(null, false)->getTitle();
        }
    }

    /**
     * @return string|null
     */
    public function getCode(): ?string
    {
        return $this->code;
    }

    /**
     * @param string|null $code
     */
    public function setCode(?string $code): void
    {
        $this->code = $code;
    }

    /**
     * @return string|null
     */
    public function getRoute(): ?string
    {
        return $this->route;
    }

    /**
     * @param string|null $route
     */
    public function setRoute(?string $route): void
    {
        $this->route = $route;
    }

    /**
     * @return mixed
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * @param mixed $parent
     */
    public function setParent($parent): void
    {
        $this->parent = $parent;
    }

    /**
     * @return mixed
     */
    public function getTeaser()
    {
        return $this->translate(null, false)->getTeaser();
    }

    /**
     * @param mixed $teaser
     */
    public function setTeaser($teaser): void
    {
        $this->translate(null, false)->setTeaser($teaser);
    }

    /**
     * @return bool
     */
    public function isEnable(): bool
    {
        return $this->translate(null, false)->isEnable();
    }

    /**
     * @param bool $enable
     */
    public function setEnable(bool $enable): void
    {
        $this->translate(null, false)->setEnable($enable);
    }

    /**
     * @return string|null
     * @deprecated use getImage()
     */
    public function getCover(): ?string
    {
        return $this->translate(null, false)->getImage();
    }

    /**
     * @param string|null $cover
     * @deprecated use setImage()
     */
    public function setCover(?string $cover): void
    {
        $this->translate(null, false)->setImage($cover);
    }

    /**
     * @deprecated use isEnable
     */
    public function getActive()
    {
        return $this->translate(null, false)->isEnable();
    }

    /**
     * @return Collection|Page[]
     */
    public function getChildren(): Collection
    {
        return $this->children;
    }

    public function addChild(Page $child): self
    {
        if (!$this->children->contains($child)) {
            $this->children[] = $child;
            $child->setParent($this);
        }

        return $this;
    }

    public function removeChild(Page $child): self
    {
        if ($this->children->removeElement($child)) {
            // set the owning side to null (unless already changed)
            if ($child->getParent() === $this) {
                $child->setParent(null);
            }
        }

        return $this;
    }

    /**
     * @return Page|null
     */
    public function getIsContentFor(): ?Page
    {
        return $this->isContentFor;
    }

    /**
     * @param Page|null $isContentFor
     */
    public function setIsContentFor(?Page $isContentFor): void
    {
        $this->isContentFor = $isContentFor;
    }

    /**
     * @return Collection|Page[]
     */
    public function getChildrenContents(): Collection
    {
        return $this->childrenContents;
    }

    public function addChildrenContent(Page $childrenContent): self
    {
        if (!$this->childrenContents->contains($childrenContent)) {
            $this->childrenContents[] = $childrenContent;
            $childrenContent->setIsContentFor($this);
        }

        return $this;
    }

    public function removeChildrenContent(Page $childrenContent): self
    {
        if ($this->childrenContents->removeElement($childrenContent)) {
            // set the owning side to null (unless already changed)
            if ($childrenContent->getIsContentFor() === $this) {
                $childrenContent->setIsContentFor(null);
            }
        }

        return $this;
    }
}

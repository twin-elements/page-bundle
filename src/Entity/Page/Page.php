<?php

namespace TwinElements\PageBundle\Entity\Page;

use TwinElements\AdminBundle\Entity\Traits\IdTrait;
use TwinElements\AdminBundle\Entity\Traits\TranslatableAttachmentsTrait;
use TwinElements\AdminBundle\Entity\Traits\TranslatableContentTrait;
use TwinElements\AdminBundle\Entity\Traits\TranslatableImageAlbumTrait;
use TwinElements\AdminBundle\Entity\Traits\TranslatableImageTrait;
use TwinElements\AdminBundle\Entity\Traits\TranslatableTitle;
use TwinElements\AdminBundle\Entity\Traits\TranslatableTitleSlug;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Contract\Entity\BlameableInterface;
use Knp\DoctrineBehaviors\Contract\Entity\LoggableInterface;
use Knp\DoctrineBehaviors\Contract\Entity\TimestampableInterface;
use Knp\DoctrineBehaviors\Contract\Entity\TranslatableInterface;
use Knp\DoctrineBehaviors\Model\Blameable\BlameableTrait;
use Knp\DoctrineBehaviors\Model\Loggable\LoggableTrait;
use Knp\DoctrineBehaviors\Model\Timestampable\TimestampableTrait;
use Knp\DoctrineBehaviors\Model\Translatable\TranslatableTrait;
use TwinElements\SeoBundle\Model\SeoInterface;
use TwinElements\SeoBundle\Model\SeoTranslatableTrait;
use TwinElements\SortableBundle\Entity\PositionInterface;
use TwinElements\SortableBundle\Model\PositionTrait;

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

    /**
     * @var bool
     * @ORM\Column(type="boolean")
     */
    private $isSeparateContent = false;

    /**
     * @var string|null
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $template = null;

    /**
     * @var bool
     * @ORM\Column(type="boolean")
     */
    private bool $excludeFromNav = false;

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
            if ($childrenContent->getIsContentFor() === $this) {
                $childrenContent->setIsContentFor(null);
            }
        }

        return $this;
    }

    /**
     * @return bool
     */
    public function isSeparateContent(): bool
    {
        return $this->isSeparateContent;
    }

    /**
     * @param bool $isSeparateContent
     */
    public function setIsSeparateContent(bool $isSeparateContent): void
    {
        $this->isSeparateContent = $isSeparateContent;
    }

    /**
     * @return string|null
     */
    public function getRedirect(): ?string
    {
        return $this->translate(null, false)->getRedirect();
    }

    /**
     * @param string|null $redirect
     */
    public function setRedirect(?string $redirect): void
    {
        $this->translate(null, false)->setRedirect($redirect);
    }

    /**
     * @return string|null
     */
    public function getTemplate(): ?string
    {
        return $this->template;
    }

    /**
     * @param string|null $template
     */
    public function setTemplate(?string $template): void
    {
        $this->template = $template;
    }

    /**
     * @return bool
     */
    public function isExcludeFromNav(): bool
    {
        return $this->excludeFromNav;
    }

    /**
     * @param bool $excludeFromNav
     */
    public function setExcludeFromNav(bool $excludeFromNav): void
    {
        $this->excludeFromNav = $excludeFromNav;
    }
}

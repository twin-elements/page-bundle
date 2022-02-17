<?php


namespace TwinElements\PageBundle\Entity\Page;

use TwinElements\AdminBundle\Entity\Traits\AttachmentsInterface;
use TwinElements\AdminBundle\Entity\Traits\AttachmentsTrait;
use TwinElements\AdminBundle\Entity\Traits\EnableInterface;
use TwinElements\AdminBundle\Entity\Traits\EnableTrait;
use TwinElements\AdminBundle\Entity\Traits\IdTrait;
use TwinElements\AdminBundle\Entity\Traits\ImageAlbumInterface;
use TwinElements\AdminBundle\Entity\Traits\ImageAlbumTrait;
use TwinElements\AdminBundle\Entity\Traits\ImageInterface;
use TwinElements\AdminBundle\Entity\Traits\ImageTrait;
use TwinElements\SeoBundle\Model\SeoInterface;
use TwinElements\SeoBundle\Model\SeoTrait;
use TwinElements\AdminBundle\Entity\Traits\TitleInterface;
use TwinElements\AdminBundle\Entity\Traits\TitleSlugTrait;
use TwinElements\AdminBundle\Entity\Traits\TitleTrait;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Knp\DoctrineBehaviors\Contract\Entity\TranslationInterface;
use Knp\DoctrineBehaviors\Model\Translatable\TranslationTrait;

/**
 * @ORM\Entity()
 * @ORM\Table(name="page_translation")
 */
class PageTranslation implements TranslationInterface, ImageAlbumInterface, AttachmentsInterface, EnableInterface, SeoInterface, TitleInterface, ImageInterface
{
    use IdTrait,
        TitleSlugTrait,
        TranslationTrait,
        ImageTrait,
        ImageAlbumTrait,
        AttachmentsTrait,
        EnableTrait,
        SeoTrait,
        TitleTrait;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $teaser;

    /**
     * @var string|null
     * @ORM\Column(type="text", nullable=true)
     */
    private $content;

    /**
     * @var string|null
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $redirect;

    /**
     * @return mixed
     */
    public function getTeaser()
    {
        return $this->teaser;
    }

    /**
     * @param mixed $teaser
     */
    public function setTeaser($teaser): void
    {
        $this->teaser = $teaser;
    }

    /**
     * @return string|null
     */
    public function getContent(): ?string
    {
        return $this->content;
    }

    /**
     * @param string|null $content
     */
    public function setContent(?string $content): void
    {
        $this->content = $content;
    }

    /**
     * @return string|null
     * @deprecated use getImage()
     */
    public function getCover(): ?string
    {
        return $this->image;
    }

    /**
     * @param string|null $cover
     * @deprecated use setImage()
     */
    public function setCover(?string $cover): void
    {
        $this->image = $cover;
    }

    /**
     * @return string|null
     */
    public function getRedirect(): ?string
    {
        return $this->redirect;
    }

    /**
     * @param string|null $redirect
     */
    public function setRedirect(?string $redirect): void
    {
        $this->redirect = $redirect;
    }

}

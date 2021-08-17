<?php


namespace TwinElements\PageBundle\Entity\Page;

use App\Model\AttachmentsInterface;
use App\Model\AttachmentsTrait;
use App\Model\EnableInterface;
use App\Model\EnableTrait;
use App\Model\IdTrait;
use App\Model\ImageAlbumInterface;
use App\Model\ImageAlbumTrait;
use App\Model\ImageInterface;
use App\Model\ImageTrait;
use App\Model\SeoInterface;
use App\Model\SeoTrait;
use App\Model\TitleInterface;
use App\Model\TitleSlugTrait;
use App\Model\TitleTrait;
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

}

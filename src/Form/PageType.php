<?php

namespace TwinElements\PageBundle\Form;

use App\Form\Admin\SeoType;
use TwinElements\PageBundle\Entity\Page\Page;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use TwinElements\AdminBundle\Service\AdminTranslator;
use TwinElements\FormExtensions\Type\AttachmentsType;
use TwinElements\FormExtensions\Type\SaveButtonsType;
use TwinElements\FormExtensions\Type\TECollectionType;
use TwinElements\FormExtensions\Type\TEEntityType;
use TwinElements\FormExtensions\Type\TEUploadType;
use TwinElements\FormExtensions\Type\TinymceType;
use TwinElements\FormExtensions\Type\ToggleChoiceType;
use function Doctrine\ORM\QueryBuilder;

class PageType extends AbstractType
{
    /**
     * @var AuthorizationCheckerInterface $authorizationChecker
     */
    private $authorizationChecker;

    /**
     * @var AdminTranslator $translator
     */
    private $translator;


    public function __construct(
        AuthorizationCheckerInterface $authorizationChecker,
        AdminTranslator $translator)
    {
        $this->translator = $translator;
        $this->authorizationChecker = $authorizationChecker;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class, [
                'label' => $this->translator->translate('admin_type.title')
            ])
            ->add('teaser', TextareaType::class, [
                'label' => 'Zajawka',
                'required' => false
            ])
            ->add('content', TinymceType::class)
            ->add('image', TEUploadType::class, [
                'file_type' => 'image',
                'label' => 'Obrazek wyróżniający',
                'required' => false
            ])
            ->add('imageAlbum', TECollectionType::class, [
                'entry_type' => TEUploadType::class,
                'entry_options' => [
                    'file_type' => 'image',
                    'label' => $this->translator->translate('admin_type.image_album.choose_image')
                ],
                'min' => 0,
                'label' => $this->translator->translate('admin_type.image_album.image_album')
            ])
            ->add('attachments', AttachmentsType::class);

        if (!$options['is_content']) {
            $builder->add('parent', TEEntityType::class, [
                'class' => Page::class,
                'required' => false,
                'label' => 'Strona nadrzędna',
                'query_builder' => function (EntityRepository $repository) {
                    $qb = $repository->createQueryBuilder('p')
                        ->join('p.translations', 'pt')
                        ->select('p', 'pt');
                    $qb
                        ->where(
                            $qb->expr()->eq('pt.enable', ':active')
                        )
                        ->setParameter('active', true)
                        ->andWhere(
                            $qb->expr()->isNull('p.isContentFor')
                        );

                    return $qb;
                },
                'choice_attr' => function (Page $choice) use ($options) {
                    if ($choice === $options['data']) {
                        return [
                            'disabled' => true
                        ];
                    }
                    return [];
                },
            ])
                ->add('seo', SeoType::class);
        }

        $builder
            ->add('enable', ToggleChoiceType::class);
        if ($this->authorizationChecker->isGranted('ROLE_SUPER_ADMIN')) {
            if (!$options['is_content']) {
                $builder->add('route', TextType::class, [
                    'required' => false
                ]);
            }

            $builder->add('code', TextType::class, [
                'required' => false
            ]);
        }

        $builder->add('buttons', SaveButtonsType::class);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Page::class,
            'is_content' => false
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'admin_page';
    }
}

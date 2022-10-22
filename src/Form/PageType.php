<?php

namespace TwinElements\PageBundle\Form;

use Symfony\Component\Form\ChoiceList\Loader\CallbackChoiceLoader;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormInterface;
use TwinElements\FormExtensions\Type\ImageAlbumType;
use TwinElements\FormExtensions\Type\TEChooseLinkType;
use TwinElements\PageBundle\Entity\Page\Page;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use TwinElements\Component\AdminTranslator\AdminTranslator;
use TwinElements\FormExtensions\Type\AttachmentsType;
use TwinElements\FormExtensions\Type\SaveButtonsType;
use TwinElements\FormExtensions\Type\TEEntityType;
use TwinElements\FormExtensions\Type\TEUploadType;
use TwinElements\FormExtensions\Type\TinymceType;
use TwinElements\FormExtensions\Type\ToggleChoiceType;
use TwinElements\SeoBundle\Form\Admin\SeoType;
use function Doctrine\ORM\QueryBuilder;

class PageType extends AbstractType
{
    private AuthorizationCheckerInterface $authorizationChecker;

    private AdminTranslator $translator;

    private array $configuration;


    public function __construct(
        array                         $twinElementsConfig,
        AuthorizationCheckerInterface $authorizationChecker,
        AdminTranslator               $translator)
    {
        $this->configuration = $twinElementsConfig;
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
            ->add('imageAlbum', ImageAlbumType::class)
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
                        )
                        ->andWhere(
                            $qb->expr()->eq('p.isSeparateContent', ':false')
                        )
                        ->setParameter('false', false);

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

        $builder->add('enable', ToggleChoiceType::class);

        $templates = [];
        $defaultTemplate = null;

        foreach ($this->configuration['templates'] as $template) {
            $templates[$this->translator->translate($this->configuration['template_translator_prefix'] . '.' . $template['name'])] = $template['path'];
            if ($template['isDefault']) {
                $defaultTemplate = $template['path'];
            }
        }
        $builder->add('template', ChoiceType::class, [
            'choices' => $templates,
            'empty_data' => $defaultTemplate,
            'label' => $this->translator->translate('page.template')
        ]);


        if ($this->authorizationChecker->isGranted('ROLE_SUPER_ADMIN')) {
            if (!$options['is_content']) {
                $builder->add('route', TextType::class, [
                    'required' => false
                ]);
            }


            $builder
                ->add('code', TextType::class, [
                    'required' => false
                ]);
        }

        if (!$options['is_content']) {
            $builder
                ->add('redirect', TEChooseLinkType::class, [
                    'label' => $this->translator->translate('page.redirect'),
                    'help' => $this->translator->translate('page.redirect_help')
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

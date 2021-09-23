<?php

namespace TwinElements\PageBundle\Security;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;
use TwinElements\AdminBundle\Entity\AdminUser;
use TwinElements\AdminBundle\Role\AdminUserRole;
use TwinElements\PageBundle\Entity\Page\Page;
use TwinElements\PageBundle\Role\PageRoles;

class PageVoter extends Voter
{
    const FULL = 'full';
    const EDIT = 'edit';
    const VIEW = 'view';

    /**
     * @var Security $security
     */
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    protected function supports($attribute, $subject)
    {
        if (!in_array($attribute, [self::VIEW, self::EDIT, self::FULL])) {
            return false;
        }

        if (!$subject instanceof Page) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        /**
         * @var AdminUser $user
         */
        $user = $token->getUser();

        if (!$user instanceof AdminUser) {
            return false;
        }

        switch ($attribute) {
            case self::VIEW:
                return $this->canView();
            case self::EDIT:
                return $this->canEdit();
            case self::FULL:
                return $this->isFull();
        }

        throw new \LogicException('This code should not be reached!');
    }

    private function canView()
    {
        if ($this->canEdit()) {
            return true;
        }

        if ($this->security->isGranted(PageRoles::ROLE_PAGE_VIEW)) {
            return true;
        }
    }

    private function canEdit()
    {
        if ($this->isFull()) {
            return true;
        }

        if ($this->security->isGranted(PageRoles::ROLE_PAGE_EDIT)) {
            return true;
        }
    }

    private function isFull()
    {
        if ($this->security->isGranted(AdminUserRole::ROLE_ADMIN)) {
            return true;
        }

        if ($this->security->isGranted(PageRoles::ROLE_PAGE_FULL)) {
            return true;
        }
    }
}

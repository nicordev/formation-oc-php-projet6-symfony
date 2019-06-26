<?php

namespace App\Security;

use App\Entity\Comment;
use App\Entity\Member;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class CommentVoter extends Voter
{
    public const ADD = "add";
    public const EDIT = "edit";
    public const DELETE = "delete";

    public const ACTIONS = [
        self::ADD,
        self::EDIT,
        self::DELETE
    ];

    /**
     * Determines if the attribute and subject are supported by this voter.
     *
     * @param string $attribute An attribute
     * @param mixed $subject The subject to secure, e.g. an object the user wants to access or any other PHP type
     *
     * @return bool True if the attribute and subject are supported, false otherwise
     */
    protected function supports($attribute, $subject)
    {
        if (!in_array($attribute, self::ACTIONS)) {
            return false;
        }

        if ($subject instanceof Comment || $subject === null) {
            return true;
        }

        return false;
    }

    /**
     * Perform a single access check operation on a given attribute, subject and token.
     * It is safe to assume that $attribute and $subject already passed the "supports()" method check.
     *
     * @param string $attribute
     * @param mixed $subject
     * @param TokenInterface $token
     *
     * @return bool
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $user = $token->getUser();

        if (!$user instanceof Member) {
            // the user must be logged in; if not, deny access
            return false;
        }

        if ($attribute === self::ADD) {
            // Every member can add comments
            return true;
        }

        $comment = $subject;

        if (in_array($attribute, [self::EDIT, self::DELETE])){
            return $this->isAuthorized($comment, $user);
        }

        throw new \LogicException('This code should not be reached!');
    }

    /**
     * Check if a Member can add, edit or delete a comment
     *
     * @param Comment $comment
     * @param Member $member
     * @return bool
     */
    private function isAuthorized(Comment $comment, Member $member)
    {
        if (
            $member->isCommentAuthor($comment) ||
            in_array(Member::ROLE_MODERATOR, $member->getRoles())
        ) {
            return true;
        }

        return false;
    }
}
<?php

namespace App\Security\Voter;

use App\Entity\Article;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class ArticleVoter extends Voter
{
    public const string EDIT = 'EDIT';

    public function __construct(private AccessDecisionManagerInterface $adm)
    {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::EDIT], true)
            && $subject instanceof Article;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        assert($subject instanceof Article);

        $user = $token->getUser();

        if (!$user instanceof User) {
            $vote?->addReason('ユーザーが未ログイン状態です。');

            return false;
        }

        return match ($attribute) {
            self::EDIT => $this->canEdit($subject, $user, $token),
            default => throw new \LogicException(),
        };
    }

    private function canEdit(Article $article, User $user, TokenInterface $token): bool
    {
        // 管理者なら許可
        if ($this->adm->decide($token, ['ROLE_ADMIN'])) {
            return true;
        }

        // ブログ記事の作成者なら許可
        if ($article->getCreatedBy() === $user->getUserIdentifier()) {
            return true;
        }

        return false;
    }
}

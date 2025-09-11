<?php

namespace App\Security\Voter;

use App\Entity\Hangout;
use DateTimeImmutable;
use phpDocumentor\Reflection\DocBlock\Tags\Throws;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;
use function PHPUnit\Framework\throwException;

final class HangoutVoter extends Voter
{
    public const EDIT = 'POST_EDIT';

    public const DELETE = 'POST_DELETE';

    public const SUBSCRIBER = 'POST_SUBSCRIBER';

    public const UNSUBSCRIBER = 'POST_UNSUBSCRIBER';

    public const ORGANIZER = 'POST_ORGANIZER';

    public const MODIFY = 'POST_MODIFY';

    public const string CANCEL = 'POST_CANCEL';

    public const SUBSCRIBED = 'POST_SUBSCRIBED';

    public const PUBLISH = 'POST_PUBLISH';



    public function __construct(private Security $security)
    {

    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        // replace with your own logic
        // https://symfony.com/doc/current/security/voters.html
        return in_array($attribute, [self::EDIT, self::DELETE, self::SUBSCRIBER, self::SUBSCRIBED, self::ORGANIZER, self::UNSUBSCRIBER, self::MODIFY, self::CANCEL, self::PUBLISH])
            && $subject instanceof \App\Entity\Hangout;


    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        $dateNow = new DateTimeImmutable();

        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            return false;
        }

        /**
         * @var Hangout $subject
         */

        if($subject->getId() ==39){

        }

//      ...  (check conditions and return true to grant permission) ...
        switch ($attribute) {
            case self::DELETE:
            case self::EDIT:
                return ($user === $subject->getOrganizer() || $this->security->isGranted('ROLE_ADMIN'));

            case self::SUBSCRIBER:
                return ($subject->getState()->getLabel() === ("OPEN") && !$subject->getSubscriberLst()->contains($user));

            case self::UNSUBSCRIBER:
                return (in_array($subject->getState()->getLabel(), ["CLOSED", "OPEN"], true) && $subject->getSubscriberLst()->contains($user));

            case self::SUBSCRIBED:
                //dd($subject->getSubscriberLst()->contains($user));
                return ($subject->getSubscriberLst()->contains($user));

            case self::ORGANIZER:
                return($user===$subject->getOrganizer());

            case self::MODIFY:
                return ($user === $subject->getOrganizer() && $subject->getState()->getLabel()==="CREATE" || $this->security->isGranted('ROLE_ADMIN') && in_array( $subject->getState()->getLabel(), ["CLOSED", "OPEN"], true));

            case self::CANCEL:
                return
                    (($user=== $subject->getOrganizer()
                        && in_array( $subject->getState()->getLabel(), ["CLOSED", "OPEN"], true))
                    || $this->security->isGranted('ROLE_ADMIN')) && in_array( $subject->getState()->getLabel(), ["CLOSED", "OPEN"], true);

            case self::PUBLISH:
                return ($subject->getState()->getLabel() === "CREATE" &&  $subject->getLastSubmitDate()> $dateNow && $user===$subject->getOrganizer());

        }
        return false;
    }
}

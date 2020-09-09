<?php


namespace App\Service\EmailReviewsManager;


use App\Models\Reviews\Review;
use App\Service\EmailReviewsManager\Contracts\EmailReviewsManagerInterface;
use App\Service\EmailReviewsManager\Managers\AbstractManager;
use App\Service\Notification\Notifier;
use App\Service\Notification\Notifiers\EmailNotifier;
use App\Service\Notification\UserNotificationsTrait;
use Carbon\Carbon;

/**
 * Class NormalAll
 * @package App\Service\EmailReviewsManager
 */
class NormalAll extends AbstractManager implements EmailReviewsManagerInterface
{
    use UserNotificationsTrait;
    /**
     * @param Review $review
     * @return mixed|void
     */
    public function ruleСheck(Review $review)
    {
        if (
            $this->range_сheck($review->paper_rate, 3.5)
            &&
            $this->range_сheck($review->support_rate, 3.5)
            &&
            $this->checkWhatWasWrong($review)
            &&
            $this->checkComment($review)
        ) {
            $this->sendEmail($review);
        } else {
            $this->getNext()->setNext(new NormalBadSupport());
            $this->getNext()->ruleСheck($review);
        }
    }

    /**
     * @param Review $review
     * @return mixed|void
     */
    public function sendEmail(Review $review)
    {
        $subject = "Order#" . $review->order->id;
        $site_name = array_get($this->sites, $review->order->user->app_client->name);
        if (!empty($site_name)) {
            (new EmailNotifier(array_merge($this->getConfig($review), [
                'view' => "emails.email_rules.{$site_name}.normal_all",
                'subject' => $subject . " Where did we fall short?",
            ])))->notify();
        }
    }
}

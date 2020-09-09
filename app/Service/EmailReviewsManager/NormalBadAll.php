<?php


namespace App\Service\EmailReviewsManager;


use App\Models\Reviews\Review;
use App\Service\EmailReviewsManager\Contracts\EmailReviewsManagerInterface;
use App\Service\EmailReviewsManager\Managers\AbstractManager;
use App\Service\Notification\Notifiers\EmailNotifier;

/**
 * Class NormalBadAll
 * @package App\Service\EmailReviewsManager
 */
class NormalBadAll extends AbstractManager implements EmailReviewsManagerInterface
{
    /**
     * @param Review $review
     * @return mixed|void
     */
    public function ruleСheck(Review $review)
    {
        if (
            $this->range_no_check($review->paper_rate, 3.5)
            &&
            $this->range_no_check($review->support_rate, 3.5)
            &&
            $this->checkWhatWasWrong($review)
            &&
            $this->checkComment($review)
        ) {
            $this->sendEmail($review);
        }
    }

    public function sendEmail(Review $review)
    {
        $subject = "Order#" . $review->order->id;
        $site_name = array_get($this->sites, $review->order->user->app_client->name);
        if (!empty($site_name)) {
            (new EmailNotifier(array_merge($this->getConfig($review), [
                'view' => "emails.email_rules.{$site_name}.normal_bad_all",
                'subject' => $subject . " Did everything fall apart?",
            ])))->notify();
        }
    }
}

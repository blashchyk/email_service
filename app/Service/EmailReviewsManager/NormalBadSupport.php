<?php


namespace App\Service\EmailReviewsManager;


use App\Models\Reviews\Review;
use App\Service\EmailReviewsManager\Contracts\EmailReviewsManagerInterface;
use App\Service\EmailReviewsManager\Managers\AbstractManager;
use App\Service\Notification\Notifiers\EmailNotifier;

/**
 * Class NormalBadSupport
 * @package App\Service\EmailReviewsManager
 */
class NormalBadSupport extends AbstractManager implements EmailReviewsManagerInterface
{
    /**
     * @param Review $review
     * @return mixed|void
     */
    public function ruleСheck(Review $review)
    {
        if (
            $this->range_no_check($review->support_rate, 3.5)
            &&
            $this->range_сheck($review->paper_rate, 3.5)
            &&
            $this->checkWhatWasWrong($review)
            &&
            $this->checkComment($review)
        ) {
            $this->sendEmail($review);
        } else {
            $this->getNext()->setNext(new NormalBadPaperWithoutRevision());
            $this->getNext()->ruleСheck($review);
        }
    }

    public function sendEmail(Review $review)
    {
        $subject = "Order#" . $review->order->id;
        $site_name = array_get($this->sites, $review->order->user->app_client->name);
        if (!empty($site_name)) {
            (new EmailNotifier(array_merge($this->getConfig($review), [
                'view' => "emails.email_rules.{$site_name}.normal_bad_support",
                'subject' => $subject . " Where did our support slip?",
            ])))->notify();
        }
    }
}

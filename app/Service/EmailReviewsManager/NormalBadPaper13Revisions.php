<?php


namespace App\Service\EmailReviewsManager;


use App\Models\Reviews\Review;
use App\Service\EmailReviewsManager\Contracts\EmailReviewsManagerInterface;
use App\Service\EmailReviewsManager\Managers\AbstractManager;
use App\Service\Notification\Notifiers\EmailNotifier;

/**
 * Class NormalBadPaper13Revisions
 * @package App\Service\EmailReviewsManager
 */
class NormalBadPaper13Revisions extends AbstractManager implements EmailReviewsManagerInterface
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
            $this->range_сheck($review->support_rate, 3.5)
            &&
            $this->checkWhatWasWrong($review)
            &&
            $this->checkComment($review)
            &&
            $this->range_сheck($this->checkingCountRevisions($review), 1, 3)
        ) {
            $this->getNext()->sendEmail($review);
        } else {
            $this->getNext()->setNext(new NormalBadPaper4Revisions());
            $this->getNext()->ruleСheck($review);
        }
    }

    public function sendEmail(Review $review)
    {
        $subject = "Order#" . $review->order->id;
        $site_name = array_get($this->sites, $review->order->user->app_client->name);
        if (!empty($site_name)) {
            (new EmailNotifier(array_merge($this->getConfig($review), [
                'view' => "emails.email_rules.{$site_name}.normal_bad_paper_1_3_revisions",
                'subject' => $subject . " Did our revisions fall short?",
            ])))->notify();
        }
    }
}

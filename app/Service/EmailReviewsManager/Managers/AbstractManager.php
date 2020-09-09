<?php


namespace App\Service\EmailReviewsManager\Managers;


use App\Models\Reviews\Review;
use App\Service\EmailReviewsManager\Contracts\EmailReviewsManagerInterface;
use App\Service\EmailReviewsManager\NormalAll;
use App\Service\EmailReviewsManager\NormalBadAll;
use App\Service\EmailReviewsManager\NormalBadPaper13Revisions;
use App\Service\EmailReviewsManager\NormalBadPaper4Revisions;
use App\Service\EmailReviewsManager\NormalBadPaperWithoutRevision;
use App\Service\EmailReviewsManager\NormalBadSupport;
use Carbon\Carbon;

/**
 * Class AbstractManager
 * @package App\Service\EmailReviewsManager\Managers
 */
abstract class AbstractManager
{
    /**
     * @var
     */
    protected $_next;

    protected $sites = [

        'writepapersforme.online' => 'writepapersforme_online',
    ];

    protected $rules = [
        NormalBadSupport::class => 'NormalBadSupport',
        NormalAll::class => 'NormalAll',
        NormalBadPaper4Revisions::class => 'NormalBadPaper4Revisions',
        NormalBadPaper13Revisions::class => 'NormalBadPaper13Revisions',
        NormalBadPaperWithoutRevision::class => 'NormalBadPaperWithoutRevision',
        NormalBadAll::class => 'NormalBadAll',
    ];

    /**
     * @param Review $review
     * @return mixed
     */
    abstract public function ruleСheck(Review $review);

    /**
     * @param EmailReviewsManagerInterface $next
     */
    public function setNext(EmailReviewsManagerInterface $next)
    {
        $this->_next = $next;
    }

    /**
     * @return mixed
     */
    public function getNext()
    {
        return $this->_next;
    }

    /**
     * @param $num
     * @param mixed ...$range
     * @return bool
     */
    public function range_сheck($num, ...$range): bool
    {
        if (isset($range[1])) {
            return (($num - $range[0]) * ($num - $range[1]) <= 0);
        }
        return ($num >= $range[0]);
    }

    /**
     * @param $num
     * @param mixed ...$range
     * @return bool
     */
    public function range_no_check($num, ...$range): bool
    {
        if (isset($range[1])) {
            return (($num - $range[0]) * ($num - $range[1]) >= 0);
        }
        return ($num <= $range[0]);
    }

    /**
     * @param $review
     * @return bool
     */
    public function checkWhatWasWrong(Review $review): bool
    {
        return empty($review->paper_quality_wrong) && empty($review->support_quality_wrong);
    }
    /**
     * @param $review
     * @return bool
     */
    public function checkComment(Review $review): bool
    {
        return empty($review->comment);
    }

    /**
     * @param $review
     * @return int
     */
    public function checkingCountRevisions(Review $review): int
    {
        return count($review->order->revisions) === 0 ? count($review->order->revisions) : $review->order->revisions[0]->number_revisions;
    }

    public function getData(Review $review): array
    {
        return [
            'site_name' => $review->order->user->app_client->name,
            'time' => Carbon::now()->toTimeString(),
            'order_id' => $review->order->id,
        ];
    }

    protected function getConfig($review)
    {
        return [
            'receiver' => [$review->order->user->email, $review->order->user->full_name],
            'sender' => [
                array_get(get_api_client_config($review->order->user), 'email'),
                array_get(get_api_client_config($review->order->user), 'company_name')
            ],
        ];
    }
}

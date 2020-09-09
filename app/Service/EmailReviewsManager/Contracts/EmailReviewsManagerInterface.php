<?php


namespace App\Service\EmailReviewsManager\Contracts;


use App\Models\Reviews\Review;

/**
 * Interface EmailReviewsManagerInterface
 * @package App\Service\EmailReviewsManager\Contracts
 */
interface EmailReviewsManagerInterface
{
    /**
     * @param Review $review
     * @return mixed
     */
    public function sendEmail(Review $review);
}

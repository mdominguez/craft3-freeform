<?php
/**
 * Freeform for Craft
 *
 * @package       Solspace:Freeform
 * @author        Solspace, Inc.
 * @copyright     Copyright (c) 2008-2019, Solspace, Inc.
 * @link          http://docs.solspace.com/craft/freeform
 * @license       https://solspace.com/software/license-agreement
 */

namespace Solspace\Freeform\Library\DataObjects;

class FreeformStatistics
{
    /** @var int */
    private $submissionCount;

    /** @var int */
    private $spamBlockCount;

    /**
     * FreeformStatistics constructor.
     *
     * @param int $submissionCount
     * @param int $spamBlockCount
     */
    public function __construct($submissionCount, $spamBlockCount)
    {
        $this->submissionCount           = $submissionCount;
        $this->spamBlockCount            = $spamBlockCount;
    }

    /**
     * @return int
     */
    public function getSubmissionCount()
    {
        return $this->submissionCount;
    }

    /**
     * @return int
     */
    public function getSpamBlockCount()
    {
        return $this->spamBlockCount;
    }
}

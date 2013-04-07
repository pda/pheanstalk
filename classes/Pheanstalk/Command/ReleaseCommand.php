<?php

namespace Pheanstalk\Command;
use Pheanstalk\IResponseParser;
use Pheanstalk\IResponse;

use Pheanstalk\Exception\ServerException;

/**
 * The 'release' command.
 * Releases a reserved job back onto the ready queue.
 *
 * @author Paul Annesley
 * @package Pheanstalk
 * @licence http://www.opensource.org/licenses/mit-license.php
 */
class ReleaseCommand extends AbstractCommand implements IResponseParser
{
    private $_job;
    private $_priority;
    private $_delay;

    /**
     * @param object $job Pheanstalk_Job
     * @param int $priority From 0 (most urgent) to 0xFFFFFFFF (least urgent)
     * @param int $delay Seconds to wait before job becomes ready
     */
    public function __construct($job, $priority, $delay)
    {
        $this->_job = $job;
        $this->_priority = $priority;
        $this->_delay = $delay;
    }

    /* (non-phpdoc)
     * @see Pheanstalk_Command::getCommandLine()
     */
    public function getCommandLine()
    {
        return sprintf(
            'release %u %u %u',
            $this->_job->getId(),
            $this->_priority,
            $this->_delay
        );
    }

    /* (non-phpdoc)
     * @see Pheanstalk_ResponseParser::parseRespose()
     */
    public function parseResponse($responseLine, $responseData)
    {
		if ($responseLine == IResponse::RESPONSE_BURIED)
		{
			throw new ServerException(sprintf(
				'Job %s %d: out of memory trying to grow data structure',
                $this->_job->getId(),
                $responseLine
            ));
        }

		if ($responseLine == IResponse::RESPONSE_NOT_FOUND)
		{
			throw new ServerException(sprintf(
				'Job %d %s: does not exist or is not reserved by client',
                $this->_job->getId(),
                $responseLine
            ));
        }

        return $this->_createResponse($responseLine);
    }
}

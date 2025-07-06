<?php
namespace App\Services;

class CircuitBreaker
{
    private $redis;
    private $failureThreshold;
    private $timeout;
    private $lastFailureTime = null;
    private $failureCount = 0;
    private $state = 'closed'; // 'closed', 'open', or 'half-open'

    public function __construct($redis, $failureThreshold = 3, $timeout = 30)
    {
        $this->redis = $redis;
        $this->failureThreshold = $failureThreshold;
        $this->timeout = $timeout;
    }

    public function attempt($callback)
    {
        if ($this->state === 'open') {
            // Check if timeout has elapsed to move to half-open state
            if (time() - $this->lastFailureTime > $this->timeout) {
                $this->state = 'half-open';
            } else {
                throw new \Exception("Circuit breaker is open (Redis unavailable)");
            }
        }

        try {
            $result = $callback();
            
            // On success, reset the circuit if it was half-open
            if ($this->state === 'half-open') {
                $this->reset();
            }
            
            return $result;
        } catch (\Exception $e) {
            $this->recordFailure();
            throw $e;
        }
    }

    private function recordFailure()
    {
        $this->failureCount++;
        $this->lastFailureTime = time();
        
        if ($this->failureCount >= $this->failureThreshold) {
            $this->state = 'open';
        }
    }

    private function reset()
    {
        $this->failureCount = 0;
        $this->lastFailureTime = null;
        $this->state = 'closed';
    }

    public function getState()
    {
        return $this->state;
    }
}
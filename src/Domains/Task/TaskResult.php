<?php namespace SuperV\Platform\Domains\Task;

class TaskResult
{
    protected $success;

    protected $message;

    /**
     * @param mixed $success
     *
     * @return TaskResult
     */
    public function setSuccess($success)
    {
        $this->success = $success;

        return $this;
    }

    /**
     * @return mixed
     */
    public function success()
    {
        return $this->success;
    }

    /**
     * @param mixed $message
     *
     * @return TaskResult
     */
    public function setMessage($message)
    {
        $this->message = $message;

        return $this;
}

    /**
     * @return mixed
     */
    public function getMessage()
    {
        return $this->message;
    }
}